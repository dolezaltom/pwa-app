<?php
/**
 * package Sp One Step Checkout
 *
 * @version 1.0.2
 * @author    MagenTech http://www.magentech.com
 * @copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

if (!defined('_PS_VERSION_'))
    exit;

class AdminSPStepCheckoutController extends ModuleAdminController
{
	public function __construct()
    {
        parent::__construct();
        if (!(bool) Tools::getValue('ajax'))
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=spstepcheckout');
    }
    
    /**
     * Process Ajax Call
     */
    public function ajaxProcessAjaxCall()
    {
        $spstepcheckout = new SpStepCheckout();
        $return         = $spstepcheckout->postProcessAjax();
        die(Tools::jsonEncode($return));
    }
}
