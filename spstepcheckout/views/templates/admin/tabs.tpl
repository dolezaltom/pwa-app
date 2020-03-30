{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}
 
{if isset($configuration_settings_saved)}
	<div class="alert alert-success">{$configuration_settings_saved}
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	</div>
{/if}
<div class="panel">
	<div class="productTabs" id="spco_tabs">
		<ul class="tab nav nav-tabs">
			{foreach $tabs as $tab}
			<li class="tab-row {if isset($active_tab) && $tab.id==$active_tab}active{/if}">
				<a class="tab-page" id="cart_rule_link_informations" href="#" data-target="#fieldset_{$tab.data_tabs|escape:'htmlall':'UTF-8'}">
				<i class="{$tab.icon|escape:'htmlall':'UTF-8'}"></i>
				{$tab.title|escape:'htmlall':'UTF-8'}</a>
			</li>
			{/foreach}
		</ul>
		
	</div>
	<div class="content" id="spco_content" data-url="{$url_ajax}">
	  {$content nofilter}
	</div>
</div>
