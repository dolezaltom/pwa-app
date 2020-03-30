/**
 * package   Sp One Step Checkout
 *
 * @version 1.0.2
 * @author    MagenTech http://www.magentech.com
 * @copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

 jQuery.extend({
    isEmpty: function () {
        var count = 0;
        $.each(arguments, function (i, data) {
            if (typeof data !== typeof undefined && data !== null && data !== '' && parseInt(data) !== 0) {
                count++;
            }
            else
                return false
        });
        return (arguments).length == count ? false : true;
    },
    isEmail: function (val) {
        var regExp = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i;
        return regExp.exec(val);
    },
    isJson: function (str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    },
    makeRequest: function (params) {
        if (typeof params.data.dataType === typeof undefined)
            params.data.dataType = 'json';

        if (typeof params.data.async === typeof undefined)
            params.data.async = true;

        if (typeof params.data.token === typeof undefined)
            params.data.token = PresTeamShop.spsco_static_token;

        if (typeof params.data.url_call === typeof undefined)
            params.data.url_call = PresTeamShop.actions_controller_url;

        $.each(params.data, function (i, d) {
            if (typeof d === 'boolean' && i != 'async') {
                params.data[i] = d ? 1 : 0;
            }
        });

		params.data.navigator = navigator.userAgent;

        $.ajax({
            type: 'POST',
            url: params.data.url_call,
            async: params.data.async,
            cache: false,
            dataType: params.data.dataType,
            data: params.data,
            beforeSend: function (request) {
                $('.has-action').addClass('disabled');

                if (typeof params.beforeSend === 'function')
                    params.beforeSend();

                if (typeof params.e !== typeof undefined && typeof params.e.target !== typeof undefined) {
                    if ($(params.e.target).hasClass('spinnable')) {
                        var $span = $('<span/>');
                        $span.addClass('spinner');
                        var $i = $('<i/>');
                        $i.addClass('icon-spin icon-refresh');
                        $i.appendTo($span);
                        $span.appendTo($(params.e.target));
                    }

                    $(params.e.target).blur();
                }
            },
            success: function (data) {
                if (typeof params.success === 'function')
                    params.success(data);

                if (typeof data !== typeof undefined)
                    if (typeof data.message !== typeof undefined)
                        $.showMessage(data.message_code, data.message);
            },
            complete: function (jqXHR, textStatus) {
                $('.has-action').removeClass('disabled');
                if (typeof params.complete === 'function')
                    params.complete(jqXHR, textStatus);

                //remove spinner
                if (typeof params.e !== 'undefined' && typeof params.e.target !== 'undefined') {
                    if ($(params.e.target).hasClass('spinnable'))
                        $(params.e.target).find('.spinner').remove();
                }

				if (typeof callbackExtraFunctions == 'function'){
                    callbackExtraFunctions(params.data.action);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (XMLHttpRequest.readyState == 0 || (XMLHttpRequest.readyState === 4 && XMLHttpRequest.status === 403 && XMLHttpRequest.statusText === 'Forbidden')) {
                    location.reload();
                    return false;
                }
            }
        });
    },
    utf8_decode: function (str_data) {
        var tmp_arr = [],
                i = 0,
                ac = 0,
                c1 = 0,
                c2 = 0,
                c3 = 0,
                c4 = 0;

        str_data += '';

        while (i < str_data.length) {
            c1 = str_data.charCodeAt(i);
            if (c1 <= 191) {
                tmp_arr[ac++] = String.fromCharCode(c1);
                i++;
            } else if (c1 <= 223) {
                c2 = str_data.charCodeAt(i + 1);
                tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
                i += 2;
            } else if (c1 <= 239) {
                // http://en.wikipedia.org/wiki/UTF-8#Codepage_layout
                c2 = str_data.charCodeAt(i + 1);
                c3 = str_data.charCodeAt(i + 2);
                tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            } else {
                c2 = str_data.charCodeAt(i + 1);
                c3 = str_data.charCodeAt(i + 2);
                c4 = str_data.charCodeAt(i + 3);
                c1 = ((c1 & 7) << 18) | ((c2 & 63) << 12) | ((c3 & 63) << 6) | (c4 & 63);
                c1 -= 0x10000;
                tmp_arr[ac++] = String.fromCharCode(0xD800 | ((c1 >> 10) & 0x3FF));
                tmp_arr[ac++] = String.fromCharCode(0xDC00 | (c1 & 0x3FF));
                i += 4;
            }
        }

        return tmp_arr.join('');
    },
    utf8_encode: function (argString) {
        if (argString === null || typeof argString === 'undefined') {
            return '';
        }

        var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
        var utftext = '',
                start, end, stringl = 0;

        start = end = 0;
        stringl = string.length;
        for (var n = 0; n < stringl; n++) {
            var c1 = string.charCodeAt(n);
            var enc = null;

            if (c1 < 128) {
                end++;
            } else if (c1 > 127 && c1 < 2048) {
                enc = String.fromCharCode(
                        (c1 >> 6) | 192, (c1 & 63) | 128
                        );
            } else if ((c1 & 0xF800) != 0xD800) {
                enc = String.fromCharCode(
                        (c1 >> 12) | 224, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
                        );
            } else { // surrogate pairs
                if ((c1 & 0xFC00) != 0xD800) {
                    throw new RangeError('Unmatched trail surrogate at ' + n);
                }
                var c2 = string.charCodeAt(++n);
                if ((c2 & 0xFC00) != 0xDC00) {
                    throw new RangeError('Unmatched lead surrogate at ' + (n - 1));
                }
                c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
                enc = String.fromCharCode(
                        (c1 >> 18) | 240, ((c1 >> 12) & 63) | 128, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
                        );
            }
            if (enc !== null) {
                if (end > start) {
                    utftext += string.slice(start, end);
                }
                utftext += enc;
                start = end = n + 1;
            }
        }

        if (end > start) {
            utftext += string.slice(start, stringl);
        }

        return utftext;
    },
    isUrlValid: function (url) {
        if ($.strpos(url, '//localhost/')) {
            return true;
        }
		return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
	},
    strpos: function (haystack, needle, offset) {
        var i = (haystack + '').indexOf(needle, (offset || 0));
        return i === -1 ? false : i;
    }
    
});

jQuery.fn.extend({
    truncate: function (options) {
        var defaults = {
            more: '...'
        };
        var options = $.extend(defaults, options);
        return this.each(function (num) {
            var height = parseInt($(this).css("height"));
            var width = parseInt($(this).css("width"));
            var content = $(this).html();
            while (this.scrollHeight > height) {
                content = content.replace(/\s+\S*$/, "");
                $(this).html(content + " " + options.more);
            }
        });
    },
    displayErrors: function (errors) {
        if (!$.isEmpty(errors)) {
            var html = '';

            errors = jQuery.parseJSON(errors);

            html = '<ol>';
            $.each(errors, function (i, message) {
                html += '<li>' + message + '</li>';
            });
            html += '</ol>';

            jQuery(this).append('<br/><br/>' + html);
        }
    },
    onlyCharacter: function () {
        jQuery(this).keypress(function (e) {
            var key = (document.all) ? e.keyCode : e.which;
            if (key == 8 || key == 0)
                return true;
            var regExp = /[A-Za-z\s]/;
            return regExp.test(String.fromCharCode(key));
        });

        return jQuery(this);
    },
    onlyNumber: function () {
        jQuery(this).keypress(function (e) {
            var key = (document.all) ? e.keyCode : e.which;
            if (key == 8 || key == 0)
                return true;
            var regExp = /^[0-9.]+$/;
            return regExp.test(String.fromCharCode(key));
        });

        return jQuery(this);
    },
    validName: function () {
        jQuery(this).keypress(function (e) {
            var key = (document.all) ? e.keyCode : e.which;
            if (key == 8 || key == 0)
                return true;

            var character = String.fromCharCode(key).toString();
            var regExp = /^[a-zA-Zá-úÁ-ÚÄ-Üà-ù.'\s]*$/;

            return regExp.test(character);
        });

        return jQuery(this);
    },
    validAddress: function () {
        jQuery(this).keypress(function (e) {
            var key = (document.all) ? e.keyCode : e.which;
            if (key == 8 || key == 0)
                return true;

            var character = String.fromCharCode(key).toString();
            var regExp = /^[a-zA-Zá-úÁ-ÚÄ-Üà-ù0-9#/.ºª\-\s,]*$/;

            return regExp.test(character);
        });

        return jQuery(this);
    },
   
});



;(function($, undefined){
	var supported, ls, mod = 'test';
	if ('localStorage' in window){
		try {
			ls = (typeof window.localStorage === 'undefined') ? undefined : window.localStorage;
			if (typeof ls == 'undefined' || typeof window.JSON == 'undefined'){
				supported = false;
			} else {
				supported = true;
			}

			window.localStorage.setItem(mod, '1');
			window.localStorage.removeItem(mod);
		}
		catch (err){
			supported = false;
		}
	}

	$.totalStorageSP = function(key, value, options){
		return $.totalStorageSP.impl.init(key, value);
	};

	$.totalStorageSP.setItem = function(key, value){
		return $.totalStorageSP.impl.setItem(key, value);
	};

	$.totalStorageSP.getItem = function(key){
		return $.totalStorageSP.impl.getItem(key);
	};

	$.totalStorageSP.getAll = function(){
		return $.totalStorageSP.impl.getAll();
	};

	$.totalStorageSP.deleteItem = function(key){
		return $.totalStorageSP.impl.deleteItem(key);
	};

	$.totalStorageSP.impl = {
		init: function(key, value){
			if (typeof value != 'undefined') {
				return this.setItem(key, value);
			} else {
				return this.getItem(key);
			}
		},

		setItem: function(key, value){
			if (!supported){
				try {
					$.cookie(key, value);
					return value;
				} catch(e){
					console.log('Local Storage not supported by this browser. Install the cookie plugin on your site to take advantage of the same functionality. You can get it at https://github.com/carhartl/jquery-cookie');
				}
			}
			var saver = JSON.stringify(value);
			ls.setItem(key, saver);
			return this.parseResult(saver);
		},
		getItem: function(key){
			if (!supported){
				try {
					return this.parseResult($.cookie(key));
				} catch(e){
					return null;
				}
			}
			var item = ls.getItem(key);
			return this.parseResult(item);
		},
		deleteItem: function(key){
			if (!supported){
				try {
					$.cookie(key, null);
					return true;
				} catch(e){
					return false;
				}
			}
			ls.removeItem(key);
			return true;
		},
		getAll: function(){
			var items = [];
			if (!supported){
				try {
					var pairs = document.cookie.split(";");
					for (var i = 0; i<pairs.length; i++){
						var pair = pairs[i].split('=');
						var key = pair[0];
						items.push({key:key, value:this.parseResult($.cookie(key))});
					}
				} catch(e){
					return null;
				}
			} else {
				for (var j in ls){
					if (j.length){
						items.push({key:j, value:this.parseResult(ls.getItem(j))});
					}
				}
			}
			return items;
		},
		parseResult: function(res){
			var ret;
			try {
				ret = JSON.parse(res);
				if (typeof ret == 'undefined'){
					ret = res;
				}
				if (ret == 'true'){
					ret = true;
				}
				if (ret == 'false'){
					ret = false;
				}
			} catch(e){
				ret = res;
			}
			return ret;
		}
	};
		//validate isName
	$.formUtils.addValidator({
		name: 'isName',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var reg = /^[^0-9!<>,;?=+()@#"°{}_$%:]+$/;

			if (value.trim() == ''){
				return false;
			}

			return reg.test(value);
		},
		errorMessage: 'This is not a valid name.',
		errorMessageKey: 'errorIsName'
	});

	//validate isGenericName
	$.formUtils.addValidator({
		name: 'isGenericName',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var reg = /^[^<>={}]+$/;
			return reg.test(value);
		},
		errorMessage: 'This is not a valid.',
		errorMessageKey: 'errorGlobal'
	});

	//validate isEmail
	$.formUtils.addValidator({
		name: 'isEmail',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var reg = new RegExp(/^\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i);
			return reg.test(value);
		},
		errorMessage: 'This is not a valid email address.',
		errorMessageKey: 'errorIsEmail'
	});

	//validate isPostCode
	$.formUtils.addValidator({
		name: 'isPostCode',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var name_object = $el.hasClass('delivery') ? 'delivery' : 'invoice';
			var id_country = SPSCOVAR.id_country_delivery_default;
			if ($('#' + name_object + '_id_country').length > 0) {
				id_country = $('#' + name_object + '_id_country option:selected').val();
			}
			var pattern = countriesNeedZipCode[id_country];

			if (typeof (pattern) == 'undefined' || pattern.length == 0)
				pattern = '[a-z 0-9-]+';
			else
			{
				var format = pattern;
				format = format.replace(/N/g, '0');
				format = format.replace(/L/g, 'A');
				format = format.replace(/C/g, countriesIsoCode[id_country]);
				language.errorIsPostCode = messageValidate.errorIsPostCode + ' (<b>ex: ' + format + '</b>)';

				var replacements = {
					' ': '( |)',
					'-': '(-|)',
					'N': '[0-9]',
					'L': '[a-zA-Z]',
					'C': countriesIsoCode[id_country],
				};

				if (value == format){
					return false;
				}

				for (var new_value in replacements)
					pattern = pattern.split(new_value).join(replacements[new_value]);
			}

			var reg = new RegExp('^' + pattern + '$', 'i');

			return reg.test(value);
		},
		errorMessage: 'This is not a valid post code.',
		errorMessageKey: 'errorIsPostCode'
	});

	//validate isAddress
	$.formUtils.addValidator({
		name: 'isAddress',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var reg = /^[^!<>?=+@{}_$%]+$/;
			return reg.test(value);
		},
		errorMessage: 'This is not a valid address.',
		errorMessageKey: 'errorIsAddress'
	});

	//validate isCityName
	$.formUtils.addValidator({
		name: 'isCityName',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var reg = /^[^!<>;?=+@#"°{}_$%]+$/;
			return reg.test(value);
		},
		errorMessage: 'This is not a valid city.',
		errorMessageKey: 'errorIsCityName'
	});

	//validate isPhoneNumber
	$.formUtils.addValidator({
		name: 'isPhoneNumber',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var reg = /^[+0-9. ()-]+$/;
			return reg.test(value);
		},
		errorMessage: 'This is not a valid phone.',
		errorMessageKey: 'errorIsPhoneNumber'
	});

	//validate isDniLite
	$.formUtils.addValidator({
		name: 'isDniLite',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var reg = /^[0-9a-z-.]{1,16}$/i;
			return reg.test(value);
		},
		errorMessage: 'This is not a valid document identifier.',
		errorMessageKey: 'errorIsDniLite'
	});

	//validate isMessage
	$.formUtils.addValidator({
		name: 'isMessage',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var reg = /^[^<>{}]+$/;
			return reg.test(value);
		},
		errorMessage: 'This is not a valid message.',
		errorMessageKey: 'errorIsMessage'
	});

	//validate isPasswd
	$.formUtils.addValidator({
		name: 'isPasswd',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			return (value.length >= 5 && value.length < 255);
		},
		errorMessage: 'This is not a valid password. Minimum 5 characters.',
		errorMessageKey: 'errorIsPasswd'
	});

	//validate isBirthDate
	$.formUtils.addValidator({
		name: 'isBirthDate',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var year;
			var month;
			var day;
			var date_format = SPSCOVAR.date_format_language.split('/');
			var date_value = value.split('/');

			if (date_format.length == 1)
				date_format = SPSCOVAR.date_format_language.split('-');
			if (date_value.length == 1)
				date_value = value.split('-');

			if (date_format.length == 1)
				date_format = SPSCOVAR.date_format_language.split('.');
			if (date_value.length == 1)
				date_value = value.split('.');

			for(i=0;i<3;i++){
				if (date_format[i] == 'dd')
					day = date_value[i];
				if (date_format[i] == 'mm')
					month = date_value[i];
				if (date_format[i] == 'yy')
					year = date_value[i];
			}

			var timestamp = new Date(year, month, day);

			if (!isNaN(timestamp))
				return true;
			return false;
		},
		errorMessage: 'This is not a valid birthdate.',
		errorMessageKey: 'errorisBirthDate'
	});

	//validate isDate
	$.formUtils.addValidator({
		name: 'isDate',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var year;
			var month;
			var day;
			var date_format = SPSCOVAR.date_format_language.split('/');
			var date_value = value.split('/');

			if (date_format.length == 1)
				date_format = SPSCOVAR.date_format_language.split('-');
			if (date_value.length == 1)
				date_value = value.split('-');

			if (date_format.length == 1)
				date_format = SPSCOVAR.date_format_language.split('.');
			if (date_value.length == 1)
				date_value = value.split('.');

			for(i=0;i<3;i++){
				if (date_format[i] == 'dd')
					day = date_value[i];
				if (date_format[i] == 'mm')
					month = date_value[i];
				if (date_format[i] == 'yy')
					year = date_value[i];
			}

			var timestamp = new Date(year, month, day);
			if (!isNaN(timestamp))
				return true;
			return false;
		},
		errorMessage: 'This is not a valid date.',
		errorMessageKey: 'errorisDate'
	});

	//validate isVatNumber
	$.formUtils.addValidator({
		name: 'isVatNumber',
		validatorFunction: function (value, $el, config, language, $form, eventContext) {
			var result = true;

			if (eventContext == 'blur') {
				var data = {
					url_call: (typeof orderOpcUrl !== typeof undefined ? orderOpcUrl : prestashop.urls.pages.order) + '?rand=' + new Date().getTime(),
					is_ajax: true,
					action: 'checkVATNumber',
					vat_number: value
				};

				var _json = {
					data: data,
					beforeSend: function(){
						$('#spstepcheckout #spsco_container .loading_small').show();
					},
					success: function(data) {
						$('#spstepcheckout #spsco_container .loading_small').hide();

						if (!$.isEmpty(data[0])){
							stepCheckout._showModal({type:'error',title:'Error', content:data[0], message : data[0]});
							result = false;
						}
					},
					complete: function() {
						if (result) {
							Carrier.getByCountry();
						}
					}
				};
				$.makeRequest(_json);
			}

			return result;
		},
		errorMessage: 'This is not a valid.',
		errorMessageKey: 'errorGlobal'
	});

	$.formUtils.addValidator({
		name: 'confirmation',
		validatorFunction: function (value, $el, config, language, $form) {
		  var password,
			passwordInputName = $el.valAttr('confirm') ||
			  ($el.attr('name') + '_confirmation'),
			$passwordInput = $form.find('[name="' + passwordInputName + '"]');
		  if (!$passwordInput.length) {
			$.formUtils.warn('Password confirmation validator: could not find an input ' +
			  'with name "' + passwordInputName + '"');
			return false;
		  }

		  password = $passwordInput.val();
		  if (config.validateOnBlur && !$passwordInput[0].hasValidationCallback) {
			$passwordInput[0].hasValidationCallback = true;
			var keyUpCallback = function () {
			  $el.validate();
			};
			$passwordInput.on('keyup', keyUpCallback);
			$form.one('formValidationSetup', function () {
			  $passwordInput[0].hasValidationCallback = false;
			  $passwordInput.off('keyup', keyUpCallback);
			});
		  }

		  return value === password;
		},
		errorMessage: '',
		errorMessageKey: 'notConfirmed'
	  });
})(jQuery); 