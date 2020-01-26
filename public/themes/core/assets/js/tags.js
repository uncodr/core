var tags = {};
(function() {
    var els = {
        btnAdd: $('.btn-add'),
        btnCancel: $('.btn-cancel'),
        formAddEdit: $('.add-edit'),
        btnTable: $('.tagslist'),
        tagsTbody: $('.tagslist-body'),
        tagsPagination: $('.tags-paginate')
    },
    /***********************Common function************************************/
    /* Show Tags rows */
    _populateList = function(data, tBody, reset) {
        var template = 'tr.template', trHTML, trTemp;
        template = tBody.children(template).eq(0);
        // if reset is truthy, then create fresh table
        if(reset) { tBody.children( ':not(.template)' ).remove(); }
        tBody.children().addClass('hidden');

        for(var i = 0, l = data.length; i < l; i++) {
            trHTML = template.html();
            trTemp = template.clone();
            trTemp.removeClass('hidden template');
            trTemp.addClass(status+' page-'+data.pageNum);
            trHTML = trHTML.replaceMoustache(data[i]);
            trTemp.html(trHTML);
            tBody.append(trTemp);
        }
        tBody.parent().removeClass('hidden');
    },
    /********************Functions used in Tags Manage start*******************/
    /* Get particular Tag details on edit button */
    evGet = function(id) {
        API._ajax('api/tags/'+id, 'get', {
            success: function(r) {
                Core.populateForm(els.formAddEdit, r.data[0]);
                els.formAddEdit.removeClass('hidden');
                els.btnAdd.addClass('hidden');
                els.btnTable.addClass('hidden');
                els.tagsPagination.addClass('hidden');
            }
        });
    },
    /* Add/Update Tag */
    evAddUpdate = function(selector) {
        var fm = Core.validateForm(selector);
        var reqType = (fm.id) ? 'patch' : 'put';
        fm.complete = function() {
            Core.unblock(selector);
        };
        fm.success = function(r) {
            toastr['success']((reqType == 'put') ? 'Record added successfully' : 'Record updated successfully');
            setTimeout(function() { location.reload(); }, 1000);
            if (reqType == 'put') {
                selector.find('input[name="id"]').val(r.data);
            }
        }
        Core.block(selector);
        API._ajax('api/tags', reqType, fm);
    },
    /* Delete Tag */
    evDelete = function(el) {
        var id = el.attr('data-id'),
        data = {
            success: function() { el.closest('tr').remove(); }
        };
        API._ajax('api/tags/'+id, 'delete', data);
    },
    /* Show Add Tag form */
    evAdd = function(el) {
        el.addClass('hidden');
        els.formAddEdit[0].reset();
        els.formAddEdit.removeClass('hidden');
        els.btnAdd.addClass('hidden');
        els.tagsPagination.addClass('hidden');
    },
    _initPagination = function(meta, params, fn) {
        Core.pagination(
            {
                itemCount: meta.count[params.type], activePage: params.page, pageSize: meta.pageSize
            },
            {
                onPageChange: function(n) {
                    params.page = n;
                    delete params.meta;
                    fn(params, false);
                }
            }
        );
    },
    _getTags = function _getTags(params, initPage) {
        if (els.tagsTbody.find('tr.page-'+params.page).length > 0){
            els.tagsTbody.children().addClass('hidden');
            els.tagsTbody.find('tr.page-'+params.page).removeClass('hidden');
        } else {
            params.success = function(r) {
                r.data.pageNum = params.page;
                _populateList(r.data, els.tagsTbody, false);

                if (initPage) {
                    _initPagination(r.meta, params, _getTags);
                }
            };
            API._ajax('api/tags', 'get', params);
        }
    },
    /* Cancel button to go back to previous page */
    evCancel = function(el) {
        el.removeClass('hidden');
        els.formAddEdit.addClass('hidden');
        els.btnAdd.removeClass('hidden');
        els.tagsPagination.removeClass('hidden');
    },
    /*********************Functions used in Tags Manage end********************/

    /*******************Functions used in tags search start********************/
    conf = {
        el: '',
        getTags: true,
        id: null,
        resource: '',
        selectedTags: []
    },
    _getElements = function _getElements() {
      var out = {
        searchTags: conf.el.find('.search-tags'),
        tagsDD: conf.el.find('.txt-hint'),
        tagsSelected: conf.el.find('.search-ul'),
        tBody: conf.el.find('tbody'),
        saveTags: conf.el.find('.save-search-tag'),
        btnAdd: conf.el.find('.btn-add'),
        btnSave: null,
        trTemplate: null
      }
      out.trTemplate = out.tBody.children('tr.template');
      return out;
    },
    createHtml = function(el) {
        el.append('<p class="label">Manage Tags</p> <ul class="search-ul tag-list list-inline h3"> </ul> <input type="text" name="tag_name" class="full search-tags"> <a class="btn save-search-tag">Save Tag</a> <a class="btn indent-left" href="admin/config/tags">Add Tag</a> <div class="panel txt-hint hidden"> <table> <tbody> <tr class="template hidden"> <td data-id="{{id}}" class="append-tag">{{tagName}}</td> </tr> </tbody> </table> </div>');
    },
    allTags = [],
    getSearchTags = function(callback, param) {
        Core.block(conf.el);
        API._ajax('api/tags', 'get', {
            success: function(r) {
                allTags = r.data;
                if (callback) {callback(param); }
            },
            complete: function() {
                Core.unblock(conf.el);
            }
        });
    };
    getTagsFiltered = function(str) {
        var strData = [], len = allTags.length;

        if(str == "") {
            els.tagsDD.addClass('hidden');
        } else {
            var patt = new RegExp(str, 'i');
            for (var i = 0; i < len; i++) {
                if (patt.test(allTags[i].tagName)) {
                    strData.push(allTags[i]);
                }
            }
            els.tagsDD.removeClass('hidden');
            _populateList(strData, els.tBody, true);
        }
    };
    getTagsByResourceID = function(id) {
        els.tagsSelected.html('');
        els.tagsDD.addClass('hidden');

        API._ajax('api/tags/resource/'+id, 'get', {
            type: conf.resource,
            fields: 'id',
            success: function(r) {
                conf.selectedTags = r.data.columns('id');
                var tempTags = allTags.columns('id');

                for (var i = 0; i < conf.selectedTags.length; i++) {
                    var key = tempTags.indexOf(conf.selectedTags[i]);
                    els.tagsSelected.append('<li data-id="'+conf.selectedTags[i]+'">'+allTags[key].tagName+'</li> ');
                }
            },
            error:function() {}
        });
    };
    /*******************Functions used in tags search end**********************/

    /***********************Tags Manage****************************************/
    /* Show Tags, add, edit & delete Tags */
    tags.init = function(){
        /* Show list of all tags */
        _getTags({page: 1, meta: JSON.stringify({count: true}), type: 'all'}, true);
        /* Get particular Tag details on click of edit btn */
        $('main').on('click', '.btn-edit', function(e){
            e.preventDefault();
            evGet($(this).attr('data-id'));
        });

        /* AJAX request for Add/Edit Tag */
        $('main').on('click', '.btn-save', function(e){
            e.preventDefault();
            evAddUpdate(els.formAddEdit);
        });

        /* Add Tag */
        els.btnAdd.click(function(e){
            e.preventDefault();
            evAdd($('.tagslist'));
        });

        /* Delete Tag */
        $('main').on('click', '.btn-delete', function(e){
            e.preventDefault();
            evDelete($(this));
        });

        /* Cancel button in Add/Edit Tag page */
        els.btnCancel.click(function(e){
            e.preventDefault();
            evCancel($('.tagslist'));
        });
    };

    /*************************Tags Search in Post/Article**********************/
    tags.search = function(el, resource) {
        conf.el = (typeof el == 'string') ? $(el) : el;
        createHtml(conf.el);
        conf.resource =  resource;
        // overwrite els var
        els = _getElements();
        // Get all tags on page load
        getSearchTags();

        /* Get particular Tag details on click of edit btn */
        els.searchTags.on('keyup', function(e){
            e.preventDefault();
            var el = $(this);
            if (conf.getTags) {
                getSearchTags(getTagsFiltered, el.val());
                conf.getTags = false;
            } else {
                getTagsFiltered(el.val());
            }
        });

        els.tagsSelected.on('click', 'li', function(e){
            var el = $(this), key = conf.selectedTags.indexOf(el.attr('data-id'));
            conf.selectedTags.splice(key, 1);
            el.remove();
        });

        els.tBody.on('click', 'td', function(e){
            e.preventDefault();
            var el = $(this), id = el.attr('data-id');
            if (!el.hasClass('template') && conf.selectedTags.indexOf(id) == -1) {
                els.tagsSelected.append('<li data-id="'+id+'">'+el.text()+'</li> ');
                conf.selectedTags.push(id);
            }
        });

        els.saveTags.on('click', function(e) {
            e.preventDefault();
            if (conf.id) {
                API._ajax('api/tags/resource', 'patch', {
                    id: conf.id,
                    type: conf.resource,
                    tagID: conf.selectedTags,

                    success: function(r) {
                        toastr['success']('Tags added successfully');
                    }
                });
            }
        });
    };

    tags.reset = function() {
        els.tagsDD.addClass('hidden');
        data = []; els.searchTags.val(''); els.tagsSelected.html('');
    };

    tags.changeID = function (id) {
        conf.id = id;
        if (allTags.length) {
            getTagsByResourceID(id);
        } else {
            getSearchTags(getTagsByResourceID, id);
        }
    };
})();
