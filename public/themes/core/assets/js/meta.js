"use strict";
var meta = {};
(function() {
  var conf = {
    el: '',
    id: 0,
    api: '',
    keys: []
  },
  _getElements = function _getElements() {
    if(typeof conf.el == 'string') { conf.el = $(conf.el); }
    var out = {
      btnAdd: conf.el.find('.btn-add'),
      btnBack: conf.el.find('.btn-back'),
      btnSave: null,
      btnCancel: null,
      tBody: conf.el.find('tbody'),
      trTemplate: null,
      trNewMeta: null,
      insKey: null,
      insKeyDD: null
    }
    out.trTemplate = out.tBody.children('tr.template').eq(0);
    out.trNewMeta = out.tBody.children('tr.new-meta');
    out.btnSave = out.trNewMeta.find('.btn-save');
    out.btnCancel = out.trNewMeta.find('.btn-cancel');
    out.insKey = out.trNewMeta.find(':input[name=key]');
    out.insKeyDD = out.trNewMeta.find('select[name=key_dd]');
    return out;
  },
  els = null,
  _meta = {data: {}, method: ''},
  _populateList = function _populateList(reset) {
    var trHTML = els.trTemplate.html(), trTemp;
    if(reset) { els.tBody.children(':not(.template)').remove(); }
    els.tBody.children().addClass('hidden');
    $.each(_meta.data, function(k) {
      trTemp = els.trTemplate.clone();
      trTemp.removeClass('hidden template');
      trTemp.attr('data-key', k);
      trTemp.html(trHTML.replaceMoustache({key: k, value: _meta.data[k]}));
      els.tBody.append(trTemp);
    });
  },
  _onSave = function _onSave() {
    if(_meta.method != 'put' && _meta.method != 'patch') { return false; }

    var data = Core.validateForm(els.trNewMeta);
    if((_meta.method == 'put') && (_meta.data[data.key] != undefined)) {
      toastr['error']('Duplicate Meta Key not allowed');
      return false;
    }
    delete data.key_dd;

    data.complete = function() {
      Core.unblock(els.trNewMeta);
    }
    data.success = function() {
      toastr['success']('Meta updated successfully');
      _meta.data[data.key] = data.value;
      _populateList(true);
      els.btnCancel.trigger('click');
    }
    data.error = function(e) {
      if (_meta.method == 'put') { toastr['error'](e.responseJSON.message); }
    }
    Core.block(els.trNewMeta);
    API._ajax('api/'+conf.api+'/'+conf.id, _meta.method, data);
  },
  _onDelete = function _onDelete(el) {
    var parent = el.closest('tr'),
      key = parent.attr('data-key');

    if(_meta.data[key] == undefined) {
      toastr['error']('Invalid Meta Key');
      return false;
    }
    API._ajax('api/'+conf.api+'/'+conf.id+'?key='+key, 'delete', {
      success: function() {
        toastr['info']('Meta key: "'+key+'" removed');
        delete _meta.data[key];
        parent.remove();
      }
    });
  },
  _getMeta = function _getMeta() {
    API._ajax('api/'+conf.api+'/'+conf.id, 'get', {
      success: function(r) {
        _meta.data = r.data;
      },
      error: function(e) {
        toastr['error']('No meta data found');
        _meta.data = {};
      },
      complete: function() {
        _populateList(true);
      }
    });
  },
  _resetMetaFields = function _resetMetaFields() {
    _meta.method = '';
    if(conf.keys.length) {
      els.insKeyDD.val('').parent().removeClass('hidden');
      els.insKey.addClass('hidden');
    } else {
      els.insKeyDD.val('').parent().addClass('hidden');
      els.insKey.removeClass('hidden');
    }
    els.insKey.val('').prop('readonly', false).removeClass('blocked');
    els.insKeyDD.parent().removeClass('blocked');
    els.trNewMeta.find(':input[name=value]').val('');
  },
  events = function events() {
    els.btnAdd.click(function(e) {
      els.btnCancel.trigger('click');
      _meta.method = 'put';
      els.trNewMeta.removeClass('hidden');
      return false;
    });
    els.btnBack.click(function(e) {
      window.history.back();
      return false;
    });
    els.btnSave.click(function(e) {
      _onSave();
      return false;
    });
    els.btnCancel.click(function(e) {
      var currentKey = els.insKey.val();
      if(currentKey) {
        els.tBody.children('tr[data-key="'+currentKey+'"]').removeClass('hidden');
      }
      _resetMetaFields();
      els.trNewMeta.addClass('hidden');
      return false;
    });
    els.insKeyDD.change(function(e) {
      var inputVal = els.insKeyDD.val();
      if(inputVal == 'other') {
        els.insKey.val('').removeClass('hidden').focus();
        els.insKeyDD.parent().addClass('hidden');
      }
      else { els.insKey.val(inputVal).addClass('hidden'); }
      return false;
    });
    els.tBody.on('click', '.btn-edit', function(e) {
      els.btnCancel.trigger('click');
      _meta.method = 'patch';
      var parent = $(this).closest('tr'),
        key = parent.attr('data-key'),
        isFoundInDD = (conf.keys.length && (conf.keys.columns('value').indexOf(key) != -1));

      Core.populateForm(els.trNewMeta, {
        key: key,
        keyDd: isFoundInDD ? key : 'other',
        value: _meta.data[key]
      });
      els.insKey.prop('readonly', true).addClass('blocked');
      els.insKeyDD.parent().addClass('blocked');
      if(isFoundInDD) {
        els.insKey.addClass('hidden');
        els.insKeyDD.parent().removeClass('hidden');
      }
      else {
        els.insKey.removeClass('hidden');
        els.insKeyDD.parent().addClass('hidden');
      }
      els.trNewMeta.removeClass('hidden');
      parent.addClass('hidden');
      return false;
    });
    els.tBody.on('click', '.btn-delete', function(e) {
      _onDelete($(this));
      return false;
    });
  },
  _populateKeyDD = function _populateKeyDD() {
    els.insKeyDD.html('');
    for (var i = 0, j = conf.keys.length; i < j; i++) {
      els.insKeyDD.append('<option value="'+conf.keys[i].value+'">'+conf.keys[i].label+'</option>');
    }
    els.insKeyDD.append('<option value="other">Other</option>');
  },
  loadConf = function loadConf(c) {
    $.each(c, function(k) {
      if(conf[k] !== undefined) { conf[k] = c[k]; }
    });
    if(!els) { els = _getElements(); }
    if(c.id != undefined) { _getMeta(); }
    if(c.keys != undefined) { _populateKeyDD(); }
  };

  meta.init = function(c) {
    loadConf(c);
    events();
  };
  meta.loadConf = function(c) {
    loadConf(c);
  };
})();
