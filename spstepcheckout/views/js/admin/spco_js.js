$(document).ready(function () {
	
	var spco_tabs = $('#spco_tabs');
	var spco_content = $('#spco_content');
	var url_ajax = spco_content.attr('data-url');
	$('div[id^="fieldset_"]', spco_content).addClass('spco-panel');
	$('div[id^="fieldset_"]:first', spco_content).addClass('spco-open');
	$('.tab-page', spco_tabs).on('click', function (e){
		e.preventDefault();
		var elem = $(this);
		var target = $(elem.data('target'));
		elem.parent().addClass('active').siblings().removeClass('active');
		$('.spco-panel').removeClass('spco-open');
		target.addClass('spco-open');
	});
	$(document).on('change', 'input[name=SPSCO_ENABLE_INVOICE_ADDRESS]', function() {
		if ($(this).val() == '1') {
			$('.invoice-address').removeClass('hide').show('slow');
		} else {
			$('.invoice-address').addClass('hide').hide('slow');
		}
	});
	
	
	$(document).on('change', 'input[name=SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS]', function() {
		if ($(this).val() == '1') {
			$('.google-address').removeClass('hide').show('slow');
		} else {
			$('.google-address').addClass('hide').hide('slow');
		}
	});
	
	$(document).on('change', 'input[name=PS_CONDITIONS]', function() {
		if ($(this).val() == '1') {
			$('.terms-conditions').removeClass('hide').show('slow');
		} else {
			$('.terms-conditions').addClass('hide').hide('slow');
		}
	});
	
	$(document).on('change', 'input[name=SPSCO_SHOW_LINK_CONTINUE_SHOPPING]', function() {
		if ($(this).val() == '1') {
			$('.link-continue').removeClass('hide').show('slow');
		} else {
			$('.link-continue').addClass('hide').hide('slow');
		}
	});
	
	
	
	var $field = $('.spsco-fields', spco_content);
	$field.sortable({
        opacity: .6,
        cursor: "move",
        update: function(b, c) {
			var _object = $(b.target).attr('data-object');
			console.log(_object);
            var d = $(this).sortable("serialize") + "&action=AjaxCall&ajax=1&updateItemsPosition=1&object="+_object+"&rand=" + new Date().getTime();
            $.post(url_ajax, d, function(json) {
                if (1 == json.success) {
					$('#SPSCO_FIELDS_SETUP').val(json.value);
					showSuccessMessage(json.message);
				}
            }, "json");
        }
    });
    $field.hover(function() {
        $(this).css("cursor", "move");
    }, function() {
        $(this).css("cursor", "auto");
    });
	
	$(document).off("click.status").on("click.status", '#spco_content .change-tatus', function(e){
		e.preventDefault();
		var _type = $(this).attr('data-action'),
			_object = $(this).attr('data-field'),
			_field = $(this).parents('.spsco-field').attr('data-id'),
			_parent =  $(this).parents('.spsco-field');
		if (!_parent.hasClass('disabled')) {
			 $.ajax({
				url: url_ajax + "&rand=" + new Date().getTime(),
				type: "POST",
				data: {
					action: "AjaxCall",
					ajax: 1,
					statusItem : 1,
					type: _type,
					object: _object,
					field: _field
				},
				cache: false,
				dataType: "json",
				success: function(json) {
					console.log(json);
					if (json.success){
						if (_object){
							$('#SPSCO_FIELDS_SETUP').val(json.value);
							if (_type == 'changeStatus'){
								$('.field-active .btn-group ',$('[data-id="'+_field+'"]')).html(json.html);
							}else{
								$('.field-required  .btn-group ',$('[data-id="'+_field+'"]')).html(json.html);
							}
						}
						showSuccessMessage(json.message);
					}
				}
				
			});
		}
	});

});





