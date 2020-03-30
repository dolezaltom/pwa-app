<?php
/**
 * package Sp Step Checkout
 *
 * @version 1.0.2
 * @author    MagenTech http://www.magentech.com
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use PrestaShop\PrestaShop\Core\Foundation\Templating\RenderableProxy;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter;

class OrderController extends OrderControllerCore
{
    public $ssl = true;
    public $php_self = 'order';
    public $page_name = 'checkout';
    public $checkoutDeliveryStep;

    public $name_module = 'spstepcheckout';
    public $spstepcheckout;
    public $spstepchekoutdir;
    public $sp_fields;
    public $is_active_module;
    private $only_register = false;
    private $show_authentication = false;

    public function init()
    {
        $this->spstepcheckout = Module::getInstanceByName($this->name_module);
        $this->spstepchekoutdir = __PS_BASE_URI__.'modules/'.$this->name_module.'/';

        $this->show_authentication = false;
        if ($this->spstepcheckout->isModuleActive('customersactivation')) {
            $this->show_authentication = true;
        }
        if ((Tools::getIsset('rc')
            || $this->show_authentication)
            && !$this->context->customer->isLogged()
            && Validate::isLoadedObject($this->context->cart)
        ) {
            $this->display_column_right = true;
            $this->display_column_left  = true;
        } else {
            $this->display_column_right = false;
            $this->display_column_left  = false;
        }

        parent::init();

        if (Validate::isLoadedObject($this->spstepcheckout)
            && $this->spstepcheckout->isModuleActive($this->name_module)
        ) {
            $this->is_active_module = true;
        } else {
            $this->is_active_module = false;
        }

        if (!$this->is_active_module) {
            return;
        }


        if ($this->context->cart->nbProducts()) {
            if (empty($this->context->cart->id_address_delivery) && empty($this->context->cart->id_address_invoice)) {
                $id_address = $this->spstepcheckout->getIdAddressAvailable('delivery');

                $this->context->cart->id_address_delivery = $id_address;
                $this->context->cart->id_address_invoice = $id_address;
                $this->context->cart->update();

                if ((bool)Configuration::get('SPSCO_REQUIRED_INVOICE_ADDRESS')) {
                    $id_address_invoice = $this->spstepcheckout->getIdAddressAvailable('invoice');
                    $this->context->cart->id_address_invoice = $id_address_invoice;
                    $this->context->cart->update();
                }

                $this->context->cart->setNoMultishipping();
            }
        }
        if (Tools::getIsset('rc')) {
            $this->only_register = true;
        } else if ($this->show_authentication && (!$this->context->customer->isLogged() && !$this->context->customer->isGuest())) {
            $this->only_register = true;
        }

        if ($this->only_register) {
            if ($this->context->customer->isLogged() || $this->context->customer->isGuest()) {
                $meta_authentication = Meta::getMetaByPage('my-account', $this->context->language->id);
            } else {
                $meta_authentication = Meta::getMetaByPage('authentication', $this->context->language->id);
            }

            $this->context->smarty->assign('meta_title', $meta_authentication['title']);
            $this->context->smarty->assign('meta_description', $meta_authentication['description']);
        }

        $this->context->smarty->assign('show_authentication', $this->show_authentication);
        $this->context->smarty->assign('register_customer', $this->only_register);

        if (Validate::isLoadedObject($this->context->customer) && $this->context->customer->isLogged()) {
            $address_customer = $this->context->customer->getAddresses($this->context->cookie->id_lang);

            if (empty($address_customer)) {
                $id_address_delivery = $this->spstepcheckout->getIdAddressAvailable('delivery');
                $this->context->cart->id_address_delivery = $id_address_delivery;
                $this->context->cart->id_address_invoice = $id_address_delivery;
                $this->context->cart->update();
            }
        }
    }

    public function initContent()
    {
        parent::initContent();

        if (!$this->is_active_module) {
            return;
        }

        if ((bool) Configuration::get('SPSCO_REDIRECT_DIRECTLY_TO_SP') 
			&& $this->spstepcheckout->areProductsAvailable()
            && !Tools::getIsset('rc')
            && !Tools::getIsset('checkout')
        ) {
            $presenter = new CartPresenter();
            $presented_cart = $presenter->present($this->context->cart, true);

            $this->context->smarty->assign(array(
                'cart' => $presented_cart,
                'static_token' => Tools::getToken(false),
            ));

            if (count($presented_cart['products']) > 0) {
                $this->setTemplate('checkout/cart');
            } else {
                $this->context->smarty->assign(array(
                    'allProductsLink' => $this->context->link->getCategoryLink(Configuration::get('PS_HOME_CATEGORY')),
                ));
                $this->setTemplate('checkout/cart-empty');
            }
        } else {
            $is_need_invoice     = false;
            $sp_fields_position = $this->spstepcheckout->getFieldsFront($is_need_invoice);
            $this->context->smarty->assign(array(
                'SPSCO_GLOBALS'     => $this->spstepcheckout->globalsVar,
                'SPSCO_FIELDS'      => $sp_fields_position,
                'is_need_invoice' => $is_need_invoice
            ));
			
            Media::addJsDef(array('is_need_invoice' => $is_need_invoice));

            $templateVars = $this->spstepcheckout->getTemplateVarsSP($this->only_register, $this->show_authentication);
            $this->context->smarty->assign($templateVars);
            Media::addJsDef($templateVars);

            if (file_exists(_PS_THEME_DIR_.'modules/spstepcheckout/views/templates/front/spstepcheckout.tpl')) {
                $this->setTemplate('../../../themes/'._THEME_NAME_.'/modules/'.$this->name_module.'/views/templates/front/spstepcheckout');
            } else {
                $this->setTemplate('../../../modules/'.$this->name_module.'/views/templates/front/spstepcheckout');
            }
        }
    }

    public function getCheckoutSession()
    {
        $deliveryOptionsFinder = new DeliveryOptionsFinder(
            $this->context,
            $this->getTranslator(),
            $this->objectPresenter,
            new PriceFormatter()
        );

        $session = new CheckoutSession(
            $this->context,
            $deliveryOptionsFinder
        );

        return $session;
    }

    protected function bootstrap()
    {
        $translator = $this->getTranslator();

        $session = $this->getCheckoutSession();

        $this->checkoutProcess = new CheckoutProcess(
            $this->context,
            $session
        );

        $this->checkoutDeliveryStep = new CheckoutDeliveryStep(
            $this->context,
            $translator
        );

        $this->checkoutDeliveryStep
            ->setRecyclablePackAllowed((bool) Configuration::get('PS_RECYCLABLE_PACK'))
            ->setGiftAllowed((bool) Configuration::get('PS_GIFT_WRAPPING'))
            ->setIncludeTaxes(
                !Product::getTaxCalculationMethod((int) $this->context->cart->id_customer)
                && (int) Configuration::get('PS_TAX')
            )
            ->setDisplayTaxesLabel((Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC')))
            ->setGiftCost(
                $this->context->cart->getGiftWrappingPrice(
                    $this->checkoutDeliveryStep->getIncludeTaxes()
                )
            );

        $this->checkoutProcess
            ->addStep(new CheckoutPersonalInformationStep(
                $this->context,
                $translator,
                $this->makeLoginForm(),
                $this->makeCustomerForm()
            ))
            ->addStep(new CheckoutAddressesStep(
                $this->context,
                $translator,
                $this->makeAddressForm()
            ))
            ->addStep($this->checkoutDeliveryStep)
            ->addStep(new CheckoutPaymentStep(
                $this->context,
                $translator,
                new PaymentOptionsFinder(),
                new ConditionsToApproveFinder(
                    $this->context,
                    $translator
                )
            ));

        if ($this->is_active_module) {
            foreach ($this->checkoutProcess->getSteps() as $step) {
                $step->setReachable(true)->setComplete(true);
            }
        }
    }

    public function updateCarrier()
    {
        $this->checkoutDeliveryStep->handleRequest(Tools::getAllValues());
    }

    public function postProcess()
    {
        parent::postProcess();

        $this->bootstrap();

        if (!$this->is_active_module) {
            return;
        }

        if (Tools::getIsset('is_ajax')) {
            define('_PTS_SHOW_ERRORS_', true);

            $data_type = 'json';
            if (Tools::isSubmit('dataType')) {
                $data_type = Tools::getValue('dataType');
            }

            $action = Tools::getValue('action');
            if (method_exists($this, $action)) {
                switch ($data_type) {
                    case 'html':
                        die($this->$action());
                    case 'json':
                        $response = Tools::jsonEncode($this->$action());
                        die($response);
                    default:
                        die('Invalid data type.');
                }
            } elseif (method_exists($this->spstepcheckout, $action)) {
                switch ($data_type) {
                    case 'html':
                        die($this->spstepcheckout->$action($this));
                    case 'json':
                        $response = Tools::jsonEncode($this->spstepcheckout->$action($this));
                        die($response);
                    default:
                        die('Invalid data type.');
                }
            } else {
                switch ($action) {
                    case 'checkRegisteredCustomerEmail':
                        $data = Customer::customerExists(Tools::getValue('email'), true);
                        die(Tools::jsonEncode((int) $data));
                    case 'checkVATNumber':
                        $errors = array();
                        $vat_number = Tools::getValue('vat_number', '');
                        $id_address = $this->context->cart->id_address_delivery;

                        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
                            $id_address = $this->context->cart->id_address_invoice;
                        }

                        if (!empty($vat_number)) {
                            if (Configuration::get('VATNUMBER_MANAGEMENT')) {
                                include_once _PS_MODULE_DIR_.'vatnumber/vatnumber.php';
                                if (class_exists('VatNumber', false) && Configuration::get('VATNUMBER_CHECKING')) {
                                    $errors = VatNumber::WebServiceCheck($vat_number);
                                }
                            }
                        }

                        if (!empty($id_address)) {
                            $address = new Address($id_address);
                            $address->vat_number = $vat_number;
                            $address->save();
                        }

                        die(Tools::jsonEncode($errors));
                }
            }
        }
    }
}
