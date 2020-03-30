{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}

{block name='step_carrier'}

<script type="text/javascript">
    var is_necessary_postcode = Boolean({if isset($is_necessary_postcode)}{$is_necessary_postcode}{/if});
    var is_necessary_city = Boolean({if isset($is_necessary_city)}{$is_necessary_city}{/if});
    var id_carrier_selected = '{if isset($delivery_option)}{$delivery_option}{/if}';

    {literal}
        if (is_necessary_postcode)
            $('div#spstepcheckout')
                .off('blur', 'input#delivery_postcode', Carrier.getByCountry)
                .on('blur', 'input#delivery_postcode', Carrier.getByCountry);
        if (is_necessary_city)
            $('div#spstepcheckout')
                .off('blur', 'input#delivery_city', Carrier.getByCountry)
                .on('blur', 'input#delivery_city', Carrier.getByCountry);
    {/literal}
</script>

    {if isset($is_virtual_cart) && $is_virtual_cart}
        <input id="input_virtual_carrier" class="hidden" type="hidden" name="id_carrier" value="0" />
    {else}
        {if ($hasError)}
            <p class="alert alert-warning">
                {foreach from=$errors key=k item="error" name="f_errors"}
                    -&nbsp;{$error}
                    {if !$smarty.foreach.f_errors.last}<br/><br/>{/if}
                {/foreach}
            </p>

			<button class="btn btn-info pull-right btn-sm" type="button" onclick="Carrier.getByCountry();">
				<i class="fa fa-spinner"></i>
				{l s='Reload' mod='spstepcheckout'}
			</button>
            <div class="clear"></div>
        {else}
            <div id="hook-display-before-carrier">
                {$hookDisplayBeforeCarrier nofilter}
            </div>

            <div id="shipping_container">
                {if $delivery_options|count}
                    <div id="js-delivery" class="delivery-options">
                        {foreach from=$delivery_options item=carrier key=carrier_id}
                            <div class="delivery-option delivery_option_{$carrier.id}{if $delivery_option == $carrier_id} selected alert alert-info{/if}">
                                <div class="row">
                                    <div class="col-auto d-flex align-items-center">
										<span class="custom-radio float-xs-left">
											<input class="delivery_option_radio not_unifrom not_uniform" type="radio" name="delivery_option[{$id_address}]" id="delivery_option_{$carrier.id}" value="{$carrier_id}" {if $delivery_option == $carrier_id}checked{/if} />
											<span></span>
										</span>
                                    </div>
                                    <div class="delivery_option_logo d-flex align-items-center {if !$CONFIGS.SPSCO_SHOW_IMAGE_CARRIER && !$CONFIGS.SPSCO_SHOW_DESCRIPTION_CARRIER}col-xs-11 col-11{else}{if $CONFIGS.SPSCO_SHOW_IMAGE_CARRIER}{else}col-xs-4 col-4{/if}{/if}">
                                        {if ($CONFIGS.SPSCO_SHOW_IMAGE_CARRIER)}
                                            {if $carrier.logo}
                                                <img src="{$carrier.logo}" alt="{$carrier.name}"/>
                                            {else}
                                                <img src="{$PATH_IMG}shipping.png" alt="{$carrier.name}"/>
                                            {/if}
                                        {else}
                                            <div class="delivery_option_title">{$carrier.name}</div>
                                        {/if}

                                        {if $carrier.external_module_name != ''}
                                            <input type="hidden" class="module_carrier" name="{$carrier.external_module_name}" value="delivery_option_{$id_address}_{$carrier@index}" />
                                            <input type="hidden" name="name_carrier" id="name_carrier_{$id_address}_{$carrier@index}" value="{$carrier.name}" />
                                        {/if}
                                    </div>
                                    {if $CONFIGS.SPSCO_SHOW_IMAGE_CARRIER || $CONFIGS.SPSCO_SHOW_DESCRIPTION_CARRIER}
                                        <div class="carrier_delay {if $CONFIGS.SPSCO_SHOW_IMAGE_CARRIER}col{else}col-xs-7 col-7{/if}">
                                            {if $CONFIGS.SPSCO_SHOW_IMAGE_CARRIER}
                                                <div class="delivery_option_title">{$carrier.name}</div>
                                            {/if}
                                            {if $CONFIGS.SPSCO_SHOW_DESCRIPTION_CARRIER and $carrier.delay}
                                                <div class="delivery_option_delay">
                                                    {$carrier.delay}
                                                </div>
                                            {/if}
                                            <div class="delivery_option_price">
                                                {$carrier.price}
                                            </div>
                                        </div>
                                    {/if}
                                    {if $carrier.external_module_name != '' && isset($carrier.extra_info_carrier)}
                                        <div class="extra_info_carrier pull-right" style="display:{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key}block{else}none{/if}">
                                            {if not empty($carrier.extra_info_carrier)}
                                                <span>{$carrier.extra_info_carrier}</span>
                                                <br />
                                                <a class="edit_pickup_point" onclick="Carrier.displayPopupModule_{$carrier.external_module_name}({$carrier.id})">{l s='Edit pickup point' mod='spstepcheckout'}</a>
                                            {else}
                                                <a class="select_pickup_point" onclick="Carrier.displayPopupModule_{$carrier.external_module_name}({$carrier.id})">{l s='Select pickup point' mod='spstepcheckout'}</a>
                                            {/if}
                                        </div>
                                    {/if}
                                </div>
                                <div class="row carrier-extra-content"{if $delivery_option != $carrier_id} style="display:none;"{/if}>
                                    {$carrier.extraContent nofilter}
                                </div>
                            </div>
                        {/foreach}
                    </div>

                    {if $recyclablePackAllowed or $gift.allowed}
                        <div class="row">
                            {if $recyclablePackAllowed}
                                <div class="col-xs-12 col-12">
                                    <label for="recyclable">
                                        <input type="checkbox" name="recyclable" id="recyclable" value="1" {if $recyclable == 1}checked="checked"{/if} class="carrier_checkbox not_unifrom not_uniform"/>
                                        {l s='I agree to receive my order in recycled packaging' mod='spstepcheckout'}
                                    </label>
                                </div>
                            {/if}
                            {if $gift.allowed}
                                <div class="col-xs-12 col-12">
                                    <label for="gift">
                                        <input type="checkbox" name="gift" id="gift" value="1" {if $gift.isGift}checked{/if} class="carrier_checkbox not_unifrom not_uniform"/>
                                        {l s='I would like the order to be gift-wrapped.' mod='spstepcheckout'}
                                    </label>
                                </div>
                            {/if}
                        </div>
                    {/if}

                    {if $gift.allowed}
                        <div class="row">
                            <div class="col-xs-12 col-12">
                                <p id="gift_div_sp" class="textarea {if !$gift.isGift}hidden{/if}">
                                    <label for="gift_message">{l s='If you\'d like, you can add a note to the gift:' mod='spstepcheckout'}</label>
                                    <textarea rows="1" id="gift_message" name="gift_message" class="form-control">{$gift.message}</textarea>
                                </p>
                            </div>
                        </div>
                    {/if}
                {else}
                    <p class="alert alert-danger">{l s='Unfortunately, there are no carriers available for your delivery address.' mod='spstepcheckout'}</p>
                {/if}
            </div>

            <div id="hook-display-after-carrier">
                {$hookDisplayAfterCarrier nofilter}
            </div>

            <div id="extra_carrier"></div>
        {/if}
    {/if}
{/block}