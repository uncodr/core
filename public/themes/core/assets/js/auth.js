"use strict";

var auth = {};
(function() {
	var checkSession = function(form, next) {
		var panel = form.closest('.panel');
		Core.block(panel);
		API.auth().isLoggedIn({
			success: function(r) {
				window.location = next;
			},
			error: function(r) {
				API.auth().clearStorage();
				Core.unblock(panel);
			}
		});
	},
	_loginSuccess = function(resp, next) {
		localStorage.setItem('sessionID', resp.sessionID);
		localStorage.setItem('authToken', resp.authToken);
		localStorage.setItem('data', JSON.stringify(resp.data));
		window.location.href = next;
	},
	formLogin = function(form, next) {
		var data = Core.validateForm(form);

		if(data) {
			// data.password = Core.encrypt(data.password);
			data.success = function(r) {
				_loginSuccess(r, next);
			};
			data.error = function(r) {
				toastr['error'](r.responseJSON.message);
				Core.tempClass(form, 'shake-xy');
				form.find(':input[type="password"]').val('');
				Core.unblock(form);
			};

			Core.block(form);
			API.auth().login(data);
		}
	},
	formRegister = function(form, next) {
		var data = Core.validateForm(form);

		if(data) {
			data.autologin = !!form.attr('data-autologin');
			data.success = function(r) {
				_loginSuccess(r.data, next);
			};
			data.error = function(r) {
				toastr['error'](r.responseJSON.message);
				Core.tempClass(form, 'shake-xy');
				Core.unblock(form);
			};

			Core.block(form);
			API.auth().register(data);
		}
	},
	formRecover = function(form) {
		var data = Core.validateForm(form);

		if(data) {
			data.success = function(r) {
				toastr['success']('OTP has been emailed');
				form.slideUp();
				localStorage.setItem('user', data.user);
				auth.reset(data.user);
			}
			data.error = function(r) {
				toastr['error'](r.responseJSON.message);
				Core.unblock(form);
			};

			Core.block(form);
			API.auth().recover(data);
		}
	},
	formReset = function(form) {
		var data = Core.validateForm(form);

		if(data) {
			if(data.password != data.password2) {
				toastr['error']('Passwords do not match.');
				var pwd2 = form.find('input[name="password2"]');
				Core.tempClass(pwd2, 'shake-xy');
				pwd2.val('');
				pwd2.focus();
				return false;
			}
			delete data.password2;
			// data.password = Core.encrypt(data.password);
			data.success = function(r) {
				toastr['success']('Password has been changed');
				API.auth().clearStorage(true);
				localStorage.setItem('logout', true);
				window.setTimeout(function() { window.location.href = 'auth'; }, 500);
			}
			data.error = function(r) {
				toastr['error'](r.responseJSON.message);
				Core.unblock(form);
			};

			Core.block(form);
			API.auth().reset(data);
		}
	};

	auth.login = function() {
		var form = $('form.login'), url = window.url.search;
		// next page after login successful
		url = (url && url.next)? url.next : 'admin';
		// if localstorage does not have 'logout' key, i.e. user is not coming from logout page
		// then check the session (whether user is already logged in)
		if(!localStorage.getItem('logout')) { checkSession(form, url); }
		else { localStorage.removeItem('logout'); }

		form.find('input[name="user"]').focus();
		form.submit(function() {
			formLogin(form, url);
			return false;
		});
	};
	auth.register = function() {
		$('form.register').submit(function() {
			formRegister($(this),'admin');
			return false;
		})
	};
	auth.recover = function() {
		var form = $('form.recover'), user = localStorage.getItem('user');
		if(user) {
			toastr['success']('OTP has been emailed');
			auth.reset(user);
		} else {
			form.removeClass('hidden');
			form.submit(function() {
				formRecover(form);
				return false;
			})
		}
	};
	auth.reset = function(user) {
		var form = $('form.reset');
		if(user) {
			form.children('input[name="user"]').val(user);
		}
		form.removeClass('hidden');
		form.submit(function() {
			formReset(form);
			return false;
		})
	};
})();
