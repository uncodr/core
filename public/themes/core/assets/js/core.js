"use strict";

/*
 * Dependency: [
 *	'../third_party/jquery-2.1.1.min.js',
 *	'00-common.js'
 * ]
 */
// Extending jQuery:
// - resizing
(function($) {
	// Debounce Function from John Hann
	// http://unscriptable.com/2009/03/20/debouncing-javascript-methods/
	var debounce = function(func, t, execAsap) {
		var timeout;
		return function debounced() {
			var obj = this, args = arguments;
			if(timeout) { clearTimeout(timeout); }
			else if(execAsap) { func.apply(obj, args); }
			timeout = setTimeout(function() {
				if(!execAsap) { func.apply(obj, args); }
				timeout = null;
			}, t || 100);
		};
	};
	// smartresize
	$.fn.extend({
		espressoResize: function(fn) {
			return fn ? this.on('resize', debounce(fn)) : this.trigger('espressoResize');
		}
	});
})($);
/*
 * replace values in handlebars
 * Usage: HTMLstring.replaceMoustache([JSON object] data);
 */
String.prototype.replaceMoustache = function(obj) {
	return this.replace(
		new RegExp('{{'+Object.keys(obj).join('}}|{{')+'}}', 'gi'),
		function(key) {
			key = key.replace(/{{/g,'').replace(/}}/g,'');
			return obj[key];
		}
	);
};
/*
 * capitalize first letter
 * Usage: string.ucfirst();
 */
String.prototype.ucfirst = function() {
	var str = this;
	return (str.substring(0,1).toUpperCase() + str.substring(1));
};

/*
 * convert any string to camelCase. Any character except letters and digits is used to split the string and capitalize the first letter of each chunk
 * Usage: string.toCamelCase();
 */
String.prototype.toCamelCase = function() {
	var str = this, l = 0;
	str = str.replace(/([^a-zA-Z\d\.]+)/g,':::').split(':::');
	l = str.length;
	for(var i = 0; i < l; i++) {
		str[i] = str[i].slice(0,1).toUpperCase() + str[i].slice(1,str[i].length);
	}
	str = str.join('');
	return str.slice(0,1).toLowerCase() + str.slice(1,str.length);
};

/*
 * convert any camelCase string to snake_case
 * Usage: string.toSnakeCase();
 */
String.prototype.toSnakeCase = function() {
	var str = this, l = 0;
	str = str.replace(/([^\w\d\.]+)/g, '');
	str = str.replace(/([A-Z\d\.]+)/g,"_$1");
	return str.toLowerCase();
};

/*
 * prepend '0' at the beginning of an integer to get a string of 'l' length
 * Usage:
 * 	string = '56'
 * 	string.pad(3); // string = 056
 */
Number.prototype.pad = function pad(l) {
	var str = ''+this;
	while (str.length < l) { str = '0' + str; }
	return str;
}

/*
 * prepend '0' at the beginning of an integer to get a string of 'l' length
 * Usage:
 * 	string = '56'
 * 	string.pad(3); // string = 056
 */
Array.prototype.columns = function columns(str) {
	var out = [];
	for (var i = this.length - 1; i >= 0; i--) {
		out[i] = this[i][str];
	}
	return out;
}

/*
 * convert search params (key1=val1&key2=val2) to json
 * Usage: string.jsonify('&','=');
 */
Array.prototype.postify = function() {
	var o = {}, str = this, type = '', re = /\[([^\]]+)\]|\[\]/;
	for(var i = 0, l = str.length; i < l; i++) {
		type = 'string';
		// key
		var key = re.exec(str[i].name);
		if(key) {
			str[i].name = str[i].name.replace(re,'');
			switch(key[0]) {
				case '[]':
					type = 'array';
					break;
				default:
					type = 'object';
					key = key[1];
			}
		}
		// create output json object
		switch(Object.prototype.toString.call(o[str[i].name])) {
			case '[object Undefined]':
			case '[object Null]':
				switch(type) {
					case 'array':
						o[str[i].name] = [str[i].value]; break;
					case 'object':
						o[str[i].name] = {};
						o[str[i].name][key] = str[i].value; break;
					default:
						o[str[i].name] = str[i].value;
				}
				break;
			case '[object Array]':
				o[str[i].name].push(str[i].value);
				break;
			case '[object Object]':
				o[str[i].name][key] = str[i].value;
				break;
			default:
				o[str[i].name] = [o[str[i].name], str[i].value];
				break;
		}
	}
	return o;

}

