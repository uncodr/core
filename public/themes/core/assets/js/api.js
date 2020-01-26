"use strict";

/*
 * Dependency: ['core.js', '../third_party/js.cookies.js']
 */
var API = function() {
	var _getSession = function() {
		var out = {
			sessionID: localStorage.getItem('sessionID'),
			authToken: localStorage.getItem('authToken')
		};
		return (out.sessionID && out.authToken)? out : {};
	},
	// 'params' object keys: url, type, data, success, error, complete, optionals: contentType, headers
	_ajax = function(params) {
		var data = {
			processData: false,
			dataType: 'json',
			headers: _getSession(),
			type: params.type,
			url: 'api/'+params.url,
			data: params.data || null,
			success: function(r) {
				if((r != undefined) && (r.sessionID != undefined)) { localStorage.setItem('sessionID', r.sessionID); }
				params.success(r);
			},
			error: function(r) {
				if(r.status == 401) { window.location.href = 'auth/logout'; }
				else if(r.status == 403) { window.location.href = 'admin'; }
				else if(params.error) { params.error(r); }
				else { toastr['error'](r.responseJSON.message); }
			},
			complete: params.complete || null
		};
		if(params.contentType != undefined) { data.contentType = params.contentType; }
		if(params.headers) {
			$.each(params.headers, function(k,v) { data.headers[k] = v; });
		}
		$.ajax(data);
	},
	_sanitize = function(data) {
		var output = {
			id: data.id,
			success: data.success,
			error: data.error,
			complete: data.complete
		};
		if(data.contentType != undefined) {
			output.contentType = data.contentType;
			delete data.contentType;
		}
		if(data.headers != undefined) {
			output.headers = data.headers;
			delete data.headers;
		}
		delete data.id;
		delete data.success;
		delete data.error;
		delete data.complete;
		output.data = (data.data == undefined)? data : data.data;
		return output;
	},
	_generic = function(url, type) {
		var output = {
			get: function(params) {
				params = _sanitize(params);
				params.type = 'get';
				params.url = url;
				if(params.id) { params.url += '/'+params.id; }
				params.data = $.param(params.data);
				_ajax(params);
			},
			put: function(params) {
				params = _sanitize(params);
				params.type = 'put';
				params.url = url;
				params.data = JSON.stringify(params.data);
				_ajax(params);
			},
			post: function(params) {
				params = _sanitize(params);
				params.type = 'post';
				params.url = url;
				params.data = JSON.stringify(params.data);
				_ajax(params);
			},
			patch: function(params) {
				params = _sanitize(params);
				params.type = 'patch';
				params.url = url;
				if(params.id) { params.url += '/'+params.id; }
				params.data = JSON.stringify(params.data);
				_ajax(params);
			},
			delete: function(params) {
				params = _sanitize(params);
				params.type = 'delete';
				params.url = url;
				if(params.id) { params.url += '/'+params.id; }
				params.data = null;
				_ajax(params);
			}
		};
		return (type)? output[type]:output;
	};

	return {
		_ajax: function(url, type, params) {
			params = _sanitize(params);
			params.type = type;
			params.url = '../'+url;
			if(params.id) {
				params.url += '/'+params.id;
				delete params.id;
			}
			if((params.headers == undefined) || params.headers.enctype != 'multipart/form-data') {
				params.data = (type == 'get')? $.param(params.data): JSON.stringify(params.data);
			}
			_ajax(params);
		},
		ajax: function(url, type, params) {
			params.type = type;
			params.url = '../'+url;
			_ajax(params);
		},
		auth: function() {
			var output = {};
			output.login = _generic('auth/validator', 'post');
			output.logout = function() {
				_ajax({
					type: 'get',
					url: 'auth/logout',
					success: function() {
						toastr['success']('You have been logged out');
						API.auth().clearStorage(true);
						localStorage.setItem('logout', true);
						window.setTimeout(function() { window.location.href = 'auth'; }, 500);
					}
				});
			};
			output.register = _generic('auth/register', 'put');
			output.recover = _generic('auth/recover', 'post');
			output.reset = _generic('auth/reset', 'patch');
			output.isLoggedIn = function(params) {
				var s = _getSession();
				if(s.sessionID && s.authToken) {
					params.type = 'get';
					params.url = 'auth/validator';
					_ajax(params);
				} else { params.error(); }
				return s;
			};
			output.clearStorage = function(clearAll) {
				localStorage.removeItem('sessionID');
				localStorage.removeItem('authToken');
				if(clearAll) { localStorage.clear(); }
			};
			return output;
		},
		posts: function() {
			var output = _generic('posts');
			output.checkSlug = function(data) {
				switch(data.slug) {
					case 'admin': case 'auth': case 'api': case 'posts': case 'assets': case 'errors': case 'setup':
						data.success();
						break;
					default:
						output.get(data);
						break;
				}
			};
			return output;
		},
		settings: function() {
			return _generic('config');
		},
		media: function() {
			return _generic('posts/media');
		},
		users: function() {
			return _generic('config/users');
		},
		groups: function() {
			return _generic('config/groups');
		}
	};
}();
