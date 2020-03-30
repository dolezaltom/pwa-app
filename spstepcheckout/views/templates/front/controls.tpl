{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}

{math assign='num_col' equation='12/a' a=$cant_fields}

<div id="field_{if $field.object neq ''}{$field.object}_{/if}{$field.name}"
     class="form-group ffl-wrapper col-xs-{$num_col} col-{$num_col} {if $field.required}required{/if} {if $cant_fields == 1} cf{/if}">
    {if $field.type_control eq $SPSCO_GLOBALS->type_control->textbox}
        <label for="{$field.name_control}" class="ffl-label">
            {$field.description}
            <sup>{if $field.required}*{/if}</sup>
        </label>
        <input
            id="{$field.id_control}"
            name="{$field.name_control}"
            type="{if $SPSCO_GLOBALS->type->{$field.type} eq 'password' or $field.name == 'conf_passwd'}password{else}text{/if}"
            class="{$field.classes|escape:'htmlall':'UTF-8'} input-sm not_unifrom not_uniform "
            data-field-name="{$field.name}"
            data-validation="{$field.type|escape:'htmlall':'UTF-8'}{if $field.size neq 0 and $SPSCO_GLOBALS->type->{$field.type} eq 'string'},length{/if} "
            data-default-value="{$field.default_value}"
            data-required="{$field.required|intval}"
            {if $field.name == 'address' && $CONFIGS.SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS}autocomplete="off"{/if}
            {if !$field.required}data-validation-optional="true"{/if}
            {if isset($field.error_message) && $field.error_message neq ''}data-validation-error-msg="{$field.error_message}"{/if}
            {if $SPSCO_GLOBALS->type->{$field.type} eq 'string'}data-validation-length="max{$field.size|intval}" maxlength="{$field.size|intval}"{/if}
        />
    {elseif $field.type_control eq $SPSCO_GLOBALS->type_control->select}
        <label for="{$field.name_control}">
            {$field.description}:
            <sup>{if $field.required}*{/if}</sup>
        </label>
        <select
            id="{$field.id_control}"
            name="{$field.name_control}"
            class="{$field.classes} input-sm not_unifrom not_uniform "
            data-field-name="{$field.name}"
            data-default-value="{$field.default_value}"
            data-required="{$field.required|intval}"
            {if $field.required}data-validation="required"{/if}
            {if isset($field.error_message) && $field.error_message neq ''}data-validation-error-msg="{$field.error_message}"{/if}>
            {if isset($field.options.empty_option) && $field.options.empty_option}
                <option value="" data-text="" {if $field.default_value eq '' or (!isset($field.options.data) and $field.options.data|count)}selected{/if}>
                    {if $field.name_control eq 'delivery_id' or $field.name_control eq 'invoice_id'}
                        {l s='Create a new address' mod='spstepcheckout'}....
                    {else}
                        --
                    {/if}
                </option>
            {/if}
            {if isset($field.options.data)}
                {foreach from=$field.options.data item='item' name='f_options'}
                    <option
                        value="{$item[$field.options.value]}"
                        data-text="{$item[$field.options.description]}"
                        {if $field.name == 'id_country'}data-iso-code="{$item['iso_code']}"{/if}
                        {if $field.default_value eq $item[$field.options.value]}selected{/if}>
                            {$item[$field.options.description]}
                    </option>
                {/foreach}
            {/if}
        </select>
    {elseif $field.type_control eq $SPSCO_GLOBALS->type_control->checkbox}
        <label for="{$field.name_control}">
            <input
                id="{$field.id_control}"
                name="{$field.name_control}"
                type="checkbox"
                class="{$field.classes} not_unifrom not_uniform "
                {if $field.default_value}checked{/if}
                data-field-name="{$field.name}"
                data-default-value="{$field.default_value}"
                data-required="{$field.required|intval}"
                {if !$field.required}data-validation-optional="true"{/if}
                {if isset($field.error_message) && $field.error_message neq ''}data-validation-error-msg="{$field.error_message}"{/if}
            />
            {$field.description}<sup>{if $field.required}*{/if}</sup>
        </label>
    {elseif $field.type_control eq $SPSCO_GLOBALS->type_control->radio}
        <label>
            {$field.description}:
            <sup>{if $field.required}*{/if}</sup>
        </label>
        <div class="row">
            {foreach from=$field.options.data item='item' name='f_options'}
                {math assign='num_col_option' equation='12/a' a=$smarty.foreach.f_options.total}
                <div class="col-xs-{$num_col_option} col-{$num_col_option}">
                    <label class="radio-inline" >
					<span class="custom-radio float-xs-left">
                        <input
                            id="{$field.id_control}_{$item[$field.options.value]}"
                            name="{$field.name}"
                            type="radio"
                            class="{$field.classes} not_unifrom not_uniform "
                            value="{$item[$field.options.value]}"
                            {if $field.default_value eq $item[$field.options.value]}checked{/if}
                            data-field-name="{$field.name}"
                            data-required="{$field.required|intval}"
                        />
						<span></span>
						</span>
                        {$item[$field.options.description]}
                    </label>
                </div>
            {/foreach}
        </div>
    {elseif $field.type_control eq $SPSCO_GLOBALS->type_control->textarea}
        <label for="{$field.name_control}">
            {$field.description}:
            <sup>{if $field.required}*{/if}</sup>
        </label>
        <textarea
            id="{$field.id_control}"
            name="{$field.name_control}"
            class="{$field.classes} input-sm not_unifrom not_uniform "
            data-field-name="{$field.name}"
            data-validation="{$field.type}{if $field.size neq 0},length{/if}"
            data-default-value="{$field.default_value}"
            data-required="{$field.required|intval}"
            {if !$field.required}data-validation-optional="true"{/if}
            {if isset($field.error_message) && $field.error_message neq ''}data-validation-error-msg="{$field.error_message}"{/if}
            {if $SPSCO_GLOBALS->type->{$field.type} eq 'text'}data-validation-length="max{$field.size|intval}"{/if}
            ></textarea>
    {/if}
</div>
