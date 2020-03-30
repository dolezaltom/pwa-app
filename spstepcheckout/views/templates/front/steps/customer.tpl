{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}

<div id="spsco_container" class="{if !$register_customer}{$classes|escape:'htmlall':'UTF-8'}{else}col-xs-12 col-12{/if}">
    <div class="loading_small"><i class="fa fa-spin fa-spinner fa-2x"></i></div>
    <div id="spsco_one">
        {include file="./../address.tpl"}
    </div>
</div>