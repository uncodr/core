"use strict";

(function() {

	// sets slug to blank and hides it
	var postType = '',

	sections,
	// show
	_showDiv = function(type, title) {
		if(title) { sections[type].children('.page-heading:first-child').children('h1').text(title); }
		$.each(sections, function(i) {
			if(i==type) { sections[i].hide().removeClass('hidden').fadeIn(); }
			else { sections[i].addClass('hidden'); }
		});
	},

	/* --- begin: functions for listing posts --- */
	tBody, filters,
	_listPosts = function(data, resetTable, replaceHistory) {
		// get the placeholder row ('no data found')
		var placeholder = tBody.children('tr.placeholder');
		placeholder.addClass('hidden');
		// data.type is the type of the post (article / page)
		data.type = postType;
		data.fields = 'id,slug,title,excerpt,author,authorName,status,publishedOn,lastUpdatedOn,commentStatus,commentCount';
		data.sort = ['-lastUpdatedOn'];
		if(replaceHistory) { data.meta = ['count']; }
		// ajax success function
		data.success = function(r) {
			_populateList({data: r.data, page: data.page, reset: resetTable});
			/*admin.fillTable(tBody, r.data, {
				reset: resetTable,
				vars: {
					status: {type: 'bool', key: 'publishedOn', is: null, val: ['Published', 'Last Updated']},
					axn: {type: 'bool', key: 'publishedOn', is: null, valKey: ['publishedOn', 'lastUpdatedOn']},
					axnDate: {type: 'epoch', key: 'axn', val: 'M d, Y'},
					axnTime: {type: 'epoch', key: 'axn', val: 'H:i:s'},
				},
				classes: {s: 'page-'+data.page, d: ['st2']}
			});*/
			/*if(obj.data[i].publishedOn) {
				status = 'published';
				trHTML = trHTML.replace(/<li><a class="btn-status-change" data-status="3" data-id="{{id}}">Publish<\/a><\/li>|<li><a class="danger btn-delete" data-id="{{id}}">Delete Forever<\/a><\/li>/g,'');
			} else {
				if(obj.data[i].status == '0') {
					obj.data[i].title += ' &mdash; Trash';
					status = 'trash';
					trHTML = trHTML.replace(/<li><a class="danger btn-status-change" data-status="0" data-id="{{id}}">Trash<\/a><\/li>/g,'');
				} else {
					obj.data[i].title += ' &mdash; Draft';
					status = 'drafts';
					trHTML = trHTML.replace('<li><a class="danger btn-delete" data-id="{{id}}">Delete Forever</a></li>','');
				}
			}
			trTemp.addClass(status+' page-'+obj.page);
			// if comments are disabled, then show lock icon
			if(obj.data[i].commentStatus == '0') {
				trHTML = trHTML.replace('<span class="chat-bubble">{{commentCount}}</span>', '<i class="ion lite ion-locked"></i>');
			}*/

			var history = window.history.state;
			if(r.meta) {
				_filterSetup(r.meta);
				history.meta = r.meta;
				window.history.replaceState(history, '', Core.changeHash(window.location.hash.substring(1)));
			}
			if(history.meta) {
				_paginate(history.meta, {status: data.status, page: data.page});
			}
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
			Core.unblock(tBody.parent());
			window.url.reload = false;
		};

		// get posts using data
		Core.block(tBody.parent());
		API.posts().get(data);
		admin.resetMultiCheck();
	},
	_populateList = function(obj) {
		var template = tBody.children('tr.template'), trHTML, trTemp, axnDate, status;
		// if reset is truthy, then create fresh table
		if(obj.reset) { tBody.children(':not(.template, .placeholder)').remove(); }
		// hide existing rows
		tBody.children().addClass('hidden');
		for(var i = 0, l = obj.data.length; i < l; i++) {
			trHTML = template.html();
			trTemp = template.clone();
			trTemp.removeClass('hidden template');
			// update post status, axnDate, axnTime
			if(obj.data[i].publishedOn) {
				status = 'published';
				axnDate = obj.data[i].publishedOn;
				obj.data[i].status = 'Published';
				trHTML = trHTML.replace(/<li><a class="btn-status-change" data-status="3" data-id="{{id}}">Publish<\/a><\/li>|<li><a class="danger btn-delete" data-id="{{id}}">Delete Forever<\/a><\/li>/g,'');
			} else {
				if(obj.data[i].status == '0') {
					obj.data[i].title += ' &mdash; Trash';
					status = 'trash';
					trHTML = trHTML.replace(/<li><a class="danger btn-status-change" data-status="0" data-id="{{id}}">Trash<\/a><\/li>/g,'');
				} else {
					obj.data[i].title += ' &mdash; Draft';
					status = 'drafts';
					trHTML = trHTML.replace('<li><a class="danger btn-delete" data-id="{{id}}">Delete Forever</a></li>','');
				}
				axnDate = obj.data[i].lastUpdatedOn;
				obj.data[i].status = 'Last Updated';
			}
			obj.data[i].axnDate = Core.getDate(axnDate, 'M d, Y');
			obj.data[i].axnTime = Core.getDate(axnDate, 'H:i:s');
			trTemp.addClass(status+' page-'+obj.page);
			// if comments are disabled, then show lock icon
			if(obj.data[i].commentStatus == '0') {
				trHTML = trHTML.replace('<span class="chat-bubble">{{commentCount}}</span>', '<i class="ion lite ion-locked"></i>');
			}
			// replace all values in handlebars
			trHTML = trHTML.replaceMoustache(obj.data[i]);
			trTemp.html(trHTML);
			// insert in table body
			tBody.append(trTemp);
		}
	},
	// add 'active' class and remove it from siblings
	_filterActivate = function(status) {
		var el = filters.children('.btn[data-status="'+status+'"]');
		if(!el) { return false; }
		// remove 'active' class from siblings
		el.siblings('.active').removeClass('active');
		// add active class to current button
		el.addClass('active');
	},
	// load filters (all, published, drafts, trash)
	_filterSetup = function(meta) {
		if(!meta) { return false; }

		// create filter buttons using counts of each status (window.url.meta)
		filters.html('');
		if(meta.count.all) {
			$.each(meta.count, function(i,v) {
				if(v) { __filterCreate(i, v); }
			});
		} else { __filterCreate('all', 0); }
		_filterActivate(window.history.state.status);
		function __filterCreate(status, count) {
			filters.append('<a class="btn btn-default" data-status="'+status+'" data-count="'+count+'">'+status.ucfirst()+' ('+count+')</a>');
		}
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
					} else { _listPosts(history, false, false); }
				}
			}
		);
	},
	_newPost = function() {
		var msg = 'Content goes here';
		Core.resetForm(post.form, ['id','template']);
		post.parent.val('0');
		if(post.commentStatus != undefined) {
			post.commentStatus.prop('checked', false).trigger('change');
		}
		post.content.val(msg);
		if(tinyMCE.activeEditor) { tinyMCE.activeEditor.setContent(msg); }
		_unsetSlug();
		post.form.find('.edit-permalink').removeClass('hidden');
		post['meta[thumbnail]'].val('').attr('data-method', 'put');
		// update post settings
		_setDefault('status','1');
		_setDefault('password','');
		_setDefault('commentStatus','0');
		// _setDefault('commentStatus','0');
		// post.form.find('.img-placeholder').html('');

		_showDiv('edit', 'Add '+postType.ucfirst());
		tags.reset();
	},
	// update post's status
	_updateStatus = function(id, status) {
		API.posts().patch({
			id: id,
			status: status,
			success: function(r) {
				var msg = {text: '', class: 'info'};
				switch(parseInt(status)) {
					case 0: msg.text = ' moved to trash'; msg.class = 'warning'; break;
					case 1: msg.text = ' moved to drafts'; break;
					case 2: msg.text = ' marked for review'; break;
					case 3: msg.text = ' published successfully'; msg.class = 'success'; break;
				}
				toastr[msg.class](postType.ucfirst()+' #'+id+msg.text);
			},
			complete: function(r) {
				/*var params = window.history.state;
				delete params.meta;
				window.history.replaceState(params, '', Core.changeHash(window.location.hash.substring(1)));*/
				_listPosts(window.history.state, true, true);
			}
		})
	},
	listEvents = function() {
		// bind filter events
		filters.on('click', '.btn', function() {
			var el = $(this), history = {}, hash;
			if(el.hasClass('active')) { return false; }

			history = window.history.state;

			history.status = el.attr('data-status');
			history.page = 1;
			_filterActivate(history.status);
			hash = 'status:'+history.status;
			if(history.search) { hash += '/search:'+history.search; }
			if(history.author) { hash += '/author:'+history.author; }
			window.history.pushState(history, '', Core.changeHash(hash));
			delete history.meta;
			_listPosts(history, true, false);
			return false;
		});

		// publish button event
		tBody.on('click', '.btn-status-change', function() {
			var el = $(this);
			_updateStatus(el.attr('data-id'), el.attr('data-status'));
			return false;
		});

		// delete button event
		tBody.on('click', '.btn-delete', function() {
			var el = $(this), id = el.attr('data-id');
			API.posts().delete({
				id: id,
				success: function(r) {
					toastr['error'](postType.ucfirst()+' #'+id+' deleted forever');
				},
				complete: function(r) {
					/*var params = window.history.state;
					delete params.meta;
					window.history.replaceState(params, '', Core.changeHash(window.location.hash.replace('#','')));*/
					_listPosts(window.history.state, true, true);
				}
			})
			return false;
		});

		// search input
		$('input[name="search"]').change(function() {
			var el = $(this), history = {status: 'all', page: 1, search: el.val()}, url;
			/*history = window.location.hash.replace('#','').jsonify('/',':');
			history.page = 1;
			history.search = el.val();*/
			url = 'status:'+history.status;
			if(history.search) { url += '/search:'+history.search; }
			else { delete history.search; }
			window.history.pushState(history, '', Core.changeHash(url));
			_listPosts(history, true, true);
			return false;
		});
	},
	/* --- end: functions for listing posts --- */
	/* --- begin: functions for adding/editing posts --- */
	post = {},
	_unsetSlug = function() {
		var perma = sections.edit.find('.post-permalink');
		perma.text('');
		perma.closest('.perma').addClass('transparent');
		post.slug.val('');
		// generate slug (permalink) from post title
		post.title.off('change').on('change', function() {
			_generateSlug($(this).val());
		});
	},
	// update slug value
	_setSlug = function(slug) {
		var perma = sections.edit.find('.post-permalink');
		perma.text(slug);
		post.slug.val(slug);
		perma.closest('.perma').removeClass('transparent');
	},
	// generate a slug, validate it and save it
	_generateSlug = function(str) {
		// if no value passed
		if(!str) {
			_unsetSlug();
			return false;
		}
		// generate post's slug
		var slug = str.trim().toLowerCase().replace(/[\s\/]+/g,'-'), l;
		slug = slug.replace(/[^\w\d-~._]+/g,'');
		l = slug.length;
		if(slug.substring(l-1)=='-') { slug = slug.substring(0, l-1); }
		// check the slug and set it
		_checkSlug(slug,0);
		return true;
	},
	// check slug by sending ajax recursively
	_checkSlug = function(slug,i) {
		var slug2 = slug;
		if(i) { slug2 += '-'+i; }
		API.posts().checkSlug({
			slug: slug2,
			success: function() { _checkSlug(slug,i+1); },
			error: function() { _setSlug(slug2); }
		});
	},
	// actions to perform, after post is saved
	_saveSuccess = function(r) {
		var summary = post.form.find('.post-status .summary'), html = summary.html();
		html = html.replace(/ \(Unsaved\)/g, '');
		summary.html(html);
		Core.unblock(post.form);
		toastr['success'](postType.ucfirst()+' has been saved successfully');
		window.url.reload = true;
	},
	// send post's data and update on the page
	_savePost = function(isPublished) {
		if(post.slug.val()) {
			Core.block(post.form);
			tinyMCE.triggerSave();
			// if post to be published, then set status = 3
			if(isPublished) {
				post.status.val(3);
				post.form.find('.summary .post_status').text('Published');
			}
			// validate required fields
			var data = Core.validateForm(post.form);
			if(!data) {
				Core.unblock(post.form);
				return false;
			}
			data.excerpt = data.content.replace(/<[^>]+>/gm, '');
			data.excerpt = data.excerpt.substr(0,80);
			// if commentStatus is disabled, then send value of '0'
			if(!data.commentStatus) { data.commentStatus = '0'; }

			if(data['dz-path']) { data.meta.thumbnail = data['dz-path']; }
			else { delete data.meta.thumbnail; }
			delete data['dz-path'];

			var meta = {put: [], patch: []};
			$.each(data.meta, function(k) {
				if (data.meta[k]) {
					var method = post['meta['+k+']'].attr('data-method');
					meta[method].push({key: k, value: data.meta[k]});
				}
			});
			data.meta = meta;

			// ajax complete function
			data.complete = function(r) {
				_saveSuccess(r);
			}
			// if id is set, then use patch request to update post
			if(post.id.val()) {
				delete data.type;
				delete data.slug;
				data.success = function(r) {
				};
				API.posts().patch(data);
			}
			// else use put request to create post
			else {
				data.success = function(r) {
					post.id.val(r.id);
				};
				API.posts().put(data);
			}
		} else {
			if(!post.title.val()) {
				toastr['error']('Title cannot be blank.');
				Core.tempClass(post.title, 'shake-xy');
				post.title.focus();
				return false;
			}
			setTimeout(function() { _savePost(isPublished); }, 200);
		}
	},
	// get post details and populate in the edit form
	_populatePost = function(id) {
		Core.block(sections.edit);
		API.posts().get({
			id: id,
			fields: 'id,slug,type,title,content,template,excerpt,parent,status,password,commentStatus',
			meta: 'thumbnail',
			success: function(r) {
				r.data = r.data[0];
				Core.unblock(sections.edit);
				postType = r.data.type;
				$.each(r.data, function(k, v) {
					if(k == 'password' || k == 'commentStatus' || k == 'meta') { return; }
					post[k].val(v);
				});
				if (r.data.meta.thumbnail != undefined) {
					post['meta[thumbnail]'].val(r.data.meta.thumbnail).attr('data-method', 'patch');
					var dz = sections.edit.find('.dropzone');
					dz.children('.placeholder').html('<img src="'+r.data.meta.thumbnail+'">');
					dz.addClass('done');
					dz.children(':input[name="dz-path"]').val(r.data.meta.thumbnail);
				} else {
					post['meta[thumbnail]'].attr('data-method', 'put');
				}
				// update slug
				_setSlug(r.data.slug,true);
				post.form.find('.edit-permalink').addClass('hidden');
				post.title.off('change');
				// set status
				_setDefault('status', r.data.status);
				// set password
				_setDefault('password', r.data.password);
				// set commentStatus
				_setDefault('commentStatus', r.data.commentStatus);
				// set post content
				setTimeout(function(){ tinyMCE.activeEditor.setContent(r.data.content)}, 0);
				tags.changeID(id);
			},
			error: function(r) {
				toastr['error']('URL is invalid.');
				// window.history.back();
			}
		});
	},
	_setDefault = function(key, value) {
		switch(key) {
			case 'status':
				// set status
				post.status.val(value);
				post.status.children('option').removeAttr('data-saved');
				var option = post.status.children('option[value='+value+']');
				option.attr('data-saved',true);
				post.status.trigger('change');
				break;
			case 'password':
				// set password
				if(post.password == undefined) { return null; }
				post.password.removeAttr('data-saved');
				var passwd;
				switch(value) {
					case '':
						passwd = post.password.eq(0);
						break;
					case 'SYSTEM':
						passwd = post.password.eq(1);
						break;
					default:
						passwd = post.password.eq(2);
						var len = value.length;
						post.form.find('input.custom-password').val(value.substring(1,len-1));
				}
				passwd.attr('data-saved',true);
				passwd.prop('checked', true).trigger('change');
				break;
			case 'commentStatus':
				if(post.commentStatus == undefined) { return null; }
				post.commentStatus.prop('checked', value=='1').trigger('change');
				break;
		}
	},
	editEvents = function(inputs) {
		// array to store the form elements
		var inputs = post.form.find(':input:not(:button)'), el, v;
		$.each(inputs, function(i) {
			el = $(this);
			v = el.attr('name');
			if(v && v != 'password') { post[el.attr('name')] = el; }
		});
		if(post.password != undefined) {
			post.password = inputs.filter(function(i) {
				return $(this).attr('name') == 'password';
			})
		}

		// generate slug (permalink) from post title
		post.title.on('change', function() {
			_generateSlug($(this).val());
		});

		// generate slug from input's value
		post.slug.on('change', function() {
			var resp = _generateSlug($(this).val());
			if(resp) { post.title.off('change'); }
		});

		// edit/save button for slug
		post.form.find('.edit-permalink').click(function() {
			var el = $(this);
			el.siblings('.post-permalink').toggleClass('hidden');
			post['slug'].toggleClass('hidden');
			el.text((el.text() == 'Edit')? 'Save' : 'Edit');
			return false;
		});

		// edit/save button for settings panel
		post.form.find('.settings-link').click(function() {
			var el = $(this), p1 = el.parent().next().children(), txt = el.text();
			p1.slideToggle(400);
			if(el.text() == 'Edit') { el.text('Done'); }
			else {
				_savePost(p1.find(':input[name=status]').val() == '3');
				el.text('Edit');
			}
			return false;
		});

		// if post's status is changed, then reflect on the summary panel
		post.status.on('change', function() {
			var el = $(this), optSel = el.find('option:selected'), txt = optSel.text();
			if(!optSel.attr('data-saved')) { txt += ' (Unsaved)'; }
			el.closest('.panel').find('.post_status').text(txt);
		});

		// if post's password (visibility) is changed, then update the summary panel
		// and show custom password field if required
		if(post.password != undefined) {
			post.password.on('change', function() {
				var el = $(this), txt = el.closest('label').text(), panel = el.closest('.panel');
				if(!el.attr('data-saved')) { txt += ' (Unsaved)'; }
				panel.find('.post_password').text(txt);
				// show/hide custom password field
				if(el.hasClass('custom')) { panel.find('input.custom-password').removeClass('hidden'); }
				else { panel.find('input.custom-password').addClass('hidden'); }
			});
		}

		// when custom password is provided, then update the parent input's value
		post.form.find('input.custom-password').on('change', function() {
			var customPwd = $(this).val();
			if(customPwd) { customPwd = '"'+customPwd+'"'; }
			post.password.filter('.custom').attr('value', customPwd);
		});

		// if post's comment-status is changed, then reflect on the summary panel
		if(post.commentStatus != undefined) {
			post.commentStatus.on('change', function() {
				var el = $(this), txt = el.prop('checked')? 'Enabled' : 'Disabled', panel = el.closest('.panel');
				panel.find('.post_commentStatus').text(txt);
			});
		}

		// save/preview/pubish buttons
		sections.edit.find('.toolbar .btn-save').on('click', function() {
			_savePost($(this).hasClass('btn-publish'));
			return false;
		});

		// button to list all posts
		$('.btn-list').click(function() {
			var el = $(this), hash = el.attr('href').split('#'), history;
			hash = hash[1];
			history = hash.jsonify('/',':');
			history.page = 1;
			window.history.pushState(history, '', Core.changeHash(hash));
			initPosts();
			return false;
		});
	},
	/* --- end: functions for adding/editing posts --- */
	initPosts = function() {
		var hash = window.location.hash,
			params = {};

		switch(hash) {
			case '#new':
				params = {new: true};
				_newPost();
				break;
			case '':
				params = {status: 'all', page: 1};
				window.history.replaceState(params, '', Core.changeHash('status:all'));
				_listPosts(params, true, true);
				_showDiv('list');
				break;
			default:
				hash = hash.substring(1);
				params = hash.jsonify('/',':');
				if(!params.page) { params.page = 1; }
				if(!params.status) { params.status = 'all'; }
				// if editing
				if(params.id) {
					// show post edit form, and populate with the data
					_populatePost(params.id);
					_showDiv('edit', 'Edit '+postType.ucfirst()+' #'+params.id);
					break;
				}
				// if on post meta
				if(params.meta) {
					meta.loadConf({id: params.meta});
					_showDiv('meta', 'Meta '+postType.ucfirst()+' #'+params.meta);
					break;
				}
				if(params.status) {
					var searchInput = sections.list.find('input[name="search"]');
					if(params.search) { searchInput.val(params.search); }
					else { searchInput.val(''); }

					var history = window.history.state;
					if(history && history.meta && !window.url.reload) {
						_filterSetup(history.meta);
						_listPosts(params, true, false);
					}
					else {
						window.history.replaceState(params, '', Core.changeHash(hash));
						_listPosts(params, true, true);
					}
					_showDiv('list');
				}
				break;
		}

		return params;
	};

	admin.posts = function(type) {
		window.url.reload = false;
		postType = type;
		sections = {list: $('section.posts-list').eq(0), edit: $('section.posts-edit').eq(0), meta: $('section.posts-meta').eq(0)};
		tBody = sections.list.find('tbody');
		filters = $('.toolbar .filters');
		post = {form: $('div.process-post')};

		if(window.history.state) { delete window.history.state.meta; }
		listEvents();
		editEvents();
		meta.init({
			el: sections.meta,
			api: 'posts/meta',
			keys: [
				{label: 'Description', value: 'header.description'},
				{label: 'Keywords', value: 'header.keywords'},
				{label: 'Author', value: 'header.author'},
				{label: 'OG: Image', value: 'header.og:image'},
				{label: 'OG: Title', value: 'header.og:title'},
				{label: 'OG: URL', value: 'header.og:url'},
				{label: 'OG: Site Name', value: 'header.og:site_name'},
				{label: 'OG: Type', value: 'header.og:type'},
			]
		});
		initPosts();
		tags.search('.tag-search', 'post');
		Core.multiCheck();
		Core.wysiwyg('.wysiwyg');
		admin.dropzone();

		// if history object changes
		window.onpopstate = function(e) {
			// if(e.state) { delete e.state.meta; }
			var url = initPosts();
		};
	};
})();
