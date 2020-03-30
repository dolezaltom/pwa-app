{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}

{if !$register_customer}
    <div id="spsco_three_container" class="{$classes|escape:'htmlall':'UTF-8'}">
        <div class="loading_small"><i class="fa fa-spin fa-spinner fa-2x"></i></div>
        <h4 class="spsco-title spsco-title_three">
            3. {l s='Payment method' mod='spstepcheckout'}
        </h4>
        <div id="spsco_three"></div>
    </div>
{/if}