/*
 * convert search params (key1=val1&key2=val2) to json
 * Usage: string.jsonify('&','=');
 */
String.prototype.jsonify = function(divider,keySeparator) {
	var o = {}, str = this, type = '', re = /\[([^\]]+)\]|\[\]/;
	str = str.split(divider);
	for(var i = 0, l = str.length; i < l; i++) {
		type = 'string';
		str[i] = decodeURIComponent(str[i].replace(/\+/g, ' '));
		str[i] = str[i].split(keySeparator);
		// value
		switch(str[i][1]) {
			case 'true': str[i][1] = true; break;
			case 'false': str[i][1] = false; break;
			case undefined: str[i][1] = ''; break;
		}
		// str[i][1] = (str[i][1])? decodeURIComponent(str[i][1].replace(/\+/g, ' ')) : '';
		// key
		var key = re.exec(str[i][0]);
		if(key) {
			str[i][0] = str[i][0].replace(re,'');
			switch(key[0]) {
				case '[]':
					type = 'array';
					break;
				default:
					type = 'object';
					key = key[1];
			}
		}
		// create output json object
		switch(Object.prototype.toString.call(o[str[i][0]])) {
			case '[object Undefined]':
			case '[object Null]':
				switch(type) {
					case 'array':
						o[str[i][0]] = [str[i][1]]; break;
					case 'object':
						o[str[i][0]] = {};
						o[str[i][0]][key] = str[i][1]; break;
					default:
						o[str[i][0]] = str[i][1];
				}
				break;
			case '[object Array]':
				o[str[i][0]].push(str[i][1]);
				break;
			case '[object Object]':
				o[str[i][0]][key] = str[i][1];
				break;
			default:
				o[str[i][0]] = [o[str[i][0]], str[i][1]];
				break;
		}
	}
	return o;
};
/*
 * convert json object to string of desired format
 * each key in json object should be string or integer
 * each value in json object can be string/integer/array ONLY
 * Usage: JSONdata.stringify('&','=');
 * http://stackoverflow.com/questions/21729895/jquery-conflict-with-native-prototype
 */
Object.defineProperty(Object.prototype, 'compare', {
	value: function(obj2) {
		var obj = this, type = '', keys2 = [], i;
		for(var y in obj2) { keys2.push(y); }
		for(var x in obj) {
			// if key does not exist in obj2
			if(obj2[x] == undefined) { return false; }
			// if types do not match
			type = Object.prototype.toString.call(obj[x]);
			if(type != Object.prototype.toString.call(obj2[x])) { return false; }
			// compare value
			switch(type) {
				case '[object Array]':
				case '[object Object]':
					if(obj[x].compare(obj2[x]) == false) { return false; }
					break;
				default:
					if(obj[x] != obj2[x]) { return false; }
					break;
			}
			i = keys2.indexOf(x);
			keys2.splice(i, 1);
		}
		return (keys2.length === 0);
	},
	enumerable: false
});

