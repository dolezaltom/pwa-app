{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}

{block name='step_payment'}
    <div id="payment_method_container">
        {foreach from=$payment_options item="module_options" key="name_module"}
            {foreach from=$module_options item="option"}
                <div class="module_payment_container">
                    <div class="row " for="{$option.action}">
                        <div class="payment_input d-flex align-items-center col-auto">
                            <span class="custom-radio float-xs-left">
								<input type="radio" id="module_payment_{$option.id_module_payment}_0"  data-module-name="{$name_module}" name="payment-option" class="payment_radio not_unifrom not_uniform" value="{$name_module}">
								<span></span>
								<input type="hidden" id="url_module_payment_{$option.id_module_payment}" value="{$option.action}">
							</span>
                        </div>
						{if !empty($option.logo) and $CONFIGS.SPSCO_SHOW_IMAGE_PAYMENT}
                            <div class="payment_image">
                                <img src="{$option.logo}" title="{$option.call_to_action_text}" class="{$name_module}">
                            </div>
                        {/if}
                        <div class="payment_content {if !empty($option.logo)}col{else}col-xs-11 col-11{/if}">
                           <span>
							{if isset($option.title_sp)}
								{$option.title_sp}
							{else}
								{$option.call_to_action_text}
							{/if}
                            </span>
                            {if isset($option.description_sp)}
                                    {$option.description_sp}
                            {/if}
                            <span class="pricePayment">Zdarma</span>
                        </div>
                    </div>
                    {if $CONFIGS.SPSCO_SHOW_DETAIL_PAYMENT}
                        {if $option.additionalInformation}
                            <div id="payment_content_html_{$option.id}" class="payment_content_html hidden definition-list">
                                {$option.additionalInformation nofilter}
                            </div>
                        {/if}
                    {/if}
                    <div
                        id="pay-with-{$option.id}-form"
                        class="js-payment-option-form {if $option.id != $selected_payment_option} ps-hidden {/if}" >
                        {if $option.form}
                            {$option.form nofilter}
                        {else}
                            <form id="payment-form" method="POST" action="{$option.action nofilter}">
                                {foreach from=$option.inputs item=input}
                                    <input type="{$input.type}" name="{$input.name}" value="{$input.value}">
                                {/foreach}
                                <button style="display:none" id="pay-with-{$option.id}" type="submit"></button>
                            </form>
                        {/if}
                    </div>
                </div>
            {/foreach}
        {foreachelse}
            <p class="alert alert-danger">
                {l s='Unfortunately, there are no payment method available.' mod='spstepcheckout'}
            </p>
        {/foreach}
    </div>
{/block}