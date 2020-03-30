/**
 * package   Sp One Step Checkout
 *
 * @version 1.0.2
 * @author    MagenTech http://www.magentech.com
 * @copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var stepCheckout = {
    _element: $('#spstepcheckout'),
    _step_one: $('#spsco_one', this._element),
    _step_two: $('#spsco_two', this._element),
    _step_three: $('#spsco_three', this._element),
    _step_review: $('#spsco_review', this._element),
    _element_valid: '#spstepcheckout #spsco_login_modal form, #spstepcheckout #spsco_form',
    _element_form: $('#spsco_form', this._element),
    _modal_login: $('#spsco_login_modal'),
    _btn_login: $('.btn-login', this._modal_login),
    _btn_logout: $('.btn-logout', this._element_form),
    _form_login: $('#spsco_login_form', this._modal_login),
    _initialized: false,
    _flag_offer: true,
    _flag_valid: false,
    _flag_click: 0,
    init: function() {
        var _self = this;
        prestashop.on('updatedDeliveryForm', function(event) {
            $('.delivery-option.selected .carrier-extra-content').show()
        });
        _self._initialized = true;
        _self._flag_click = 0;
        _self._addValidAndLoad();

    },
    _addValidAndLoad: function() {
        var _self = this;
        if (typeof SPSCOVAR !== typeof undefined) {
            if (typeof $.formUtils !== typeof undefined && typeof $.validate !== typeof undefined) {
                $.validate({
                    form: _self._element_valid,
                    validateHiddenInputs: true,
                    language: messageValidate,
                    onError: function() {
                        _self._flag_valid = false;
                        return false;
                    },
                    onSuccess: function() {
                        _self._flag_valid = true;

                        return false;
                    }
                });
            }
            Address.launch();
            _self._loginAndLogout();
            Carrier.launch();
            Payment.launch();
            Review.launch();
			
            $('input[data-validation*="isBirthDate"]', _self._step_one).datepicker({
                dateFormat: SPSCOVAR.date_format_language,
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                yearRange: '-100:+0',
                isRTL: parseInt(prestashop.language.is_rtl)
            });
        }else{
			 _self._editLink();
			 prestashop.on('updateCart', function (event) {
				var _edl = setTimeout(function() {
					_self._editLink();
				 }, 800);
				
			});
			
		}
    },
	
	_editLink : function () {
		var separator_param = '?';
		if ($.strpos(prestashop.urls.current_url, '?')) {
			separator_param = '&';
		}
		if ($('.cart-detailed-actions a').length) {
			$('.cart-detailed-actions a').attr('href', prestashop.urls.current_url + separator_param + 'checkout=1');
			$('form#voucher').attr('action', prestashop.urls.current_url);

			var href_delete_voucher = $('a.price_discount_delete').attr('href');
			if (typeof(href_delete_voucher) != 'undefined') {
				href_delete_voucher = href_delete_voucher.split('?');
				$('a.price_discount_delete').attr('href', prestashop.urls.current_url + '?' + href_delete_voucher[1]);
			}
		}
	},
    _loginAndLogout: function() {
        var _self = this;
        _self._btn_login.on('click', function(e) {
            _self._loginCustomer()
        });
        _self._btn_logout.on('click', function(e) {
            window.location = $(e.currentTarget).data('link');
        });
		
        $('.txt-password', _self._modal_login).keypress(function(e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13)
                _self._loginCustomer();
        });

    },
    _loginCustomer: function() {
        var _self = this;
        var _email = $('.txt-email', _self._form_login).val();
        var _pass = $('.txt-password', _self._form_login).val();
        var login_success = false;
        var data = {
            is_ajax: true,
            action: 'loginCustomer',
            email: _email,
            password: _pass
        };
        _self._flag_click++;
        if (_self._flag_click == 1 || _self._form_login.find('.has-success').length > 1) {
            _self._form_login.submit();
        }

        if (_self._flag_valid) {
            $.ajax({
                type: 'POST',
                url: prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
                cache: false,
                dataType: 'json',
                data: data,
                beforeSend: function() {
                    _self._btn_login.attr('disabled', 'true');
                    $('.loading_small', _self._modal_login).show();
                    $('.alert', _self._modal_login).empty().addClass('hidden');
                },
                success: function(json) {
                    if (json.success) {
                        $.totalStorageSP('id_address_delivery', null);
                        $.totalStorageSP('id_address_invoice', null);
                        if ($('#spsco_review_container', _self._element).length > 0) {
                            window.parent.location.reload();
                        } else {
                            if (parseInt($('.shopping_cart .ajax_cart_quantity', _self._element).text()) > 0) {
                                window.parent.location = prestashop.urls.pages.order;
                            } else {
                                window.parent.location = prestashop.urls.base_url;
                            }
                        }

                        login_success = true;
                    } else {
                        if (json.errors) {
                            $('.alert', _self._modal_login).html(json.errors).removeClass('hidden');
                        }
                    }
                },
                complete: function() {
                    if (!login_success) {
                        stepCheckout._btn_login.removeAttr('disabled');
                        $('.loading_small', _self._modal_login).hide();
                    }
                }
            });
        }
    },
    _openCMS: function(params) {
        var _self = this;
        var param = $.extend({}, {
            id_cms: ''
        }, params);

        var data = {
            url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
            is_ajax: true,
            dataType: 'json',
            action: 'loadCMS',
            id_cms: param.id_cms
        };

        var _json = {
            data: data,
            beforeSend: function() {
                _self._loadingBig(true);
            },
            success: function(json) {
                if (!$.isEmpty(json.content)) {
                    _self._showModal({
                        title: json.title,
                        content: json.content
                    });
                }
            },
            complete: function() {
                _self._loadingBig(false);
            }
        };
        $.makeRequest(_json);
    },
    _loadingBig: function(show) {
        var _self = this;
        if (show) {
            _self._element.find('.spsco-loading-lg').show();
        } else {
            _self._element.find('.spsco-loading-lg').hide();
        }
    },
    _showModal: function(params) {
        var _self = this;
        var option = $.extend({}, {
            name: 'modal',
            type: 'normal',
            title: '',
            title_icon: '',
            message: '',
            content: '',
            close: true,
            button_close: false,
            size: '',
            callback: '',
            callback_close: ''
        }, params);

        $('#spsco_modal').on('show.bs.modal', function(event) {
                var modal = $(this)
                modal.find('.modal-title').text(option.title);
                modal.find('.modal-body').html(option.content);
            }),
            $('#spsco_modal').modal('show');
    },
    _launchAddress: function() {

    }
}

var Fronted = {
    showModal: function(params) {
        var param = $.extend({}, {
            name: 'modal',
            type: 'normal',
            title: '',
            title_icon: '',
            message: '',
            content: '',
            close: true,
            button_close: false,
            size: '',
            callback: '',
            callback_close: ''
        }, params);

        $('#' + param.name).remove();

        var windows_height = $(window).height();

        var parent_content = '';
        if (typeof param.content === 'object') {
            parent_content = param.content.parent();
        }

        var $modal = $('<div/>').attr({
            id: param.name,
            'class': 'modal fade',
            role: 'dialog'
        });
        var $modal_dialog = $('<div/>').attr({
            'class': 'modal-dialog ' + param.size
        });
        var $modal_header = $('<div/>').attr({
            'class': 'modal-header'
        });
        var $modal_content = $('<div/>').attr({
            'class': 'modal-content'
        });
        var $modal_body = $('<div/>').attr({
            'class': 'modal-body'
        });
        var $modal_footer = $('<div/>').attr({
            'class': 'modal-footer'
        });
        var $modal_button_close = $('<button/>')
            .attr({
                type: 'button',
                'class': 'close'
            })
            .click(function() {
                $('#' + param.name).modal('hide');
            })
            .append('<i class="fa fa-close"></i>');
        var $modal_button_close_footer = $('<button/>')
            .attr({
                type: 'button',
                'class': 'btn btn-default'
            })
            .click(function() {
                $('#' + param.name).modal('hide');
            })
            .append('OK');
        var $modal_title = '';

        if (typeof param.message === 'array') {
            var message_html = '';
            $.each(param.message, function(i, message) {
                message_html += '- ' + message + '<br/>';
            });
            param.message = message_html;
        }

        if (param.type == 'error') {
            $modal_title = $('<span/>')
                .attr({
                    'class': 'panel-title'
                })
                .append(param.close ? $modal_button_close : '')
                .append('<i class="fa fa-times-circle fa-2x" style="color:red"></i>')
                .append(param.message);
        } else if (param.type == 'warning') {
            $modal_title = $('<span/>')
                .attr({
                    'class': 'panel-title'
                })
                .append(param.close ? $modal_button_close : '')
                .append('<i class="fa fa-warning fa-2x" style="color:orange"></i>')
                .append(param.message);
        } else {
            $modal_title = $('<span/>')
                .attr({
                    'class': 'panel-title'
                })
                .append(param.close ? $modal_button_close : '')
                .append('<i class="fa ' + param.title_icon + ' fa-1x"></i>')
                .append(param.title);
        }

        $modal_header.append($modal_title);
        $modal_content.append($modal_header);

        if (param.type == 'normal') {
            if (typeof param.content === 'object') {
                param.content.removeClass('hidden').appendTo($modal_body);
            } else {
                $modal_body.append(param.content);
            }

            $modal_content.append($modal_body);

            if (param.button_close) {
                $modal_footer.append($modal_button_close_footer);
                $modal_content.append($modal_footer);
            }
        }

        $modal_dialog.append($modal_content);
        $modal.append($modal_dialog);

        $modal.on('hide.bs.modal', function() {
            if (!param.close) {
                return false;
            } else {
                if (typeof param.callback_close !== typeof undefined && typeof param.callback_close === 'function') {
                    if (!param.callback_close()) {
                        return false;
                    }
                }

                if (!$.isEmpty(parent_content)) {
                    param.content.appendTo(parent_content).addClass('hidden');
                }

                $('body').removeClass('modal-open');
            }
        });

        $('#spstepcheckout').prepend($modal);

        $('#' + param.name).modal('show');

        if (!$('#' + param.name).hasClass('in')) {
            $('#' + param.name).addClass('in').css({
                display: 'block'
            });
        }

        var paddingTop = 0
        if (windows_height > $modal_dialog.height()) {
            paddingTop = (windows_height - $modal_dialog.height()) / 2;
        }

        $('#' + param.name).css({
            paddingTop: paddingTop
        });

        stepCheckout._loadingBig(false);

        if (typeof param.callback !== typeof undefined && typeof param.callback === 'function')
            param.callback();

        $('.pakkelabels_modal-backdrop').remove();

        window.scrollTo(0, $('#spstepcheckout').offset().top);
    }

}

var Address = {
    id_customer: 0,
    id_address_delivery: 0,
    id_address_invoice: 0,
    delivery_vat_number: false,
    invoice_vat_number: false,
    launch: function() {
        var _self = this,
            _wrap = stepCheckout;
        $('#field_customer_id', _wrap._element).addClass('hidden');
        $('.btn-save-customer', _wrap._element).click(_self.createCustomer);

        _wrap._element.on('blur', '#customer_email', function(e) {
            _self.checkEmailCustomer($(e.currentTarget).val());
        });

        _wrap._step_one.find('input.customer, input.delivery,' +
            +'input.invoice, #customer_conf_passwd,' +
            +'#customer_conf_email').on('paste', function(e) {
            var $element = $(e.currentTarget);
            setTimeout(function() {
                $element.val($.trim($element.val()));
            }, 100);
        });

        _wrap._step_one.find('.container_help_invoice span').click(function() {
            $('#nav_invoice a', _wrap._step_one).trigger('click');
        });

        if ($('#iput_virtual_carrier', _wrap._step_two).length <= 0) {
            if (!SPSCOVAR.IS_LOGGED) {
                $('#field_delivery_id', _wrap._element).addClass('hidden');
            }

            if ($('select#delivery_id_country', _wrap._element).length <= 0) {
                _self.updateState({
                    object: 'delivery',
                    id_country: SPSCOVAR.id_country_delivery_default
                });
            }

            _self.initPostCodeGeonames({
                object: 'delivery'
            });

            _wrap._element
                .on('change', '#delivery_city', function() {
                    $('#delivery_city_list').val('');
                })
                .on('change', 'select#delivery_id_state', function(event) {
                    _self.getCitiesByState({
                        object: 'delivery'
                    });

                    if (SPSCOVAR.CONFIGS.SPSCO_RELOAD_SHIPPING_BY_STATE) {
                        Carrier.getByCountry();
                    }

                    $(event.currentTarget).validate();
                })
                .on('change', 'select#delivery_id_country', function(event) {
                    _self.isNeedDniByCountryId({
                        object: 'delivery'
                    });
                    _self.isNeedPostCodeByCountryId({
                        object: 'delivery'
                    });
                    _self.updateState({
                        object: 'delivery',
                        id_country: $(event.currentTarget).val()
                    });
                    _self.initPostCodeGeonames({
                        object: 'delivery'
                    });
                    Carrier.getByCountry();

                    if (typeof event.originalEvent !== typeof undefined && _wrap._element.find('input#delivery_postcode').length > 0 && !$.isEmpty(_wrap._element.find('input#invoice_postcode').val())) {
                        _wrap._element.find('input#delivery_postcode').validate();
                    }

                    if (SPSCOVAR.CONFIGS.SPSCO_SHOW_LIST_CITIES_GEONAMES) {
                        $('#spsco_one #delivery_city_list').empty().hide();
                        $('#spsco_one #delivery_city').val('');
                    }

                    _self.loadAutocompleteAddress();
                })
                .on('change', 'select#delivery_id', function(e) {
                    if (!$.isEmpty($(e.currentTarget).val()))
                        _self.load({
                            object: 'delivery'
                        });
                    else {
                        _self.createAddressAjax({
                            object: 'delivery'
                        });

                    }
                })
                .on('click', 'input#checkbox_create_account_guest', _self.checkGuestAccount)
                .on('click', 'input#checkbox_create_account', _self.checkGuestAccount)
                .on('change', 'select#id_district', Carrier.getByCountry) 
                .on('change', 'select#id_subdistrict', Carrier.getByCountry); 

            _self.checkGuestAccount();
            _self.isNeedDniByCountryId({
                object: 'delivery'
            });
            _self.isNeedPostCodeByCountryId({
                object: 'delivery'
            });
            _self.getCityByPostCode({
                object: 'delivery'
            });
        }

        if (SPSCOVAR.CONFIGS.SPSCO_ENABLE_INVOICE_ADDRESS) {
            if (typeof $.totalStorageSP !== typeof undefined) {
                if ($.totalStorageSP('create_invoice_address')) {
                    $('#checkbox_create_invoice_address', _wrap._element).attr('checked', 'true');
                }
            }

            if (!SPSCOVAR.IS_LOGGED) {
                $('#field_invoice_id', _wrap._element).addClass('hidden');
            }

            if (SPSCOVAR.CONFIGS.SPSCO_ENABLE_INVOICE_ADDRESS) {
                _self.checkNeedInvoice();

                _wrap._element.on('click', 'input#checkbox_create_invoice_address', function(event) {
                    _self.checkNeedInvoice();
                    if ($(event.currentTarget).is(':checked')) {
                        _self.updateAddressInvoice();
                    } else {
                        _self.removeAddressInvoice();
                    }
                });
            }

            if ($('select#invoice_id_country', _wrap._element).length <= 0) {
                _self.updateState({
                    object: 'invoice',
                    id_country: SPSCOVAR.id_country_invoice_default
                });
            }

            _self.initPostCodeGeonames({
                object: 'invoice'
            });

            _wrap._element
                .on('change', '#invoice_city', function() {
                    $('#invoice_city_list').val('');
                })
                .on('change', 'select#invoice_id_state', function(event) {
                    _self.getCitiesByState({
                        object: 'invoice'
                    });
                    _self.updateAddressInvoice();

                    $(event.currentTarget).validate();
                })
                .on('change', 'select#invoice_id_country', function(event) {
                    _self.isNeedDniByCountryId({
                        object: 'invoice'
                    });
                    _self.isNeedPostCodeByCountryId({
                        object: 'invoice'
                    });
                    _self.updateState({
                        object: 'invoice',
                        id_country: $(event.currentTarget).val()
                    });
                    _self.updateAddressInvoice();
                    _self.initPostCodeGeonames({
                        object: 'invoice'
                    });

                    if (typeof event.originalEvent !== typeof undefined && _wrap._element.find('input#invoice_postcode').length > 0 && !$.isEmpty(_wrap._element.find('input#invoice_postcode').val())) {
                        _wrap._element.find('input#invoice_postcode').validate();
                    }

                    if (SPSCOVAR.CONFIGS.SPSCO_SHOW_LIST_CITIES_GEONAMES) {
                        $('#invoice_city_list', _wrap._step_one).empty().hide();
                        $('#invoice_city', _wrap._step_one).val('');
                    }

                    _self.loadAutocompleteAddress();
                })
                .on('change', 'select#invoice_id', function(e) {
                    if (!$.isEmpty($(e.currentTarget).val())) {
                        _self.load({
                            object: 'invoice'
                        });
                    } else {
                        _self.createAddressAjax({
                            object: 'invoice'
                        });
                    }
                });

            _self.isNeedDniByCountryId({
                object: 'invoice'
            });
            _self.isNeedPostCodeByCountryId({
                object: 'invoice'
            });
            _self.getCityByPostCode({
                object: 'invoice'
            });
        }
        Address.load();
    },

    initPostCodeGeonames: function(params) {
        var _self = this,
            _wrap = stepCheckout;
        var param = $.extend({}, {
            object: 'delivery'
        }, params);

        if (SPSCOVAR.CONFIGS.SPSCO_AUTO_ADDRESS_GEONAMES && stepCheckout._step_one.find('#' + param.object + '_postcode').length > 0) {
            var $id_country = $('#' + param.object + '_id_country', _wrap._step_one);
            var iso_code_country = '';

            if ($id_country.length > 0) {
                iso_code_country = $id_country.find('option:selected').data('iso-code');
            } else {
                iso_code_country = SPSCOVAR.iso_code_country_delivery_default;
            }
        }
    },
    getCityByPostCode: function(params) {
        var _self = this,
            _wrap = stepCheckout;
        var param = $.extend({}, {
            object: 'delivery'
        }, params);

        if (1 == 2) {
            var $city_list = $('#' + param.object + '_city_list', _wrap._step_one);

            if ($city_list.length <= 0 || ($city_list.length > 0 && !$city_list.is(':visible'))) {
                var $id_country = $('#' + param.object + '_id_country', _wrap._step_one);
                var $postcode = $(' #' + param.object + '_postcode', _wrap._step_one);
                var $city = $('#' + param.object + '_city', _wrap._step_one);

                if ($postcode.length > 0 && $city.length > 0) {
                    $postcode.jeoPostalCodeLookup({
                        country: $id_country.find('option:selected').data('iso-code'),
                        target: $city
                    });
                }
            }
        }
    },
    getCitiesByState: function(params) {
        var _self = this,
            _wrap = stepCheckout;
        var param = $.extend({}, {
            object: 'delivery'
        }, params);

        if (SPSCOVAR.CONFIGS.SPSCO_SHOW_LIST_CITIES_GEONAMES) {
            var $id_country = $('#' + param.object + '_id_country', _wrap._step_one);
            var $id_state = $('#' + param.object + '_id_state', _wrap._step_one);
            var iso_code_country = '';

            if ($id_country.length > 0) {
                iso_code_country = $id_country.find('option:selected').data('iso-code');
            } else {
                iso_code_country = SPSCOVAR.iso_code_country_delivery_default;
            }

            var name_state = $.trim($id_state.find('option:selected').data('text'));

            if ($id_state.length > 0 && !$.isEmpty(name_state)) {
                var cities = Array();
                var current_city = $('#' + param.object + '_city', _wrap._step_one).val();

                jeoquery.getGeoNames(
                    'search', {
                        q: name_state,
                        country: iso_code_country,
                        featureClass: 'P',
                        style: 'full'
                    },
                    function(data) {
                        //ordenar array de objetos por una propiedad en especifico
                        function dynamicSort(property) {
                            var sortOrder = 1;
                            if (property[0] === "-") {
                                sortOrder = -1;
                                property = property.substr(1);
                            }
                            return function(a, b) {
                                var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
                                return result * sortOrder;
                            }
                        }

                        $.each(data.geonames, function(i, item) {
                            if ($.inArray(item.name, cities) == -1) {
                                cities.push({
                                    name: $.trim(item.name),
                                    postcode: item.adminCode3
                                });
                            }
                        });
                        cities.sort(dynamicSort('name'));

                        var $city_list = $('#' + param.object + '_city_list', _wrap._step_one);
                        if ($city_list.length <= 0) {
                            $city_list = $('<select/>')
                                .attr({
                                    id: param.object + '_city_list',
                                    class: 'form-control input-sm not_unifrom not_uniform'
                                })
                                .on('change', function(event) {
                                    var option_selected = $(event.currentTarget).find('option:selected');

                                    $('#' + param.object + '_city', _wrap._step_one).val($(option_selected).attr('value')).trigger('blur');
                                    $('#' + param.object + '_postcode', _wrap._step_one).val($(option_selected).attr('data-postcode'));
                                });
                        } else {
                            $city_list.html('').show();
                        }

                        var $option = $('<option/>')
                            .attr({
                                value: ''
                            }).append('--');
                        $option.appendTo($city_list);
                        $.each(cities, function(i, city) {
                            var $option = $('<option/>')
                                .attr({
                                    'value': city.name,
                                    'data-postcode': city.postcode
                                }).append(city.name);

                            if (city == current_city) {
                                $option.attr('selected', 'true');
                            }

                            $option.appendTo($city_list);
                        });
                        $('#field_' + param.object + '_city', _wrap._step_one).append($city_list);
                    });
            } else {
                $('#' + param.object + '_city_list', _wrap._step_one).hide();
            }
        }
    },
    loadAddressesCustomer: function(params) {
        var _self = this,
            _wrap = stepCheckout;
        var param = $.extend({}, {
            callback: ''
        }, params);

        var data = {
            url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
            is_ajax: true,
            action: 'loadAddressesCustomer'
        };
        var _json = {
            data: data,
            success: function(json) {
                if (typeof json.addresses !== typeof undefined) {
                    $delivery_id = $('#delivery_id', _wrap._element);
                    $invoice_id = $('#invoice_id', _wrap._element);

                    if ($delivery_id.length > 0) {
                        if (SPSCOVAR.IS_LOGGED && SPSCOVAR.IS_GUEST) {
                            $('#field_delivery_id', _wrap._step_one).parent().hide();
                        } else {
                            $delivery_id.find('option').prop('selected', false)
                            $delivery_id.find('option:not(:first)').remove();

                            $.each(json.addresses, function(i, address) {
                                var $option = $('<option/>')
                                    .attr({
                                        value: address.id_address,
                                    }).append(address.alias);

                                if (json.id_address_delivery == address.id_address) {
                                    $option.prop('selected', true);
                                }

                                $option.appendTo($delivery_id);
                            });

                            if (typeof $.totalStorageSP !== typeof undefined) {
                                var id_address_delivery = $.totalStorageSP('id_address_delivery');
                                if (id_address_delivery) {
                                    var $option = $('#delivery_id option[value=' + id_address_delivery + ']', _wrap._element);
                                    if ($option.length > 0) {
                                        $option.attr('selected', 'true');
                                    }
                                }
                            }
                        }
                    }

                    if ($invoice_id.length > 0) {
                        if (SPSCOVAR.IS_LOGGED && SPSCOVAR.IS_GUEST) {
                            $('#field_invoice_id', _wrap._step_one).parent().hide();
                        } else {
                            $invoice_id.find('option:not(:first)').remove();

                            $.each(json.addresses, function(i, address) {
                                var $option = $('<option/>')
                                    .attr({
                                        value: address.id_address,
                                    }).append(address.alias);

                                if (json.id_address_invoice == address.id_address) {
                                    $option.prop('selected', true);
                                }

                                $option.appendTo($invoice_id);
                            });

                            if (typeof $.totalStorageSP !== typeof undefined) {
                                var id_address_invoice = $.totalStorageSP('id_address_invoice');
                                if (id_address_invoice) {
                                    var $option = $('#invoice_id option[value=' + id_address_invoice + ']', _wrap._element);

                                    if ($option.length > 0) {
                                        $option.attr('selected', 'true');
                                    }
                                }
                            }
                        }
                    }
                }
            },
            complete: function() {
                if (typeof param.callback !== typeof undefined && typeof param.callback === 'function') {
                    param.callback();
                }
            }
        };
        $.makeRequest(_json);
    },
    createAddressAjax: function(params) {
        var _self = this,
            _wrap = stepCheckout;
        var param = $.extend({}, {
            callback: '',
            object: 'delivery'
        }, params);

        var data = {
            url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
            is_ajax: true,
            dataType: 'html',
            action: 'createAddressAjax',
            object: param.object
        };
        var _json = {
            data: data,
            success: function(id_address) {
                if (!$.isEmpty(id_address)) {
                    if (typeof $.totalStorageSP !== typeof undefined) {
                        if (param.object == 'delivery') {
                            $.totalStorageSP('id_address_delivery', id_address)
                        }
                        if (param.object == 'invoice') {
                            $.totalStorageSP('id_address_invoice', id_address)
                        }
                    }

                    var callback = function() {
                        _self.clearFormByObject(param.object);
                    }

                    _self.loadAddressesCustomer({
                        callback: callback
                    });
                }
            },
            complete: function() {
                if (typeof param.callback !== typeof undefined && typeof param.callback === 'function') {
                    param.callback();
                }
            }
        };
        $.makeRequest(_json);
    },
    createCustomer: function() {
        var _self = this,
            _wrap = stepCheckout;
        _wrap._element_form.submit();
        if (!stepCheckout._flag_valid) {
            if ($('#delivery_address_container .required.has-error', _wrap._step_one).length == 0 && $('#invoice_address_container .required.has-error', _wrap._step_one).length > 0) {
                $('#nav_address .nav-link, #delivery_address_container', _wrap._step_one).removeClass('active');
                $('#nav_invoice .nav-link, #invoice_address_container', _wrap._step_one).removeClass('active');
                $('#nav_invoice .nav-link, #invoice_address_container', _wrap._step_one).addClass('active');
            }
        }

        if (_wrap._flag_valid) {
            var invoice_id = '';
            var fields = Review.getFields();

            if (SPSCOVAR.CONFIGS.SPSCO_ENABLE_INVOICE_ADDRESS && $('#spstepcheckout #checkbox_create_invoice_address').length > 0) {
                if ($('#spstepcheckout #checkbox_create_invoice_address').is(':checked')) {
                    invoice_id = $('#invoice_id').val();
                }
            } else {
                invoice_id = $('#invoice_id').val();
            }

            var _extra_data = Review.getFieldsExtra({});
            var _data = $.extend({}, _extra_data, {
                'url_call': prestashop.urls.pages.order + '?checkout=1&rand=' + new Date().getTime(),
                'is_ajax': true,
                'dataType': 'json',
                'action': (SPSCOVAR.IS_LOGGED ? 'placeOrder' : 'createCustomerAjax'),
                'id_customer': (!$.isEmpty(_wrap._step_one.find('#customer_id').val()) ? _wrap._step_one.find('#customer_id').val() : ''),
                'id_address_delivery': (!$.isEmpty(_wrap._step_one.find('#delivery_id').val()) ? _wrap._step_one.find('#delivery_id').val() : ''),
                'id_address_invoice': invoice_id,
                'is_new_customer': (_wrap._step_one.find('#checkbox_create_account_guest').is(':checked') ? 0 : 1),
                'fields_sp': JSON.stringify(fields),
            });

            var _json = {
                data: _data,
                beforeSend: function() {
                    $('#spstepcheckout #spsco_container .loading_small').show();
                },
                success: function(data) {
                    if (typeof data.redirect !== typeof undefined) {
                        window.parent.location = data.redirect;

                        return false;
                    }

                    if (data.isSaved && (!SPSCOVAR.PS_GUEST_CHECKOUT_ENABLED || $('#checkbox_create_account_guest').is(':checked'))) {
                        _wrap._step_one.find('#customer_id').val(data.id_customer);
                        _wrap._step_one.find('#customer_email, #customer_conf_email, #customer_passwd, #customer_conf_passwd').attr({
                            'disabled': 'true',
                            'data-validation-optional': 'true'
                        });

                        $('#field_customer_passwd, #field_customer_conf_passwd, #field_customer_email, #field_customer_conf_email, #spstepcheckout #spsco_container .account_creation').addClass('hidden');
                    }

                    if (data.hasError) {
                        _wrap._showModal({
                            type: 'error',
							title: 'Error',
							content: data.errors,
                            message: data.errors
                        });
                    } else {
                        if (typeof $.totalStorageSP !== typeof undefined) {
                            $.totalStorageSP('id_address_delivery', data.id_address_delivery);
                            $.totalStorageSP('id_address_invoice', data.id_address_invoice);
                        }

                        if (!SPSCOVAR.IS_LOGGED && !SPSCOVAR.IS_GUEST) {
                            if (prestashop.cart.products_count > 0) {
                                window.parent.location = prestashop.urls.pages.order;
                            } else {
                                window.parent.location = prestashop.urls.pages.my_account;
                            }

                            $('.btn-save-customer', _wrap._element).attr('disabled', 'true');
                        } else {
                            var callback = function() {
                                if (!SPSCOVAR.IS_VIRTUAL_CART) {
                                    Carrier.getByCountry();
                                } else {
                                    Payment.getByCountry();
                                }
                            };

                            _self.loadAddressesCustomer({
                                callback: callback
                            });
                        }
                    }
                },
                complete: function() {
                    $('#spsco_container .loading_small', _wrap._element).hide();
                }
            };
            $.makeRequest(_json);
        }
    },
    load: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            object: ''
        }, params);

        var loaded = false;

        if (!$.isEmpty($("#delivery_id").val())) {
            _self.id_address_delivery = $("#delivery_id").val();
            $.totalStorageSP('id_address_delivery', _self.id_address_delivery);
        } else {
            if (typeof $.totalStorageSP !== typeof undefined) {
                if ($.totalStorageSP('id_address_delivery')) {
                    _self.id_address_delivery = $.totalStorageSP('id_address_delivery');
                }
            }
        }

        if (!$.isEmpty($("#invoice_id").val())) {
            _self.id_address_invoice = $("#invoice_id").val();
            $.totalStorageSP('id_address_invoice', _self.id_address_invoice);
        } else {
            if (typeof $.totalStorageSP !== typeof undefined) {
                if ($.totalStorageSP('id_address_invoice')) {
                    _self.id_address_invoice = $.totalStorageSP('id_address_invoice');
                }
            }
        }

        var callback = function() {
			var _self = this,
				_wrap = stepCheckout;
            Address.getCitiesByState({
                object: 'delivery'
            });
            if ($('#invoice_address_container', _wrap._element).length > 0) {
                Address.getCitiesByState({
                    object: 'invoice'
                });
            }

            if (is_virtual_cart && !loaded) {
                if ($('#delivery_id_country', _wrap._element).length > 0) {
                   $('#delivery_id_country', _wrap._element).trigger('change');
                } else {
                    Payment.getByCountry();
                }
            } else {
                if ($('#delivery_id_country', _wrap._element).length > 0 && !SPSCOVAR.IS_LOGGED) {
                    $('#delivery_id_country', _wrap._element).trigger('change');
                } else {
                    if (!is_virtual_cart)
                        Carrier.getByCountry();
                }
            }

            Address.loadAutocompleteAddress();
        }

        if (SPSCOVAR.IS_LOGGED || SPSCOVAR.IS_GUEST) {
            var data = {
                url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
                is_ajax: true,
                action: 'loadAddress',
                delivery_id: _self.id_address_delivery,
                invoice_id: _self.id_address_invoice,
                is_set_invoice: _wrap._step_one.find('input#checkbox_create_invoice_address').is(':checked')
            };
            var _json = {
                data: data,
                beforeSend: function() {
                    $('#spsco_container .loading_small', _wrap._element).show();
                },
                success: function(json) {
                    if (!json.hasError && (!$.isEmpty(json.customer.id) || !$.isEmpty(json.address_delivery.id) || !$.isEmpty(json.address_invoice.id))) {
                        _self.id_address_delivery = $.isEmpty(json.address_delivery.id) ? 0 : json.address_delivery.id;
                        _self.id_address_invoice = $.isEmpty(json.address_invoice.id) ? 0 : json.address_invoice.id;
                        _self.id_customer = $.isEmpty(json.customer.id) ? 0 : json.customer.id;

                        if ($('#delivery_id option', _wrap._element).length <= 1) {
                            _self.loadAddressesCustomer();
                        }
                        var object_load = '.customer, ' + (param.object == '' ? '.delivery, .invoice' : '.' + param.object);
                        _wrap._step_one.find(object_load).each(function(i, field) {
                            var $field = $(field);
                            var name = $field.attr('data-field-name');
                            var default_value = $field.attr('data-default-value');
                            var object = '';

                            if ($field.hasClass('customer')) {
                                var value = json.customer[name];
                                object = 'customer';
                            } else if ($field.hasClass('delivery')) {
                                var value = json.address_delivery[name];
                                object = 'delivery';
                            } else if ($field.hasClass('invoice')) {
                                var value = json.address_invoice[name];
                                object = 'invoice';
                            }

                            $check_invoice = $('input#checkbox_create_invoice_address',_wrap._step_one);
                            if (object == 'invoice' && !SPSCOVAR.CONFIGS.SPSCO_REQUIRED_INVOICE_ADDRESS && !$check_invoice.is(':checked')) {
                                $('#invoice_id', _wrap._step_one).val('');
                                return;
                            }

                            if ( name == 'id_state') {
                                return;
                            }

                            if (value == '0000-00-00')
                                value = '';

                            if ($field.is(':checkbox')) {
                                if (parseInt(value))
                                    $field.attr('checked', 'true');
                                else
                                    $field.removeAttr('checked');
                            } else if ($field.is(':radio')) {
                                if ($field.val() == value)
                                    $field.attr('checked', 'true');
                            } else {
                                if (name == 'birthday') {
                                    var date_value = value.split('-');
                                    var date_string = SPSCOVAR.date_format_language.replace('dd', date_value[2]);
                                    date_string = date_string.replace('mm', date_value[1]);
                                    date_string = date_string.replace('yy', date_value[0]);

                                    $field.val(date_string);
                                } else {
                                    $field.val(value);
                                }

                                if ($field.is(':text'))
                                    if (value == default_value)
                                        $field.val('');
                            }

                            if (name == 'email') {
                                if ((SPSCOVAR.IS_LOGGED && !SPSCOVAR.IS_GUEST) || !SPSCOVAR.PRESTASHOP.CONFIGS.PS_GUEST_CHECKOUT_ENABLED) {
                                    $field.attr('disabled', 'true').addClass('disabled');
                                } else {
                                    $('#customer_conf_email',_wrap._step_one).val($field.val());
                                }
                            }
                        });

                        _self.isNeedDniByCountryId({
                            object: 'delivery'
                        });
                        _self.updateState({
                            object: 'delivery',
                            id_state_default: json.address_delivery['id_state']
                        });
                        _self.isNeedDniByCountryId({
                            object: 'invoice'
                        });
                        _self.updateState({
                            object: 'invoice',
                            id_state_default: json.address_invoice['id_state']
                        });

                        if (is_virtual_cart) {
                            Payment.getByCountry();

                            loaded = true;
                        }
                    } else {
                        if (json.hasError) {
                          _wrap._showModal({
								type: 'error',
								title: 'Error',
								content: data.errors,
								message: data.errors
							});
                        } else if (json.hasWarning) {
                            _wrap._showModal({
                                type: 'warning',
								title: 'Upozornění',
								content: json.warnings,
                                message: json.warnings
                            });
                        }
                    }
                },
                complete: function() {
                    $('#spstepcheckout #spsco_container .loading_small').hide();

                    callback();
                }
            };
            $.makeRequest(_json);
        } else {
            callback();
        }
    },
    loadAutocompleteAddress: function() {
		var _self = this,
			_wrap = stepCheckout;
        if (SPSCOVAR.CONFIGS.SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS && !$.isEmpty(SPSCOVAR.CONFIGS.SPSCO_GOOGLE_API_KEY) && typeof google.maps.places !== typeof undefined) {
            if ($('#delivery_address1').length > 0) {
                var iso_code_country = null;
                var $id_country = $('#spstepcheckout select#delivery_id_country');

                if ($id_country.length > 0) {
                    iso_code_country = $id_country.find('option:selected').data('iso-code');
                } else {
                    iso_code_country = SPSCOVAR.iso_code_country_delivery_default;
                }

                _self.autocomplete_delivery = new google.maps.places.Autocomplete(
                    (document.getElementById('delivery_address1')), {
                        types: ['geocode'],
                        componentRestrictions: {
                            country: iso_code_country
                        }
                    }
                );
                google.maps.event.addListener(_self.autocomplete_delivery, 'place_changed', function() {
                    _self.fillInAddress('delivery', _self.autocomplete_delivery);
                });
            }

            if ($('#invoice_address1', _wrap._element).length > 0) {
                var iso_code_country = null;
                var $id_country = $('select#invoice_id_country', _wrap._element);

                if ($id_country.length > 0) {
                    iso_code_country = $id_country.find('option:selected').data('iso-code');
                } else {
                    iso_code_country = SPSCOVAR.iso_code_country_invoice_default;
                }

                _self.autocomplete_invoice = new google.maps.places.Autocomplete(
                    (document.getElementById('invoice_address1')), {
                        types: ['geocode'],
                        componentRestrictions: {
                            country: iso_code_country
                        }
                    }
                );

                google.maps.event.addListener(_self.autocomplete_invoice, 'place_changed', function() {
                    _self.fillInAddress('invoice', _self.autocomplete_invoice);
                });
            }
        }
    },
    fillInAddress: function(address, autocomplete) {
		var _self = this,
			_wrap = stepCheckout;
        _self.componentForm = {
            postal_code: {
                index: 0,
                type: 'long_name',
                field: address + '_postcode'
            },
            locality: {
                index: 1,
                type: 'long_name',
                field: address + '_city'
            },
            administrative_area_level_1: {
                index: 2,
                type: 'select',
                field: address + '_id_state'
            },
            administrative_area_level_2: {
                index: 3,
                type: 'select',
                field: address + '_id_state'
            },
            administrative_area_level_3: {
                index: 4,
                type: 'select',
                field: address + '_id_state'
            },
            country: {
                index: 5,
                type: 'select',
                field: address + '_id_country'
            },
        };

        var place = autocomplete.getPlace();

        $.each(_self.componentForm, function(c, component) {
            if (component.type !== 'select' && component.field != (address + '_address1')) {
                $('#' + component.field).val('');
            }
        });

        var components = [];
        var components_state = [];

        $.each(place.address_components, function(a, component) {
            if (typeof _self.componentForm[component.types[0]] !== typeof undefined) {
                var field = _self.componentForm[component.types[0]].field;
                var type = _self.componentForm[component.types[0]].type;
                var index = _self.componentForm[component.types[0]].index;

                components[index] = {
                    field: field,
                    type: type,
                    name: component.types[0],
                    short_name: component.short_name,
                    long_name: component.long_name,
                    value: (typeof component[type] !== typeof undefined) ? component[type] : component.long_name
                };
            }
        });

        $.each(components, function(c, component) {
            if (typeof component !== typeof undefined) {
                if (component.type === 'select') {
                    if (component.name === 'country') {
                        $('#' + address + '_id_country option').prop('selected', false);
                        $('#' + address + '_id_country option[data-iso-code="' + component.short_name + '"]').prop('selected', true);
                        $('#' + address + '_id_country').trigger('change');
                        _self.getCitiesByState({
                            object: address
                        });
                    } else if (typeof $('#' + address + '_id_state')[0] !== typeof undefined) {
                        components_state.push(component)

                        _self.callBackState = function() {
                            var id_state = '';

                            $.each(components_state, function(c, component_state) {
                                if ($('#' + address + '_id_state option[data-iso-code="' + component_state.short_name + '"]').length > 0) {
                                    id_state = $('#' + address + '_id_state option[data-iso-code="' + component_state.short_name + '"]').val();

                                    return false;
                                } else if ($('#' + address + '_id_state option[data-text="' + component_state.value + '"]').length > 0) {
                                    id_state = $('#' + address + '_id_state option[data-text="' + component_state.value + '"]').val();

                                    return false;
                                }
                            });
                            $('#' + address + '_id_state option').prop('selected', false);
                            $('#' + address + '_id_state').val(id_state);
                        }
                    }
                } else {
                    if (component.field != (address + '_address1')) {
                        $('#' + component.field).val(component.value);
                    }
                }
            }
        });

        if (typeof is_necessary_postcode !== typeof undefined && is_necessary_postcode) {
            $('#' + address + '_postcode', _wrap._step_one).trigger('blur');
        } else if (typeof is_necessary_city !== typeof undefined && is_necessary_city) {
            $('#' + address + '_city', _wrap._step_one).trigger('blur');
        }
    },
    updateAddressInvoice: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            callback: '',
            load_review: true
        }, params);

        if (SPSCOVAR.PRESTASHOP.CONFIGS.PS_TAX_ADDRESS_TYPE == 'id_address_invoice' || (is_virtual_cart && ($('#checkbox_create_invoice_address', _wrap._element).is(':checked') || SPSCOVAR.CONFIGS.SPSCO_REQUIRED_INVOICE_ADDRESS))) {
            var data = {
                url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
                is_ajax: true,
                action: 'updateAddressInvoice',
                dataType: 'html'
            };

            if ($('#invoice_id_country', _wrap._element).length > 0)
                data['id_country'] = $('#spstepcheckout #invoice_id_country').val();

            if ($('#invoice_id_state', _wrap._element).length > 0)
                data['id_state'] = $('#spstepcheckout #invoice_id_state').val();

            if ($('#invoice_postcode' , _wrap._element).length > 0)
                data['postcode'] = $('#spstepcheckout #invoice_postcode').val();

            if ($('#invoice_city', _wrap._element).length > 0)
                data['city'] = $('#spstepcheckout #invoice_city').val();

            if ($('#invoice_id', _wrap._element).length > 0)
                data['id_address_invoice'] = $('#spstepcheckout #invoice_id').val();

            if ($('#invoice_vat_number', _wrap._element).length > 0)
                data['vat_number'] = $('#invoice_vat_number', _wrap._element).val();

            var _json = {
                data: data,
                beforeSend: function() {
                    $('#spsco_container .loading_small' , _wrap._element).show();
                },
                success: function() {
                    Carrier.getByCountry();
                },
                complete: function() {
                    $('#spsco_container .loading_small' , _wrap._element).hide();

                    if (typeof param.callback !== typeof undefined && typeof param.callback === 'function')
                        param.callback();
                }
            };
            $.makeRequest(_json);
        }
    },
    removeAddressInvoice: function(params) {
        var _self = this,
			_wrap = stepCheckout;
		var param = $.extend({}, {
            callback: ''
        }, params);

        if (!$('#checkbox_create_invoice_address', _wrap._element).is(':checked')) {
            var data = {
                url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
                is_ajax: true,
                action: 'removeAddressInvoice',
                dataType: 'html'
            };

            var _json = {
                data: data,
                beforeSend: function() {
                    $('#spsco_container .loading_small', _wrap._element).show();
                },
                success: function() {
                    Carrier.getByCountry();
                },
                complete: function() {
                    $('#spsco_container .loading_small', _wrap._element).hide();

                    if (typeof param.callback !== typeof undefined && typeof param.callback === 'function')
                        param.callback();
                }
            };
            $.makeRequest(_json);
        }
    },
    updateState: function(params) {
        var _self = this,
			_wrap = stepCheckout;
		var param = $.extend({}, {
            object: '',
            id_state_default: '',
            id_country: ''
        }, params);

        var states = null;
        if (!$.isEmpty(param.object)) {
            var $id_country = $('#' + param.object + '_id_country', _wrap._element);
            var $id_state = $('select#' + param.object + '_id_state', _wrap._element);
            var id_country = null;

            if ($id_country.length > 0) {
                id_country = $id_country.val();
            } else {
                if (param.object == 'delivery') {
                    id_country = SPSCOVAR.id_country_delivery_default;
                } else if (param.object == 'invoice') {
                    id_country = SPSCOVAR.id_country_invoice_default;
                }
            }

            var states = countries[id_country];

            $id_state.find('option').remove();

            if (!$.isEmpty(states)) {
                var $option = $('<option/>')
                    .attr({
                        value: '',
                    }).append('--');
                $option.appendTo($id_state);

                $.each(states, function(i, state) {
                    var $option = $('<option/>')
                        .attr({
                            'data-text': state.name,
                            'data-iso-code': state.iso_code,
                            value: state.id,
                        }).append(state.name);

                    if (param.id_state_default == state.id) {
                        $option.attr('selected', 'true');
                    }

                    $option.appendTo($id_state);
                });

                if (typeof Address.callBackState === 'function') {
                    Address.callBackState();
                } else {
                    //auto select state.
                    if ($.isEmpty($id_state.find('option:selected').val())) {
                        var default_value = $id_state.attr('data-default-value');

                        if (default_value != '0') {
                            //$id_state.val(default_value);
                        } else {
                            $id_state.find(':eq(1)').attr('selected', 'true');
                        }
                    }
                }

                if (param.object == 'delivery' || (param.object == 'invoice' && ($('#checkbox_create_invoice_address', _wrap._element).is(':checked') || SPSCOVAR.CONFIGS.SPSCO_REQUIRED_INVOICE_ADDRESS))) {
                    $id_state.attr('data-validation', 'required').addClass('required');
                }
                $('#field_' + param.object + '_id_state', _wrap._element).find('sup').html('*');
                $('#field_' + param.object + '_id_state', _wrap._element).show();
            } else {
                $id_state.removeAttr('data-validation').removeClass('required');
                $('#field_' + param.object + '_id_state', _wrap._element).find('sup').html('');
                $('#field_' + param.object + '_id_state', _wrap._element).hide();
            }
        }
    },
    checkNeedInvoice: function() {
		var _self = this,
			_wrap = stepCheckout;
        if ($('#checkbox_create_invoice_address' , _wrap._element).is(':checked') || SPSCOVAR.CONFIGS.SPSCO_REQUIRED_INVOICE_ADDRESS) {
            Address.isNeedDniByCountryId({
                object: 'invoice'
            });
            Address.updateState({
                object: 'invoice'
            });

            $('#invoice_address_container .fields_container div.spsco_lock' , _wrap._element).remove();

            $('#invoice_address_container .invoice.required' , _wrap._element).each(function(i, item) {
                $(item).removeAttr('data-validation-optional');
            });

            if (typeof $.totalStorageSP !== typeof undefined) {
                $.totalStorageSP('create_invoice_address', true);
            }
        } else {
            $('#invoice_address_container .fields_container' , _wrap._element).prepend('<div class="spsco_lock"></div>');

            $('#invoice_address_container .invoice.required' , _wrap._element).each(function(i, item) {
                $(item).attr('data-validation-optional', 'true').trigger('reset');
            });

            if (typeof $.totalStorageSP !== typeof undefined) {
                $.totalStorageSP('create_invoice_address', false);
            }
        }
    },
    checkGuestAccount: function() {
        var _self = this,
			_wrap = stepCheckout;
		if (SPSCOVAR.PRESTASHOP.CONFIGS.PS_GUEST_CHECKOUT_ENABLED) {
            if ($('#checkbox_create_account_guest', _wrap._element).is(':checked')) {
                $('#field_customer_passwd, #field_customer_conf_passwd' , _wrap._element)
                    .fadeIn()
                    .addClass('required');
                $('#field_customer_passwd sup, #field_customer_conf_passwd sup', _wrap._element).html('*');
                $(' #customer_passwd, #customer_conf_passwd', _wrap._element).removeAttr('data-validation-optional').val('');
            } else {
                $('#field_customer_passwd,#field_customer_conf_passwd' , _wrap._element)
                    .fadeOut()
                    .removeClass('required')
                    .trigger('reset');
                $('#field_customer_passwd sup,#field_customer_conf_passwd sup', _wrap._element).html('');
                $('#customer_passwd, #customer_conf_passwd', _wrap._element).attr('data-validation-optional', 'true');
            }
        } else {
            if ($('#customer_passwd,  #customer_conf_passwd', _wrap._element).length) {
                if ($('#checkbox_create_account', _wrap._element).is(':checked')) {
                    $('#field_customer_passwd,#field_customer_conf_passwd' , _wrap._element)
                        .fadeIn()
                        .addClass('required');
                    $('#field_customer_passwd sup, #field_customer_conf_passwd sup', _wrap._element).html('*');
                    $('#customer_passwd, #customer_conf_passwd', _wrap._element).removeAttr('data-validation-optional').val('');
                } else {
                    $(' #field_customer_passwd, #field_customer_conf_passwd' , _wrap._element)
                        .fadeOut()
                        .removeClass('required')
                        .trigger('reset');
                    $(' #field_customer_passwd sup, #field_customer_conf_passwd sup', _wrap._element).html('');
                    $('#customer_passwd,  #customer_conf_passwd', _wrap._element).attr('data-validation-optional', 'true');
                }
            }
        }
    },
    isNeedDniByCountryId: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            object: ''
        }, params);

        if (!$.isEmpty(param.object)) {
            var id_country = null;
            var $id_country = $('select#' + param.object + '_id_country', _wrap._step_one);

            if ($id_country.length > 0) {
                id_country = $id_country.val();
            } else {
                if (param.object == 'delivery') {
                    id_country = SPSCOVAR.id_country_delivery_default;
                } else if (param.object == 'invoice') {
                    id_country = SPSCOVAR.id_country_invoice_default;
                }
            }

            if (!$.isEmpty(id_country) && typeof countries !== typeof undefined && $('#field_' + param.object + '_dni').length > 0) {
                if (countriesNeedIDNumber[id_country]) {
                    if ((param.object === 'invoice' && $(' #checkbox_create_invoice_address', _wrap._step_one).is(':checked')) ||
                        param.object === 'delivery') {
                        $('#field_' + param.object + '_dni').addClass('required').show();
                        $('#field_' + param.object + '_dni sup').html('*');
                        $('#' + param.object + '_dni').removeAttr('data-validation-optional').addClass('required');
                    } else {
                        $('#field_' + param.object + '_dni').removeClass('required').hide();
                        $('#field_' + param.object + '_dni sup').html('');
                        $('#' + param.object + '_dni').attr('data-validation-optional', 'true').removeClass('required');
                    }
                } else {
                    if ($('#' + param.object + '_dni').attr('data-required') == '0') {
                        $('#field_' + param.object + '_dni').removeClass('required');
                        $('#field_' + param.object + '_dni sup').html('');
                        $('#' + param.object + '_dni').attr('data-validation-optional', 'true').removeClass('required');
                    }
                }
            }
        }
    },
    isNeedPostCodeByCountryId: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            object: ''
        }, params);

        if (!$.isEmpty(param.object)) {
            var $id_country = $('select#' + param.object + '_id_country', _wrap._step_one);

            if ($id_country.length > 0) {
                id_country = $id_country.val();
            } else {
                if (param.object == 'delivery') {
                    id_country = SPSCOVAR.id_country_delivery_default;
                } else if (param.object == 'invoice') {
                    id_country = SPSCOVAR.id_country_invoice_default;
                }
            }

            if (!$.isEmpty(id_country) && typeof countries !== typeof undefined && $('#field_' + param.object + '_postcode').length > 0) {
                if (!$.isEmpty(countriesNeedZipCode[id_country])) {
                    var format = countriesNeedZipCode[id_country];
                    format = format.replace(/N/g, '0');
                    format = format.replace(/L/g, 'A');
                    format = format.replace(/C/g, countriesIsoCode[id_country]);
                    $('#' + param.object + '_postcode').attr('data-default-value', format);

                    $('#field_' + param.object + '_postcode').addClass('required').show();
                    $('#field_' + param.object + '_postcode sup').html('*');

                    if (param.object === 'delivery' || (param.object === 'invoice' && $('#checkbox_create_invoice_address', _wrap._element).is(':checked'))) {
                        $('#' + param.object + '_postcode').removeAttr('data-validation-optional').addClass('required');
                    }
                } else {
                    if ($('#' + param.object + '_postcode').attr('data-required') == '0') {
                        $('#field_' + param.object + '_postcode').removeClass('required');
                        $('#field_' + param.object + '_postcode sup').html('');
                        $('#' + param.object + '_postcode').attr('data-validation-optional', 'true').removeClass('required');
                    }
                }
            }
        }
    },
    checkEmailCustomer: function(email) {
		var _self = this,
			_wrap = stepCheckout;
        var data = {
            url_call: prestashop.urls.pages.order + '?checkout=1&rand=' + new Date().getTime(),
            is_ajax: true,
            dataType: 'html',
            action: 'checkRegisteredCustomerEmail',
            email: email
        };

        if (!$.isEmpty(email) && $.isEmail(email)) {
            var _json = {
                data: data,
                success: function(data) {
                    if (data != 0) {
                        _wrap._showModal({
                                name: 'email_check_modal',
                                type: 'normal',
								title: 'Upozornění',
                                content: SPSCOVAR.Msg.error_registered_email_guest,
                            });
                    }
                }
            };
            $.makeRequest(_json);
        }
    },
    clearFormByObject: function(object) {
		var _self = this,
			_wrap = stepCheckout;
        _wrap._step_one.find('.' + object).each(function(i, field) {
            $field = $(field);

            if ($field.is(':text')) {
                $field.val('');
            }

            if ($field.attr('data-field-name') == 'id_country') {
                $field.val($field.attr('data-default-value')).trigger('change');
            }
        });
    }
}

var Carrier = {
    id_delivery_option_selected: 0,
    launch: function() {
		var _self = this,
			_wrap = stepCheckout;
        if (!is_virtual_cart) {
            $('#gift_message', _wrap._element).empty();
            $('#spsco_two_container', _wrap._element)
                .on('click', '.delivery-option .delivery_option_logo', function(event) {
                    var $option_radio = $(event.currentTarget).parents('.delivery-option').find('.delivery_option_radio');
                    if (!$option_radio.is(':checked')) {
                        $option_radio.attr('checked', true).trigger('change');
                    }
                })
                .on('click', '.delivery-option .carrier_delay', function(event) {
                    var $option_radio = $(event.currentTarget).parents('.delivery-option').find('.delivery_option_radio');
                    if (!$option_radio.is(':checked')) {
                        if ($(event.currentTarget).find('.btn.btn-warning').length <= 0) { 
                            $option_radio.attr('checked', true).trigger('change');
                        }
                    }
                })
                .on('click', '.delivery_option_radio', function(event) {
                    if (typeof showWidgetMr !== typeof undefined) {
                        showWidgetMr();
                    }
                })
                .on('change', '.delivery_option_radio', function(event) {
                    $('.delivery-option',_wrap._step_two).removeClass('selected alert alert-info');
                    $(this).parent().parent().parent().addClass('selected alert alert-info');

                    Carrier.update({
                        delivery_option_selected: $(event.currentTarget),
                        load_carriers: true,
                        load_payments: false,
                        load_review: false
                    });
                })
                .on('change', '#recyclable', Carrier.update)
                .on('blur', '#gift_message', Carrier.update)
                .on('click', '#gift', function(event) {
                    Carrier.update({
                        load_payments: true
                    });

                    if ($(event.currentTarget).is(':checked'))
                        $('#gift_div_sp', _wrap._element).removeClass('hidden');
                    else
                        $('#gift_div_sp', _wrap._element).addClass('hidden');
                });
        }
    },
    getByCountry: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            callback: ''
        }, params);

        if (register_customer)
            return;

        if (!is_virtual_cart) {
            var extra_params = '';
            $.each(document.location.search.substr(1).split('&'), function(c, q) {
                if (q != undefined && q != '') {
                    var i = q.split('=');
                    if ($.isArray(i)) {
                        extra_params += '&' + i[0].toString();
                        if (i[1].toString() != undefined)
                            extra_params += '=' + i[1].toString();
                    }
                }
            });

            var data = {
                url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime() + extra_params,
                is_ajax: true,
                action: 'loadCarrier',
                dataType: 'html'
            };

            $address_delivery = stepCheckout._step_one.find('#delivery_id');
            $address_invoice = stepCheckout._step_one.find('#invoice_id');

            if ($('#delivery_id_country option', _wrap._element).length > 0)
                data['id_country'] = $('#delivery_id_country', _wrap._element).val();

            if ($('#delivery_id_state option', _wrap._element).length > 0)
                data['id_state'] = $('#delivery_id_state', _wrap._element).val();

            if ($('#delivery_postcode', _wrap._element).length > 0)
                data['postcode'] = $('#delivery_postcode', _wrap._element).val();

            if ($('#delivery_city', _wrap._element).length > 0)
                data['city'] = $('#delivery_city', _wrap._element).val();

            if ($address_delivery.length > 0)
                data['id_address_delivery'] = $address_delivery.val();

            if ($address_invoice.length > 0)
                data['id_address_invoice'] = $address_invoice.val();

            if ($('#delivery_vat_number', _wrap._element).length > 0)
                data['vat_number'] = $('#delivery_vat_number', _wrap._element).val();

            var _json = {
                data: data,
                beforeSend: function() {
                    $('#spsco_two_container .loading_small', _wrap._element).show();
                },
                success: function(html) {
                    if (!$.isEmpty(html)) {
                        _wrap._step_two.html(html);

                        if (typeof id_carrier_selected !== typeof undefined)
                            $('.delivery_option_radio[value="' + id_carrier_selected + ',"]', _wrap._element).attr('checked', true);

                        if ($('#gift', _wrap._element).is(':checked'))
                            $('#gift_div_sp', _wrap._element).show();

                        if ( _wrap._step_two.find('.alert-warning').length <= 0)
                            Carrier.update({
                                load_payments: true
                            });
                        else {
                            Payment.getByCountry();
                            Review.display();
                        }
                    }
                },
                complete: function() {
                    $('#spsco_two_container .loading_small',_wrap._element).hide();

                    $(document).trigger('sp-load-carrier:completed', {});

                    if (typeof frontDeliveryTimeLink !== typeof undefined) {
                        $('.delivery_option_radio[value="' + id_carrier_selected + '"]',_wrap._element).trigger('click');
                    }

                    if (typeof param.callback !== typeof undefined && typeof param.callback === 'function')
                        param.callback();
                }
            };
            $.makeRequest(_json);
        } else {
            Payment.getByCountry();
            Review.display();
        }
    },
    update: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            delivery_option_selected: $('.delivery_option_radio:checked',_wrap._element),
            load_carriers: false,
            load_payments: false,
            load_review: true,
            callback: ''
        }, params);

        if (!is_virtual_cart) {
            var data = {
                url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
                is_ajax: true,
                action: 'updateCarrier',
                dataType: 'html',
                recyclable: ($('#recyclable').is(':checked') ? $('#recyclable').val() : ''),
                gift: ($('#gift').is(':checked') ? $('#gift').val() : ''),
                gift_message: (!$.isEmpty($('#gift_message').val()) ? $('#gift_message').val() : '')
            };

            if ($(param.delivery_option_selected).length > 0)
                data[$(param.delivery_option_selected).attr('name')] = $(param.delivery_option_selected).val();

            $('input[type="text"]:not(.customer, .delivery, .invoice),'+
				+'input[type="hidden"]:not(.customer, .delivery, .invoice),'+
				+'select:not(.customer, .delivery, .invoice)', _wrap._step_two).each(function(i, input) {
                var name = $(input).attr('name');
                var value = $(input).val();

                if (!$.isEmpty(name))
                    data[name] = value;
            });

            var _json = {
                data: data,
                beforeSend: function() {
                    $('#spsco_two_container .loading_small',_wrap._element).show();
                },
                success: function(json) {
                    if (json.hasError) {
                       _wrap._showModal({
							type: 'error',
							title: 'Error',
							content: data.errors,
							message: data.errors
						});
                    } else if (json.hasWarning) {
                        _wrap._showModal({
                            type: 'warning',
							title: 'Upozornění',
							content: json.warnings,
                            message: json.warnings
                        });
                    }
                },
                complete: function() {
                    $('#spsco_two_container .loading_small',_wrap._element).hide();

                    if (typeof mustCheckOffer !== 'undefined' && event_dispatcher !== undefined && event_dispatcher === 'carrier' && stepCheckout._flag_offer) {
                        stepCheckout._flag_offer = false;
                        mustCheckOffer = undefined;
                        checkOffer(function() {
                        });
                    }

                    if (param.load_carriers)
                        Carrier.getByCountry();
                    if (param.load_payments)
                        Payment.getByCountry();
                    if (param.load_review && !param.load_payments)
                        Review.display();
                    if (typeof param.callback !== typeof undefined && typeof param.callback === 'function')
                        param.callback();
                }
            };
            $.makeRequest(_json);
        }
    }
}

var Payment = {
    id_payment_selected: '',
    name_module_selected: '',
    launch: function() {
		var _self = this,
			_wrap = stepCheckout;
       _wrap._step_three
            .on('click', '.module_payment_container', function(event) {
                if (!$(event.target).hasClass('payment_radio')) {
                    $(event.currentTarget).find('.payment_radio').trigger('click').trigger('change');
                }
            })
            .off('change', "input[name=payment-option]").on("change", "input[name=payment-option]", function(e) {
				e.preventDefault();
				e.stopPropagation();
                $('.extra_fee' , _wrap._step_review).addClass('hidden');

                Payment.id_payment_selected = $(this).attr('id');
                Payment.name_module_selected = $(this).val();

                $('.module_payment_container', _wrap._step_three).removeClass('selected alert alert-info');
                $('.payment_content_html', _wrap._step_three).addClass('hidden');
                $('.js-payment-option-form', _wrap._step_three).addClass('hidden');

                $(this).parents('.module_payment_container').addClass('selected alert alert-info').find('.payment_content_html, .js-payment-option-form').show().removeClass('hidden');
				if ($("#codwfeeplus_payment_infos").length){
					if ($(this).attr('data-module-name') != 'codwfeeplus') {
						_self.replaceCartSummary(false);
					}else{
						_self.replaceCartSummary(true);
					}
				}
            });
    },
	replaceCartSummary:  function (cod_active) {
		var	_wrap = stepCheckout;
        var cart_summary = $("#js-checkout-summary");
        var cart_table = $("#order-items");
        var _url = $("#codwfeeplus_payment_infos").attr("data-ajaxurl");
        var datas = {cod_active: cod_active ? 1 : 0};
		if ($("#codwfeeplus_payment_infos").length){
			$.ajax({
				type: 'POST',
				url: _url,
				data: datas,
				beforeSend: function (){
					 $('#spsco_review_container .loading_small', _wrap._element).show();
				},
				success: function (data) {
					if (typeof data !== "undefined") {
						if (cart_summary.length) {
							cart_summary.replaceWith(data.preview);
						}
						if (cart_table.length) {
							cart_table.replaceWith(data.table_preview);
						}
						$('#spsco_review_container .loading_small', _wrap._element).hide();
					}
				},
				error: function (data) {
				}

			})
		}
    },
    getByCountry: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            callback: '',
            show_loading: true
        }, params);

        if (register_customer)
            return;
        if (_wrap._step_two.find('.alert-warning').length > 0) {
            _wrap._step_three.html('<p class="alert alert-warning col-xs-12">' + SPSCOVAR.Msg.shipping_method_required + '</p>');
            return;
        }

        var extra_params = '';
        $.each(document.location.search.substr(1).split('&'), function(c, q) {
            if (q != undefined && q != '') {
                var i = q.split('=');
                if ($.isArray(i)) {
                    extra_params += '&' + i[0].toString();
                    if (i[1].toString() != undefined)
                        extra_params += '=' + i[1].toString();
                }
            }
        });

        var data = {
            url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime() + extra_params,
            is_ajax: true,
            dataType: 'html',
            action: 'loadPayment'
        };

        var _json = {
            data: data,
            beforeSend: function() {
                if (param.show_loading) {
                    $('#spsco_three_container .loading_small',_wrap._element).show();
                }
            },
            success: function(html) {
                _wrap._step_three.html('');
                _wrap._step_three.html(html);
                $('.module_payment_container', _wrap._step_three).removeClass('selected alert alert-info');
                $('.payment_content_html', _wrap._step_three).addClass('hidden');
                $('.js-payment-option-form', _wrap._step_three).addClass('hidden');
                $('.module_payment_container.selected', _wrap._step_three).find('.payment_content_html').removeClass('hidden');

                if (!$.isEmpty(Payment.id_payment_selected)) {
                    $('#payment_method_container #' + Payment.id_payment_selected, _wrap._step_three).parent().parent().trigger('click');
                } else if ($('#payment_method_container .module_payment_container', _wrap._step_three).length == 1) {
                    $('#payment_method_container .module_payment_container', _wrap._step_three).trigger('click');
                } else if (!$.isEmpty(SPSCOVAR.CONFIGS.SPSCO_DEFAULT_PAYMENT_METHOD)) {
                    $('#payment_method_container [value="' + SPSCOVAR.CONFIGS.SPSCO_DEFAULT_PAYMENT_METHOD + '"]', _wrap._step_three).parent().parent().trigger('click');
                }
            },
            complete: function() {
                if (param.show_loading)
                    $('#spsco_three_container .loading_small', _wrap._element).hide();

                if (typeof param.callback !== typeof undefined && typeof param.callback === 'function') {
                    param.callback();
                } else {
                    Review.display();
                }
				
				if (typeof stripe_isInit !== typeof undefined && typeof StripePubKey !== typeof undefined && typeof initStripeOfficial !== typeof undefined) {
                    if (StripePubKey && typeof stripe_v3 !== 'object') {
                        stripe_v3 = Stripe(StripePubKey);
                    }
                    initStripeOfficial();
                }
				
            }
        };
        $.makeRequest(_json);
    },
    change: function() {
		var _self = this,
			_wrap = stepCheckout;
        if (!_wrap._flag_offer || typeof mustCheckOffer === 'undefined' || (event_dispatcher !== undefined && event_dispatcher !== 'payment_method')) {
        } else {
            _wrap._flag_offer = false;
            checkOffer(function() {
            });
        }
    }
}

var Review = {
    message_order: '',
    launch: function() {
		var _self = this,
			_wrap = stepCheckout;
        _wrap._step_review.find('.remove-from-cart').off('click');

        _wrap._element
			.on('click', '#conditions-to-approve label, #conditions-to-approve input', function(e) {
				var _interval = setInterval(function() {
					if($('#payment-confirmation button[type=submit]',_wrap._element).removeAttr('disabled') === 'disabled') {
						clearInterval(_interval);
						$('#payment-confirmation button[type=submit]',_wrap._element).removeAttr('disabled');
					}    
				}, 500);
			})
            .on('click', '#conditions-to-approve a', function(e) {
                e.preventDefault();
                e.stopPropagation();

                _wrap._openCMS({
                    id_cms: SPSCOVAR.CONFIGS.PS_CONDITIONS_CMS_ID
                });
            })
            .on("click", "#btn_place_order", function() {
                if (parseInt(SPSCOVAR.CONFIGS.SPSCO_PAYMENTS_WITHOUT_RADIO) && $('#free_order', _wrap._step_three).length <= 0) {
                    window.scrollTo(0, _wrap._element.offset().top);
                    _wrap._step_three.addClass('alert alert-warning');
                    return false;
                } else {
                    Review.placeOrder();
                }
            })
			.on("click", "#btn_continue_shopping", function() {
				var _href = $(this).data('link');
				if (typeof _href === typeof undefined) {
					_href = prestashop.urls.pages.index;
				}
				window.location = _href;
			})
            .on("change", '#cgv', function(e) {
                if (typeof mustCheckOffer !== 'undefined' && event_dispatcher !== undefined && event_dispatcher === 'terms' && _wrap._flag_offer) {
                    if ($(e.target).is(':checked')) {
                        if (!offerApplied) {
                            _wrap._flag_offer = false;
                            checkOffer(function() {
                                $(e.target).unbind('change');
                            });
                        }
                    }
                }
            });

        _wrap._step_review
            .on('click', '.bootstrap-touchspin-up', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var url_call = '';
                var $input = $(e.currentTarget).parents('.bootstrap-touchspin').find('.cart-line-product-quantity');
				var max_quantity = parseInt($input.attr('data-quantity-available'));
				var current_value = parseInt($input.val());
                if ($(e.currentTarget).hasClass('bootstrap-touchspin-up')) {
                    url_call = $input.data('up-url');
                } else if ($(e.currentTarget).hasClass('bootstrap-touchspin-down')) {
                    url_call = $input.data('down-url');
                } else {
                    url_call = $(e.currentTarget).attr('href');
                }
				if (current_value < max_quantity || $input.attr('data-allow_oosp') == 1){
                var _json = {
                    data: {
                        url_call: url_call,
                        action: 'update',
                        ajax: 1,
                        token: static_token
                    },
                    beforeSend: function() {
                        $('#spsco_review_container .loading_small',_wrap._element).show();
                    },
                    success: function(json) {
                        if (json.success) {
                            Review.updateCartSummary(json);
                        } else if (json.hasError && json.errors.length > 0) {
                            $(e.currentTarget).val(json.quantity);
                            _wrap._showModal({
								type: 'error',
								title: 'Error',
								content: data.errors,
								message: data.errors
							});

                            $('#spsco_review_container .loading_small',_wrap._element).hide();
                        }
                    }
                };
                $.makeRequest(_json);
				} else {
					var notice_quantity = $input.attr('data-product-name') + $input.attr('data-notice-quantity');
					 _wrap._showModal({
						type: 'warning',
						title: 'Upozornění',
						content: notice_quantity
					});
					$input.val(max_quantity);
					$input.trigger('click');
				}
            })
			.on('click', '.bootstrap-touchspin-down, .remove-from-cart', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var url_call = '';
                var $input = $(e.currentTarget).parents('.bootstrap-touchspin').find('.cart-line-product-quantity');
				var max_quantity = parseInt($input.attr('data-quantity-available'));
				var current_value = parseInt($input.val());
                if ($(e.currentTarget).hasClass('bootstrap-touchspin-up')) {
                    url_call = $input.data('up-url');
                } else if ($(e.currentTarget).hasClass('bootstrap-touchspin-down')) {
                    url_call = $input.data('down-url');
                } else {
                    url_call = $(e.currentTarget).attr('href');
                }
				if (current_value <= max_quantity || $(e.currentTarget).hasClass('remove-from-cart') || $input.attr('data-allow_oosp') == 1){
					var _json = {
						data: {
							url_call: url_call,
							action: 'update',
							ajax: 1,
							token: static_token
						},
						beforeSend: function() {
							$('#spsco_review_container .loading_small',_wrap._element).show();
						},
						success: function(json) {
							if (json.success) {
								Review.updateCartSummary(json);
							} else if (json.hasError && json.errors.length > 0) {
								$(e.currentTarget).val(json.quantity);
								_wrap._showModal({
									type: 'error',
									title: 'Error',
									content: data.errors,
									message: data.errors
								});

								$('#spsco_review_container .loading_small',_wrap._element).hide();
							}
						}
					};
					$.makeRequest(_json);
				} else {
					var notice_quantity = $input.attr('data-product-name') + $input.attr('data-notice-quantity');
					 _wrap._showModal({
						type: 'warning',
						title: 'Upozornění',
						content: notice_quantity
					});
					$input.val(max_quantity);
					$input.trigger('click');
				}
            })
			.off('click',".#promo-code form button, .promo-name li a")
            .on("click", "#promo-code form button, .promo-name li a", Review.processDiscount)
            .on("click", "#payment_paypal_express_checkout", function() {
                $('#paypal_payment_form').submit();
            })
            .on('blur', '.cart-line-product-quantity', function(e) {
				e.preventDefault();
				e.stopPropagation();
                var before_qty = $(e.currentTarget).attr('value');
                var actual_qty = parseInt($(e.currentTarget).val());
				var max_quantity = parseInt($(e.currentTarget).attr('data-quantity-available'));
                if (actual_qty == 0) {
                    $(e.currentTarget).val(before_qty);
                } else if (actual_qty <= max_quantity || $(e.currentTarget).attr('data-allow_oosp') == 1){
                    var operation = 'down';
                    var qty = actual_qty - before_qty;

                    if (qty != 0) {
                        var url_call = $(e.currentTarget).data('update-url');

                        if (qty > 0) {
                            operation = 'up';
                        }

                        var _json = {
                            data: {
                                url_call: url_call,
                                action: 'update',
                                ajax: 1,
                                token: static_token,
                                op: operation,
                                qty: Math.abs(qty)
                            },
                            beforeSend: function() {
                                $('#btn_place_order',_wrap._element).attr('disabled', 'true');
                                $('#spsco_review_container .loading_small',_wrap._element).show();
                            },
                            success: function(json) {
                                if (json.success) {
                                    Review.updateCartSummary(json);
                                } else if (json.hasError && json.errors.length > 0) {
                                    $(e.currentTarget).val(json.quantity);

                                    _wrap._showModal({
										type: 'error',
										title: 'Error',
										content: data.errors,
										message: data.errors
									});

                                    $('#spsco_review_container .loading_small',_wrap._element).hide();
                                }
                            }
                        };
                        $.makeRequest(_json);
                    }
                } else{
					var notice_quantity = $(e.currentTarget).attr('data-product-name') + $(e.currentTarget).attr('data-notice-quantity');
					_wrap._showModal({
						type: 'warning',
						title: 'Upozornění',
						content: notice_quantity
					});	
					$(e.currentTarget).val(max_quantity);
					$(e.currentTarget).trigger('blur');
				}
            })
            .on("blur", ".spsco-leave-message #message", function() {
                Review.message_order = $(this).val();
            });
			
			
    },

    updateCartSummary: function(json) {
        var _self = this,
			_wrap = stepCheckout;
        if ($('.blockcart').length > 0 || $('.spblockcart').length > 0 || $('.cart_mobilelayout').length > 0) {
            
			if($('.cart_mobilelayout').length > 0){
				var refreshURL = $('.cart_mobilelayout').data('refresh-url');
			}else{
				var refreshURL = $('.spblockcart').data('refresh-url');
			}

            $.post(refreshURL, {}).then(function(resp) {
				if($('.cart_mobilelayout').length > 0){
					$('.cart_mobilelayout .cart-products-count').text($(resp.preview).find(".cart-products-count").html());
				}else{
					$('.blockcart, .spblockcart').replaceWith($(resp.preview).find('.blockcart, .spblockcart'));
				}
                
            });
        }

        if (typeof json !== typeof undefined) {
            if (json.is_virtual_cart) {
                $('#spsco_two_container').remove();
                $('#spsco_three_container').removeClass('col-md-6');

                if (!SPSCOVAR.SHOW_DELIVERY_VIRTUAL) {
                    $('#nav_address', _wrap._step_one).remove();
                    $('#nav_invoice', _wrap._step_one).addClass('active');
                    $('#delivery_address_container', _wrap._step_one).remove();
                    $('#invoice_address_container', _wrap._step_one).addClass('active');
                }

                Payment.getByCountry();
                Review.display();
            } else {
                if (typeof json.load === typeof undefined) {
                    $('#spsco_review_container .loading_small',  _wrap._element);

                    Carrier.getByCountry();
                }
            }
        }
		
    },
    display: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            callback: ''
        }, params);

        if (register_customer)
            return;

        if (SPSCOVAR.CONFIGS.PS_CONDITIONS)
            var cgv = $('#cgv').is(':checked');

        var id_country = !$.isEmpty($('#delivery_id_country').val()) ? $('#delivery_id_country').val() : '';
        var id_state = !$.isEmpty($('#delivery_id_state').val()) ? $('#delivery_id_state').val() : '';

        if (is_virtual_cart) {
            if ($('#checkbox_create_invoice_address', _wrap._element).is(':checked') || SPSCOVAR.CONFIGS.SPSCO_REQUIRED_INVOICE_ADDRESS) {
                if (_wrap._step_one.find('#invoice_id_country').length > 0) {
                    id_country = _wrap._step_one.find('#invoice_id_country').val();
                } else {
                    id_country = SPSCOVAR.id_country_invoice_default;
                }
            }
        }

        var data = {
            url_call: prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
            is_ajax: true,
            dataType: 'html',
            action: 'loadReview',
            id_country: id_country,
            id_state: id_state
        };

        var _json = {
            data: data,
            beforeSend: function() {
                $('#spsco_review_container .loading_small', _wrap._element).show();
            },
            success: function(html) {
				_wrap._step_review.html(html);

                if (SPSCOVAR.CONFIGS.PS_CONDITIONS && cgv)
                    $('#cgv', _wrap._element).attr('checked', 'true');


            },
            complete: function() {
                $('#spsco_review_container .loading_small',_wrap._element).hide();

                if ($('p.alert-warning', _wrap._step_two).length > 0) {
                    $('#spsco_review_container .item_total:not(.cart_total_product)',_wrap._element).hide();
                }

                $('#container_express_checkout').remove();

                if (SPSCOVAR.CONFIGS.SPSCO_SHOW_ZOOM_IMAGE_PRODUCT) {
                    //image zoom on product list.
                    $('#order-detail-content .cart_item a > img',_wrap._element).mouseenter(function(event) {
                        $('#order-detail-content .image_zoom',_wrap._element).hide();
                        $(event.currentTarget).parents('.image_product').find('.image_zoom').show();
                    });
                    $('#order-detail-content .image_zoom',_wrap._element).click(function(event) {
                        $(event.currentTarget).toggle();
                    });
                    $('#order-detail-content .image_zoom',_wrap._element).hover(function(event) {
                        $(event.currentTarget).show();
                    }, function(event) {
                        $(event.currentTarget).hide();
                    });
                }
                if (typeof mustCheckOffer !== 'undefined' && event_dispatcher !== undefined && event_dispatcher === 'init' && _wrap._flag_offer) {
                    _wrap._flag_offer = false;
                    mustCheckOffer = undefined;
                    setTimeout(checkOffer, time_load_offer * 1000);
                }
                if ($('input[type=radio][name=payment-option]').is(':checked')){
					if ($('input[type=radio][name=payment-option]:checked').attr('data-module-name') != 'codwfeeplus'){
						Payment.replaceCartSummary(false);
					}else{
						Payment.replaceCartSummary(true);
					}
				}
                $('#spsco_review_container #message',_wrap._element).val(Review.message_order);

                if (typeof param.callback !== typeof undefined && typeof param.callback === 'function')
                    param.callback();
            }
        };
        $.makeRequest(_json);
    },
	
    processDiscount: function(e) {
		var _self = this,
			_wrap = stepCheckout,
			$element = $(e.currentTarget);
		e.preventDefault();	
        var _data = {
            url_call: prestashop.urls.pages.cart,
            action: 'update',
            ajax: 1,
            token: static_token
        }
        if ($element.attr('data-link-action') == 'remove-voucher') {
		   var _value = new RegExp('[\?&]' + 'deleteDiscount' + '=([^&#]*)').exec($element.attr('href'));
		   _data.deleteDiscount = _value[1] || 0;
        } else {
            _data.addDiscount = 1;
            _data.discount_name = _wrap._step_review.find('input[name=discount_name]').val();
        }

        var _json = {
            data: _data,
            beforeSend: function() {
                $('#spsco_review_container .loading_small', _wrap._element).show();
            },
            success: function(json) {
                if (json.hasError) {
                    $('#spsco_review_container .loading_small', _wrap._element).hide();
                    _wrap._showModal({
                        title: 'Errors',
                        content: json.errors
                    });
                } else {
                    if ($('#input_virtual_carrier' , _wrap._step_two).length > 0) {
                        Payment.getByCountry();
                    } else {
                        Carrier.getByCountry();
                    }
                }
            },
            complete: function() {
                $('#submitAddDiscount',_wrap._step_review).attr('disabled', false);
            }
        };
        $.makeRequest(_json);
    },
    getFields: function() {
		var _self = this,
			_wrap = stepCheckout;
        var fields = Array();
        var $paypalpro_payment_form = $('#paypalpro-payment-form', _wrap._step_three);
        $('.customer, .delivery, .invoice', _wrap._step_one)
            .each(function(i, field) {
                if ($(field).is('span'))
                    return true;

                var name = $(field).attr('data-field-name');
                var value = '';
                var object = '';

                if ($.isEmpty(name))
                    return true;

                if ($(field).hasClass('customer')) {
                    object = 'customer';
                } else if ($(field).hasClass('delivery')) {
                    object = 'delivery';
                } else if ($(field).hasClass('invoice')) {
                    object = 'invoice';
                }

                if (object == 'invoice' && $('#checkbox_create_invoice_address', _wrap._step_one).length > 0) {
                    if (!$('#checkbox_create_invoice_address', _wrap._element).is(':checked'))
                        return true;
                }

                if (!$.isEmpty(object)) {
                    if ($(field).is(':checkbox')) {
                        value = $(field).is(':checked') ? 1 : 0;
                    } else if ($(field).is(':radio')) {
                        var tmp_value = $('input[name="' + name + '"]:checked').val();
                        if (typeof tmp_value !== typeof undefined)
                            value = tmp_value;
                    } else {
                        value = $(field).val();

                        if (value === null)
                            value = '';
                    }

                    if ($.strpos(value, '\\')) {
                        value = (value + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
                    }

                    if ($.strpos(value, '\n')) {
                        value = value.replace(/\n/gi, '\\n');
                    }

                    if (!$.isEmpty(value) && typeof value == 'string') {
                        value = value.replace(/\"/g, '\'');
                    }

                    value = $.trim(value);

                    fields.push({
                        'object': object,
                        'name': name,
                        'value': value
                    });
                    
                }
            });

        return fields;
    },
    getFieldsExtra: function(_data) {
		var _self = this,
			_wrap = stepCheckout;
        $('input[type="text"]:not(.customer, .delivery, .invoice), input[type="hidden"]:not(.customer, .delivery, .invoice), select:not(.customer, .delivery, .invoice)', _wrap._element_form).each(function(i, input) {
            var name = $(input).attr('name');
            var value = $(input).val();

            if (name == 'action') {
                return;
            }

            if (!$.isEmpty(name))
                _data[name] = value;
        });

        $('input[type="checkbox"]:not(.customer, .delivery, .invoice)', _wrap._element_form).each(function(i, input) {
            var name = $(input).attr('name');
            var value = $(input).is(':checked') ? $(input).val() : '';

            if (!$.isEmpty(name))
                _data[name] = value;
        });

        $('input[type="radio"]:not(.customer, .delivery, .invoice):checked', _wrap._element_form).each(function(i, input) {
            var name = $(input).attr('name');
            var value = $(input).val();

            if (!$.isEmpty(name))
                _data[name] = value;
        });

        delete _data['id_customer'];
        _data['id_customer'];
        _data['id_customer'];

        return _data;
    },
    placeOrder: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            validate_payment: true,
            position_element: null
        }, params);

        if ($('.delivery-option.selected div.extra_info_carrier a.select_pickup_point',_wrap._step_two).length > 0) {
            alert(SPSCOVAR.Msg.need_select_pickup_point);

            $('.delivery-option.selected div.extra_info_carrier a.select_pickup_point',_wrap._step_two).trigger('click');

            return false;
        }

        $('#btn_place_order',_wrap._element).attr('disabled', 'true');

        var fields = Review.validateAllForm({
            validate_payment: param.validate_payment
        });

        if (fields && _wrap._flag_valid) {
            var invoice_id = '';

            if (SPSCOVAR.CONFIGS.SPSCO_ENABLE_INVOICE_ADDRESS && $('#checkbox_create_invoice_address',_wrap._element).length > 0) {
                if ($('#checkbox_create_invoice_address',_wrap._element).is(':checked')) {
                    invoice_id = $('#invoice_id').val();
                }
            } else {
                invoice_id = $('#invoice_id').val();
            }

            var _extra_data = Review.getFieldsExtra({});
            var _data = $.extend({}, _extra_data, {
                'url_call': prestashop.urls.pages.order + '?rand=' + new Date().getTime(),
                'is_ajax': true,
                'action': 'placeOrder',
                'id_customer': (!$.isEmpty(_wrap._step_one.find('#customer_id').val()) ? _wrap._step_one.find('#customer_id').val() : ''),
                'id_address_delivery': (!$.isEmpty(_wrap._step_one.find('#delivery_id').val()) ? _wrap._step_one.find('#delivery_id').val() : ''),
                'id_address_invoice': invoice_id,
                'fields_sp': JSON.stringify(fields),
                'message': (!$.isEmpty(_wrap._step_review.find('#message').val()) ? _wrap._step_review.find('#message').val() : ''),
                'is_new_customer': (_wrap._step_one.find('#checkbox_create_account_guest').is(':checked') ? 0 : 1),
                'token': static_token
            });

            var _json = {
                data: _data,
                beforeSend: function() {
                    _wrap._loadingBig(true);
                    window.scrollTo(0, _wrap._element.outerHeight() / 3);
                },
                success: function(data) {
                    if (data.isSaved && (!SPSCOVAR.PS_GUEST_CHECKOUT_ENABLED || $('#checkbox_create_account_guest', _wrap._step_one).is(':checked'))) {
                        _wrap._step_one.find('#customer_id').val(data.id_customer);
                        _wrap._step_one.find('#customer_email, #customer_conf_email, #customer_passwd, #customer_conf_passwd').attr({
                            'disabled': 'true',
                            'data-validation-optional': 'true'
                        }).addClass('disabled').trigger('reset');

                        $(' #field_customer_passwd, #field_customer_conf_passwd, #spstepcheckout #spsco_container .account_creation, #field_customer_checkbox_create_account', _wrap._element).addClass('hidden');
                    }

                    if (data.hasError) {
                       _wrap._showModal({
							type: 'error',
							title: 'Error',
							content: data.errors,
							message: data.errors
						});
                    } else if (data.hasWarning) {
                        _wrap._showModal({
                            type: 'warning',
							title: 'Upozornění',
							content: data.warnings,
                            message:data.warnings
                        });
                    } else {
                        if (typeof $.totalStorageSP !== typeof undefined) {
                            $.totalStorageSP('id_address_delivery', data.id_address_delivery);
                            $.totalStorageSP('id_address_invoice', data.id_address_invoice);
                        }

                        if (!SPSCOVAR.PRESTASHOP.CONFIGS.PS_GUEST_CHECKOUT_ENABLED || $('#checkbox_create_account_guest', _wrap._step_one).is(':checked')) {
                            $('#field_delivery_id, #field_invoice_id' , _wrap._element).removeClass('hidden');
                            $('#field_customer_checkbox_create_account_guest', _wrap._element).addClass('hidden');
                        }

                        if (!stepCheckout._flag_offer || typeof mustCheckOffer === 'undefined' || (event_dispatcher !== undefined && event_dispatcher !== 'confirm')) {
                            window['checkOffer'] = function(callback) {
                                callback();
                            };
                        }

                        if (param.validate_payment === true) {
                            var $payment_selected = stepCheckout._step_three.find('#' + Payment.id_payment_selected + ':checked');
                            var name_payment = $payment_selected.val();
                            var arr_reload_payment_modules = ['sofortbanking', 'ps_checkpayment'];

                            var callback_placeorder = function() {
                                var $payment_selected = stepCheckout._step_three.find('#' + Payment.id_payment_selected + ':checked');
                                var url_payment = $payment_selected.next().val();
                                var form_payment_selected = $payment_selected.parents('.module_payment_container.selected').find('form')[0];

                                if (typeof form_payment_selected !== typeof undefined) {
                                    if (name_payment == 'culqi' && typeof Culqi !== typeof undefined) {
                                        Culqi.createToken();

                                        if (!$.isEmpty(Culqi.token)) {
                                            form_payment_selected.submit();
                                        } else {
                                            stepCheckout._loadingBig(false);
                                        }
                                    } else {
                                        form_payment_selected.submit();
                                    }
                                } else {
                                    window.location = url_payment;
                                }
                            };

                            if (!SPSCOVAR.IS_LOGGED && $.inArray(name_payment, arr_reload_payment_modules) != -1) {
                                Payment.getByCountry({
                                    show_loading: false,
                                    callback: callback_placeorder
                                });
                            } else {
                                callback_placeorder();
                            }
                        }
                    }
					_wrap._loadingBig(false);
                },
                complete: function() {},
                error: function(data) {
                    alert(data);
                    _wrap._loadingBig(false);
                }
            };
            $.makeRequest(_json);
        }
    },
    validateAllForm: function(params) {
		var _self = this,
			_wrap = stepCheckout;
        var param = $.extend({}, {
            validate_payment: true
        }, params);

        _wrap._element_form.submit();

        if (!stepCheckout._flag_valid) {
            if ($('#delivery_address_container .required.has-error', _wrap._element).length == 0 && $('#invoice_address_container .required.has-error', _wrap._element).length > 0) {
                $('#nav_address .nav-link, #delivery_address_container' , _wrap._step_one).removeClass('active');
                $('#nav_invoice .nav-link, #invoice_address_container', _wrap._step_one).removeClass('active');

                $('#nav_invoice .nav-link, #invoice_address_container', _wrap._step_one).addClass('active');
            }
        }

        if (stepCheckout._flag_valid) {
            _wrap._step_two.removeClass('alert alert-danger');
            _wrap._step_three.removeClass('alert alert-warning');
            $('#conditions-to-approve label',  _wrap._step_review).removeClass('alert alert-warning');

            if ($('.delivery_options_address',_wrap._step_two).length >= 0 && !is_virtual_cart) {
                var id_carrier = $('.delivery_option_radio:checked',_wrap._step_two).val();

                if (!$.isEmpty(id_carrier)) {
                    Carrier.id_delivery_option_selected = id_carrier;

                    stepCheckout._flag_valid = true;
                } else {
                    Carrier.id_delivery_option_selected = null;
                    $('#shipping_container',_wrap._step_two).addClass('alert alert-warning');
					_wrap._showModal({	
                        type: 'warning',
						title: 'Upozornění',
						content: SPSCOVAR.Msg.shipping_method_required,
                        message: SPSCOVAR.Msg.shipping_method_required
                    });
                    stepCheckout._flag_valid = false;
                }
            }

            if (stepCheckout._flag_valid && param.validate_payment === true) {
                if ($('#free_order',_wrap._step_three).length <= 0) {
                    var payment = $('input[name="payment-option"]:checked',_wrap._step_three);

                    if (payment.length > 0) {
                        Payment.id_payment_selected = $(payment).attr('id');

                        stepCheckout._flag_valid = true;
                    } else {
                        Payment.id_payment_selected = '';
						_wrap._step_three.addClass('alert alert-warning');
						
						_wrap._showModal({	
							type: 'warning',
							title: 'Upozornění',
							content: SPSCOVAR.Msg.payment_method_required,
							message: SPSCOVAR.Msg.payment_method_required
						});

						_wrap._flag_valid = false;
                    }
                }
            }

            if (_wrap._flag_valid && _wrap._step_review.find('#conditions-to-approve').length) {
                _wrap._step_review.find('#conditions-to-approve input').each(function(i, condition) {
                    if (!$(condition).is(':checked')) {
                        $(condition).parent().addClass('alert alert-warning');

                        _wrap._flag_valid = false;
                    }
                });

                if (!_wrap._flag_valid) {
					_wrap._showModal({	
						type: 'warning',
						title: 'Upozornění',
						content: SPSCOVAR.Msg.agree_terms_and_conditions,
						message: SPSCOVAR.Msg.agree_terms_and_conditions
					});
                }
            }



            if (_wrap._flag_valid) {
                $('#btn_place_order',_wrap._element).removeAttr('disabled');
                return Review.getFields();
            }
        } else {
			_wrap._showModal({
                type: 'warning',
				title:'Upozornění',
				content: SPSCOVAR.Msg.fields_required_to_process_order + '\n' + SPSCOVAR.Msg.check_fields_highlighted,
                message: SPSCOVAR.Msg.fields_required_to_process_order + '\n' + SPSCOVAR.Msg.check_fields_highlighted
            });
        }

        $('#btn_place_order',_wrap._element).removeAttr('disabled');

        return false;
    }
}

$(document).ready(function(){
	 stepCheckout.init();
});
