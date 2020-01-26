"use strict";

(function() {

  var callbacks = null,
  conf = null,
  controls = {},

  _paginateActivate = function(pageNum) {
    controls.pNum.val(pageNum);
    controls.btns.prev.attr('data-page', pageNum - 1);
    controls.btns.next.attr('data-page', pageNum + 1);
    switch(pageNum) {
      case 1:
        controls.btns.first.addClass('disabled');
        controls.btns.prev.addClass('disabled');
        controls.btns.next.removeClass('disabled');
        controls.btns.last.removeClass('disabled');
        break;
      case conf.pageCount:
        controls.btns.first.removeClass('disabled');
        controls.btns.prev.removeClass('disabled');
        controls.btns.next.addClass('disabled');
        controls.btns.last.addClass('disabled');
        break;
      default:
        controls.btns.first.removeClass('disabled');
        controls.btns.prev.removeClass('disabled');
        controls.btns.next.removeClass('disabled');
        controls.btns.last.removeClass('disabled');
        break;
    }
  },
  _paginateJumpto = function(pageNum) {
    pageNum = parseInt(pageNum);
    if((pageNum > conf.pageCount) || (pageNum <= 0)) { return null; }
    _paginateActivate(pageNum);

    if (callbacks.onPageChange != undefined ) { callbacks.onPageChange(pageNum); }
  },
  _paginateSetup = function() {
    // Create HTML
    if(conf.el == undefined) { conf.el = '.pagination'; }
    conf.el = $(conf.el);
    conf.el.html('<input type="text" name="page-num" value=""> of <span class="bold page-count"></span><span class="btn-group"><a class="btn btn-default first" data-page=""><i class="ion ion-skip-backward"></i></a><a class="btn btn-default prev" data-page=""><i class="ion ion-arrow-left-b"></i></a><a class="btn btn-default next" data-page=""><i class="ion ion-arrow-right-b"></i></a><a class="btn btn-default last" data-page=""><i class="ion ion-skip-forward"></i></a></span>');

    // Update conf variables
    conf.itemCount = parseInt(conf.itemCount);
    conf.pageSize = parseInt(conf.pageSize);
    conf.activePage = parseInt(conf.activePage);
    conf.pageCount = Math.ceil(conf.itemCount/conf.pageSize);

    // Scan elements
    controls.btns = {};
    controls.btns.first = conf.el.find('.btn.first');
    controls.btns.prev = conf.el.find('.btn.prev');
    controls.btns.next = conf.el.find('.btn.next');
    controls.btns.last = conf.el.find('.btn.last');
    controls.pNum = conf.el.children('input');

    // Update data attribute for elements
    conf.el.children('.page-count').text(conf.pageCount);
    controls.btns.first.attr('data-page', 1);
    controls.btns.last.attr('data-page', conf.pageCount);

    // show/hide pagination controls
    if(conf.pageCount == 1) { conf.el.addClass('hidden'); }
    else {
      conf.el.removeClass('hidden');
      _paginateActivate(conf.activePage);
    }
  },
  _paginateEvents = function() {
    // Bind button & input events
    $.each(controls.btns, function(k, el) {
      el.on('click', function() {
        // var el = $(this);
        if(el.hasClass('disabled')) { return false; }
        _paginateJumpto(el.attr('data-page'));
        return false;
      });
    })
    controls.pNum.on('change', function() {
      _paginateJumpto($(this).val());
    });
  };

  Core.pagination = function(c, fn) {
    // c is an object with keys: itemCount, activePage & pageSize
    conf = c;
    callbacks = fn;
    _paginateSetup();
    _paginateEvents();
  };
})();
