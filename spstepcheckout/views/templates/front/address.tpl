{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}

{assign var='addresses_tab' value=(isset($SPSCO_FIELDS[$SPSCO_GLOBALS->object->delivery]) && isset($SPSCO_FIELDS[$SPSCO_GLOBALS->object->invoice]) && $CONFIGS.SPSCO_ENABLE_INVOICE_ADDRESS && !$is_virtual_cart && sizeof($SPSCO_FIELDS[$SPSCO_GLOBALS->object->delivery]) > 1) || $CONFIGS.SPSCO_ENABLE_INVOICE_ADDRESS && $is_virtual_cart}

<form id="spsco_form" autocomplete="on">
    {if isset($SPSCO_FIELDS[$SPSCO_GLOBALS->object->customer])}
        <h4 class="spsco-title spsco-title_one">
            1. {l s='Your Details' mod='spstepcheckout'}
        </h4>

        {$hook_create_account_top nofilter}
        <section id="customer_container">
            {foreach from=$SPSCO_FIELDS[$SPSCO_GLOBALS->object->customer] item='fields' name='f_row_fields'}
                <div class="row">
                    {foreach from=$fields item='field' name='f_fields'}
                        {include file="./controls.tpl" field=$field cant_fields=$smarty.foreach.f_fields.total}
                    {/foreach}
                </div>
            {/foreach}
        </section>
    {/if}
    {$hook_create_account_form nofilter}
    {if $addresses_tab}
        <ul class="nav nav-tabs">
            <li id="nav_address" class="nav-item">
                <a class="nav-link active" href="#delivery_address_container" data-toggle="tab" aria-expanded="true">{l s='Delivery address' mod='spstepcheckout'}</a>
            </li>
            <li id="nav_invoice" class="nav-item">
                <a class="nav-link" href="#invoice_address_container" data-toggle="tab" aria-expanded="false">{l s='Invoice address' mod='spstepcheckout'}</a>
            </li>
        </ul>
    {/if}

    <div class="{if $addresses_tab}tab-content{/if}">
        {if isset($SPSCO_FIELDS[$SPSCO_GLOBALS->object->delivery]) && sizeof($SPSCO_FIELDS[$SPSCO_GLOBALS->object->delivery]) > 1}
            {if !$is_virtual_cart}
                {if not $addresses_tab}
                    <h5 id="p_delivery_address" class="spsco-title p_address">{l s='Delivery address' mod='spstepcheckout'}</h5>
                {/if}
                <section id="delivery_address_container" class="{if $addresses_tab}page-product-box tab-pane active{/if}">
                    <div class="fields_container">
                        {foreach from=$SPSCO_FIELDS[$SPSCO_GLOBALS->object->delivery] item='fields' name='f_row_fields'}
                            <div class="row">
                                {foreach from=$fields item='field' name='f_fields'}
                                    {include file="./controls.tpl" field=$field cant_fields=$smarty.foreach.f_fields.total}
                                {/foreach}
                            </div>
                        {/foreach}
                    </div>
                    {if $CONFIGS.SPSCO_ENABLE_INVOICE_ADDRESS}
                        <div class="row">
                            <div class="form-group col-xs-12 col-12 container_help_invoice">
                                {if $CONFIGS.SPSCO_REQUIRED_INVOICE_ADDRESS}
                                    <span>{l s='Remember to set your invoice address.' mod='spstepcheckout'}</span>
                                {else}
                                    <span>{l s='Do you want to enter another address for billing?' mod='spstepcheckout'}</span>
                                {/if}
                            </div>
                        </div>
                    {/if}
                </section>
            {/if}
        {else}
            <input type="hidden" id="delivery_id" value="{$id_address_delivery|intval}"/>
        {/if}

        {if isset($SPSCO_FIELDS[$SPSCO_GLOBALS->object->invoice]) && sizeof($SPSCO_FIELDS[$SPSCO_GLOBALS->object->invoice]) > 1}
            {if $CONFIGS.SPSCO_ENABLE_INVOICE_ADDRESS}
                {if not $addresses_tab}
                    <h5  class="spsco-title p_address">{l s='Invoice address' mod='spstepcheckout'}</h5>
                {/if}
                <section id="invoice_address_container" class="{if $addresses_tab}page-product-box tab-pane{/if}">
                    <div class="row {if $CONFIGS.SPSCO_REQUIRED_INVOICE_ADDRESS}hidden{/if}">
                        <div class="form-group col-xs-12 col-12">
                            <label for="checkbox_create_invoice_address" class="mb-0 mt-2">
                                <input type="checkbox" {if $is_need_invoice}checked="true"{/if} name="checkbox_create_invoice_address" id="checkbox_create_invoice_address" class="input_checkbox not_unifrom not_uniform"/>
                                {l s='I want to set another address for my invoice.' mod='spstepcheckout'}
                            </label>
                        </div>
                    </div>
                    <div class="fields_container">
                        {foreach from=$SPSCO_FIELDS[$SPSCO_GLOBALS->object->invoice] item='fields' name='f_row_fields'}
                            <div class="row">
                                {foreach from=$fields item='field' name='f_fields'}
                                    {include file="./controls.tpl" field=$field cant_fields=$smarty.foreach.f_fields.total}
                                {/foreach}
                            </div>
                        {/foreach}
                    </div>
                </section>
            {/if}
        {else}
            <input type="hidden" id="invoice_id" value="{$id_address_invoice|intval}"/>
        {/if}
    </div>
</form>