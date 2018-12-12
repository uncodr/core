"use strict";

(function() {
	var oldConfig = {},
	jsonFields = ['registration', 'comments', 'login', 'email'],
	homepage = {type: '', id: null},
	settingsFetch = function(form) {
		Core.block(form);
		API.posts().get({
			fields: 'id,title',
			type: 'page',
			status: 'published',
			success: function(r) {
				var selIn = form.find(':input[name="page_id"]');
				for(var i = r.data.length - 1; i >= 0; i--) {
					selIn.append('<option value="'+r.data[i].id+'">'+r.data[i].title+'</option');
				}
			}
		});
		API.settings().get({
			success: function(response) {
				oldConfig = response.data;
				oldConfig.notification = JSON.parse(oldConfig.notification);
				switch(oldConfig.homepage) {
					case 'app': homepage.type = 'app'; break;
					case 'blog': homepage.type = 'blog'; break;
					default:
						var temp = oldConfig.homepage.split(':');
						oldConfig.homepage = homepage.type = temp[0];
						homepage.id = temp[1];
						break;
				}
				for(var i = 0, l = jsonFields.length; i < l; ++i) {
					oldConfig[jsonFields[i]] = JSON.parse(oldConfig[jsonFields[i]]);
				}
				_settingsPopulate(form);
				Core.unblock(form);
			}
		});
	},
	showTab = function() {
		var hash = window.location.hash.replace('#','');
		if(!hash) { hash = 'general'; }
		$('.tab-wrapper').addClass('hidden');
		$('.tab-wrapper.'+hash).removeClass('hidden');
		$('a.tab-links').removeClass('active');
		$('a.tab-links[data-href="#'+hash+'"').addClass('active');
	},
	settingsSave = function(form) {
		var params, newParams, isChanged;
		form.on('submit', function() {
			// block form and check all required fields
			Core.block(form);
			params = Core.validateForm(form);
			if(!params) { return Core.unblock(form); }

			newParams = {}; isChanged = false;
			// check whether slug or id is mentioned in permalinks
			var perma = params.perma_links.split('/');
			perma = perma[perma.length - 1].substring(1);
			if(perma != 'id' && perma != 'slug') {
				form.prev().find('a:eq(1)').trigger('click');
				toastr['error']('Permanent URL must be unique. Include \':slug\' or \':id\' in the url.');
				Core.tempClass(form.find(':input[name="perma_links"]:checked').closest('li'), 'shake-xy');
				return Core.unblock(form);
			}
			// check site visibility
			params.is_crawlable = (params.is_crawlable == undefined)? 1 : 0;
			console.log(params.email, oldConfig.email);
			// check email settings
			if(!params.email.host) { params.email = {}; }
			else {
				if(JSON.stringify(params.email) == JSON.stringify(oldConfig.email)) {
					delete params.email;
				} else {
					params.email.pass = Core.encrypt(params.email.pass);
					form.find(':input[name="email[pass]"]').val(params.email.pass);
					isChanged = true;
				}
			}
			// comment settings
			// params.comments = {enable:0,sort:"DESC",author_info:1,public:0,moderation:1,autoclose:0};
			params.comments.enable = parseInt(params.comments.enable);
			if(params.comments.sort == undefined) { params.comments.sort = 'DESC'; }
			params.comments.author_info = (params.comments.author_info == undefined)? 0 : 1;
			params.comments.public = (params.comments.public == undefined)? 1 : 0;
			params.comments.moderation = (params.comments.moderation == undefined)? 0 : 1;
			params.comments.autoclose = parseInt(params.comments.autoclose);

			// registration settings
			// params.registration = {"enable":0,"default_group":"user","autologin":1};
			params.registration.enable = parseInt(params.registration.enable);
			params.registration.autologin = (params.registration.autologin == undefined)? 0 : 1;

			// login settings
			params.login.disable_on_expiry = (params.login.disable_on_expiry == undefined)? 0 : 1;
			params.login.unverified_limit = (params.uvlimit == undefined)? parseInt(params.login.unverified_limit) : 0;
			delete params.uvlimit;
			// email notifications
			if(params.notification == undefined) { params.notification = []; }
			// convert form data to camelcased fields
			$.each(params, function(k, v) {
				if((jsonFields.indexOf(k) != -1) || k == 'notification') {
					if(v.compare(oldConfig[k]) == false) { _updatePatchData(k, v, true); }
				} else {
					k = k.toCamelCase();
					if(v != oldConfig[k]) { _updatePatchData(k, v, false); }
				}
			});
			// send api request only if at least one form field has been updated
			newParams.error = function(r) { toastr['error']('No field updated.'); };
			newParams.complete = function() { return Core.unblock(form); };
			if(!isChanged) {
				newParams.error();
				newParams.complete();
			} else {
				newParams.success = function(r) {
					toastr['success']('Site Configuration updated successfully');
				};
				API.settings().patch(newParams);
			}
			return false;
		});

		function _updatePatchData(k, v, toStr) {
			oldConfig[k] = v;
			newParams[k] = (toStr)? JSON.stringify(v) : v;
			isChanged = true;
		}
	},
	_settingsPopulate = function(form) {
		// update form fields
		form.find('input[name="notification[]"]').filter(function() {
			return oldConfig.notification.indexOf($(this).attr('value')) != -1;
		}).prop('checked', true).trigger('change');
		Core.populateForm(form, oldConfig, jsonFields);
		// update unverified login limit
		var uvlimit = form.find('input[name="uvlimit"]');
		if(!oldConfig.login.unverified_limit) {
			form.find('.more-uvlimit').addClass('hidden');
			uvlimit.prop('checked', true);
		} else {
			form.find('.more-uvlimit').removeClass('hidden');
			uvlimit.prop('checked', false);
		}

		// trigger events
		_parseDateTime(form);
		form.find('input[name="perma_links"]').trigger('change');
		form.find('input[name="comments[enable]"]:checked').trigger('change');
		form.find('input[name="registration[enable]"]:checked').trigger('change');
		form.find('input[name="homepage"]').trigger('change');
	},
	_parseDateTime = function(form) {
		var inputDate = form.find('input[name="date_format"]:checked');
		var inputTime = form.find('input[name="time_format"]:checked');
		var el = form.find('span.dt-sample');
		el.text(
			Core.getDate(el.attr('data-value'), inputDate.val() + ' - ' + inputTime.val())
		);
	},
	// date, time and timezone settings
	evDateTime = function(form) {
		var timezones = form.find('select[name="timezone"]'), el, txt;
		$.each(timezones.children(), function(i) {
			el = $(this);
			txt = el.text().match(/\(([\s\S]+)\)/g);
			txt = txt[0].replace(/[\(UTC\)]/g,'');
			if(!txt) { txt = '0:00'; }
			el.text(txt+el.text().replace(/\(UTC/g,'').replace(txt,''));
		});
		form.find('input[name="date_format"]').on('change', function() { _parseDateTime(form); });
		form.find('input[name="time_format"]').on('change', function() { _parseDateTime(form); });
	},
	// custom format fields (permaLink, Date, Time)
	evCustomFields = function(form) {
		// custom fields
		form.find('input.custom').on('keyup', function() {
			var el = $(this), input = el.prev().find(':input');
			input.attr('value', el.val());
			input.prop('checked', true).trigger('change');
		});
	},
	// toggler inputs to show/hide or block/unblock elements
	evToggler = function(form, obj) {
		var el, insMore, check;
		$.each(obj, function(k, v) {
			form.find(':input[name="'+k+'"]').on('change', function() {
				el = $(this);
				insMore = (el.attr('data-more') || el.attr('name'));
				insMore = form.find('.more-'+insMore);
				if(el.attr('type') != 'checkbox') { check = el.val(); }
				else { check = el.prop('checked')? 1 : 0; }
				if(check == insMore.attr('data-val')) { insMore.removeClass(v); }
				else { insMore.addClass(v); }

				if(k == 'homepage') { insMore.find(':input[name="page_id"]').val(homepage.id); }
			});
		})
	},
	// show permaLink example by parsing format
	evPermaLinks = function(form) {
		var ins = form.find('input[name="perma_links"]'),
			page = {id: 43, slug: 'sample-post', y: '2017', m: '01', author: 'john'};
		ins.on('change', function() {
			var newIns = ins.filter(function() { return $(this).prop('checked'); });
			var el = form.find('span.perma-sample'), val;
			val = newIns.val().replace(
				new RegExp(':'+Object.keys(page).join('|:'), 'gi'),
				function(key) { return page[key.substring(1)]; }
			);
			el.text(val);
		});
	},
	// reset settings form
	evSettingsReset = function(form) {
		$('.btn-reset').on('click', function() {
			if(oldConfig) { _settingsPopulate(form); }
			return false;
		});
	};

	admin.settings = function() {
		var form = $('form.config-form');
		showTab();
		settingsFetch(form);
		settingsSave(form);
		evSettingsReset(form);
		evDateTime(form);
		evCustomFields(form);
		evToggler(form, {'comments[enable]': 'blocked', 'registration[enable]': 'blocked', homepage: 'hidden', uvlimit: 'hidden'});
		evPermaLinks(form);
		Core.multiCheck(true);
		// if history object changes
		window.onpopstate = function(e) { showTab(); }
	};
	admin.themes = function() {
	};
})();
