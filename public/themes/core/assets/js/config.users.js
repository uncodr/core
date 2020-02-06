"use strict";

(function() {
	var sections,
	// show
	_showDiv = function(type, title) {
		if(title) { sections[type].children('.page-heading:first-child').children('h1').text(title); }
		$.each(sections, function(i) {
			if(i==type) { sections[i].hide().removeClass('hidden').fadeIn(); }
			else { sections[i].addClass('hidden'); }
		});
	},
	tBody, filters, _axnsUpdated = false,
	_listUser = function(data, resetTable, replaceHistory) {
		// get the placeholder row ('no data found')
		var placeholder = tBody.children('tr.placeholder');
		placeholder.addClass('hidden');
		// data.type is the type of the post (article / page)
		data.fields = {users:['id','email','login','name','addedOn','lastLogin','emailVerified','status','loginCount'],groups:['id','name']};
		data.fields = JSON.stringify(data.fields);
		if(replaceHistory) { data.meta = ['count']; }
		// ajax success function
		data.success = function(r) {
			for (var i = 0; i < r.data.length; i++) {
				r.data[i].groups = (r.groups[r.data[i].id] != undefined)? r.groups[r.data[i].id] : [];
			}
			admin.fillTable(tBody, r.data, {
				reset: resetTable,
				vars: {
					status: {type: 'bool', is: 1, val: ['active', 'trash']},
					emailVerified: {type: 'bool', is: 1, val: ['', ' <i class="ion ion-alert-circled danger"></i>']},
					addedOn: {type: 'epoch', val: 'M d, Y H:i'},
					lastLogin: {type: 'epoch', val: 'M d, Y H:i'},
					loginCount: {type: 'int'},
					groups: {type: 'array', glue: ', ', val: '<a href="admin/config/users#status:all/gid:{{id}}">{{name}}</a>'}
				},
				classes: {s: 'page-'+data.page, d: ['status']}
			});
			var history = window.history.state;
			if(r.meta) {
				_checkPermission(r.meta._);
				_filterSetup(r.meta);
				history.meta = r.meta;
				window.history.replaceState(history, '', Core.changeHash(window.location.hash.substring(1)));
			}
			if(history.meta) { _paginate(history.meta, {status: data.status, page: data.page}); }
			var searchVal = (data.search == undefined)? '':data.search;
			_searchTitle(searchVal);
		};
		// ajax error function
		data.error = function(r) {
			// alert(JSON.stringify(r));
			placeholder.removeClass('hidden');
			if(r.meta) { _filterSetup(r.meta); }
			tBody.children(':not(.template, .placeholder)').remove();
		};
		// ajax complete function
		data.complete = function(r) {
			// unblock table body
			Core.unblock(sections.list);
			window.url.reload = false;
		};

		// get posts using data
		Core.block(sections.list);
		API.users().get(data);
		admin.resetMultiCheck();
	},
	_filterSetup = function(meta) {
	},
	_paginate = function(meta, params) {
		Core.pagination(
			{
				itemCount: (params.status)? meta.count[params.status] : meta.count.all,
				activePage: params.page,
				pageSize: meta.pageSize
			}, {
				onPageChange: function(n) {
					var history = window.history.state, hash = 'status:'+history.status;
					history.page = n;
					if(history.search) { hash += '/search:'+history.search; }
					hash += '/page:'+n;
					window.history.pushState(history, '', Core.changeHash(hash));
					delete history.meta;

					var tRows = tBody.children('.page-'+n);
					if(tRows.length) {
						admin.resetMultiCheck();
						tBody.children().addClass('hidden');
						tRows.removeClass('hidden');
					} else { _listUser(history, false, false); }
				}
			}
		);
	},
	_evDeleteUser = function(id) {
	},
	_checkPermission = function(val) {
		if(_axnsUpdated) { return; }
		switch(val) {
			case 1:
				$('.edit-user, .edit-pwd, .group-add, .btn-remove').addClass('hidden');
				$('.group-save').closest('ul').addClass('hidden');
				break;
		}
		_axnsUpdated = true;
	},
	_searchTitle = function(val) {
		var searchInput = sections.list.find('input[name="search"]');
		if(val) { $('.search-title').removeClass('hidden').html('Search Results for: <strong>'+val+'</strong>'); }
		else { $('.search-title').addClass('hidden'); }
		searchInput.val(val);
	},
	listEvents = function() {
		// delete selected users
	},
	user = {},
	isEditing = false, isAdding = false,
	_editUser = function(id) {
		Core.block(sections.edit);
		API.users().get({
			id: id,
			meta: true,
			fields: JSON.stringify({users:['email','login','screenName','name','lastLogin','loginCount','emailVerified','status','addedOn'],groups:['id','name','expiry','status']}),
			success: function(r) {
				_checkPermission(r.meta._);
				user.id = id;
				user.data = r.data;
				user.meta = r.meta;
				user.groups = r.groups;
				_populateUserData();
				_populateGroups();
				if(isEditing) { $('.edit-user').trigger('click'); }
				if(isAdding) { $('.group-add').trigger('click'); }
			},
			complete: function(r) {
				Core.unblock(sections.edit);
			}
		});
	},
	_populateUserData = function() {
		admin.fill($('div.user-details'), user.data, {
			vars: {
				status: {type: 'bool', is: 1, val: ['Active', 'Inactive <i class="ion ion-alert-circled danger"></i>']},
				emailVerified: {type: 'bool', is: 1, val: ['Yes', 'No <i class="ion ion-alert-circled danger"></i>']},
				addedOn: {type: 'epoch', val: 'M d, Y H:i:s'},
				lastLogin: {type: 'epoch', val: 'M d, Y H:i:s'},
				loginCount: {type: 'int'}
			}
		});
	},
	_populateGroups = function() {
		var div = $('.user-groups'), template = div.children('li.template'), trHTML, trTemp, data;
		// if reset is truthy, then create fresh table
		div.children(':not(.template, .list-title, .add, .adding)').remove();
		if(!user.groups.length) {
			div.append($('<li>No groups found</li>'));
			return false;
		}
		for(var i = 0, l = user.groups.length; i < l; i++) {
			data = {};
			$.each(user.groups[i], function(k) {
				data[k] = user.groups[i][k];
			});
			trHTML = template.html();
			trTemp = template.clone();
			trTemp.removeClass('hidden template');
			data.status = (data.status == 1)? 'Active':'Inactive';
			data.expiry = Core.getDate(data.expiry, 'M d, Y H:i');
			console.log(data.id, user.meta._)
			data.class = ((data.id == '1') && (parseInt(user.meta._) != 7))? 'hidden':'';
			trTemp.attr('data-num', i);
			// replace all values in handlebars
			trHTML = trHTML.replaceMoustache(data);
			trTemp.html(trHTML);
			// fill inputs in the row
			data = {status: user.groups[i].status, expiry: ''}
			if(user.groups[i].expiry) { data.expiry = Core.getDate(user.groups[i].expiry, 'd/m/Y'); }
			Core.populateForm(trTemp, data);
			// insert in table body
			div.append(trTemp);
		}
	},
	_updateUserGroup = function(el) {
		var data = Core.validateForm(el);
		if(!data.expiry) { data.expiry = null; }
		if(data.status == undefined) { data.status = '0'; }
		if(data.id == undefined) { _patchUserGroup(data, el); }
		else { _addUserGroup(data, el); }
	},
	_addUserGroup = function(data, el) {
		Core.block(el);
		data.expiry = Core.parseDate(data.expiry,'d/m/Y');
		API.users().patch({
			id: user.id,
			group: {add: [data]},
			success: function(r) {
				toastr['success']('User added to the group');
				el.toggleClass('add adding');
				el.slideUp();
				data.name = el.find('select[name="id"] > option:selected').text();
				user.groups.splice(0,0,data);
				_populateGroups();
			},
			error: function(r) {
				toastr['error'](r.responseJSON.message);
				el.find('select[name="id"]').val('');
			},
			complete: function(r) {
				el.hide();
				isAdding = false;
				Core.unblock(el);
			}
		});
	},
	_patchUserGroup = function(data, el) {
		var i = parseInt(el.attr('data-num'));
		data.expiry = Core.parseDate(data.expiry,'d/m/Y');
		if(user.groups[i].expiry == data.expiry) { delete data.expiry; }
		if(user.groups[i].status == data.status) { delete data.status; }
		if(typeof data.expiry != 'undefined' || typeof data.status != 'undefined') {
			Core.block(el);
			data.id = user.groups[i].id;
			API.users().patch({
				id: user.id,
				group: {update: [data]},
				success: function(r) {
					if(typeof data.expiry != 'undefined') { user.groups[i].expiry = data.expiry; }
					if(typeof data.status != 'undefined') { user.groups[i].status = data.status; }
					_populateGroups();
				},
				error: function(r) {
					toastr['error'](r.responseJSON.message);
					Core.populateForm(el, user.groups[i]);
				},
				complete: function(r) {
					Core.unblock(el);
					isEditing = false;
				}
			});
		}
	},
	_evEditUser = function(el) {
		var divs = el.parent().next().children('.user-details'),
			form = divs.filter(function(k, v) { return (v.tagName == 'FORM'); });
		if(el.html() == 'Edit') {
			Core.block(form);
			el.html('Cancel');
			Core.populateForm(form, user.data);
			Core.unblock(form);
		} else {
			el.html('Edit');
		}
		isEditing = !isEditing;
		divs.slideToggle();
	},
	_evSaveUser = function(el) {
		var data = Core.validateForm(el);
		if(el.hasClass('user-pwd')) {
			if(data.password != data.password2) {
				toastr['error']('Passwords do not match. Please confirm password again');
				var ins = el.find('input[name="password2"]');
				Core.tempClass(ins, 'shake-xy');
				ins.val('');
				ins.focus();
				return false;
			} else { delete data.password2; }
			data.success = function(r) {
				toastr['success']('Password Updated');
				el.slideToggle();
			};
			data.error = function(r) { toastr['error']('Password Update failed'); };
		} else {
			data.screen_name = data.screen_name.toLowerCase();
			$.each(data, function(k, v) {
				delete data[k];
				k = k.toCamelCase();
				if(v != user.data[k]) { data[k.toCamelCase()] = v; }
			});
			if(JSON.stringify(data) == '{}') {
				toastr['error']('No fields updated.');
				return false;
			}
			data.success = function(r) {
				toastr['success']('User Details Updated');
				var fields = ['email', 'login', 'emailVerified', 'screenName', 'name', 'status'];
				for(var i = fields.length - 1; i >= 0; i--) {
					if(data[fields[i]] != undefined) {
						user.data[fields[i]] = data[fields[i]];
					}
				}
				_populateUserData();
				$('.edit-user').trigger('click');
			};
			data.error = function(r) {
				if(r.status == 400) {
					for (var i = r.responseJSON.error.length - 1; i >= 0; i--) {
						r.responseJSON.error[i] = r.responseJSON.error[i].toSnakeCase();
					}
					var ins = el.find(':input[name="'+r.responseJSON.error.join('"],:input[name="')+'"]');
					Core.tempClass(ins, 'shake-xy');
					toastr['error'](r.responseJSON.message);
					ins.eq(0).focus();
				}
			};
		}
		Core.block(el);
		data.id = user.id;
		data.complete = function(r) { Core.unblock(el); };
		API.users().patch(data);
	},
	_evAddGroup = function() {
		var row = $('.user-groups').children('li.add, li.adding');
		if(row.hasClass('add')) {
			var gIDs = user.groups.columns('id'),
				options = row.find('select[name="id"] > option');
			options.removeAttr('disabled');
			options.filter(function(k) {
				return ((k == 0) || (gIDs.indexOf($(this).attr('value')) != -1));
			}).attr('disabled', 'disabled');
		}
		row.toggleClass('add adding');
		isAdding = !isAdding;
		row.slideToggle();
	},
	_evDeleteGroup = function(el) {
		var row = el.closest('li.row');
		var i = parseInt(row.attr('data-num'));
		Core.block(row);
		API.users().patch({
			id: user.id,
			group: {remove: [user.groups[i].id]},
			success: function(r) {
				user.groups.splice(i, 1);
				_populateGroups();
			},
			error: function(r) {
				toastr['error'](r.responseJSON.message);
				Core.unblock(row);
			}
		});
	},
	_evSaveGroup = function(el) {
		var row = el.closest('li.row');
		if(row.hasClass('adding')) { _updateUserGroup(row); }
		else {
			row.find('.field').toggleClass('hidden');
			if(row.hasClass('editing')) { _updateUserGroup(row); }
			el.html((el.html() == 'Edit')? 'Save':'Edit');
			row.toggleClass('editing');
		}
	},
	_newUser = function() {
	},
	editEvents = function() {
		$('.btn-remove').click(function(e) {
			e.preventDefault();
			var activePage = sections.edit.hasClass('hidden')? 'list':'edit';
			Core.block(sections[activePage]);
			if(activePage == 'list') {
				var ids = [];
				$.each($('.multicheck:checked'), function(i,el) {
					var val = $(el).val();
					if(val) { ids.push(val); }
				});
				ids = ids.join('-');
			} else { ids = user.id; }
			API.users().delete({
				id: ids,
				success: function(r) {
					var params = {status: 'all', page: 1};
					window.history.replaceState(params, '', Core.changeHash('status:all'));
					_listUser(params, true, true);
					_showDiv('list');
				},
				complete: function(r) { Core.unblock(sections[activePage]); }
			});
		});
		$('.edit-user').click(function(e) {
			e.preventDefault();
			_evEditUser($(this));
		});
		$('.edit-pwd').click(function(e) {
			e.preventDefault();
			$('form.user-pwd').slideToggle();
		});
		$('form.user-details, form.user-pwd').submit(function() {
			_evSaveUser($(this));
			return false;
		});
		$('.reset-pwd').click(function(e) {
			e.preventDefault();
			var el = $(this).closest('.panel');
			Core.block(el);
			API.auth().recover({
				user: user.data.email,
				success: function(r) { toastr['success']('Reset Link has been emailed'); },
				error: function(r) { toastr['error'](r.responseJSON.message); },
				complete: function() { Core.unblock(el); },
			});
		});
		$('.group-add').click(function(e) {
			e.preventDefault();
			_evAddGroup();
		});
		$('.user-groups').on('click', '.group-delete', function(e) {
			e.preventDefault();
			_evDeleteGroup($(this));
		});
		$('.user-groups').on('click', '.group-save', function(e) {
			e.preventDefault();
			_evSaveGroup($(this));
		});
		$('.user-groups select[name="id"]').change(function() {
			var el = $(this), exp = el.children('option:selected').attr('data-expiry');
			el = el.closest('.row').find(':input[name="expiry"]');
			el.val((exp)? Core.getDate(exp, 'd/m/Y') : '');
		});
		$('input[name="search"]').change(function() {
			var el = $(this), history = {status: 'all', page: 1, search: el.val()}, url;
			url = 'status:'+history.status;
			if(history.search) { url += '/search:'+history.search; }
			else { delete history.search; }
			window.history.pushState(history, '', Core.changeHash(url));
			_listUser(history, true, true);
			return false;
		});
	},
	initUsers = function() {
		var hash = window.location.hash, params = {};

		switch(hash) {
			case '#new':
				Core.modal('.user-add').show();
				params = {new: true};
				_newUser();
				break;
			case '':
				Core.modal('.user-add').hide();
				params = {status: 'all', page: 1};
				window.history.replaceState(params, '', Core.changeHash('status:all'));
				_listUser(params, true, true);
				_showDiv('list');
				break;
			default:
				Core.modal('.user-add').hide();
				hash = hash.substring(1);
				params = hash.jsonify('/',':');
				if(!params.page) { params.page = 1; }
				if(!params.status) { params.status = 'all'; }
				// if editing
				if(params.id) {
					// show post edit form, and populate with the data
					_editUser(params.id);
					_showDiv('edit', 'Edit User #'+params.id);
					break;
				}
				if(params.status) {
					var history = window.history.state;
					if(history && history.meta && !window.url.reload) {
						_filterSetup(history.meta);
						_listUser(params, true, false);
					}
					else {
						window.history.replaceState(params, '', Core.changeHash(hash));
						_listUser(params, true, true);
					}
					_showDiv('list');
				}
				break;
		}

		return params;
	};

	admin.users = function() {
		window.url.reload = false;
		sections = {list: $('section.users-list'), edit: $('section.users-edit')};
		tBody = sections.list.find('tbody');
		filters = $('.toolbar .filters');

		if(window.history.state) { delete window.history.state.meta; }
		listEvents();
		editEvents();
		initUsers();
		Core.multiCheck();
		Core.modal('.user-add').events();
		$('form.register').submit(function() {
			var data = Core.validateForm($(this));
			data.success = function() { toastr['success']('User added'); }
			API.auth().register(data);
			return false;
		});

		// if history object changes
		window.onpopstate = function(e) {
			// if(e.state) { delete e.state.meta; }
			initUsers();
		};
	};
})();
