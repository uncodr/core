"use strict";
/*
 * 1. init: init app
 *	- detect the (a) IE version, (b) mobile devices, (c) css-transition support, (d) activate the sidebars events,
 * 2. _evSideBars: Hamburger and Swipe events for Left Sidebar
 * 3. reposition: adjust the elements' position based on the window size
 * 4. _getViewport: get viewport/window size (width and height)
 * 5. _resizeContainer: adapt the Main Content height to the Main Navigation height
 * 6. ajaxLoader: load content with ajax
 * 7. checkSession: check whether user is logged in or not
 * 8. dropzone: file uploader
 * 9. _evDropzone: (a) show thumbnail on image selection, (b) show thumbnail on file path update, (c) cancel & save button events
 *
 * Output:
 * 4. fillTable: fill a table body (el) using data. Sample conf:
 *			conf = {
 *				reset: true,
 *				vars: {
 *					status: {key: 'publishedOn', type: 'bool', is: '1', val: ['truthy', 'falsey']},
 *					addedOn: {type: 'epoch', val: 'M d, Y H: i'},
 *					groups: {type: 'array', glue: ', ', val: 'string {{var1}} lorem ipsum {{var2}}'},
 *				},
 *				classes: {s: 'classname', d: ['key1']}, // s: static class names, d: dynamic classes
 *				attrs: ['attr1', 'attr2']
 *			}
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
	_evSideBars = function() {
		// (a)
		topBar.children(".hamburger").on("click", function(e) {
			e.preventDefault();
			sideLeft.toggleClass('none');
			$(window).trigger('resize');
			$(this).toggleClass('active');
		});
		$body.click(function(e) {
			if(!e.isDefaultPrevented()) {
				sideLeft.addClass('none');
				topBar.children(".hamburger").removeClass('active');
			}
		});
		// (b)
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
	reposition = function() {
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
	ajaxLoader = function(url, el) {
		Core.block(el);
		loadPage(url, el);

		function loadPage(url, el2) {
			$.ajax({
				type: "GET",
				cache: false,
				url: url,
				dataType: 'html',
				complete: function() { Core.unblock(el2); },
				success: function(data) { el2.html(data); },
				error: function(xhr, ajaxOptions, thrownError) {
					el2.html('<h4>Could not load the requested content.</h4>');
					Core.tempClass(el2, 'shake-xy');
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
					error: function() { window.location.href = window.url.base+'/auth/logout'; }
				});
			} else { Core.unblock($body); }
		}
		else { window.location.href = window.url.base+'/auth/logout'; }
	},
	dropzone = function() {
		var input = 'dz-file', dz = $('.dropzone');
		for (var i = dz.length-1; i >= 0; i--) {
			var el = dz.eq(i);
			el.append('<input type="file" name="'+input+'"><span class="btn-group axns"><a class="btn btn-save"><i class="ion ion-checkmark"></i></a><a class="btn btn-red btn-cancel"><i class="ion ion-close-round"></i></a></span><span class="placeholder">Choose a file</span><input type="hidden" name="dz-path" value="">');
			_evDropzone(el, input);
		};
	},
	_evDropzone = function(el, title) {
		var ins = el.children(':input'), label = el.children('.placeholder');
		// (a)
		ins.eq(0).change(function(e) {
			if(e.target.files.length) {
				var reader = new FileReader();
				reader.onload = function (e) {
					label.html('<img src="'+e.target.result+'">');
				}
				reader.readAsDataURL(ins.eq(0)[0].files[0]);
				el.addClass('valid');
			} else { el.removeClass('valid'); }
		});
		// (b)
		ins.eq(1).change(function() {
			var el2 = $(this), val = el2.val();
			if(val) {
				label.html('<img src="'+window.url.base+'/'+val+'">');
				el.addClass('done');
				ins.eq(0).val('');
			}
		});
		// (c)
		el.find('.btn-cancel').click(function() {
			el.removeClass('valid done');
			ins.val('');
			label.html('Choose a file');
		});
		el.find('.btn-save').click(function() {
			if(!ins.eq(0)[0].files.length) { return null; }
			Core.block(el);
			var fd = new FormData();
			fd.append(title, ins.eq(0)[0].files[0], ins.eq(0)[0].files[0].name);
			API._ajax('api/posts/uploader/'+title, 'post', {
				data: fd,
				contentType: false,
				headers: {enctype: 'multipart/form-data'},
				success: function(r) {
					Core.unblock(el);
					el.removeClass('valid').addClass('done');
					ins.eq(0).val('');
					ins.eq(1).val(r.path);
				}
			});
		});
	},
	_fill = function(html, data, conf) {
		var d = {};
		$.each(data, function(k, v) { d[k] = v; });
		if(conf.vars != undefined) {
			$.each(conf.vars, function(k, v) {
				var key = (v.key === undefined)? k:v.key;
				if(d[key] === undefined) { return true; }
				switch(v.type) {
					case 'bool':
						if(v.valKey == undefined) {
							d[k] = (d[key] == v.is)? v.val[0] : v.val[1];
						} else {
							d[k] = (d[key] == v.is)? d[v.valKey[0]] : d[v.valKey[1]];
						}
						break;
					case 'epoch': d[k] = Core.getDate(d[key], v.val); break;
					case 'int': d[k] = (d[key])? parseInt(d[key]):0; break;
					case 'array':
						var temp = d[key];
						for (var j = 0, m = temp.length; j < m; j++) {
							temp[j] = v.val.replaceMoustache(temp[j]);
						}
						d[k] = temp.join(v.glue);
						break;
				}
			});
		}
		var classes = [];
		if(conf.classes != undefined) {
			if(conf.classes.s != undefined) { classes = classes.concat(conf.classes.s.split(' ')); }
			if(conf.classes.d != undefined) { classes = classes.concat($.map(conf.classes.d, function(v) { return d[v]; })); }
		}
		return {html: html.replaceMoustache(d), classes: classes};
	},
	fillForm = function(el, data) {
	};

	return {
		init: function() {
			init();
			reposition();
			checkSession(true);
			Core.dropdown();
			// waits until the user is done resizing the window, then execute
			$(window).espressoResize(function() { reposition(); });
		},
		checkSession: function() { checkSession(true); },
		dropzone: function() { dropzone(); },
		fillTable: function(el, data, conf = {}) {
			var template = el.children('tr.template').eq(0), trTemp, out;
			if(conf.reset == undefined) { conf.reset = false; }
			if(conf.reset) { el.children(':not(.template, .placeholder)').remove(); }
			el.children().addClass('hidden');
			for(var i = 0, l = data.length; i < l; i++) {
				trTemp = template.clone();
				trTemp.removeClass('hidden template');
				out = _fill(template.html(), data[i], conf);
				trTemp.html(out.html);
				if(out.classes.length) { trTemp.addClass(out.classes.join(' ')); }
				el.append(trTemp);
			}
		},
		fill: function(el, data, conf = {}) {
			$.each(el.find('.field'), function() {
				var el = $(this);
				el.html('{{'+el.attr('data-name')+'}}');
			});
			var out = _fill(el.html(), data, conf);
			el.html(out.html);
			if(out.classes.length) { el.addClass(out.classes.join(' ')); }
		},
		resetMultiCheck: function() {
			var checks = $('main .multicheck.all');
			checks.prop('checked', false);
			checks.eq(0).trigger('change');
		}
	};
}();