var Core = function() {
	var wysiwyg = function(element, options = {}) {
		tinymce.init({
			selector: element,
			height: options.height || 340,
			menubar: false,
			toolbar: 'styleselect | bold italic strikethrough | alignleft aligncenter alignright | bullist numlist blockquote | link removeformat',
			plugins : 'advlist autolink link lists wordcount visualblocks',
			elementpath: false,
			style_formats: [
				{
					title: 'Elements',
					items: [
						{ title: 'Paragraph', format: 'p' },
						{ title: 'Header 1', format: 'h1' },
						{ title: 'Header 2', format: 'h2' },
						{ title: 'Header 3', format: 'h3' },
						{ title: 'Header 4', format: 'h4' },
						{ title: 'Header 5', format: 'h5' },
						{ title: 'Header 6', format: 'h6' },
						{ title: 'Preformatted', format: 'pre' }
					]
				}, {
					title: 'Font Size',
					items: [
						{ title: 'Small', inline: 'span', styles: { fontSize: '0.8em', 'font-size': '0.8em' } },
						{ title: 'Normal', inline: 'span', styles: { fontSize: '1em', 'font-size': '1em' } },
						{ title: 'Large', inline: 'span', styles: { fontSize: '1.5em', 'font-size': '1.5em' } },
						{ title: 'X-Large', inline: 'span', styles: { fontSize: '2em', 'font-size': '2em' } }
					]
				}, {
					title: 'Blocks',
					items: [
						{ title: 'Section', block: 'section', wrapper: true, merge_siblings: false },
						{ title: 'Div', block: 'div', wrapper: true, merge_siblings: false },
						{ title: 'Header', block: 'header', wrapper: true, merge_siblings: false },
						{ title: 'Footer', block: 'footer', wrapper: true, merge_siblings: false }
					]
				}
			],
			end_container_on_empty_block: true
			/*setup: function(editor) {
				editor.on('change', function() {
					editor.triggerSave();
				});
				editor.on('init', function () {
				});
				// don't forget to add 'sizebutton' to the toolbar
				editor.addButton('sizebutton', {
					type: 'menubutton',
					text: 'Size',
					menu: [
						{text: 'Small', onclick: function() {
							editor.insertContent('<span style="font-size: 0.8em;">');
						}}
					]
				});
			}*/
		});
	},
	multiCheck = function(incHidden) {
		var checks = [], ins, insAll;
		$('body').off('change').on('change', '.multicheck', function() {
			var el = $(this),
				group = el.attr('data-group'), // current checkbox's group name
				status = !!(el.prop('checked')), // status of current checkbox
				axns, l;

			ins = (group)? '[data-group="'+group+'"]' : '';
			ins = $('body .multicheck'+ins);
			// exclude hidden inputs
			if(!incHidden) { ins = ins.filter(function(i) { return !$(this).closest('.hidden').length; }); }
			insAll = ins.filter('.all'); // checkboxes that toggle all
			axns = insAll.eq(0).attr('data-axn'); // action buttons' classname
			l = ins.length - insAll.length; // number of checkboxes in the group

			// set the counter for current group as 0
			if(typeof checks[group] == 'undefined') { checks[group] = 0; }
			// if current checkbox toggles all
			if(el.hasClass('all')) {
				checks[group] = (status)? l : 0;
				ins.prop('checked', status);
			}
			else { checks[group] += (status)? 1 : -1; }

			if(!checks[group]) {
				insAll.prop('checked', false).removeClass('part');
				$('.'+axns).addClass('hidden');
			} else {
				if(checks[group]==l) { insAll.prop('checked', true).removeClass('part'); }
				else { insAll.prop('checked', false).addClass('part'); }
				$('.'+axns).removeClass('hidden');
			}
		});
	},
	// convert form data to JSON
	validateForm = function(form) {
		var valid = true, el;
		// get inputs within the form
		form = form.find(':input:not(:button)');
		// check whether required inputs have value
		$.each(form, function(i) {
			el = form.eq(i);
			if(el.prop('required') && el.val()==='') {
				valid = false;
				el.focus();
				Core.tempClass(el, 'shake-xy');
				return false;
			}
		});
		// return (valid)? form.serialize().jsonify('&', '=') : null;
		return (valid)? $(form).serializeArray().postify() : null;
	},
	_updateJsonField = function(el, data, key) {
		$.each(data, function(k, v) {
			_updateField(el.find(':input[name="'+key+'['+k+']"]'), v);
		});
	},
	_updateField = function(el, v) {
		switch(el.attr('type')) {
			case 'radio': case 'checkbox':
				var elNew = el.filter(function() {
					return ($(this).attr('value') == v);
				});
				if(!elNew.length && el.attr('type')=='radio') {
					// select last input (custom field)
					elNew = el.last();
					elNew.attr('value', v);
					elNew.parent().next().val(v);
				}
				elNew.prop('checked', true);
				break;
			default:
				el.val(v);
				break;
		}
	},
	// submit form via ajax
	ajaxSubmit = function(form, obj = {}) {
		// obj: {success: function(r) { }, error: function(r) { }}
		var data = validateForm(form);
		if(data) {
			Core.block(form.parent());
			var param = {
				url: obj.action || form.attr('data-action'),
				type: obj.method || form.attr('method'),
				processData: false,
				dataType: 'json',
				data: JSON.stringify(data),
				success: obj.success || function(r) {
					// form.trigger('reset');
					var redirect = form.attr('data-redirect');
					if(redirect) {
						if(redirect.substring(0,1) != '/') { redirect = '/'+redirect; }
						window.location = window.url.base+redirect;
					}
					else {
						toastr['success']('Form submitted successfully.');
						Core.unblock(form.parent());
					}
				},
				error: obj.error || function(r) {
					Core.tempClass(form, 'shake-xy');
					Core.unblock(form.parent());
					toastr['error'](r.responseJSON.message);
				}
			};
			$.ajax(param);
			return true;
		}
		return false;
	},
	parseURL = function(wrtRoot) {
		var loc = window.location, o = {}, l;
		o.base = $('base').attr('href') || loc.origin;
		l = o.base.length;
		// if last character of base url is '/', then remove it
		if(o.base.substring(l - 1) == '/') { o.base = o.base.substring(0, l - 1); }
		// get the path name
		o.base = o.base.replace(loc.origin, '');
		o.pathname = (wrtRoot)? loc.pathname : loc.pathname.substring(o.base.length);
		// get params
		var params = loc.search.replace(/\?/g,'');
		o.search = (params)? params.jsonify('&', '=') : null;
		return o;
	},
	// convert object to query string: key1/val1/key2/val2
	objToParams = function(obj) {
		var q = '?';
		for(var x in obj) {
			if(!obj.hasOwnProperty(x)) { continue; }
			q += x+'='+obj[x]+'&';
		}
		return q.substring(0, q.length - 1);
	},
	tabs = function() {
		if(window.location.hash) {
			var el = $('.tab-links[data-href="'+window.location.hash+'"]');
			if(el.length) { showTab(el); }
		}
		$('.tab-links').click(function(e) {
			showTab($(this));
			e.preventDefault();
		});

		function showTab(el) {
			if(el.hasClass('active')) { return; }

			var group, target;
			group = el.attr('data-group') || '.tab-links';
			target = el.attr('data-target').split(':');
			$(group).removeClass('active');
			el.addClass('active');
			$(target[0]).addClass('hidden');
			$(target[1]).removeClass('hidden');
			target = el.attr('data-href');
			if(target) { window.location.hash = target; }
		}
	},
	randomInt = function(min, max) {
		min = Math.ceil(min);
		max = Math.floor(max);
		return Math.floor(Math.random() * (max - min)) + min;
	},
	randomArray = function(length) {
		var out = [];
		for(var i = 0; i < length; i++) {
			out[i] = String.fromCharCode(randomInt(33,126));
		}
		return out;
	};

	return {
		// check required fields and convert form data to JSON
		validateForm: function(el) { return validateForm(el); },
		populateForm: function(el, data, jsonFields = []) {
			// update form fields
			$.each(data, function(k, v) {
				if(jsonFields && (jsonFields.indexOf(k) != -1)) { _updateJsonField(el, data[k], k); }
				else { _updateField(el.find(':input[name='+k.toSnakeCase()+']'), v); }
			});
		},
		// submit form via AJAX
		// obj: {success: callback on success, error: callback on error}
		ajaxSubmit: function(form, obj) { ajaxSubmit(form, obj); },
		resetForm: function(form, hidden) {
			// var ins = form.find(':input:not(:button)').filter(function() { return $(this).attr('type') != 'hidden'; });
			var ins = form.find(':input:not(:button,[type="hidden"])');
			ins.filter(':not([type="checkbox"],[type="checkbox"])').val('');
			ins.filter('[type="checkbox"],[type="checkbox"]').prop('checked',false);
			if(hidden) {
				hidden = hidden.join('"],input[name="');
				hidden = 'input[name="'+hidden+'"]';
				form.find(hidden).val('');
			}
		},
		changeHash: function(hash) {
			return window.url.base+window.url.pathname+'#'+hash;
		},
		encrypt: function(n) {
			var l = n.length, h = randomArray(4*l), k;
			n = n.match(/.{1,1}/g);
			// 0, 5, 10, 15, 16, 21, 26, 31, 32, 37, 42, 47, 48
			for(var i = 0; i < l; i++) {
				k = 5*i - 4*Math.floor(i/4);
				h[k] = n[i];
			}
			return h.join('');
		},
		decrypt: function(h) {
			var l = h.length/4, n = [], k;
			h = h.match(/.{1,1}/g);
			for(var i = 0; i < l; i++) {
				k = 5*i - 4*Math.floor(i/4);
				n[i] = h[k];
			}
			return n.join('');
		},
		// show loading bar [blockUI]
		block: function(el) {
			var html = '<div class="loading';
			html += (el.width() > 480)? ' big' : '';
			html += '"></div>';
			el.append(html);
		},
		// show loading bar [blockUI]
		unblock: function(el) {
			el.children('.loading').remove();
			return false;
		},
		tempClass: function(el, className, t) {
			if(!t) { t = 1; }
			el.addClass(className);
			setTimeout(function() { el.removeClass(className); }, 1000*t);
		},
		objectToEpoch: function(obj, centuryNum) {
			var F = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
				l = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
				keys = ['Y', 'n', 'j', 'G', 'i', 's'], out = {}, dt = new Date();

			out.Y = dt.getFullYear();
			out.n = dt.getMonth();
			out.j = dt.getDate();
			out.G = 23;
			out.i = 59;
			out.s = 59;
			/*out.G = dt.getHours();
			out.i = dt.getMinutes();
			out.s = dt.getSeconds();*/
			$.each(obj, function(k, v) {
				if((k != 'a') && (k != 'A')) { v = parseInt(v); }
				if(k == 'n') { out.n = v-1; }
				else if(keys.indexOf(k) != -1) { out[k] = v; }
				else {
					switch(k) {
						case 'd':
							out.j = v;
							break;
						case 'F':
							var key = F.indexOf(v);
							if(key != -1) { out.n = key; }
							break;
						case 'M':
							var M = [];
							for (var i = F.length - 1; i >= 0; i--) { M[i] = F[i]; }
							var key = M.indexOf(v);
							if(key != -1) { out.n = key; }
							break;
						case 'm':
							out.n = v-1;
							break;
						case 'y':
							if(!centuryNum) {
								centuryNum = Math.floor(out.Y/100);
							}
							out.Y = centuryNum*100 + v;
							break;
						case 'g': case 'h':
							var pm = (obj.a || obj.A);
							if(pm != undefined) {
								out.G = v;
								if(v < 12 && pm.toLowerCase() == 'pm') { out.G += 12; }
							}
							break;
						case 'H':
							out.G = v;
							break;
						case 'w': case 'l': case 'D':
							break;
					}
				}
			});
			return (new Date(out.Y, out.n, out.j, out.G, out.i, out.s)).getTime() / 1000;
		},
		parseDate: function(str, format, centuryNum) {
			if(!str || !format) { return null; }
			var reg = /[^\w\d]+/g, temp = {};

			if(format.match(reg).join() != str.match(reg).join()) { return null; }
			else {
				format = format.split(reg);
				str = str.split(reg);
				for (var i = format.length - 1; i >= 0; i--) {
					temp[format[i]] = str[i];
				}
			}
			return Core.objectToEpoch(temp, centuryNum);
		},
		getDate: function(d, format) {
			if(!d) { return '--'; }
			var F = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
				l = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
				dt = new Date(0),
				out = {};
			dt.setUTCSeconds(parseInt(d));
			out.j = dt.getDate();		// 1 to 31
			out.d = out.j.pad(2);		// 01 to 31
			out.w = dt.getDay();		// 0 to 6
			out.l = l[out.w];			// Sunday to Saturday
			out.D = out.l.substring(0,3);	// Sun to Sat
			out.n = dt.getMonth();		// 0 to 11
			out.F = F[out.n];			// January to December
			out.M = out.F.substring(0,3);	// Jan to Dec
			out.n += 1;					// 1 to 12
			out.m = out.n.pad(2);		// 01 to 12
			out.Y = dt.getFullYear();	// 1970: year number
			out.y = out.Y % 100;		// 70: year number
			out.G = dt.getHours();		// 0 to 23
			out.g = (out.G == 12)? 12 : out.G % 12;			// 0 to 11
			out.H = out.G.pad(2);		// 00 to 23
			out.h = out.g.pad(2);		// 01 to 12
			out.i = dt.getMinutes().pad(2);	// 00 to 59
			out.s = dt.getSeconds().pad(2);	// 00 to 59
			out.a = (parseInt(out.G/12) > 0)? 'pm' : 'am';
			out.A = out.a.toUpperCase();	// AM or PM

			return format.replace(
				new RegExp(Object.keys(out).join('|'), 'gi'),
				function(key) {
					return out[key];
				}
			);
		},
		multiCheck: function(incHidden) { multiCheck(incHidden); },
		wysiwyg: function(selector) { wysiwyg(selector); },
		dropdown: function() {
			$('body').on('click', function(e) {
				e.stopPropagation();
				var el = $(e.target), dropdown = el.closest('.dropdown');
				if(dropdown.length) {
					// clicked on dropdown's content
					if(el.closest('.content').length) {
						if(!dropdown.attr('data-persist')) { dropdown.removeClass('active'); }
					}
					// clicked on dropdown but not on content
					else if(!dropdown.hasClass('active')) {
						hideDD();
						dropdown.addClass('active');
						return false;
					}
					else {
						dropdown.removeClass('active');
						return false;
					}
				}
				else {
					hideDD();
				}
			});

			function hideDD() { $('.dropdown').removeClass('active'); }
		},
		modal: function(el) {
			var _showModal = function() {
				el.hide().removeClass('hidden').fadeIn();
				$('main').addClass('has-modal');
			},
			_hideModal = function() {
				el.fadeOut();
				$('main').removeClass('has-modal');
				setTimeout(function() { el.addClass('hidden'); }, 1000);
				if(destroy) { el.remove(); }
			},
			// click event for outside modal content
			_evClick = function() {
				el.click(function(e) {
					// e.preventDefault();
					var el2 = $(e.target);
					if(el2.closest('.modal-close').length || (!el2.closest('.content').length && !el.attr('data-persist'))) {
						_hideModal();
					}
				});
			},
			destroy = false;
			if(typeof el === 'string') { el = $(el+'.modal'); }
			else {
				destroy = true;
			}

			return {
				show: function(bindEv) {
					_showModal();
					if(bindEv) { _evClick(); }
				},
				hide: function() { _hideModal(); },
				events: function() { _evClick(); }
			}
		},
		init: function() {
			window.url = parseURL();
			tabs();
			// forms with class ajax will be submitted via ajax
			$('form.ajax').submit(function() {
				ajaxSubmit($(this));
				return false;
			});
			toastr.options = {
				'closeButton': false,
				'debug': true,
				'newestOnTop': true,
				'progressBar': false,
				'positionClass': 'toast-top-right',
				'preventDuplicates': false,
				'onclick': null,
				'showDuration': '300',
				'hideDuration': '1000',
				'timeOut': '5000',
				'extendedTimeOut': '1000',
				'showEasing': 'swing',
				'hideEasing': 'linear',
				'showMethod': 'fadeIn',
				'hideMethod': 'fadeOut'
			}
		}
	};
}();
