{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}

{block name='step_review'}
    {if isset($minimal_purchase)}
        <div class="alert alert-warning">
            {$minimal_purchase}
        </div>
    {/if}
    <div id="order-detail-content" class="cart-detailed-totals cf">
		{block name='cart_detailed_product'}
		  <div class="cart-overview js-cart" data-refresh-url="{url entity='cart' params=['ajax' => true, 'action' => 'refresh']}">
			{if $cart.products}
			<ul class="cart-items">
			  {foreach from=$cart.products item=product}
				<li class="cart-item">
				  {block name='cart_detailed_product_line'}
					{include file='./cart-detailed-product-line.tpl' product=$product}
				  {/block}
				</li>
				{if $product.customizations|count >1}<hr>{/if}
			  {/foreach}
			</ul>
			{else}
			  <span class="no-items">{l s='There are no more items in your cart' d='Shop.Theme.Checkout'}</span>
			{/if}
		  </div>
		{/block}
		{block name='cart_summary'}
                {include file='checkout/_partials/cart-summary.tpl' cart = $cart}
		{/block}
        
    </div>
{/block}