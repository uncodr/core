"use strict";

(function() {

	// sets slug to blank and hides it
	var sections, gallery, dropzone,
	_showDiv = function(type, title) {
		if(title) { sections[type].children('.page-heading:first-child').children('h1').text(title); }
		$.each(sections, function(i) {
			if(i==type) { sections[i].hide().removeClass('hidden').fadeIn(); }
			else { sections[i].addClass('hidden'); }
		});
	},
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
		API.media().get(data);
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
		dropzone.on('click','.btn-save',function() {
			getUploadPath();
		});

		gallery.on('click', '.items > li', function() {
			if(!gallery.hasClass('selectable')) { return false; }

			var el = $(this);
			el.toggleClass('selected');
			if(!el.children('.ion').length) { el.append('<i class="ion ion-checkmark-round"></i>'); }
			else { el.children('.ion').remove(); }
		});

		function getUploadPath() {
			var t = setTimeout(function() {
				if(dropzone.hasClass('done')) {
					sections.new.find('.upload-path').html(dropzone.children(':input[name="dz-path"]').val());
				} else {
					getUploadPath();
				}
				clearTimeout(t);
			}, 10);
		}

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

	admin.media = function() {
		sections = {gallery: $('section.media-gallery').eq(0), new: $('section.media-new').eq(0)};
		dropzone = sections.new.find('.dropzone');
		gallery = $('.gallery');

		initGallery();
		galleryEvents();
	}
})();
