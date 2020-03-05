"use strict";
var meta = {};
(function() {
  var conf = {
    el: '', // element selector where meta table is loaded
    id: 0, // resource id that will be concatenated in the api requests
    api: '', // api url where get, patch, put, post and delete requests will be sent
    keys: [], // keys to be shown in the dropwdown while adding new meta
    data: null, // initial data (used to reset)
    _: 0 // permissions
  },
  _getElements = function() {
    if(typeof conf.el == 'string') { conf.el = $(conf.el); }
    var out = {
      btnAdd: conf.el.find('.btn-add'),
      btnSave: null,
      tBody: conf.el.find('tbody'),
      trNewMeta: null
    }
    out.btnSave = out.tBody.prev().find('.btn-save');
    out.trNewMeta = out.tBody.children('tr.new-meta');
    return out;
  },
  els = null,
  _meta = {data: {}, editCount: 0, isModified: false},
  _populateListConf = {
    vars: {
      value: {type: 'stringify'},
      key2: {type: 'stringify', key: 'key'}
    }, attrs: ['key2', 'type']
  },
  _updateEditCount = function(val = 0, isModified = false) {
    if(isModified) { _meta.isModified = true; }
    else {
      _meta.editCount += val;
      _meta.isModified = !!_meta.editCount;
    }

    if(_meta.isModified) { els.btnSave.removeClass('hidden'); }
    else { els.btnSave.addClass('hidden'); }
  },
  _populateList = function() {
    els.tBody.children(':not(.template, .placeholder)').remove();
    _recursiveFill(els.tBody, els.tBody.children('tr.template').eq(0), _meta.data, _populateListConf);
    els.btnSave.addClass('hidden');
    modifyPermButtons();
  },
  _recursiveFill = function(tBody, template, data, conf) {
    var trTemp, type;
    $.each(data, function(k, v) {
      type = Core.helpers.getType(v);
      trTemp = template.clone();
      trTemp.removeClass('hidden template');
      if(!isNaN(k)) { ++k; }
      tBody.append(trTemp);
      var key = tBody.attr('class');
      key = ((key != undefined))? key.substr(6) : '';
      switch(type) {
        case 'arr': case 'obj':
          trTemp.addClass('has-child');
          trTemp.find('.btn-edit').html('<i class="ion ion-chevron-down"></i>');
          key += k+'-';
          $(_createTable(key)).insertAfter(trTemp);
          _recursiveFill(trTemp.next().find('tbody.child-'+key), template, v, conf);
          break;
      }
      conf.vars.key2.prepend = key;
      admin.fill(trTemp, {key: k, value: v, type: type}, conf);
    });
    return trTemp;
  },
  _createTable = function(key) {
    var title = key.split('-'), l = title.length;
    title = title[l-2];
    return '<tr class="hidden child"><td colspan="3"><span class="bold clearfix">'+title+' <span class="btn-group"><a class="btn-cancel btn btn-default" title="Cancel"><i class="ion ion-close-round"></i></a><a class="btn-add btn btn-default" title="Add"><i class="ion ion-plus-round"></i></a><a class="btn-delete btn btn-default danger" title="Delete"><i class="ion ion-trash-a"></i></a></span></span><table class="meta-table panel hover bordered all"><tbody class="child-'+key+'"></tbody></table></td></tr>';
  },
  modifyPermButtons = function() {
    var btns = els.tBody.find('.btn-delete, .btn-copy, .btn-add, tr:not(.has-child) > td > .btn-group .btn-edit');
    switch(conf._) {
      case 0: case 1:
        els.btnAdd.addClass('hidden');
        btns.addClass('hidden');
        break;
      default:
        els.btnAdd.removeClass('hidden');
        btns.removeClass('hidden');
        break;
    }
  },
  _newRow = function(el, data = null, disable = false) {
    var trTemp = els.trNewMeta.clone(), m = 'put';
    trTemp.removeClass('hidden template new-meta');
    if(data && disable) {
      m = 'patch';
      trTemp.attr('data-key2', data.key);
      data.keyDd = _toggleKeyDD(data.key, trTemp, true);
      // trTemp.find('.btn-save').remove();
      trTemp.find(':input[name="value"]').removeClass('hidden');
    } else {
      if(!data) { data = (conf.keys.length)? {key: conf.keys[0].value, type: conf.keys[0].type} : {type: ''}; }
      _toggleKeyDD(data.key, trTemp, disable);
    }
    Core.populateForm(trTemp, data);
    trTemp.attr('data-method', m);
    el.after(trTemp);
    // _updateEditCount(1);
  },
  _onCancel = function(row) {
    if(row.hasClass('child')) {
      row.addClass('hidden');
      row.prev().removeClass('hidden');
    }
    else {
      if(row.attr('data-method') == 'patch') {
        row.closest('tbody').children('tr[data-key2="'+row.attr('data-key2')+'"]').removeClass('hidden');
      }
      row.remove();
      // _updateEditCount(-1);
    }
  },
  _onCopy = function(row) {
    var conf = _populateListConf,
      key = row.attr('data-key2'),
      tempKey = key.split('-'),
      tempData = _metaVal(key),
      data = {};

    tempKey.pop();
    tempKey = tempKey.join('-');
    conf.vars.key2.prepend = tempKey;
    data[tempData.key] = tempData.value;
    var newRow = _recursiveFill(row.closest('tbody'), els.tBody.children('tr.template').eq(0), data, conf);
    newRow.find('.btn-edit').trigger('click');
    if(newRow.hasClass('has-child')) { newRow.next().find('.btn-edit').trigger('click'); }
    // _updateEditCount(0, true);
  },


  _onDelete = function _onDelete(el) {
    var parent = el.closest('tr'),
      key = parent.attr('data-key2');

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
  _metaVal = function _metaVal(params) {
    if(typeof params == 'string') {
      var arr = params.split('-'),
        isNum = false,
        o = {key: params, value: _meta.data, type: 'str'},
        l = arr.length;
      if(arr[l-1]==='') {
        arr.pop();
        --l;
      }
      for (var i = 0; i < l; i++) {
        if(isNum) { arr[i] = parseInt(arr[i])-1; }
        o.value = o.value[arr[i]];
        o.type = Core.helpers.getType(o.value);
        isNum = o.type == 'arr';
      }
      return o;
    } else {
      /*var schema = _meta.data;  // a moving reference to internal objects within obj
      var pList = params.key.split('-');
      var len = pList.length;
      for(var i = 0; i < len-1; i++) {
          var elem = pList[i];
          if( !schema[elem] ) schema[elem] = {}
          schema = schema[elem];
      }

      schema[pList[len-1]] = value;*/

      var arr = params.key.split('-'),
        isNum = false,
        o = _meta.data,
        l = arr.length;
      if(arr[l-1]==='') {
        arr.pop();
        --l;
      }
      for(var i = 0; i < l-1; i++) {
        if(isNum) { arr[i] = parseInt(arr[i])-1; }
        if(o[arr[i]] == undefined) {
          o[arr[i]] = {key: arr[i]};
          break;
        }
        else {
          o = o[arr[i]];

          o.type = Core.helpers.getType(o.value);
          isNum = o.type == 'arr';
        }
      }
    }
  },
  _toggleKeyDD = function _toggleKeyDD(key, row, block = false) {
    var isFoundInDD = (key && conf.keys.length && (conf.keys.columns('value').indexOf(key) != -1)),
      ins = row.find(':input[name=key]'), insDD = row.find('select[name=key_dd]');
    if(block) {
      ins.prop('readonly', true);
      ins.parent().addClass('blocked');
      var typ = row.find(':input[name=type]');
      typ.prop('readonly', true);
      typ.parent().addClass('hidden');
    }
    row.find(':input[name=type]').val(insDD.find('option:selected').attr('data-type'));
    if(isFoundInDD) {
      ins.addClass('hidden');
      insDD.parent().removeClass('hidden');
      ins.val(key);
      return key;
    } else {
      ins.removeClass('hidden');
      insDD.parent().addClass('hidden');
      ins.val('').focus();
      return 'other';
    }
  },
  _onDone = function _onDone(el) {
    var row = el.closest('tr'), data = Core.validateForm(row), m = row.attr('data-method');
    if((m != 'put' && m != 'patch') || !data) { return false; }
    if(m == 'put' && _meta.data[data.key] != undefined) {
      toastr['error']('Duplicate Key name not allowed');
      return false;
    }
    delete data.key_dd;

    console.log(data);

    /*data.complete = function() { Core.unblock(row); }
    data.success = function() {
      _onCancel(row);
      toastr['success']('Meta updated successfully');
      _meta.data[form.key] = Core.helpers.parse(form.value, form.type);
      _populateList();
    }
    data.error = function(e) {
      if (m == 'put') { toastr['error'](e.responseJSON.message); }
    }
    Core.block(row);
    API._ajax('api/'+conf.api+'/'+conf.id, m, data);*/
  },
  _onSave = function() {
    var form = {put: [], patch: []};
    $.each(els.tBody.find('tr'), function() {
      var row = $(this);
      var m = row.attr('data-method');
      if(m) {
        var d = Core.validateForm(row)
        if(d) {
          delete d.key_dd;
          d.value = Core.helpers.parse(d.value, d.type);
          if(_meta.data[d.key] != d.value) { form[m].push(d); }
        }
      }
    });
    console.log(form);
    /*var row = el.closest('tr'), form = Core.validateForm(row), m = row.attr('data-method');
    if((m != 'put' && m != 'patch') || !form) { return false; }
    if(m == 'put' && _meta.data[form.key] != undefined) {
      toastr['error']('Duplicate Meta Key not allowed');
      return false;
    }
    delete form.key_dd;

    var data = {data: [form]};

    data.complete = function() { Core.unblock(row); }
    data.success = function() {
      toastr['success']('Meta updated successfully');
      _meta.data[form.key] = Core.helpers.parse(form.value, form.type);
      _populateList();
    }
    data.error = function(e) {
      if (m == 'put') { toastr['error'](e.responseJSON.message); }
    }
    Core.block(row);
    API._ajax('api/'+conf.api+'/'+conf.id, 'post', data);*/
  },
  _getMeta = function _getMeta() {
    API._ajax('api/'+conf.api+'/'+conf.id, 'get', {
      success: function(r) {
        conf.data = r.data;
        _meta.data = r.data;
      },
      error: function(e) {
        toastr['error']('No meta data found');
        _meta.data = {};
      },
      complete: function() { _populateList(); }
    });
  },
  events = function events() {
    els.btnAdd.click(function(e) {
      e.preventDefault();
      _newRow(els.trNewMeta);
    });
    els.tBody.on('click', '.btn-cancel', function(e) {
      e.preventDefault();
      _onCancel($(this).closest('tr'));
    });
    els.tBody.on('click', '.btn-edit', function(e) {
      e.preventDefault();
      var parent = $(this).closest('tr'), key = parent.attr('data-key2');
      if(parent.hasClass('has-child')) {
        parent.next().removeClass('hidden');
      } else {
        _newRow(parent, _metaVal(key), true);
      }
      parent.addClass('hidden');
    });
    els.tBody.on('click', '.btn-copy', function(e) {
      e.preventDefault();
      _onCopy($(this).closest('tr'));
    });



    els.tBody.on('click', '.btn-delete', function(e) {
      e.preventDefault();
      _onDelete($(this));
    });
    els.btnSave.click(function(e) {
      e.preventDefault();
      _onSave();
    })
    els.tBody.on('change', 'select[name=key_dd]', function(e) {
      e.preventDefault();
      var el = $(this);
      _toggleKeyDD(el.val(), el.closest('tr'), false);
    });
    els.tBody.on('click', '.btn-done', function(e) {
      e.preventDefault();
      _onDone($(this));
    });
  },
  _populateKeyDD = function _populateKeyDD() {
    var insKeyDD = els.trNewMeta.find('select[name=key_dd]');
    insKeyDD.html('');
    for (var i = 0, j = conf.keys.length; i < j; i++) {
      insKeyDD.append('<option value="'+conf.keys[i].value+'" data-type="'+conf.keys[i].type+'">'+conf.keys[i].label+'</option>');
    }
    insKeyDD.append('<option value="other" data-type="">Other</option>');
  },
  loadConf = function loadConf(c) {
    $.each(c, function(k) {
      if(conf[k] !== undefined) { conf[k] = c[k]; }
    });
    if(!els) { els = _getElements(); }
    if(c.keys != undefined) { _populateKeyDD(); }
    if(c.data != undefined) {
      _meta.data = c.data;
      _populateList();
    }
    else if(c.id != undefined) { _getMeta(); }
  };

  meta.init = function(c) {
    loadConf(c);
    events();
    if(c.keys == undefined) { els.trNewMeta.find('select[name=key_dd]').parent().remove(); }
  };
  meta.loadConf = function(c) { loadConf(c); };
})();
