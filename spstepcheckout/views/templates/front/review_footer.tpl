{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}

<div class="spsco-leave-message">
    <p>{l s='If you would like to add a comment about your order, please write it below.' mod='spstepcheckout'}</p>
    <textarea name="message" id="message" class="form-control" rows="2">{if isset($oldMessage)}{$oldMessage|escape:'htmlall':'UTF-8'}{/if}</textarea>
</div>
<div id="conditions-to-approve1" class="approveCond1 mt-15 pb-3">
Objednáním souhlasíte s <a class="underline" href="/obchodni-podminky" target="_blank">obchodními podmínkami</a> a se <a class="underline" href="/nakladani-s-osobnimi-udaji" target="_blank">zpracováním osobních údajů</a>.
</div>
{if $conditions_to_approve|count and $CONFIGS.PS_CONDITIONS}
    <div id="conditions-to-approve">
        <ul>
          {foreach from=$conditions_to_approve item="condition" key="condition_name"}
            <li>
                <label class="js-terms" for="conditions_to_approve[{$condition_name}]">
                    <input  id    = "conditions_to_approve[{$condition_name}]"
                            name  = "conditions_to_approve[{$condition_name}]"
                            required
                            type  = "checkbox"
                            value = "1"
                            class = "ps-shown-by-js"
                    >
                    {$condition nofilter}
                </label>
            </li>
          {/foreach}
        </ul>
    </div>
{/if}


<div class="row spsco-footer_review">
	<div class="col-xs-12 col-12">
	{if $CONFIGS.SPSCO_SHOW_LINK_CONTINUE_SHOPPING}
		<button type="button" id="btn_continue_shopping" class="btn btn-link btn-sm pull-left btn-cotinue-shopping"
				{if not empty($CONFIGS.SPSCO_LINK_CONTINUE_SHOPPING)}data-link="{$CONFIGS.SPSCO_LINK_CONTINUE_SHOPPING|escape:'htmlall':'UTF-8'}"{/if}>
			<i class="fa fa-chevron-left fa-1x"></i>
			{l s='Continue shopping' mod='spstepcheckout'}
		</button>
	{/if}
		{*<button type="button" id="btn_place_order" class="btn btn-primary  pull-right" >
			{l s='Checkout' mod='spstepcheckout'}
		</button>*}
		 <div id="payment-confirmation">
			<button type="submit" id="btn_place_order" class="btn btn-primary  pull-right w-100" >
				{l s='Potvrdit objednávku' mod='spstepcheckout'}
			</button>
		 </div>
	</div>
</div>

{if $CONFIGS.SPSCO_ENABLE_HOOK_SHOPPING_CART}
    <div id="HOOK_SHOPPING_CART" class="row">
        {block name='hook_shopping_cart'}
            {hook h='displayShoppingCart'}
        {/block}
    </div>
    <div id="hook_shopping_cart_footer">
        {block name='hook_shopping_cart_footer'}
          {hook h='displayShoppingCartFooter'}
        {/block}
    </div>
{/if}

{block name='display_reassurance'}
    {hook h='displayReassurance'}
{/block}

