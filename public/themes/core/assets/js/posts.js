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
	tBody, filters, pageControls = {},
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
			if(r.meta) {
				_filterSetup(r.meta);
				var history = window.history.state;
				history.meta = r.meta;
				window.history.replaceState(history, '', Core.changeHash(window.location.hash.substring(1)));
			}
			_paginateSetup();
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
				trHTML = trHTML.replace('<li><a class="danger btn-delete" data-id="{{id}}">Delete Forever</a></li>','');
			} else {
				if(obj.data[i].status == '0') {
					obj.data[i].title += ' &mdash; Trash';
					status = 'trash';
					trHTML = trHTML.replace('<li><a class="danger btn-trash" data-id="{{id}}">Trash</a></li>','');
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
		// add status and count attributes to pagination wrapper
		pageControls.itemCount = parseInt(el.attr('data-count'));
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
	_paginateActivate = function(pageNum) {
		pageControls.pNum.val(pageNum);
		pageControls.prev.attr('data-page', pageNum - 1);
		pageControls.next.attr('data-page', pageNum + 1);
		switch(pageNum) {
			case 1:
				pageControls.first.addClass('disabled');
				pageControls.prev.addClass('disabled');
				pageControls.next.removeClass('disabled');
				pageControls.last.removeClass('disabled');
				break;
			case pageControls.pageCount:
				pageControls.first.removeClass('disabled');
				pageControls.prev.removeClass('disabled');
				pageControls.next.addClass('disabled');
				pageControls.last.addClass('disabled');
				break;
			default:
				pageControls.first.removeClass('disabled');
				pageControls.prev.removeClass('disabled');
				pageControls.next.removeClass('disabled');
				pageControls.last.removeClass('disabled');
				break;
		}
	},
	_paginateJumpto = function(pageNum) {
		pageNum = parseInt(pageNum);
		if((pageNum > pageControls.pageCount) || (pageNum <= 0)) { return false; }

		var history = window.history.state, hash = 'status:'+history.status;
		history.page = pageNum;
		if(history.search) { hash += '/search:'+history.search; }
		hash += '/page:'+pageNum;
		window.history.pushState(history, '', Core.changeHash(hash));
		delete history.meta;

		_paginateActivate(pageNum);
		var tRows = tBody.children('.page-'+pageNum);
		if(tRows.length) {
			admin.resetMultiCheck();
			tBody.children().addClass('hidden');
			tRows.removeClass('hidden');
		} else { _listPosts(history, false, false); }
	},
	_paginateSetup = function() {
		var controls = $('.toolbar .pagination'), history = window.history.state;

		pageControls.pageCount = Math.ceil(pageControls.itemCount/history.meta.pageSize);

		pageControls.pCount.text(pageControls.pageCount);
		pageControls.first.attr('data-page', 1);
		pageControls.last.attr('data-page', pageControls.pageCount);
		_paginateActivate(history.page);
		// show/hide pagination controls
		if(pageControls.pageCount == 1) { controls.addClass('hidden'); }
		else { controls.removeClass('hidden'); }
	},
	_newPost = function() {
		var msg = 'Content goes here';
		Core.resetForm(post.form, ['id','template']);
		post.parent.val('0');
		post.commentStatus.prop('checked', false).trigger('change');
		post.content.val(msg);
		if(tinyMCE.activeEditor) { tinyMCE.activeEditor.setContent(msg); }
		_unsetSlug();
		post.form.find('.edit-permalink').removeClass('hidden');
		// update post settings
		_setDefault('status','1');
		_setDefault('password','');
		_setDefault('commentStatus','0');
		// _setDefault('commentStatus','0');
		post.form.find('.img-placeholder').html('');

		_showDiv('edit', 'Add '+postType.ucfirst());
	},
	listEvents = function() {
		/* pagination controls */
		var controls = $('.toolbar .pagination');
		// get all controls within pagination toolbar
		pageControls.pCount = controls.children('.page-count');
		pageControls.pNum = controls.children('input');
		pageControls.first = controls.find('.btn.first');
		pageControls.prev = controls.find('.btn.prev');
		pageControls.next = controls.find('.btn.next');
		pageControls.last = controls.find('.btn.last');

		pageControls.first.on('click', function() {
			var el = $(this);
			if(el.hasClass('disabled')) { return false; }
			_paginateJumpto(el.attr('data-page'));
			return false;
		});
		pageControls.next.on('click', function() {
			var el = $(this);
			if(el.hasClass('disabled')) { return false; }
			_paginateJumpto(el.attr('data-page'));
			return false;
		});
		pageControls.prev.on('click', function() {
			var el = $(this);
			if(el.hasClass('disabled')) { return false; }
			_paginateJumpto(el.attr('data-page'));
			return false;
		});
		pageControls.last.on('click', function() {
			var el = $(this);
			if(el.hasClass('disabled')) { return false; }
			_paginateJumpto(el.attr('data-page'));
			return false;
		});
		pageControls.pNum.on('change', function() {
			_paginateJumpto($(this).val());
		});

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

		// trash button event
		tBody.on('click', '.btn-trash', function() {
			var el = $(this), id = el.attr('data-id');
			API.posts().patch({
				id: id,
				status: '0',
				success: function(r) {
					toastr['success'](postType.ucfirst()+' #'+id+' moved to trash');
				},
				complete: function(r) {
					/*var params = window.history.state;
					delete params.meta;
					window.history.replaceState(params, '', Core.changeHash(window.location.hash.substring(1)));*/
					_listPosts(window.history.state, true, true);
				}
			})
			return false;
		});

		// delete button event
		tBody.on('click', '.btn-delete', function() {
			var el = $(this), id = el.attr('data-id');
			API.posts().delete({
				id: id,
				success: function(r) {
					toastr['success'](postType.ucfirst()+' #'+id+' deleted forever');
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
			// if commentStatus is disabled, then send value of '0'
			if(!data.commentStatus) { data.commentStatus = '0'; }
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
			success: function(r) {
				Core.unblock(sections.edit);
				r.data = r.data[0];
				postType = r.data.type;
				$.each(r.data, function(k, v) {
					if(k == 'password' || k == 'commentStatus') { return; }
					post[k].val(v);
				});
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
				tinyMCE.activeEditor.setContent(r.data.content);
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
		post.password = inputs.filter(function(i) {
			return $(this).attr('name') == 'password';
		})

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
		post.password.on('change', function() {
			var el = $(this), txt = el.closest('label').text(), panel = el.closest('.panel');
			if(!el.attr('data-saved')) { txt += ' (Unsaved)'; }
			panel.find('.post_password').text(txt);
			// show/hide custom password field
			if(el.hasClass('custom')) { panel.find('input.custom-password').removeClass('hidden'); }
			else { panel.find('input.custom-password').addClass('hidden'); }
		});

		// when custom password is provided, then update the parent input's value
		post.form.find('input.custom-password').on('change', function() {
			var customPwd = $(this).val();
			if(customPwd) { customPwd = '"'+customPwd+'"'; }
			post.password.filter('.custom').attr('value', customPwd);
		});

		// if post's comment-status is changed, then reflect on the summary panel
		post.commentStatus.on('change', function() {
			var el = $(this), txt = el.prop('checked')? 'Enabled' : 'Disabled', panel = el.closest('.panel');
			panel.find('.post_commentStatus').text(txt);
		});

		// save/preview/pubish buttons
		$('.btn-save').on('click', function() {
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

		// dropzone
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
	},

	gallery,
	_toggleHidden = function(els) {
		$.each(els, function() {
			$(this).toggleClass('hidden');
		})
	},
	_loadGallery = function(data, showFormOnError) {
		data.success = function(r) {
			_populateGallery(r.data);
		}
		data.error = function(r) {
			if(showFormOnError) {
				window.history.replaceState({new: true}, '', Core.changeHash('new'));
				_newMedia();
			}
			else { toastr['error'](r.responseJSON.message); }
		}
		API.assets().get(data);
	},
	_populateGallery = function(data) {
	},
	_newMedia = function() {
		_showDiv('new');
	},
	galleryEvents = function() {
		var trashBtn = $('.gallery-trash'), trashAxns = $('.trash-axn');
		// trash button actions
		trashBtn.click(function() {
			_toggleHidden([trashBtn, trashAxns]);
			gallery.toggleClass('selectable');
		});
		$('.gallery-trash-cancel').click(function() {
			deselectItems();
		});

		gallery.on('click', '.items > li', function() {
			if(!gallery.hasClass('selectable')) { return false; }

			var el = $(this);
			el.toggleClass('selected');
			if(!el.children('.ion').length) { el.append('<i class="ion ion-checkmark-round"></i>'); }
			else { el.children('.ion').remove(); }
		});

		function deselectItems() {
			gallery.find('.items > li').removeClass('selected').children('.ion').remove();
			gallery.removeClass('selectable');
			trashBtn.removeClass('hidden');
			trashAxns.addClass('hidden');
		}

		// add new button
		$('.btn-new').click(function() {
			window.history.pushState({new: true}, '', Core.changeHash('new'));
			_newMedia();
			return false;
		});
	},
	initGallery = function() {
		var hash = window.location.hash,
			params = {};

		switch(hash) {
			case '#new':
				params = {new: true};
				_newMedia();
				break;
			case '':
				params = {type: 'all'};
				window.history.replaceState(params, '', Core.changeHash('type:all'));
				window.url.page = 1;
				_loadGallery(params, true);
				_showDiv('gallery');
				break;
			default:
				hash = hash.substring(1);
				params = hash.jsonify('/',':');
				if(!params.type) { params.type = 'all'; }
				// if viewing particular media
				if(params.id) {
					// _getMedia(params.id);
					// _showDiv('edit', 'Edit '+postType.ucfirst()+' #'+params.id);
					break;
				}
				// if editing
				if(params.type) {
					_loadGallery(params, true);
					_showDiv('gallery');
					break;
				}
				break;
		}

		return params;
	};

	admin.posts = function(type) {
		window.url.reload = false;
		postType = type;
		sections = {list: $('section.posts-list').eq(0), edit: $('section.posts-edit').eq(0)};
		tBody = $('#post-list');
		filters = $('.toolbar .filters');
		post = {form: $('div.process-post')};

		if(window.history.state) { delete window.history.state.meta; }
		listEvents();
		editEvents();
		initPosts();
		Core.multiCheck();
		Core.wysiwyg('.wysiwyg');

		// if history object changes
		window.onpopstate = function(e) {
			// if(e.state) { delete e.state.meta; }
			var url = initPosts();
		};
	};
	admin.media = function() {
		sections = {gallery: $('section.media-gallery').eq(0), new: $('section.media-new').eq(0)};
		gallery = $('.gallery');

		initGallery();
		galleryEvents();
	}
})();
