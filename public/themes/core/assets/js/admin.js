"use strict";
/*
 * 1. init: init app
 *  - detect the (a) IE version, (b) mobile devices, (c) css-transition support,
 *  - (d) activate the sidebars events,
 * 3. viewport: get viewport/window size (width and height)
 * 4. _resizeContainer: adapt the Main Content height to the Main Navigation height
 * 5. _evSideBars: Hamburger and Swipe events for Left Sidebar
 * 7. ajaxLoader: load content with ajax
 */
var admin = function() {
	var isMobile = false, supportTransition = true, $body = $('body'), $size = {width: 0, height: 0}, sideLeft = $('body > aside.left'), topBar = $("body > header.topbar"), mainContainer = $('body > main'), footer = $("body > footer"), activeAnimation = false;
	var init = function() {
		// (a)
		if(/MSIE (\d+\.\d+);/.test(navigator.userAgent)) {
			var ieversion = new Number(RegExp.$1);
			if(ieversion == 8) { $body.addClass('isIE8'); }
			else if(ieversion == 9) { $body.addClass('isIE9'); }
		}
		// (b)
		if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
			isMobile = true;
			$body.addClass('is-mobile');
		};
		if(navigator.userAgent.match(/IEMobile\/10\.0/)) {
			var msViewportStyle = document.createElement("style");
			msViewportStyle.appendChild(document.createTextNode("@-ms-viewport{width:auto!important}"));
			document.getElementsByTagName("head")[0].appendChild(msViewportStyle);
		}
		// (c)
		var thisBody = document.body || document.documentElement, thisStyle = thisBody.style;
		supportTransition = thisStyle.transition !== undefined || thisStyle.WebkitTransition !== undefined || thisStyle.MozTransition !== undefined || thisStyle.MsTransition !== undefined || thisStyle.OTransition !== undefined;
		// (d)
		_evSideBars();
	},
	// Window Resize Function
	onWindowResize = function(func, threshold, execAsap) {
		// waits until the user is done resizing the window, then execute
		$(window).espressoResize(function() { repositionElements(); });
	},
	// adjust the template elements based on the window size
	repositionElements = function() {
		$size = _getViewport();
		_resizeContainer();
	},
	_getViewport = function() {
		var e = window, a = 'inner';
		if(!('innerWidth' in window )) {
			a = 'client';
			e = document.documentElement || document.body;
		}
		return { width: e[a + 'Width'], height: e[a + 'Height'] };
	},
	_resizeContainer = function() {
		mainContainer.css({"min-height": $size.height - topBar.outerHeight(true) - footer.outerHeight(true)});
	},
	_evSideBars = function() {
		// (a)
		topBar.children(".hamburger").on("click", function(e) {
			e.preventDefault();
			sideLeft.toggleClass('none');
			$(window).trigger('resize');
			$(this).toggleClass('active');
		});
		// (c)
		$body.click(function(e) {
			if(!e.isDefaultPrevented()) {
				sideLeft.addClass('none');
				topBar.children(".hamburger").removeClass('active');
			}
		});
		if(isMobile) {
			$('html').swipe({
				swipeRight: function(ev, dirxn, dist, durn, fingerCount) {
					sideLeft.removeClass('none');
					topBar.children(".hamburger").addClass('active');
				},
				swipeLeft: function(ev, dirxn, dist, durn, fingerCount) {
					sideLeft.addClass('none');
					topBar.children(".hamburger").removeClass('active');
				}
			});
		}
	},
	ajaxLoader = function(url, element) {
		Core.block(element);
		loadPage(url, element);

		function loadPage(url, element) {
			$.ajax({
				type: "GET",
				cache: false,
				url: url,
				dataType: "html",
				complete: function() { Core.unblock(element); },
				success: function(data) { element.html(data); },
				error: function(xhr, ajaxOptions, thrownError) {
					element.html('<h4>Could not load the requested content.</h4>');
					Core.tempClass(element, 'shake-xy');
				}
			});
		};
	},
	checkSession = function(validate) {
		var session = localStorage.getItem('data');
		if(session) {
			session = JSON.parse(session);
			topBar.find('.username').text(' '+session.name);
			if(validate) {
				Core.block($body);
				API.auth().isLoggedIn({
					success: function() { Core.unblock($body); },
					error: function() { window.location.href = 'auth/logout'; }
				});
			} else { Core.unblock($body); }
		}
		else { window.location.href = 'auth/logout'; }
	},
	fn = function() {
	};

	return {
		init: function() {
			onWindowResize();
			init();
			repositionElements();
			Core.dropdown();
			checkSession(false);

			// dropzone uploader
			var input = 'dz-file';
			$.each($('.dropzone'), function(i, el) {
				var ins, label;
				el.append('<input type="file" name="'+input+'"><span class="btn-group axns"><a class="btn btn-save"><i class="ion ion-checkmark"></i></a><a class="btn btn-red btn-cancel"><i class="ion ion-close-round"></i></a></span><span class="placeholder">Choose a file</span>');
				ins = el.children('input[name="'+input+'"]');
				label = el.children('.placeholder');
				ins.change(function(e) {
					if(e.target.files.length) {
						var reader = new FileReader();
						reader.onload = function (e) {
							label.html('<img src="'+e.target.result+'">');
						}
						reader.readAsDataURL(ins[0].files[0]);
						el.addClass('valid');
					} else { el.removeClass('valid'); }
				});
				el.find('.btn-cancel').click(function() {
				});
				el.find('.btn-save').click(function() {
					if(!ins[0].files.length) { return null; }
					el.addClass('blocked loading');
					var fd = new FormData();
					fd.append(input, ins[0].files[0], ins[0].files[0].name);
					API.ajax('api/posts/uploader/'+input, 'post', {
						data: fd,
						contentType: false,
						headers: {enctype: 'multipart/form-data'},
						success: function(r) {
							el.removeClass('blocked loading valid').addClass('done');
							ins.val('');
							console.log(r);
						}
					});
				});
			});
		},
		checkSession: function() {
			checkSession(true);
		},
		resetMultiCheck: function() {
			var checks = $('main .multicheck.all');
			checks.prop('checked', false);
			checks.eq(0).trigger('change');
		}
	};
}();
