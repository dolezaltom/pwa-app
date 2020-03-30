<?php
/**
 * package Sp One Step Checkout
 *
 * @version 1.0.3
 * @author    MagenTech http://www.magentech.com
 * @copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter;

class SpStepCheckout extends Module implements WidgetInterface
{
	private $templateFile;
	protected $_html = '';
	protected $_postErrors = [];
	public $globalsVar;
	protected $errors = [];
	public $configVars = [];
	protected $warnings = [];
	protected $configArr = [
		['name' => 'SPSCO_FIELDS_SETUP', 'default_value' => '', 'is_html' => false, 'is_bool' => false],
		['name' => 'SPSCO_ID_CUSTOMER', 'default_value' => 1, 'is_html' => false, 'is_bool' => false],
		['name' => 'PS_GUEST_CHECKOUT_ENABLED', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_DEFAULT_GROUP_CUSTOMER', 'default_value' => 3, 'is_html' => false, 'is_bool' => false],
		['name' => 'SPSCO_ENABLE_INVOICE_ADDRESS', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_REQUIRED_INVOICE_ADDRESS', 'default_value' => 0, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_USE_SAME_NAME_CONTACT_BA', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_USE_SAME_NAME_CONTACT_DA', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_REQUEST_CONFIRM_EMAIL', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS', 'default_value' => 0, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_GOOGLE_API_KEY', 'default_value' => '', 'is_html' => false, 'is_bool' => false],
		['name' => 'SPSCO_SHOW_DESCRIPTION_CARRIER', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_SHOW_IMAGE_CARRIER', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_RELOAD_SHIPPING_BY_STATE', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_FORCE_NEED_POSTCODE', 'default_value' => 0, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_FORCE_NEED_CITY', 'default_value' => 0, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_DEFAULT_PAYMENT_METHOD', 'default_value' => '', 'is_html' => false, 'is_bool' => false],
		['name' => 'SPSCO_SHOW_IMAGE_PAYMENT', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_SHOW_DETAIL_PAYMENT', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_REDIRECT_DIRECTLY_TO_SP', 'default_value' => '', 'is_html' => false, 'is_bool' => false],
		['name' => 'PS_CONDITIONS', 'default_value' => 0, 'is_html' => false, 'is_bool' => true],
		['name' => 'PS_CONDITIONS_CMS_ID', 'default_value' => 0, 'is_html' => false, 'is_bool' => false],
		['name' => 'SPSCO_SHOW_LINK_CONTINUE_SHOPPING', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_ENABLE_HOOK_SHOPPING_CART', 'default_value' => 1, 'is_html' => false, 'is_bool' => true],
		['name' => 'SPSCO_LINK_CONTINUE_SHOPPING', 'default_value' => '', 'is_html' => false, 'is_bool' => false],
	];

    /**
     * SpStepCheckout constructor.
     */
	public function __construct()
    {
        $this->name = 'spstepcheckout';
        $this->author = 'MagenTech';
        $this->version = '1.0.3';
		$this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Sp One Step Checkout');
		$this->description = $this->l('Powerful and intuitive checkout process.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		$this->ps_versions_compliancy = [
            'min' => '1.7.1.0',
            'max' => _PS_VERSION_,
        ];
        $this->templateFile = 'module:spstepcheckout/views/templates/hook/spstepcheckout.tpl';
		$this->fillConfigVars();
		$this->globalsVar = new StdClass();
		$this->globalsVar->object = (object) array(
                'customer' => 'customer',
                'delivery' => 'delivery',
                'invoice'  => 'invoice',
        );

        $this->globalsVar->type = (object) array(
                'isAddress'     => 'string',
                'isBirthDate'   => 'string',
                'isDate'        => 'string',
                'isBool'        => 'boolean',
                'isCityName'    => 'string',
                'isDniLite'     => 'string',
                'isEmail'       => 'string',
                'isGenericName' => 'string',
                'isMessage'     => 'text',
                'isName'        => 'string',
                'isPasswd'      => 'password',
                'isPhoneNumber' => 'string',
                'isPostCode'    => 'string',
                'isVatNumber'   => 'string',
                'number'        => 'integer',
                'url'           => 'string',
                'confirmation'  => 'string',
        );
		$this->globalsVar->lang = new StdClass();
		$this->globalsVar->lang->object = array(
            'customer' => $this->l('Customer'),
            'delivery' => $this->l('Address delivery'),
            'invoive'  => $this->l('Address invoice'),
        );
		
		 $this->globalsVar->type_control = (object) array(
                'select'   => 'select',
                'textbox'  => 'textbox',
                'textarea' => 'textarea',
                'radio'    => 'radio',
                'checkbox' => 'checkbox'
        );

		$query_cs = new DbQuery();
        $query_cs->from('customer');
        $query_cs->where('id_customer = '.(int) Configuration::get('SPSCO_ID_CUSTOMER'));
        $result_cs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query_cs);

        $query_csg = new DbQuery();
        $query_csg->from('customer_group');
        $query_csg->where('id_customer = '.(int) Configuration::get('SPSCO_ID_CUSTOMER'));
        $result_csg = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query_csg);

        if ((!$result_cs || !$result_csg)) {
            $this->createCustomerTemp();
        }
        if (isset($this->context->cookie->sp_suggest_address)
            && (!$this->context->customer->isLogged()
                || ($this->context->customer->isLogged() && !isset($this->context->cookie->id_cart)))
        ) {
            unset($this->context->cookie->sp_suggest_address);
        }
    }

    /**
     * @return bool
     */
	protected function fillConfigVars()
    {
        foreach ($this->configArr as $config) {
            if (isset($config['is_bool']) && $config['is_bool']) {
                $this->configVars[$config['name']] = (bool)Configuration::get($config['name']);
            } else {
                $this->configVars[$config['name']] = Configuration::get($config['name']);
            }
        }
    }
	
	private function getFieldsSetUp($object = null, $name_fields = []){
		require_once( dirname(__FILE__).'/setupfields.php');
		$fieldsChoice = [];
		if ($object !== null){
			foreach($fields as $field){
				if ($field['object'] == $object){
					if (!empty($name_fields) && in_array($field['name'], $name_fields)){
						$fieldsChoice[] = $field;
					}else{
						$fieldsChoice[] = $field;
					}
				}
			}
			return $fieldsChoice;
		}
		return $fields;
	}

    /**
     * @param null $object
     * @param array $name_fields
     * @return array
     */
	public function getAllFields($object = null, $name_fields = [] , $active = false) {
		$fields = $this->getAllFieldsSort();
		$fieldsChoice = [];
		if ($object !== null){
			foreach($fields as $field){
				if ($field['object'] == $object){
					if ( !empty($name_fields) && in_array($field['name'], $name_fields)){
						if ($active){
							if ($field['active'] > 0){
								$fieldsChoice[] = $field;
							}
						}else{
							$fieldsChoice[] = $field;
						}
					}
				}
			}
			return $fieldsChoice;
		}
		return $fields;
	}
	
	private function getAllFieldsSort($sort = true){
		$fields = $this->configVars['SPSCO_FIELDS_SETUP'];
		$fieldsUnserialize = unserialize($fields);
		if ($sort)
			$this->sortByFields('row', $fieldsUnserialize);
		return $fieldsUnserialize;
	}

    /**
     * @return array
     */
	public function getFieldsPosition()
    {
        $fields = $this->getAllFields();
        $position = array();
        foreach ($fields as $field) {
            $position[$field['group']][$field['row']][$field['col']] = $field;
        }
        return $position;
    }

    /**
     * @param $is_need_invoice
     * @return array
     */
	public function getFieldsFront(&$is_need_invoice)
    {
        $language = $this->context->language;
        $selected_country = (int) $this->getDefaultValue('delivery', 'id_country');
        if (!$this->context->customer->isLogged() && (Configuration::get('PS_GEOLOCATION_ENABLED'))) {
            if ($this->context->country->active) {
                $selected_country = $this->context->country->id;
            }
        }
        if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
            $countries = Carrier::getDeliveredCountries($language->id, true, true);
        } else {
            $countries = Country::getCountries($language->id, true);
        }

        $sp_fields          = array();
        $sp_fields_position = array();
        $fields = $this->getAllFields();
        foreach ($fields as $field) {
            if (!$field['active'] || (Tools::getIsset('rc'))) {
                continue;
            }
            if ($field['object'] == 'customer') {
				if ($field['name'] == 'id_gender') {
                    $genders = array();
                    foreach (Gender::getGenders() as $i => $gender) {
                        $genders[$i]['id_gender'] = $gender->id_gender;
                        $genders[$i]['name']      = $gender->name;
                    }

                    $field['options'] = [
                        'value'       => 'id_gender',
                        'description' => 'name',
                        'data'        => $genders
                    ];
                } else if ($field['name'] == 'passwd') {
					if ($this->context->customer->isLogged() || $this->context->customer->isGuest()) {
                        continue;
                    }
					if ($field['active'] == '1' && !Configuration::get('PS_GUEST_CHECKOUT_ENABLED')){
						$new_field = [
							'options' => [],
							'id_control' => 'checkbox_create_account',
							'name_control' => 'checkbox_create_account',
							'error_message' => '',
							'help_message'=> '',
							'classes' => '',
							'id' => null,
							'name' => 'checkbox_create_account',
							'object' => 'customer',
							'description' => 'I want to configure a custom password.',
							'lang' => $this->l('I want to configure a custom password'),
							'type' => 'isBool',
							'size' => '0',
							'type_control' => 'checkbox',
							'default_value' => '0',
							'group' => null,
							'row' => null,
							'col' => null,
							'required' => '0',
							'active' => '1',
						];
						if (isset($new_field['description']))
							$new_field['description'] = $this->l($new_field['description']);				
						$sp_fields[$new_field['object'].'_'.$new_field['name']] = $new_field;				
					}
					if (Configuration::get('PS_GUEST_CHECKOUT_ENABLED')) {
						$new_field = [
							'options' => [],
							'id_control' => 'checkbox_create_account_guest',
							'name_control' => 'checkbox_create_account_guest',
							'error_message' => '',
							'help_message'=> '',
							'classes' => '',
							'id' => null,
							'name' => 'checkbox_create_account_guest',
							'object' => 'customer',
							'description' => 'Create an account and enjoy the benefits of a registered customer.',
							'lang' => $this->l('Create an account and enjoy the benefits of a registered customer.'),
							'type' => 'isBool',
							'size' => '0',
							'type_control' => 'checkbox',
							'default_value' => '0',
							'group' => null,
							'row' => null,
							'col' => null,
							'required' => '0',
							'active' => (bool) Configuration::get('PS_GUEST_CHECKOUT_ENABLED') ? '1' : '0',
						];
						if (isset($new_field['description']))
							$new_field['description'] = $this->l($new_field['description']);
						$sp_fields[$new_field['object'].'_'.$new_field['name']] = $new_field;				
					}
					if ($field['name_control'] ==  'passwd'){
							$field['name_control'] = 'passwd_confirmation';
							$field['required'] = '1';
							if (isset($field['description']))
								$field['description'] = $this->l($field['description'] ,'setupfields');
							$sp_fields[$field['object'].'_'.$field['name']] = $field;
							$new_field = [
								'options' => [],
								'id_control' => 'customer_conf_passwd',
								'name_control' => 'passwd',
								'error_message' => '',
								'help_message'=> '',
								'classes' => 'customer',
								'id' => null,
								'name' => 'conf_passwd',
								'object' => 'customer',
								'description' => 'Repeat password',
								'lang' => $this->l('Repeat password'),
								'type' => 'confirmation',
								'size' => '32',
								'type_control' => 'textbox',
								'default_value' => '0',
								'group' => null,
								'row' => null,
								'col' => null,
								'required' => '1' ,
								'active' => '1',
							];
							if (isset($new_field['description']))
									$new_field['description'] = $this->l($new_field['description']);
							$sp_fields[$new_field['object'].'_'.$new_field['name']] = $new_field;					
					}	
					continue;
				}else if ($field['name'] == 'email') {
					   if (!$this->context->customer->isLogged() && !$this->context->customer->isGuest()) {
							$field['name_control'] = 'email_confirmation';
							$sp_fields[$field['object'].'_'.$field['name']] = $field;
							if ((bool) Configuration::get('SPSCO_REQUEST_CONFIRM_EMAIL')) {
								$new_field =[
									'options' => [],
									'id_control' => 'customer_conf_email',
									'name_control' => 'email',
									'error_message' => '',
									'help_message'=> '',
									'classes' => 'customer',
									'id' => null,
									'name' => 'conf_email',
									'object' => 'customer',
									'description' => 'Confirm email',
									'lang' => $this->l('Confirm email'),
									'type' => 'confirmation',
									'size' => '128',
									'type_control' => 'textbox',
									'default_value' => '',
									'group' => 'customer',
									'row' => null,
									'col' => null,
									'required' => '1',
									'active' => '1',
								];
								if (isset($new_field['description']))
									$new_field['description'] = $this->l($new_field['description']);
								$sp_fields[$new_field['object'].'_'.$new_field['name']] = $new_field;				
							}	
                        continue;
                    }

				}	

            } else if ($field['object'] == 'delivery') {
                if ((bool) Configuration::get('SPSCO_USE_SAME_NAME_CONTACT_DA')) {
						if ($field['name'] == 'firstname') {
                            continue;
                        } elseif ($field['name'] == 'lastname') {
                            continue;
                        }
                }
            } elseif ($field['object'] == 'invoice') {
                if ((bool) Configuration::get('SPSCO_ENABLE_INVOICE_ADDRESS')) {
                    if ((bool) Configuration::get('SPSCO_USE_SAME_NAME_CONTACT_BA')) {
                        if ($field['name'] == 'firstname') {
                            continue;
                        } elseif ($field['name'] == 'lastname') {
                            continue;
                        }
                    }
					if ((bool) Configuration::get('SPSCO_REQUIRED_INVOICE_ADDRESS')) {
                        $is_need_invoice = true;
                    }
                }
            }
			
			 if ($field['name'] == 'id_country') {
                $field['default_value'] = $selected_country;
                $field['options']       = array(
                    'empty_option' => true,
                    'value'        => 'id_country',
                    'description'  => 'name',
                    'data'         => $countries
                );
					
            }
			
			if ($field['name'] == 'vat_number') {
				if ($this->isModuleActive('vatnumber')) {
					if (Configuration::get('VATNUMBER_MANAGEMENT') || Configuration::get('VATNUMBER_CHECKING')) {
						$field['type'] = 'isVatNumber';
					}
				}
			}
			if (isset($field['description']))
				$field['description'] = $this->l($field['description'] ,'setupfields');
			$sp_fields[$field['object'].'_'.$field['name']] = $field;
        }
        $fields_position = $this->getFieldsPosition();
        if ($fields_position) {
            foreach ($fields_position as $group => $rows) {
                foreach ($rows as $row => $fields) {
                    foreach ($fields as $position => $field) {
                        if ($field['name'] == 'passwd') {
                            if (isset($sp_fields[$field['object'].'_checkbox_create_account'])) {
                                $index = $field['object'].'_checkbox_create_account';
                                $sp_fields_position[$group][-1][-1] = $sp_fields[$index];
                            }
                            if (isset($sp_fields[$field['object'].'_checkbox_create_account_guest'])) {
                                $index = $field['object'].'_checkbox_create_account_guest';
                                $sp_fields_position[$group][-1][-1] = $sp_fields[$index];
                            }
                        }
                        if (isset($sp_fields[$field['object'].'_'.$field['name']])) {
                            $index = $field['object'].'_'.$field['name'];
                            $sp_fields_position[$group][$row][$position] = $sp_fields[$index];
                        }
                        if ($field['name'] == 'passwd') {
                            if (isset($sp_fields[$field['object'].'_conf_passwd'])) {
                                $index                                            = $field['object'].'_conf_passwd';
                                $sp_fields_position[$group][$row][$position + 1] = $sp_fields[$index];
                            }
                        } elseif ($field['name'] == 'email') {
                            if (isset($sp_fields[$field['object'].'_conf_email'])) {
                                $index                                            = $field['object'].'_conf_email';
                                $sp_fields_position[$group][$row + 1][$position] = $sp_fields[$index];
                            }
                        }
                    }
                }
            }
        }
        return $sp_fields_position;
    }

    /**
     *
     */
	 public function removeAddressInvoice()
    {
        $this->context->cart->id_address_invoice = $this->context->cart->id_address_delivery;
        $this->context->cart->update();
    }

    /**
     * @return bool
     */
    public function install()
    {
        $this->_clearCache('*');
        
		foreach ($this->configArr as $config) {
            if ($config['name'] !== 'SPSCO_FIELDS_SETUP' && !Configuration::updateValue($config['name'], $config['default_value'], $config['is_html'])) {
                return false;
            }
		}
		Configuration::updateValue('SPSCO_FIELDS_SETUP', serialize($this->getFieldsSetUp()));
		
        if (!parent::install() ||
            !$this->registerHook('displayHeader') ||
            !$this->registerHook('displayShoppingCart') ||
            !$this->registerHook('displayAdminOrder') ||
			!$this->registerHook('displayBackOfficeHeader')
        ) {
            return false;
        }
		$this->createCustomerTemp();
		return true;
    }

    /**
     * @return bool
     */
	public function uninstall()
    {
        $this->_clearCache('*');
		foreach ($this->configArr as $config) {
            Configuration::deleteByName($config['name']);
        }
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    /**
     * @param $php_format
     * @return string
     */
    public function dateFormartPHPtoJqueryUI($php_format)
    {
        $symbols_matching = array(
            // Day
            'd' => 'dd',
            'D' => 'D',
            'j' => 'd',
            'l' => 'DD',
            'N' => '',
            'S' => '',
            'w' => '',
            'z' => 'o',
            // Week
            'W' => '',
            // Month
            'F' => 'MM',
            'm' => 'mm',
            'M' => 'M',
            'n' => 'm',
            't' => '',
            // Year
            'L' => '',
            'o' => '',
            'Y' => 'yy',
            'y' => 'y',
            // Time
            'a' => '',
            'A' => '',
            'B' => '',
            'g' => '',
            'G' => '',
            'h' => '',
            'H' => '',
            'i' => '',
            's' => '',
            'u' => '',
        );
        $jqueryui_format  = '';
        $escaping         = false;
        $size_format      = Tools::strlen($php_format);
        for ($i = 0; $i < $size_format; $i++) {
            $char = $php_format[$i];
            if ($char === '\\') { 
                $i++;
                if ($escaping) {
                    $jqueryui_format .= $php_format[$i];
                } else {
                    $jqueryui_format .= '\''.$php_format[$i];
                }
                $escaping = true;
            } else {
                if ($escaping) {
                    $jqueryui_format .= "'";
                    $escaping = false;
                }
                if (isset($symbols_matching[$char])) {
                    $jqueryui_format .= $symbols_matching[$char];
                } else {
                    $jqueryui_format .= $char;
                }
            }
        }
        return $jqueryui_format;
    }

    /**
     * @param $only_register
     * @param $show_authentication
     * @return array
     */
	public function getTemplateVarsSP($only_register, $show_authentication)
    {
        $language = $this->context->language;
        $countries = Country::getCountries($this->context->language->id, true);
        $countries_js = array();
        $countriesNeedIDNumber = array();
        $countriesNeedZipCode = array();
        $countriesIsoCode = array();
        foreach ($countries as $country) {
            $id_country = (int)$country['id_country'];
            $countriesIsoCode[$id_country] = $country['iso_code'];
            $countriesNeedIDNumber[$id_country] = (int)$country['need_identification_number'];
            if (!empty($country['zip_code_format'])) {
                $countriesNeedZipCode[$id_country] = $country['zip_code_format'];
            }
            if ($country['contains_states'] == 1 && isset($country['states']) && count($country['states']) > 0) {
                foreach ($country['states'] as $state) {
                    if ($state['active'] == 1) {
                        $countries_js[$id_country][] = array(
                            'id' => (int)$state['id_state'],
                            'name' => $state['name'],
                            'iso_code' => $state['iso_code']
                        );
                    }
                }
            }
        }

        $is_set_invoice = false;
        if (isset($this->context->cookie->is_set_invoice)) {
            $is_set_invoice = $this->context->cookie->is_set_invoice;
        }
        $date_format_language = $this->dateFormartPHPtoJqueryUI($language->date_format_lite);
        $id_country_delivery_default = Context::getContext()->country->id;
        $iso_code_country_delivery_default = Country::getIsoById($id_country_delivery_default);
        $id_country_invoice_default = Context::getContext()->country->id;
        $iso_code_country_invoice_default = Country::getIsoById($id_country_invoice_default);
        $position_steps = [
            0 => [
                'classes' => ($only_register ? '' : 'col-lg-4').' col-12 pr-2 pr-lg-3',
                'rows' => [
                    0 => [
                        'name_step' => 'customer',
                        'classes' => 'col-xs-12 col-12'
                    ]
                ]
            ],
            1 => [
                'classes' => 'col-lg-4 col-12 px-2 px-lg-3',
                'rows' => [
                    0 => [
                        'name_step' => 'carrier',
                        'classes' => 'col-12'
                    ],
                    1 => [
                        'name_step' => 'payment',
                        'classes' => 'col-12 '.($this->context->cart->isVirtualCart() ? 'col-md-12' : 'col-md-12')
                    ]
               ]
           ],
            2 => [
                'classes' => 'col-lg-4 col-12 pl-2 pl-lg-3',
                'rows' => [
                    0 => [
                        'name_step' => 'review',
                        'classes' => 'col-xs-12 col-12'
                    ]
               ]
           ]
        ];

        $messageValidate = [
			'errorGlobal'           => $this->l('This is not a valid.'),
			'errorIsName'           => $this->l('This is not a valid name.'),
			'errorIsEmail'          => $this->l('This is not a valid email address.'),
			'errorIsPostCode'       => $this->l('This is not a valid post code.'),
			'errorIsAddress'        => $this->l('This is not a valid address.'),
			'errorIsCityName'       => $this->l('This is not a valid city.'),
			'isMessage'             => $this->l('This is not a valid message.'),
			'errorIsDniLite'        => $this->l('This is not a valid document identifier.'),
			'errorIsPhoneNumber'    => $this->l('This is not a valid phone.'),
			'errorIsPasswd'         => $this->l('This is not a valid password. Minimum 5 characters.'),
			'errorisBirthDate'      => $this->l('This is not a valid birthdate.'),
			'errorisDate'           => $this->l('This is not a valid date.'),
			'badUrl'                => $this->l('This is not a valid url.'),
			'badInt'                => $this->l('This is not a valid.'),
			'notConfirmed'          => $this->l('The values do not match.'),
			'lengthTooLongStart'    => $this->l('It is only possible enter'),
			'lengthTooShortStart'   => $this->l('The input value is shorter than '),
			'lengthBadEnd'          => $this->l('characters.'),
			'requiredField'         => $this->l('This is a required field.'),
		];

        $register_customer = (bool)Tools::getValue('rc', false);
        if (($register_customer == 1 && !$this->context->customer->isLogged()) ||
            ($show_authentication && !$this->context->customer->isLogged())) {
            $register_customer = true;
        }

        $templateVars = [
			'PresTeamShop' => [
				'spsco_static_token'          => Tools::encrypt('spstepcheckout/index'),
				'module_dir'                => $this->_path,
				'module_img'                => $this->_path.'views/img/',
				'iso_lang'                  => Language::getIsoById($this->context->language->id),
				'success_code'              => 0,
				'error_code'                => -1,
				'id_language_default'       => Configuration::get('PS_LANG_DEFAULT')
			],
            'messageValidate'               => $messageValidate,
            'spsco_static_token'              => Tools::encrypt('spstepcheckout/index'),
            'static_token'                  => Tools::getToken(false),
            'countries'                     => $countries_js,
            'countriesNeedIDNumber'         => $countriesNeedIDNumber,
            'countriesNeedZipCode'          => $countriesNeedZipCode,
            'countriesIsoCode'              => $countriesIsoCode,
            'position_steps'                => $position_steps,
            'is_virtual_cart'               => $this->context->cart->isVirtualCart(),
            'hook_create_account_top'       => Hook::exec('displayCustomerAccountFormTop'),
            'hook_create_account_form'      => Hook::exec('displayCustomerAccountForm'),
            'is_set_invoice'                => $is_set_invoice,
            'register_customer' => $register_customer,
            'SPSCOVAR' => [
                'date_format_language'          => $date_format_language,
                'id_country_delivery_default'   => $id_country_delivery_default,
                'id_country_invoice_default'    => $id_country_invoice_default,
                'iso_code_country_delivery_default' => $iso_code_country_delivery_default,
                'iso_code_country_invoice_default'  => $iso_code_country_invoice_default,
                'IS_GUEST' => (bool)$this->context->customer->isGuest(),
                'IS_LOGGED' => (bool)$this->context->customer->isLogged(),
                'CONFIGS' => $this->configVars,
                'PATH_DIR' => __PS_BASE_URI__.'modules/'.$this->name.'/',
                'PATH_IMG' => __PS_BASE_URI__.'modules/'.$this->name.'/'.'views/img/',
                'PRESTASHOP' => [
                    'CONFIGS' => [
                        'PS_TAX_ADDRESS_TYPE' => Configuration::get('PS_TAX_ADDRESS_TYPE'),
                        'PS_GUEST_CHECKOUT_ENABLED' => (int)Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
                    ],
                ],
                'Msg' => [
                    'there_are' => $this->l('There are'),
                    'there_is' => $this->l('There is'),
                    'error' => $this->l('Error'),
                    'errors' => $this->l('Errors'),
                    'field_required' => $this->l('Required'),
                    'dialog_title' => $this->l('Confirm Order'),
                    'no_payment_modules' => $this->l('There are no payment methods available.'),
                    'validating' => $this->l('Validating, please wait'),
                    'error_zipcode' => $this->l('The Zip / Postal code is invalid'),
                    'error_registered_email' => $this->l('An account is already registered with this e-mail'),
                    'error_registered_email_guest' => $this->l('This email is already registered, you can login or fill form again.'),
                    'delivery_billing_not_equal' => $this->l('Delivery address alias cannot be the same as billing address alias'),
                    'errors_trying_process_order' => $this->l('The following error occurred while trying to process the order'),
                    'agree_terms_and_conditions' => $this->l('You must agree to the terms of service before continuing.'),
                    'agree_privacy_policy' => $this->l('You must agree to the privacy policy before continuing.'),
                    'fields_required_to_process_order' => $this->l('You must complete the required information to process your order.'),
                    'check_fields_highlighted' => $this->l('Check the fields that are highlighted and marked with an asterisk.'),
                    'error_number_format' => $this->l('The format of the number entered is not valid.'),
                    'oops_failed' => $this->l('Oops! Failed'),
                    'continue_with_step_3' => $this->l('Continue with step 3.'),
                    'email_required' => $this->l('Email address is required.'),
                    'email_invalid' => $this->l('Invalid e-mail address.'),
                    'password_required' => $this->l('Password is required.'),
                    'password_too_long' => $this->l('Password is too long.'),
                    'password_invalid' => $this->l('Invalid password.'),
                    'addresses_same' => $this->l('You must select a different address for shipping and billing.'),
                    'create_new_address' => $this->l('Are you sure you wish to add a new delivery address? You can use the current address and modify the information.'),
                    'cart_empty' => $this->l('Your shopping cart is empty. You need to refresh the page to continue.'),
                    'dni_spain_invalid' => $this->l('DNI/CIF/NIF is invalid.'),
                    'payment_method_required' => $this->l('Please select a payment method to proceed.'),
                    'shipping_method_required' => $this->l('Please select a shipping method to proceed.'),
                    'select_pickup_point' => $this->l('To select a pick up point is necessary to complete your information and delivery address in the first step.'),
                    'need_select_pickup_point' => $this->l('You need to select on shipping a pickup point to continue with the purchase.'),
                    'select_date_shipping' => $this->l('Please select a date for shipping.'),
                    'confirm_payment_method' => $this->l('Confirmation payment'),
                    'to_determinate' => $this->l('To determinate'),
                    'login_customer' => $this->l('Login'),
                    'processing_purchase' => $this->l('Processing purchase')
                ]
            ]
        ];

        return $templateVars;
    }

    /**
     * @param $tpl
     * @param $params
     */
	public function includeTemplate($tpl, $params)
    {
        $this->smarty->assign($params);
        if (file_exists(_PS_THEME_DIR_.'modules/'.$this->name.'/views/templates/front/'.$tpl)) {
            echo $this->fetch(_PS_THEME_DIR_.'modules/'.$this->name.'/views/templates/front/'.$tpl);
        } else {
            echo $this->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/front/'.$tpl);
        }
    }

    /**
     * @return bool
     */
	private function createCustomerTemp()
    {
        $values = [
            'firstname' => 'Name Temp',
            'lastname' => 'Name Temp',
            'email' => pSQL('noreply@'.$this->context->shop->domain),
            'passwd' => Tools::encrypt('SPCO123456'),
            'id_shop' => (int)Context::getContext()->shop->id,
            'id_shop_group' => (int)Context::getContext()->shop->id_shop_group,
            'id_default_group' => (int)Configuration::get('PS_CUSTOMER_GROUP'),
            'id_lang' => (int)Context::getContext()->language->id,
            'birthday' => '0000-00-00',
            'secure_key' => md5(uniqid(rand(), true)),
            'active' => 0,
            'deleted' => 1
       ];
        Db::getInstance(_PS_USE_SQL_SLAVE_)->insert('customer', $values);
        $id_customer = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->Insert_ID();
        if (empty($id_customer)) {
            return false;
        } else {
            $values = array(
                'id_customer' => (int)$id_customer,
                'id_group' => (int)Configuration::get('PS_CUSTOMER_GROUP')
            );
            Db::getInstance(_PS_USE_SQL_SLAVE_)->insert('customer_group', $values);
            Configuration::updateValue('SPSCO_ID_CUSTOMER', $id_customer);
        }
    }

    /**
     * @param string $template
     * @param null $cache_id
     * @param null $compile_id
     */
    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);
    }

    /**
     * @param $name_module
     * @param bool $function_exist
     * @return bool|Module
     */
	public function isModuleActive($name_module, $function_exist = false)
    {
        if (Module::isInstalled($name_module)) {
            $module = Module::getInstanceByName($name_module);
            if (Validate::isLoadedObject($module) && $module->active) {
                $active_device = true;

                if (method_exists(Context::getContext(), 'getDevice')) {
                    $sql = new DbQuery();
                    $sql->from('module_shop', 'm');
                    $sql->where('m.id_module = '.(int)$module->id);
                    $sql->where('m.enable_device & '.(int)Context::getContext()->getDevice());
                    $sql->where('m.id_shop = '.(int)Context::getContext()->shop->id);
                    $active_device = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                }
                if ($active_device) {
                    if ($function_exist) {
                        if (method_exists($module, $function_exist)) {
                            return $module;
                        } else {
                            return false;
                        }
                    }
                    return $module;
                }
            }
        }
        return false;
    }

    /**
     * @param string $object
     * @return int|string
     */
	public function getIdAddressAvailable($object = 'delivery')
    {
		
        $query = new DbQuery();
        $query->select('id_address');
        $query->from('address');
        $query->where('id_customer = '.(int)Configuration::get('SPSCO_ID_CUSTOMER'));
        $query->where('id_address NOT IN (SELECT id_address_delivery FROM '._DB_PREFIX_.'cart)');
        $query->where('deleted = 0');
        $query->where('active = 1');

        $id_address = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        if (!empty($id_address)) {
            if ($this->context->customer->isLogged()) {
                $values = array('id_customer' => (int)$this->context->customer->id);
                $where = 'id_address = '.(int)$id_address;

                Db::getInstance(_PS_USE_SQL_SLAVE_)->update('address', $values, $where);
            }
        } else {
            $id_address = $this->createAddress($object);
        }
        return $id_address;
    }

    /**
     * @param $object
     * @param $name_field
     * @return null
     */
	private function getDefaultValue($object, $name_field){
		$fields = $this->getAllFields();
		foreach($fields as $key => $field){
			if ($fields[$key]['object'] == $object && $fields[$key]['name'] == $name_field){
				return $fields[$key]['default_value'];
			}
		}
		
		return null;
		
	}

    /**
     * @param $object
     * @param $name_field
     * @return mixed
     */
	public  function getField($object, $name_field)
    {
        $fields = $this->getAllFields();
	
		foreach($fields as $key => $field){
			if ($fields[$key]['object'] == $object && $fields[$key]['name'] == $name_field){
				return $fields[$key];
			}
		}
    }

    /**
     * @param string $object
     * @return int|string
     */
    public function createAddress($object = 'delivery')
    {
		$this->getDefaultValue($object, 'firstname');
        $values = [
            'firstname'  => pSQL($this->getDefaultValue($object, 'firstname')),
            'lastname'   => pSQL($this->getDefaultValue($object, 'lastname')),
            'address1'   => pSQL($this->getDefaultValue($object, 'address1')),
            'city'       => pSQL($this->getDefaultValue($object, 'city')),
            'postcode'   => pSQL($this->getDefaultValue($object, 'postcode')),
            'id_country' => (int)$this->getDefaultValue($object, 'id_country'),
            'id_state'   => (int)$this->getDefaultValue($object, 'id_state'),
            'alias'      => pSQL($this->getDefaultValue($object, 'alias')),
            'date_add'   => date('Y-m-d H:i:s'),
            'date_upd'   => date('Y-m-d H:i:s'),
        ];
        if ($this->context->customer->isLogged()) {
            $addresses = $this->context->customer->getAddresses($this->context->language->id);
            $alias_count = count($addresses) + 1;
			$values['alias'] = count($addresses) > 1 ? $addresses[count($addresses) -1]['alias'] : $values['alias'];
            $values['alias'] .= ' '.$alias_count;
        } else {
            $values['alias'] .= (version_compare(_PS_VERSION_, '1.6', '>=') ? ' #' : '').date('s');
        }

        $address            = new Address();
        $fields_db_required = $address->getFieldsRequiredDatabase();
        foreach ($fields_db_required as $field) {
            $values[$field['field_name']] = pSQL($this->getDefaultValue($object, $field['field_name']));
        }

        if (empty($values['id_country'])) {
            $values['id_country'] = (int)Configuration::get('PS_COUNTRY_DEFAULT');
        }
          
        $field_state = $this->getField($object, 'id_state');
		
        if ($field_state['active'] == '0') {
            if (Country::containsStates((int) $values['id_country'])) {
                $states = State::getStatesByIdCountry((int) $values['id_country']);
                if (count($states)) {
                    $values['id_state'] = (int)$states[0]['id_state'];
                }
            }
        }
        if (empty($values['postcode'])) {
            $country = new Country((int) $values['id_country']);
            if (Validate::isLoadedObject($country)) {
                $values['postcode'] = str_replace(
                    'C',
                    $country->iso_code,
                    str_replace(
                        'N',
                        '0',
                        str_replace(
                            'L',
                            'A',
                            $country->zip_code_format
                        )
                    )
                );
            }
        }

        if ($this->context->customer->isLogged()) {
            if ((bool) Configuration::get('SPSCO_USE_SAME_NAME_CONTACT_DA') && $object == 'delivery') {
                $values['firstname'] = pSQL($this->context->customer->firstname);
                $values['lastname']  = pSQL($this->context->customer->lastname);
            }

            if ((bool) Configuration::get('SPSCO_USE_SAME_NAME_CONTACT_BA') && $object == 'invoice') {
                $values['firstname'] = pSQL($this->context->customer->firstname);
                $values['lastname']  = pSQL($this->context->customer->lastname);
            }

            $values['id_customer'] = (int)$this->context->customer->id;
        } else {
            $values['id_customer'] = (int)Configuration::get('SPSCO_ID_CUSTOMER');
        }

        Db::getInstance(_PS_USE_SQL_SLAVE_)->insert('address', $values);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->Insert_ID();
    }

    /**
     * @return string
     */
    public function loadPayment()
    {
        $this->context->smarty->assign('link', $this->context->link);
        $payment_need_register = false;
        $paymentOptionsFinder = new PaymentOptionsFinder();
        $payment_options = $paymentOptionsFinder->present();
        if ($payment_options) {
            foreach ($payment_options as $name_module => &$module_options) {
                foreach ($module_options as &$option) {
                    $path_image = _PS_MODULE_DIR_.$this->name.'/views/img/payments/'.$name_module;
                    $module_payment = Module::getInstanceByName($name_module);
                    $option['id_module_payment'] = $module_payment->id;

                    if (empty($option['logo'])) {
                        if (file_exists($path_image.'.png')) {
                            $option['logo'] = __PS_BASE_URI__.'modules/'.$this->name.'/views/img/payments/'.$name_module.'.png';
                        } elseif (file_exists($path_image.'.gif')) {
                            $option['logo'] = __PS_BASE_URI__.'modules/'.$this->name.'/views/img/payments/'.$name_module.'.gif';
                        } elseif (file_exists($path_image.'.jpeg')) {
                            $option['logo'] = __PS_BASE_URI__.'modules/'.$this->name.'/views/img/payments/'.$name_module.'.jpeg';
                        }
                    }
                }
            }
        }

        $templateVars = [
            'payment_options' => $payment_options,
            'selected_payment_option' => false,
            'CONFIGS' => $this->configVars,
            'payment_need_register' => $payment_need_register
        ];
        $this->context->smarty->assign($templateVars);
        $html = $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/front/payment.tpl');
        return $html;
    }

    /**
     * @return string
     */
	public function loadReview()
    {
        $set_id_customer_sp = false;
        if (!$this->context->cookie->id_customer) {
            $this->context->cookie->id_customer = $this->configVars['SPSCO_ID_CUSTOMER'];
            if (!$this->context->customer->id) {
                $this->context->customer->id = $this->configVars['SPSCO_ID_CUSTOMER'];
            }
            if (!$this->context->cart->id_customer) {
                $this->context->cart->id_customer = $this->configVars['SPSCO_ID_CUSTOMER'];
            }
            $set_id_customer_sp = true;
            $this->context->cart->update();
        }

        if (Tools::getIsset('id_country') && Tools::getIsset('id_state')) {
            $id_state = (int) Tools::getValue('id_state');
            if (!empty($id_state)) {
                $this->context->country->id_zone = State::getIdZone($id_state);
            }
        }

        if ($old_message = Message::getMessageByCartId((int) $this->context->cart->id)) {
            $this->context->smarty->assign('oldMessage', $old_message['message']);
        }

        $cartPresenter = new CartPresenter();
        $presented_cart = $cartPresenter->present($this->context->cart);
        $conditionsToApproveFinder = new ConditionsToApproveFinder($this->context, $this->context->getTranslator());
        $this->context->smarty->assign([
            'link' => $this->context->link,
            'ps_stock_management' => Configuration::get('PS_STOCK_MANAGEMENT'),
            'cart' => $presented_cart,
            'customer' => ($this->context->customer->isLogged() ? $this->context->customer : false),
            'spstepcheckout' => $this,
            'CONFIGS'               => $this->configVars,
            'PATH_IMG' => __PS_BASE_URI__.'modules/'.$this->name.'/views/img/',
            'PATH_TPL' => _PS_ROOT_DIR_.'/modules/'.$this->name.'/',
            'PS_WEIGHT_UNIT'        => Configuration::get('PS_WEIGHT_UNIT'),
            'urls' => $this->context->controller->getTemplateVarUrls(),
            'conditions_to_approve' => $conditionsToApproveFinder->getConditionsToApproveForTemplate(),
			'static_token' => Tools::getToken(false),
            'total_cart' => Tools::displayPrice(
                $this->context->cart->getOrderTotal(),
                new Currency($this->context->cart->id_currency),
                false
            )
        ]);

        $presenter = new CartPresenter();
        $presented_cart = $presenter->present($this->context->cart, true);
        $this->context->cart->getSummaryDetails();
        $this->context->smarty->assign(array('cart' => $presented_cart));

        $total_free_ship = 0;
        $free_ship       = Tools::convertPrice(
            (float) Configuration::get('PS_SHIPPING_FREE_PRICE'),
            new Currency((int) $this->context->cart->id_currency)
        );

        if (empty($free_ship)) {
            $carrier = new Carrier($this->context->cart->id_carrier);

            if (Validate::isLoadedObject($carrier)) {
                if ($carrier->shipping_method == Carrier::SHIPPING_METHOD_PRICE && $carrier->is_free == 0) {
                    $total_products = $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
                    $ranges = RangePrice::getRanges((int)$carrier->id);
                    $id_zone = Address::getZoneById((int)$this->context->cart->id_address_delivery);
                    foreach ($ranges as $range) {
                        $query = new DbQuery();
                        $query->select('price');
                        $query->from('delivery');
                        $query->where('id_range_price = '.(int)$range['id_range_price']);
                        $query->where('id_zone = '.(int)$id_zone);
                        $query->where('id_carrier = '.(int)$carrier->id);
                        $cost_shipping = Db::getInstance()->getValue($query);

                        $delimiter1 = Tools::convertPrice($range['delimiter1'], $this->context->currency);
                        $cost_shipping = Tools::convertPrice($cost_shipping, $this->context->currency);

                        if ($cost_shipping == 0 && $total_products < $delimiter1) {
                            $free_ship = $delimiter1;
                            break;
                        }
                    }
                }
            }
        }

        if ($free_ship) {
            $discounts         = $this->context->cart->getCartRules();
            $total_discounts   = $this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
            $total_products_wt = $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
            $total_free_ship   = $free_ship - ($total_products_wt - $total_discounts);
            foreach ($discounts as $discount) {
                if ($discount['free_shipping'] == 1) {
                    $total_free_ship = 0;
                    break;
                }
            }
            $total_free_ship = Tools::displayPrice($total_free_ship, $this->context->currency);
        }
        $this->context->smarty->assign('free_ship', $total_free_ship);

        if ($set_id_customer_sp) {
            $this->context->customer->id = null;
            unset($this->context->cookie->id_customer);

            $this->context->cart->id_customer = null;
            $this->context->cart->update();
        }
        $html = '';
        $minimal_purchase = $this->checkMinimalPurchase();
        if (!empty($minimal_purchase)) {
            $this->context->smarty->assign('minimal_purchase', $minimal_purchase);
        }
		$html .= $this->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/front/review.tpl');
        $html .= $this->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/front/review_footer.tpl');
        return $html;
    }

    /**
     * @return int|string
     */
    public function createAddressAjax()
    {
        $object = Tools::getValue('object');
        $id_address = $this->createAddress($object);
        if ($object == 'delivery') {
            $this->context->cart->id_address_delivery = $id_address;
        }
        if ($object == 'invoice') {
            $this->context->cart->id_address_invoice = $id_address;
        }
        $this->context->cart->save();
        return $id_address;
    }

    /**
     * @param $customer
     * @return bool
     */
	public function singInCustomer($customer)
    {
        $this->context->updateCustomer($customer);
        Hook::exec('actionAuthentication', array('customer' => $customer));
        CartRule::autoRemoveFromCart();
        CartRule::autoAddToCart();
        return true;
    }

    /**
     *
     */
	public function updateAddressInvoice()
    {
        $id_country          = (int) Tools::getValue('id_country');
        $id_state            = (int) Tools::getValue('id_state');
        $postcode            = Tools::getValue('postcode', '');
        $city                = Tools::getValue('city', '');
        $vat_number          = Tools::getValue('vat_number', '');
        $id_address_invoice  = Tools::getValue('id_address_invoice', '');

        if (empty($id_address_invoice)) {
            if (empty($this->context->cart->id_address_invoice) && !$this->context->customer->isLogged()) {
                $id_address_invoice = $this->getIdAddressAvailable('invoice');
            } else {
                $id_address_invoice = $this->context->cart->id_address_invoice;
            }
        }

        if (!empty($id_address_invoice)) {
            if (empty($id_country)) {
                $id_country = (int) $this->getDefaultValue('invoice', 'id_country');
            }
            if (empty($id_state)) {
                $id_state = (int) $this->getDefaultValue('invoice', 'id_state');
            }

            if (empty($city)) {
                $city_tmp = $this->getDefaultValue('invoice', 'city');
                if ($city != '.' && !empty($city)) {
                    $city = $city_tmp;
                }
            }

            $invoice_address = new Address($id_address_invoice);

            $invoice_address->id_country = $id_country;
            $invoice_address->id_state   = $id_state;
            $invoice_address->vat_number = $vat_number;

            if (!empty($postcode)) {
                $invoice_address->postcode = $postcode;
            } else {
                $invoice_address->postcode = '';
            }

            if (!empty($city)) {
                $invoice_address->city = $city;
            }

            $invoice_address->update();

            $this->context->cart->id_address_invoice = $id_address_invoice;
            $this->context->cart->update();

            if ($this->context->cart->isVirtualCart()) {
                $this->context->cart->id_address_delivery = $this->context->cart->id_address_invoice;
                $this->context->cart->update();
            }
        }
    }

    /**
     * @return array
     */
    public function loadAddressesCustomer()
    {
        $result = array();

        if (Validate::isLoadedObject($this->context->customer) && !empty($this->context->customer->id)) {
            $addresses = $this->context->customer->getAddresses($this->context->language->id);
            $result = array(
                'id_address_delivery' => $this->context->cart->id_address_delivery,
                'id_address_invoice'  => $this->context->cart->id_address_invoice,
                'addresses'           => $addresses,
            );
        }

        return $result;
    }

    /**
     * @return array
     */
	public function loginCustomer()
    {
        $is_logged = false;
        Hook::exec('actionAuthenticationBefore');
        $customer = new Customer();
        $authentication = $customer->getByEmail(
            Tools::getValue('email'),
            Tools::getValue('password')
        );
        if (isset($authentication->active) && !$authentication->active) {
            $this->errors[] = $this->l('Your account isn\'t available at this time, please contact us');
        } elseif (!$authentication || !$customer->id || $customer->is_guest) {
            $this->errors[] = $this->l('The email or password is incorrect. Verify your information and try again.');
        } else {
            if (count($this->errors) == 0) {
                $is_logged = $this->singInCustomer($customer);
            }
        }
        $results = array(
            'success' => $is_logged,
            'errors'  => $this->errors,
        );
        return $results;
    }

    /**
     * @return string
     */
	public function loadCMS()
    {
        $html   = [];
        $id_cms = Tools::getValue('id_cms', '');
        $cms = new CMS($id_cms, $this->context->language->id);
        if (Validate::isLoadedObject($cms)) {
            $html['title'] = $cms->meta_title;
			$html['content'] = $cms->content;
        }
        die(Tools::jsonEncode($html));
    }

    /**
     * @return string
     */
	public function checkMinimalPurchase()
    {
        $msg = '';
        $currency = Currency::getCurrency((int) $this->context->cart->id_currency);
        $minimal_purchase = Tools::convertPrice((float) Configuration::get('PS_PURCHASE_MINIMUM'), $currency);
        $total_products = $this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
        if ($this->isModuleActive('syminimalpurchase')) {
            $customer = new Customer((int)($this->context->customer->id));
            $id_group = $customer->id_default_group;
            $minimal_purchase_groups = Tools::jsonDecode(Configuration::get('syminimalpurchase'));

            if ($minimal_purchase_groups && isset($minimal_purchase_groups->{$id_group})) {
                $minimal_purchase = $minimal_purchase_groups->{$id_group};
            }
        } elseif ($minimumpurchasebycg = $this->isModuleActive('minimumpurchasebycg')) {
            if (!$minimumpurchasebycg->hasAllowedMinimumPurchase()) {
                $minimal_purchase = $minimumpurchasebycg->minimumpurchaseallowed;
            }
        }
        if ($total_products < $minimal_purchase) {
            $msg = sprintf(
                $this->l('A minimum purchase total of %1s (tax excl.) is required to validate your order, current purchase total is %2s (tax excl.).'),
                Tools::displayPrice($minimal_purchase, $currency),
                Tools::displayPrice($total_products, $currency)
            );
        }
        return $msg;
    }

    /**
     * @param $delivery_address
     * @param $invoice_address
     * @return bool
     */
	public function isSameAddress($delivery_address, $invoice_address)
    {
        $is_same = true;
        if ($delivery_address->id_country != $invoice_address->id_country) {
            $is_same = false;
        }
        if ($delivery_address->id_state != $invoice_address->id_state) {
            $is_same = false;
        }
        if ($delivery_address->alias != $invoice_address->alias) {
            $is_same = false;
        }
        if ($delivery_address->company != $invoice_address->company) {
            $is_same = false;
        }
        if ($delivery_address->lastname != $invoice_address->lastname) {
            $is_same = false;
        }
        if ($delivery_address->firstname != $invoice_address->firstname) {
            $is_same = false;
        }
        if ($delivery_address->address1 != $invoice_address->address1) {
            $is_same = false;
        }
        if ($delivery_address->address2 != $invoice_address->address2) {
            $is_same = false;
        }
        if ($delivery_address->postcode != $invoice_address->postcode) {
            $is_same = false;
        }
        if ($delivery_address->city != $invoice_address->city) {
            $is_same = false;
        }
        if ($delivery_address->other != $invoice_address->other) {
            $is_same = false;
        }
        if ($delivery_address->phone != $invoice_address->phone) {
            $is_same = false;
        }
        if ($delivery_address->phone_mobile != $invoice_address->phone_mobile) {
            $is_same = false;
        }
        if ($delivery_address->dni != $invoice_address->dni) {
            $is_same = false;
        }
        return $is_same;
    }

    /**
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
	public function loadAddress()
    {
        $id_address_delivery = (int) Tools::getValue('delivery_id');
        $id_address_invoice  = (int) Tools::getValue('invoice_id');
        $is_set_invoice      = Tools::getValue('is_set_invoice');
        if (!isset($this->context->cookie->sp_suggest_address)) {
            if ($this->context->customer->isLogged()) {
                $query = 'SELECT o.id_address_delivery, o.id_address_invoice FROM `'._DB_PREFIX_.'orders` AS o';
                $query .= ' INNER JOIN `'._DB_PREFIX_.'address` AS ad ON (ad.id_address = o.id_address_delivery OR ';
                $query .= ' ad.id_address = o.id_address_invoice)';
                $query .= ' WHERE o.id_customer = '.(int)$this->context->customer->id.' AND ad.deleted = 0';
                $query .= ' ORDER BY o.id_order DESC LIMIT 1';

                $result = Db::getInstance()->executeS($query);

                if ($result) {
                    $id_address_delivery_tmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_address` FROM '._DB_PREFIX_.'address a WHERE a.deleted = 0 AND a.active = 1 AND a.`id_address` = '.(int)$result[0]['id_address_delivery']);
                    if ($id_address_delivery_tmp) {
                        $id_address_delivery = $id_address_delivery_tmp;
                        $this->context->cart->id_address_delivery = $id_address_delivery;
                    }

                    if ($is_set_invoice || $this->configVars['SPSCO_REQUIRED_INVOICE_ADDRESS']) {
                        $id_address_invoice_tmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_address` FROM '._DB_PREFIX_.'address a WHERE a.deleted = 0 AND a.active = 1 AND a.`id_address` = '.(int)$result[0]['id_address_invoice']);
                        if ($id_address_invoice_tmp) {
                            $id_address_invoice = $id_address_invoice_tmp;
                            $this->context->cart->id_address_invoice = $id_address_invoice;
                        }
                    }

                    if (!$this->context->cart->update()) {
                        $this->errors[] = $this->l('An error occurred while updating your cart.');
                    }

                    $this->context->cookie->sp_suggest_address = true;
                }
            }
        }

        $this->checkAddressExist($id_address_delivery, $id_address_invoice);

        if (empty($id_address_delivery)
            && empty($id_address_invoice)
            && empty($this->context->cart->id_address_delivery)
            && empty($this->context->cart->id_address_invoice)
            && $this->context->customer->isLogged()
        ) {
            $query = 'SELECT id_address FROM '._DB_PREFIX_.'address WHERE id_customer = '.(int)$this->context->customer->id;
            $query .= ' AND active = 1 AND deleted = 0';
            $id_address = Db::getInstance()->getValue($query);

            if (!empty($id_address)) {
                $id_address_delivery = $id_address;
                $id_address_invoice = $id_address;
            }
        }

        if (empty($id_address_delivery)) {
            $id_address_delivery = $this->context->cart->id_address_delivery;
        }
        if (empty($id_address_invoice)) {
            $id_address_invoice = $id_address_delivery;
        }

        if (empty($id_address_invoice) && empty($id_address_delivery) && $this->context->customer->isLogged()) {
            $id_address_delivery = (int) $this->createAddress();
        }

        $address_delivery = new Address((int) $id_address_delivery);
        $address_invoice  = new Address((int) $id_address_invoice);
        $customer         = $this->context->customer;

        if ($address_invoice->id_customer != $customer->id) {
            $address_invoice  = new Address();
        }
        if ($address_delivery->id_customer != $customer->id) {
            $address_delivery  = new Address();
        }

        if (Validate::isLoadedObject($address_delivery) && Validate::isLoadedObject($customer)) {
            if (!Validate::isDate($address_delivery->date_add)) {
                $address_delivery->date_add = date('Y-m-d H:i:s');
            }
            if (!Validate::isDate($address_delivery->date_upd)) {
                $address_delivery->date_upd = $address_delivery->date_add;
            }
            
            if ($address_delivery->id_customer != $customer->id) {
                $this->errors[] = $this->l('This address is not yours.');
            } elseif (!Validate::isLoadedObject($address_delivery) || $address_delivery->deleted) {
                $this->errors[] = $this->l('This address is invalid. Sign out of session and login again.');
            } else {
                $this->context->cart->id_address_delivery = $id_address_delivery;

                if ($this->configVars['SPSCO_USE_SAME_NAME_CONTACT_DA']) {
                    $address_delivery->firstname = $customer->firstname;
                    $address_delivery->lastname  = $customer->lastname;
                    $address_delivery->update();
                }
                
                if (!$this->context->cart->update()) {
                    $this->errors[] = $this->l('An error occurred while updating your cart.');
                }
            }
        }

        if (Validate::isLoadedObject($address_invoice) && Validate::isLoadedObject($customer)) {
            if (!Validate::isDate($address_invoice->date_add)) {
                $address_invoice->date_add = date('Y-m-d H:i:s');
            }
            if (!Validate::isDate($address_invoice->date_upd)) {
                $address_invoice->date_upd = $address_invoice->date_add;
            }
            
            if ($address_invoice->id_customer != $customer->id) {
                $this->errors[] = $this->l('This address is not yours.');
            } elseif (!Validate::isLoadedObject($address_invoice) || $address_invoice->deleted) {
                $this->errors[] = $this->l('This address is invalid. Sign out of session and login again.');
            } else {
                $this->context->cart->id_address_invoice = $id_address_invoice;

                if ($this->configVars['SPSCO_USE_SAME_NAME_CONTACT_BA']) {
                    $address_invoice->firstname = $customer->firstname;
                    $address_invoice->lastname  = $customer->lastname;
                    $address_invoice->update();
                }
                
                if (!$this->context->cart->update()) {
                    $this->errors[] = $this->l('An error occurred while updating your cart.');
                }
            }
        }

        $result = array(
            'hasError'         => (boolean) count($this->errors),
            'errors'           => $this->errors,
            'address_delivery' => $address_delivery,
            'address_invoice'  => $address_invoice,
            'customer'         => $customer,
        );

        return $result;
    }

    /**
     * @param $id_address_delivery
     * @param $id_address_invoice
     */
	public function checkAddressExist(&$id_address_delivery, &$id_address_invoice)
    {
        $is_same_address = false;
        if ($this->context->cart->id_address_delivery == $this->context->cart->id_address_invoice) {
            $is_same_address = true;
        }

        if (!empty($this->context->cart->id_address_delivery)) {
            $query = new DbQuery();
            $query->from('address');
            $query->where('id_address = '.(int)$this->context->cart->id_address_delivery);
            $query->where('active = 1');
            $query->where('deleted = 0');

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
            if (!$result) {
                $id_address = $this->getIdAddressAvailable();
                $this->updateAddressCart('delivery', $this->context->cart->id_address_delivery, $id_address);
            } else {
                if (!$this->context->customer->isLogged() && !$this->context->customer->isGuest()) {
                    if ($result['id_customer'] != $this->configVars['SPSCO_ID_CUSTOMER']) {
                        $id_address = $this->getIdAddressAvailable();
                        $this->updateAddressCart('delivery', $this->context->cart->id_address_delivery, $id_address);
                    }
                } else {
                    if ($this->context->customer->isLogged() || $this->context->customer->isGuest()) {
                        if ($result['id_customer'] == $this->configVars['SPSCO_ID_CUSTOMER']) {
                            $address = new Address($this->context->cart->id_address_delivery);
                            $address->id_customer = $this->context->customer->id;
                            $address->update();
                        }
                    }
                }
            }
        }

        if (!empty($id_address_delivery)) {
            $address = new Address($id_address_delivery);

            if ((Validate::isLoadedObject($address) && $address->id_customer != $this->context->customer->id)
                || !Validate::isLoadedObject($address)
            ) {
                $id_address_delivery = null;
            }
        }

        if (!$is_same_address && !empty($this->context->cart->id_address_invoice)) {
            $query = new DbQuery();
            $query->from('address');
            $query->where('id_address = '.(int)$this->context->cart->id_address_invoice);
            $query->where('active = 1');
            $query->where('deleted = 0');

            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
            if (!$result) {
                $id_address = $this->getIdAddressAvailable();
                $this->updateAddressCart('invoice', $this->context->cart->id_address_invoice, $id_address);
            } else {
                if (!$this->context->customer->isLogged() && !$this->context->customer->isGuest()) {
                    if ($result['id_customer'] != $this->configVars['SPSCO_ID_CUSTOMER']) {
                        $id_address = $this->getIdAddressAvailable();
                        $this->updateAddressCart('invoice', $this->context->cart->id_address_invoice, $id_address);
                    }
                } else {
                    if ($this->context->customer->isLogged() || $this->context->customer->isGuest()) {
                        if ($result['id_customer'] == $this->configVars['SPSCO_ID_CUSTOMER']) {
                            $address = new Address($this->context->cart->id_address_invoice);
                            $address->id_customer = $this->context->customer->id;
                            $address->update();
                        }
                    }
                }
            }
        }

        if (!empty($id_address_invoice)) {
            $address = new Address($id_address_invoice);

            if ((Validate::isLoadedObject($address) && $address->id_customer != $this->context->customer->id)
                || !Validate::isLoadedObject($address)
            ) {
                $id_address_invoice = null;
            }
        }

        if (($this->context->customer->isLogged() || $this->context->customer->isGuest()) && !empty($this->context->cart->id_address_delivery)) {
            $sql = 'UPDATE `'._DB_PREFIX_.'cart_product`
                SET `id_address_delivery` = '.(int)$this->context->cart->id_address_delivery.'
                WHERE `id_cart` = '.(int)$this->context->cart->id.'
                    AND `id_shop` = '.(int)$this->context->shop->id;
            Db::getInstance()->execute($sql);
        }
    }

    /**
     * @return array
     */
	public function getOptionsByField()
    {
        $id_field = Tools::getValue('id_field');
        $options  = FieldOptionClass::getOptionsByIdField($id_field);
        return array('message_code' => 0, 'options' => $options);
    }

    /**
     *
     */
	public function checkAddressOrder()
    {
        $query = new DbQuery();
        $query->from('orders');
        $query->where('id_address_delivery = '.(int)$this->context->cart->id_address_delivery);
        $query->where('id_customer != '.(int)$this->context->cart->id_customer);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
        if ($result) {
            $id_address_delivery = $this->getIdAddressAvailable();
            $this->updateAddressCart(null, $this->context->cart->id_address_delivery, $id_address_delivery);
        }
    }

    /**
     * @param $dni
     * @param $id_country
     * @return bool
     */
	public function checkDni($dni, $id_country)
    {
        $iso_country = Country::getIsoById($id_country);
		return Validate::isDniLite($dni) ? true : false;
    }

    /**
     * @param $fields
     * @param $address
     */
	private function validateFieldsAddress(&$fields, &$address)
    {
        foreach ($fields as $name => $field) {
            if ($field['type'] == 'url') {
                $field['type']  = 'isUrl';

                if (Tools::substr($field['value'], 0, 4) != 'http') {
                    $field['value'] = 'http://'.$field['value'];
                }
            } elseif ($field['type']  == 'number') {
                $field['type']  = 'isInt';
            } elseif ($field['type']  == 'isDate' || $field['type']  == 'isBirthDate') {
                $field['value'] = date('Y-m-d', strtotime(str_replace('/', '-', $field['value'])));
            }

            $valid = call_user_func(array('Validate', $field['type'] ), $field['value']);
            if ($field['required'] == 1 && empty($field['value'])) {
                if ($field['name'] != 'id_state') {
                    $this->errors[] = sprintf(
                        $this->l('The field %s is required.'),
                        ObjectModel::displayFieldName(
                            $name,
                            get_class($address),
                            true
                        )
                    );
                }
            } elseif (!empty($field['value']) && !$valid) {
                $this->errors[] = sprintf(
                    $this->l('The field %s is invalid.'),
                    ObjectModel::displayFieldName(
                        $name,
                        get_class($address),
                        true
                    )
                );
            }

            if ($field['active'] == 0 && !empty($address->{$name})) {
                continue;
            }

            $address->{$name} = $field['value'];
        }

        if (!count($this->errors)) {
            if ($address->id_country) {
                if (!($country = new Country($address->id_country)) || !Validate::isLoadedObject($country)) {
                    $this->errors[] = $this->l('Country cannot be loaded.');
                }
                if ((int) $country->contains_states) {
                    if (!(int) $address->id_state) {
                        $this->errors[] = $this->l('This country requires you to chose a State.');
                    } else {
                        $state = new State((int)$address->id_state);
                        if (Validate::isLoadedObject($state) && $state->id_country != $country->id) {
                            $this->errors[] = $this->l('The selected state does not correspond to the country.');
                        }
                    }
                } else {
                    $address->id_state = null;
                }

                if (!$country->active) {
                    $this->errors[] = $this->l('This country is not active.');
                }
                if ($country->zip_code_format && !$country->checkZipCode($address->postcode)) {
                    if (!empty($address->postcode)) {
                        $this->errors[] = sprintf(
                            $this->l('The Zip/Postal code you\'ve entered is invalid. It must follow this format: %s'),
                            str_replace(
                                'C',
                                $country->iso_code,
                                str_replace(
                                    'N',
                                    '0',
                                    str_replace(
                                        'L',
                                        'A',
                                        $country->zip_code_format
                                    )
                                )
                            )
                        );
                    } else {
                        $address->postcode = str_replace(
                            'C',
                            $country->iso_code,
                            str_replace(
                                'N',
                                '0',
                                str_replace(
                                    'L',
                                    'A',
                                    $country->zip_code_format
                                )
                            )
                        );
                    }
                } elseif (empty($address->postcode) && $country->need_zip_code) {
                    $address->postcode = str_replace(
                        'C',
                        $country->iso_code,
                        str_replace(
                            'N',
                            '0',
                            str_replace(
                                'L',
                                'A',
                                $country->zip_code_format
                            )
                        )
                    );
                }
                if (!empty($address->dni)) {
                    if ($country->isNeedDni()
                        && (!$address->dni)
                        || !$this->checkDni($address->dni, $address->id_country)
                    ) {
                        $this->errors[] = $this->l('The field identification number is invalid.');
                    }
                } elseif (!$country->isNeedDni()) {
                    $address->dni = null;
                }
            }

            if (!Validate::isDate($address->date_add)) {
                $address->date_add = date('Y-m-d H:i:s');
            }
            if (!Validate::isDate($address->date_upd)) {
                $address->date_upd = $address->date_add;
            }
        }
    }

    /**
     * @param $fields
     * @param $customer
     * @param $password
     */
	private function validateFieldsCustomer(&$fields, &$customer, &$password)
    {
		
        foreach ($fields as $name => $field) {
            if ($field['type'] == 'url') {
                $field['type'] = 'isUrl';

                if (!empty($field['value']) && Tools::substr($field['value'], 0, 4) != 'http') {
                    $field['value'] = 'http://'.$field['value'];
                }
            } elseif ($field['type'] == 'number') {
                $field['type'] = 'isInt';
            } elseif ($field['type'] == 'isDate' || $field['type'] == 'isBirthDate') {
                if (!empty($field['value'])) {
                    $field['value'] = date('Y-m-d', strtotime(str_replace('/', '-', $field['value'])));
                }
            }

            if ($name == 'passwd') {
                if ($this->context->customer->isLogged()) {
                    continue;
                } else {
                    $password = $field['value'];
                    if ($field['active'] != '1'
                        || ($field['active'] == '1'
                            && empty($field['value']))
                        || (Configuration::get('PS_GUEST_CHECKOUT_ENABLED')
                        && Tools::getValue('is_new_customer') == 1)
                    ) {
                        $password = Tools::passwdGen();
                    }

                    $field['value'] = Tools::encrypt($password);
                }
            } elseif ($name == 'email') {
                if (empty($field['value'])) {
                    $field['value'] = date('His').'@auto-generated.sp';
                }

                if (!$this->context->customer->isLogged()
                    && Customer::customerExists($field['value'])
                    && !Configuration::get('PS_GUEST_CHECKOUT_ENABLED')
                    && Tools::getValue('is_new_customer') == 1
                ) {
                    $this->errors[] = $this->l('An account using this email address has already been registered.');
                }
            }

            $valid = call_user_func(array('Validate', $field['type']), $field['value']);

            if ($field['required'] == 1 && empty($field['value'])) {
                $this->errors[] = sprintf(
                    $this->l('The field %s is required.'),
                    ObjectModel::displayFieldName(
                        $name,
                        get_class($customer),
                        true
                    )
                );
            } elseif (!empty($field['value']) && !$valid) {
                $this->errors[] = sprintf(
                    $this->l('The field %s is invalid.'),
                    ObjectModel::displayFieldName(
                        $name,
                        get_class($customer),
                        true
                    )
                );
            }

            if ($field['active'] == 0 && !empty($customer->{$name})) {
                continue;
            }

            $customer->{$name} = $field['value'];
        }
    }

    /**
     * @param $fields
     * @param $customer
     * @param $address_delivery
     * @param $address_invoice
     * @param $password
     * @param $is_set_invoice
     */
	public function validateFields($fields, &$customer, &$address_delivery, &$address_invoice, &$password, &$is_set_invoice)
    {
        $fields_by_object = array();
        foreach ($fields as $field) {
            if ($field->name == 'id') {
                continue;
            }

            $field_db = $this->getField(
                $field->object,
                $field->name
            );

            if ($field_db) {
                $field_db['value']   = $field->value;
                $fields_by_object[$field->object][$field->name] = $field_db;
            }
        }

        foreach ($fields_by_object as $name_object => $fields) {
            if ($name_object == 'customer') {
                if (empty($customer)) {
                    $customer = new Customer();
                }

                $this->addFieldsRequired($fields, $name_object, $customer);
                $this->validateFieldsCustomer($fields, $customer, $password);
            } elseif ($name_object == 'delivery') {
                if (empty($address_delivery)) {
                    $address_delivery = new Address();
                }

                $this->addFieldsRequired($fields, $name_object, $address_delivery);
                $this->validateFieldsAddress($fields, $address_delivery);
            } elseif ($name_object == 'invoice') {
                if (empty($address_invoice)) {
                    $address_invoice = new Address();
                }

                $this->addFieldsRequired($fields, $name_object, $address_invoice);
                $this->validateFieldsAddress($fields, $address_invoice);

                $is_set_invoice = true;
            }
        }
    }

    /**
     * @param $fields
     * @param $name_object
     * @param $object
     */
	private function addFieldsRequired(&$fields, $name_object, $object)
    {
        $fields_tmp = array();

        $fields_db_required = $object->getFieldsRequiredDatabase();
        $fields_object      = ObjectModel::getDefinition($object);

        foreach ($fields_db_required as $field) {
            array_push($fields_tmp, $field['field_name']);
        }

        foreach ($fields_object['fields'] as $name_field => $field) {
            if (isset($field['required']) && $field['required'] == 1) {
                array_push($fields_tmp, $name_field);
            }
        }

        array_push($fields_tmp, 'id_country');
        array_push($fields_tmp, 'id_state');

        $fields_db = $this->getAllFields(
            $name_object,
            $fields_tmp,
			true
        );
		foreach ($fields_db as $field) {
				if (!isset($fields[$field['name']]) || (isset($fields[$field['name']]) && empty($fields[$field['name']]['value']))) {
					if ($field['name'] == 'alias') {
						$field['value'] = $field['default_value'].' #'.date('s');
					} else {
						$field['value'] = $field['default_value'];
					}

					$fields[$field['name']] = $field;
				}
				$fields[$field['name']]['required'] = 1;
		}
    }

    /**
     * @param $order_controller
     * @return array
     */
	public function placeOrder($order_controller)
    {
        $password       = '';
        $is_set_invoice = false;
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        if (Tools::getIsset('fields_sp')) {
            $fields                          = Tools::jsonDecode(Tools::getValue('fields_sp'));
            $id_customer                     = Tools::getValue('id_customer', null);
            $id_address_delivery             = Tools::getValue('id_address_delivery', null);
            $id_address_invoice              = Tools::getValue('id_address_invoice', null);
            $checkbox_create_invoice_address = Tools::getValue('checkbox_create_invoice_address', null);

            if ($this->context->customer->isLogged()) {
                if (empty($id_customer)) {
                    $id_customer = $this->context->cart->id_customer;

                    if (empty($id_address_delivery)) {
                        $id_address_delivery = $this->context->cart->id_address_delivery;
                    }
                    if (empty($id_address_invoice)) {
                        $id_address_invoice = $this->context->cart->id_address_invoice;
                    }
                } else {
                    if (empty($id_address_delivery) &&
                        (!$this->context->cart->isVirtualCart())) {
                        $id_address_delivery = $this->createAddress();
                    }
                    if (empty($id_address_invoice)
                        && (!empty($checkbox_create_invoice_address)
                            || ($this->configVars['SPSCO_ENABLE_INVOICE_ADDRESS']
                                && $this->configVars['SPSCO_REQUIRED_INVOICE_ADDRESS']))
                    ) {
                        $id_address_invoice = $this->createAddress('invoice');
                    }
                }
            } elseif (empty($id_address_delivery) && !empty($this->context->cart->id_address_delivery)) {
                $this->checkAddressOrder();
                
                $id_address_delivery = $this->context->cart->id_address_delivery;
            }

            $customer         = new Customer((int) $id_customer);
            $address_delivery = new Address((int) $id_address_delivery);
            $address_invoice  = new Address((int) $id_address_invoice);

            $this->validateFields($fields, $customer, $address_delivery, $address_invoice, $password, $is_set_invoice);
            $delivery_option = array($address_delivery->id => $this->context->cart->id_carrier.',');
            $this->context->cart->setDeliveryOption($delivery_option);
            $this->context->cart->update();
            $minimal_purchase = $this->checkMinimalPurchase();
            if (!empty($minimal_purchase)) {
                $this->errors[] = $minimal_purchase;
            }
			
			

            foreach ($this->context->cart->getProducts() as $product) {
                $show_message_stock = true;

                if ($show_message_stock
                    && (!$product['active']
                        || !$product['available_for_order']
                        || (!$product['allow_oosp'] && $product['stock_quantity'] < $product['cart_quantity']))
                ) {
                    $this->errors[] = sprintf(
                        $this->l('The product "%s" is not available or does not have stock.'),
                        $product['name']
                    );
                }
            }

            if (!count($this->errors)) {
				if (empty($customer->firstname)){
					$customer->firstname = $this->l('First Name Empty');
				}
				if (empty($customer->lastname)){
					$customer->lastname = $this->l('Last Name Empty');
				}
				if (empty($customer->passwd)){
					$customer->passwd = Tools::encrypt('SPCO123456');
				}
                if ($this->configVars['SPSCO_USE_SAME_NAME_CONTACT_DA']) {
                    $address_delivery->firstname = $customer->firstname;
                    $address_delivery->lastname  = $customer->lastname;
                }

                if ($this->configVars['SPSCO_USE_SAME_NAME_CONTACT_BA']) {
                    $address_invoice->firstname = $customer->firstname;
                    $address_invoice->lastname  = $customer->lastname;
                }

                if (!$this->context->cart->isVirtualCart()) {
                    Hook::exec('actionCarrierProcess', array('cart' => $this->context->cart));
                }
                if (!$this->context->customer->isLogged() && !$this->context->customer->isGuest()) {
                    $this->createCustomer($customer, $address_delivery, $address_invoice, $password, $is_set_invoice);

                    if (!count($this->errors)) {
                        Hook::exec('actionACSPSaveInformation', array(
                            'id_cart' => $this->context->cart->id,
                            'id_customer' => $customer->id
                        ));
                        
                        if ($customer->id == $this->configVars['SPSCO_ID_CUSTOMER']) {
                            $this->errors[] = $this->l('Problem occurred when processing your order, please contact us.');
                        }
                        CartRule::autoRemoveFromCart();
                        CartRule::autoAddToCart();

                        if (Tools::getIsset('message')) {
                            $checkout_session = $order_controller->getCheckoutSession();

                            if (method_exists($checkout_session, 'setMessage')) {
                                $checkout_session->setMessage(Tools::getValue('message'));
                            }
                        }

                        return array(
                            'hasError'            => !empty($this->errors),
                            'errors'              => $this->errors,
                            'isSaved'             => true,
                            'isGuest'             => $customer->is_guest,
                            'id_customer'         => (int) $customer->id,
                            'secure_key'          => $this->context->cart->secure_key,
                            'id_address_delivery' => $this->context->cart->id_address_delivery,
                            'id_address_invoice'  => $this->context->cart->id_address_invoice,
                            'token'               => Tools::getToken(false),
                        );
                    }
                } else {
                    if ($customer->update()) {
                        $this->context->cookie->customer_lastname  = $customer->lastname;
                        $this->context->cookie->customer_firstname = $customer->firstname;

                        if ((int) $customer->newsletter == 1) {
                            Db::getInstance(_PS_USE_SQL_SLAVE_)->update(
                                'customer',
                                array('newsletter' => 1),
                                'id_customer = '.(int)$customer->id
                            );
                        }

                        if ((int) $customer->optin == 1) {
                            Db::getInstance(_PS_USE_SQL_SLAVE_)->update(
                                'customer',
                                array('optin' => 1),
                                'id_customer = '.(int)$customer->id
                            );
                        }
                    } else {
                        $this->errors[] = $this->l('An error occurred while creating your account.');
                    }

                    if (!$this->context->cart->isVirtualCart()) {
                        if (empty($address_delivery->id_customer)) {
                            $address_delivery->id_customer = $customer->id;
                        }

                        if (!$address_delivery->save()) {
                            $this->errors[] = $this->l('An error occurred while updating your delivery address.');
                        }

                        if ($is_set_invoice && $address_delivery->id == $address_invoice->id) {
                            if (!$this->isSameAddress($address_delivery, $address_invoice)) {
                                $address_invoice->id = null;
                                $address_invoice->alias .= ' 2';
                            }
                        }
                    }

                    if ($is_set_invoice && empty($address_invoice->id_customer)) {
                        $address_invoice->id_customer = $customer->id;
                    }

                    if ($is_set_invoice && !$address_invoice->save()) {
                        $this->errors[] = $this->l('An error occurred while creating your delivery address.');
                    }

                    if (!count($this->errors)) {
                        if (!Validate::isLoadedObject($address_delivery)  && $this->context->cart->isVirtualCart()) {
                            $address_delivery = $address_invoice;
                        }
                        
                        if (!$is_set_invoice) {
                            $this->updateAddressCart(null, $this->context->cart->id_address_delivery, $address_delivery->id);
                        } else {
                            $this->updateAddressCart('delivery', $this->context->cart->id_address_delivery, $address_delivery->id);
                            $this->updateAddressCart('invoice', $this->context->cart->id_address_invoice, $address_invoice->id);
                        }

                        if (Tools::getIsset('message')) {
                            $checkout_session = $order_controller->getCheckoutSession();

                            if (method_exists($checkout_session, 'setMessage')) {
                                $checkout_session->setMessage(Tools::getValue('message'));
                            }
                        }
                    }
                }
            }

            return array(
                'hasError'            => !empty($this->errors),
                'hasWarning'          => !empty($this->warnings),
                'errors'              => $this->errors,
                'warnings'            => $this->warnings,
                'secure_key'          => $this->context->cart->secure_key,
                'id_address_delivery' => $this->context->cart->id_address_delivery,
                'id_address_invoice'  => $this->context->cart->id_address_invoice
            );
        }
    }

    /**
     * @return array
     */
    public function deleteEmptyAddressesSP()
    {
        $query = 'DELETE FROM '._DB_PREFIX_.'address WHERE id_customer = '.(int)$this->configVars['SPSCO_ID_CUSTOMER'];
        Db::getInstance()->execute($query);

        $query = new DbQuery();
        $query->select('*');
        $query->from('cart');
        $query->where('id_cart NOT IN (SELECT id_cart FROM '._DB_PREFIX_.'orders)');

        $carts = Db::getInstance()->executeS($query);

        if (count($carts) > 0) {
            foreach ($carts as $cart) {
                $query = 'SELECT * FROM '._DB_PREFIX_.'address WHERE id_address = '.(int)$cart['id_address_delivery'];
                $result = Db::getInstance()->executeS($query);

                if ((int)$cart['id_customer'] == (int)$this->configVars['SPSCO_ID_CUSTOMER'] || !$result) {
                    Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'cart WHERE id_cart = '.(int) $cart['id_cart']);
                    Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'cart_product WHERE id_cart = '.(int) $cart['id_cart']);
                    Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'cart_cart_rule WHERE id_cart = '.(int) $cart['id_cart']);
                }
            }
        }

        return array(
            'message_code' => 0,
            'message' => $this->l('Created temporary addresses were deleted successfully.')
        );
    }

    /**
     * @param $object
     * @param $id_address
     * @param $id_address_new
     */
	public function updateAddressCart($object, $id_address, $id_address_new)
    {
        if ($object == 'invoice' || is_null($object)) {
            $this->context->cart->id_address_invoice = $id_address_new;
        }

        if ($object == 'delivery' || is_null($object)) {
            $this->context->cart->id_address_delivery = $id_address_new;

            $sql = 'UPDATE `'._DB_PREFIX_.'cart_product`
            SET `id_address_delivery` = '.(int)$id_address_new.'
            WHERE  `id_cart` = '.(int)$this->context->cart->id.'
                AND `id_address_delivery` = '.(int)$id_address;
            Db::getInstance()->execute($sql);

            $sql = 'UPDATE `'._DB_PREFIX_.'customization`
                SET `id_address_delivery` = '.(int)$id_address_new.'
                WHERE  `id_cart` = '.(int)$this->context->cart->id.'
                    AND `id_address_delivery` = '.(int)$id_address;
            Db::getInstance()->execute($sql);

            if (!empty($this->context->cart->id_carrier)) {
                $delivery_option = array($id_address_new => $this->context->cart->id_carrier.',');
                //$this->context->cart->delivery_option = serialize($delivery_option);
				 $this->context->cart->setDeliveryOption($delivery_option);
                $this->context->cart->update();
            }
        }

        $this->context->cart->update();
    }

    /**
     * @param $order_controller
     * @return string
     */
	public function loadCarrier($order_controller)
    {
        $set_id_customer_sp = false;

        $id_country          = Tools::getValue('id_country');
        $id_state            = Tools::getValue('id_state');
        $postcode            = Tools::getValue('postcode');
        $city                = Tools::getValue('city');
        $id_address_delivery = (int)Tools::getValue('id_address_delivery');
        $id_address_invoice  = (int)Tools::getValue('id_address_invoice');

        if (empty($id_country)) {
            $id_country = (int) $this->getDefaultValue('delivery', 'id_country');
        }

        $is_same_address = false;
        if ($this->context->cart->id_address_delivery == $this->context->cart->id_address_invoice) {
            $is_same_address = true;
        }

        $this->checkAddressExist($id_address_delivery, $id_address_invoice);

        if (empty($id_address_delivery)) {
            $id_address_delivery = $this->context->cart->id_address_delivery;

            if (empty($id_address_delivery) && !$this->context->customer->isLogged()) {
                $id_address_delivery = $this->getIdAddressAvailable('delivery');

                $this->updateAddressCart('delivery', $this->context->cart->id_address_delivery, $id_address_delivery);
            }
        }
        if (empty($id_address_invoice)) {
            $id_address_invoice = $this->context->cart->id_address_invoice;

            if (empty($id_address_invoice) && !$this->context->customer->isLogged() && !$is_same_address) {
                $id_address_invoice = $this->getIdAddressAvailable('invoice');

                $this->updateAddressCart('invoice', $this->context->cart->id_address_invoice, $id_address_invoice);
            } else {
                $id_address_invoice = $this->context->cart->id_address_delivery;
            }
        }

        $this->checkAddressOrder();

        if (!$this->context->cart->isVirtualCart()) {
            if (!empty($id_country)) {
                $delivery_address = new Address($id_address_delivery);
                $delivery_address->deleted = 0;

                if (empty($id_state)) {
                    if (empty($delivery_address->id_state)) {
                        $id_state = (int) $this->getDefaultValue('delivery', 'id_state');
                    } else {
                        $id_state = $delivery_address->id_state;
                    }
                }

                $country = new Country($id_country);
                if ($country->contains_states && empty($id_state)) {
                    $delivery_address->id_state = null;
                    $delivery_address->save();

                    $this->errors[] = $this->l('Select a state to show the different shipping options.');
                } else {
                    if (!$country->contains_states && !empty($id_state)) {
                        $id_state = null;
                    }

                    $delivery_address->id_country = $id_country;
                    $delivery_address->id_state   = $id_state;

                    if (empty($delivery_address->firstname)) {
                        $delivery_address->firstname = $this->getDefaultValue('delivery', 'firstname');
                    }
                    if (empty($delivery_address->lastname)) {
                        $delivery_address->lastname = $this->getDefaultValue('delivery', 'lastname');
                    }

                    if (Tools::getIsset('postcode')) {
                        if (empty($postcode)) {
                            if (empty($this->context->customer->id) && empty($postcode)) {
                                $delivery_address->postcode = $postcode;
                            }
                        } else {
                            $delivery_address->postcode = $postcode;
                        }
                    }

                    if (!empty($city)) {
                        $delivery_address->city = $city;
                    }

                    $fields = array();

                    if (!$this->checkDni($delivery_address->dni, $delivery_address->id_country)) {
                        $delivery_address->dni = '';
                    }

                    $this->validateFieldsAddress($fields, $delivery_address);

                    if (!count($this->errors)) {
                        if (!$delivery_address->save()) {
                            $this->errors[] = $this->l('An error occurred while updating your delivery address.');
                        }

                        if (Validate::isLoadedObject($delivery_address)) {
                            if (empty($this->context->cookie->id_customer)) {
                                $module_exception = false;

                                if (!$module_exception) {
                                    $this->context->cookie->id_customer = $this->configVars['SPSCO_ID_CUSTOMER'];

                                    if (empty($this->context->customer->id)) {
                                        $this->context->customer = new Customer($this->configVars['SPSCO_ID_CUSTOMER']);
                                        $this->context->customer->logged = 1;
                                    }

                                    if (empty($this->context->cart->id_customer)) {
                                        $this->context->cart->id_customer = $this->configVars['SPSCO_ID_CUSTOMER'];
                                    }

                                    $set_id_customer_sp = true;
                                }
                            }

                            $this->context->cart->id_address_delivery = $delivery_address->id;
                            if (empty($this->context->cart->id_address_invoice)) {
                                $this->context->cart->id_address_invoice  = $delivery_address->id;
                            }
                            $this->context->cart->update();

                            if ($this->context->customer->isLogged()
                                && $this->context->customer->id != $this->configVars['SPSCO_ID_CUSTOMER']
                            ) {
                                CartRule::autoRemoveFromCart($this->context);
                            }
                            CartRule::autoAddToCart($this->context);

                            $this->context->country->id_zone = Address::getZoneById((int) $delivery_address->id);

                            if (!Address::isCountryActiveById((int) $delivery_address->id)) {
                                $this->errors[] = $this->l('This address is not in a valid area.');
                            }

                            if (!$this->context->cart->isMultiAddressDelivery()) {
                                $this->context->cart->setNoMultishipping();
                            }
                        } else {
                            $this->l('This address is invalid. Sign out of session and login again.');
                        }
                    }
                    
                    if (!count($this->errors)) {
                        $delivery_option = $order_controller->getCheckoutSession()->getSelectedDeliveryOption();
                        $delivery_options = $order_controller->getCheckoutSession()->getDeliveryOptions();

                        if (!count($delivery_options)) {
                            $this->errors[] = $this->l('There are no shipping methods available for your address.');
                        }

                        $is_necessary_postcode = false;
                        $is_necessary_city     = false;

                        $delivery_options_tmp = array();
                        foreach ($delivery_options as $id_carrier => $carrier) {
                            $delivery_options_tmp[$id_carrier] = $carrier;
                        }

                        $delivery_options = $delivery_options_tmp;

                        if (!$is_necessary_postcode) {
                            if ($this->configVars['SPSCO_FORCE_NEED_POSTCODE']) {
                                $is_necessary_postcode = true;
                            }
                        }

                        if (!$is_necessary_city) {
                            if ($this->configVars['SPSCO_FORCE_NEED_CITY']) {
                                $is_necessary_city = true;
                            } 
                        }

                        if (empty($city) && $is_necessary_city) {
                            $this->errors = $this->l('You need to place a city to show shipping options.');
                        }
                        
                        if (empty($postcode) && $is_necessary_postcode) {
                            $this->errors = $this->l('You need to place a post code to show shipping options.');
                        }

                        $this->context->smarty->assign(array(
                            'id_address' => $order_controller->getCheckoutSession()->getIdAddressDelivery(),
                            'delivery_options' => $delivery_options,
                            'delivery_option' => $delivery_option,
                            'is_necessary_postcode' => $is_necessary_postcode,
                            'is_necessary_city' => $is_necessary_city,
                        ));
                    }
                }
            } else {
                $this->errors[] = $this->l('Select a country to show the different shipping options.');
            }
        }
		  $templateVars = array(
            'PATH_IMG' => __PS_BASE_URI__.'modules/'.$this->name.'/views/img/',
            'CONFIGS' => $this->configVars,
            'is_virtual_cart' => (int)$order_controller->getCheckoutSession()->getCart()->isVirtualCart(),
            'hasError' => !empty($this->errors),
            'errors' => $this->errors,
            'hookDisplayBeforeCarrier' => Hook::exec('displayBeforeCarrier', array('cart' => $order_controller->getCheckoutSession()->getCart())),
            'hookDisplayAfterCarrier' => Hook::exec('displayAfterCarrier', array('cart' => $order_controller->getCheckoutSession()->getCart())),
            'recyclable' => $order_controller->getCheckoutSession()->isRecyclable(),
            'recyclablePackAllowed' => $order_controller->checkoutDeliveryStep->isRecyclablePackAllowed(),
            'gift' => array(
                'allowed' => $order_controller->checkoutDeliveryStep->isGiftAllowed(),
                'isGift' => $order_controller->getCheckoutSession()->getGift()['isGift'],
                'label' => $this->l('I would like my order to be gift wrapped').$order_controller->checkoutDeliveryStep->getGiftCostForLabel(),
                'message' => $order_controller->getCheckoutSession()->getGift()['message'],
            ),
        );
        $this->context->smarty->assign($templateVars);

        if ($set_id_customer_sp) {
            $this->context->customer         = new Customer();
            $this->context->customer->logged = 0;
            unset($this->context->cookie->id_customer);

            $this->context->cart->id_customer = null;
            $this->context->cart->update();
        }

        $html = $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/front/carrier.tpl');

        return $html;
    }

    /**
     *
     */
	private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmitGeneral')) {
			foreach ($this->configArr as $config) {
				Configuration::updateValue($config['name'], Tools::getValue($config['name']));
			}
            $this->context->smarty->assign('configuration_settings_saved', $this->l('Settings updated successful'));
        }
    }

    /**
     * @return string
     */
    public function getContent()
    {
		if (Tools::isSubmit('btnSubmitGeneral')) {
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        }
        $protocol = Tools::getShopDomainSsl(true, true);
        $spco_js = $protocol . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/js/admin/spco_js.js';
        $spco_css = $protocol . __PS_BASE_URI__ . 'modules/' . $this->name . '/views/css/admin/spco_css.css';

        if (!Tools::getValue('tab')) {
            $active_tab = 'general_setting';
        } else {
            $active_tab = Tools::getValue('spsco_tab');
        }

        $template_vars = array(
			'url_ajax' => $this->context->link->getAdminLink('AdminSPStepCheckout'),
            'tabs' => $this->getConfigTabs(),
			'content' => $this->getConfigContent(),
            'active_tab' => 'general_setting',
            'spco_js' => $spco_js,
            'spco_css' => $spco_css
        );

        $this->context->smarty->assign($template_vars);
        return $this->display(__FILE__, 'views/templates/admin/tabs.tpl');
    }

    /**
     * @return array
     */
	protected function getConfigTabs()
    {
        $tabs = array();
        $tabs[] = array(
            'id' => 'general_setting',
            'title' => $this->l('General Setting'),
			'data_tabs' => 'general_setting',
			'icon' => 'icon-cog',
        );

        $tabs[] = array(
            'id' => 'login_register',
            'title' => $this->l('Fields Register'),
            'data_tabs' => 'login_register_1',
			'icon' => 'icon-user',
        );
		
		$tabs[] = array(
            'id' => 'setting_address',
            'title' => $this->l('Fields Address'),
			'data_tabs' => 'setting_address_5',
			'icon' => 'icon-fax',
        );
		
		$tabs[] = array(
            'id' => 'setting_invoice',
            'title' => $this->l('Fields Invoice'),
			'data_tabs' => 'setting_invoice_6',
			'icon' => 'icon-building',
        );

        $tabs[] = array(
            'id' => 'setting_shipping',
            'title' => $this->l('Shipping'),
            'data_tabs' => 'setting_shipping_2',
			'icon' => 'icon-truck ',
        );


        $tabs[] = array(
            'id' => 'setting_payment',
            'title' => $this->l('Payment'),
            'data_tabs' => 'setting_payment_3',
			'icon' => 'icon-credit-card',
        );
		
		$tabs[] = array(
            'id' => 'setting_review',
            'title' => $this->l('Review'),
			'data_tabs' => 'setting_review_4',
			'icon' => 'icon-shopping-cart',
        );
		
        return $tabs;
    }

    /**
     * @return array
     */
	public function getConfigFieldsValues()
    {
		return array(
			'SPSCO_FIELDS_SETUP' => Tools::getValue('SPSCO_FIELDS_SETUP', Configuration::get('SPSCO_FIELDS_SETUP')),
			'SPSCO_ID_CUSTOMER' => Tools::getValue('SPSCO_ID_CUSTOMER', Configuration::get('SPSCO_ID_CUSTOMER')),
            'PS_GUEST_CHECKOUT_ENABLED' => Tools::getValue('PS_GUEST_CHECKOUT_ENABLED', Configuration::get('PS_GUEST_CHECKOUT_ENABLED')),
			'SPSCO_DEFAULT_GROUP_CUSTOMER' => Tools::getValue('SPSCO_DEFAULT_GROUP_CUSTOMER', Configuration::get('SPSCO_DEFAULT_GROUP_CUSTOMER')),
            'SPSCO_REDIRECT_DIRECTLY_TO_SP' => Tools::getValue('SPSCO_REDIRECT_DIRECTLY_TO_SP', Configuration::get('SPSCO_REDIRECT_DIRECTLY_TO_SP')),
			'PS_CONDITIONS' => Tools::getValue('PS_CONDITIONS', Configuration::get('PS_CONDITIONS')),
			'PS_CONDITIONS_CMS_ID' => Tools::getValue('PS_CONDITIONS_CMS_ID', Configuration::get('PS_CONDITIONS_CMS_ID')),
			'SPSCO_SHOW_LINK_CONTINUE_SHOPPING' => Tools::getValue('SPSCO_SHOW_LINK_CONTINUE_SHOPPING', Configuration::get('SPSCO_SHOW_LINK_CONTINUE_SHOPPING')),
			'SPSCO_LINK_CONTINUE_SHOPPING' => Tools::getValue('SPSCO_LINK_CONTINUE_SHOPPING', Configuration::get('SPSCO_LINK_CONTINUE_SHOPPING')),
			'SPSCO_ENABLE_HOOK_SHOPPING_CART'  => Tools::getValue('SPSCO_ENABLE_HOOK_SHOPPING_CART', Configuration::get('SPSCO_ENABLE_HOOK_SHOPPING_CART')),
			'SPSCO_ENABLE_INVOICE_ADDRESS' => Tools::getValue('SPSCO_ENABLE_INVOICE_ADDRESS', Configuration::get('SPSCO_ENABLE_INVOICE_ADDRESS')),
			'SPSCO_REQUIRED_INVOICE_ADDRESS' => Tools::getValue('SPSCO_REQUIRED_INVOICE_ADDRESS', Configuration::get('SPSCO_REQUIRED_INVOICE_ADDRESS')),
			'SPSCO_USE_SAME_NAME_CONTACT_BA' => Tools::getValue('SPSCO_USE_SAME_NAME_CONTACT_BA', Configuration::get('SPSCO_USE_SAME_NAME_CONTACT_BA')),
			'SPSCO_USE_SAME_NAME_CONTACT_DA' => Tools::getValue('SPSCO_USE_SAME_NAME_CONTACT_DA', Configuration::get('SPSCO_USE_SAME_NAME_CONTACT_DA')),
			'SPSCO_REQUEST_CONFIRM_EMAIL' => Tools::getValue('SPSCO_REQUEST_CONFIRM_EMAIL', Configuration::get('SPSCO_REQUEST_CONFIRM_EMAIL')),
			'SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS' => Tools::getValue('SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS', Configuration::get('SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS')),
			'SPSCO_GOOGLE_API_KEY' => Tools::getValue('SPSCO_GOOGLE_API_KEY', Configuration::get('SPSCO_GOOGLE_API_KEY')),
            'SPSCO_SHOW_DESCRIPTION_CARRIER' => Tools::getValue('SPSCO_SHOW_DESCRIPTION_CARRIER', Configuration::get('SPSCO_SHOW_DESCRIPTION_CARRIER')),
			'SPSCO_SHOW_IMAGE_CARRIER' => Tools::getValue('SPSCO_SHOW_IMAGE_CARRIER', Configuration::get('SPSCO_SHOW_IMAGE_CARRIER')),
			'SPSCO_RELOAD_SHIPPING_BY_STATE' => Tools::getValue('SPSCO_RELOAD_SHIPPING_BY_STATE', Configuration::get('SPSCO_RELOAD_SHIPPING_BY_STATE')),
			'SPSCO_FORCE_NEED_POSTCODE' => Tools::getValue('SPSCO_FORCE_NEED_POSTCODE', Configuration::get('SPSCO_FORCE_NEED_POSTCODE')),
			'SPSCO_FORCE_NEED_CITY' => Tools::getValue('SPSCO_FORCE_NEED_CITY', Configuration::get('SPSCO_FORCE_NEED_CITY')),
			'SPSCO_DEFAULT_PAYMENT_METHOD' => Tools::getValue('SPSCO_DEFAULT_PAYMENT_METHOD', Configuration::get('SPSCO_DEFAULT_PAYMENT_METHOD')),
            'SPSCO_SHOW_IMAGE_PAYMENT' => Tools::getValue('SPSCO_SHOW_IMAGE_PAYMENT', Configuration::get('SPSCO_SHOW_IMAGE_PAYMENT')),
			'SPSCO_SHOW_DETAIL_PAYMENT' => Tools::getValue('SPSCO_SHOW_DETAIL_PAYMENT', Configuration::get('SPSCO_SHOW_DETAIL_PAYMENT')),
        );
    }

    /**
     * @return string
     */
	protected function getConfigContent(){
		$field_values = $this->getConfigFieldsValues();
		$payment_methods = array(array('id_module' => '', 'name' => '--'));
        $payment_methods_ori = PaymentModule::getInstalledPaymentModules();
        foreach ($payment_methods_ori as $payment) {
            $payment_methods[] = $payment;
        }
		
		$groups  = Group::getGroups($this->context->cookie->id_lang);
		$other_groups = [];
		$id_group = [];
		foreach($groups as $gr){
			if ($field_values['SPSCO_DEFAULT_GROUP_CUSTOMER'] && $field_values['SPSCO_DEFAULT_GROUP_CUSTOMER'] !== $gr['id_group']){
				$other_groups[] = $gr;
			}
		}
        $fields_form["general_setting"] = array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Guest Checkout'),
                        'hint' => $this->l('Allow guest visitors to place an order without registering.'),
                        'name' => 'PS_GUEST_CHECKOUT_ENABLED',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
						'type' => 'hidden',
						'name' => 'SPSCO_ID_CUSTOMER',
					),
					array(
						'type'    => 'select',
						'label'   => $this->l('Add New Customers To The Group'),
						'name'    => 'SPSCO_DEFAULT_GROUP_CUSTOMER',
						'hint'    => $this->l('When a customer registers in your store from the checkout page, this should be recorded in a group of customers. In this option you can set which group the new customer will be added to by default. '),
						'options' => array(
							'query' => Group::getGroups($this->context->cookie->id_lang),
							'id'    => 'id_group',
							'name'  => 'name'
						)
					),
                ),
                'submit' => array(
					'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
				),
            ),
        );
        $fields_form['login_register'] = array(
            'form' => array(
                'input' => array(
					array(
                        'type' => 'switch',
                        'label' => $this->l('Confirmation Email'),
						'hint' => $this->l('Request confirmation email'),
                        'name' => 'SPSCO_REQUEST_CONFIRM_EMAIL',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					
					array(
                        'type' => 'switch',
                        'label' => $this->l('Autocomplete from Google'),
						'hint' => $this->l('Use address autocomplete from Google.'),
                        'name' => 'SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
                        'type' => 'text',
						'class' => ' fixed-width-xxl',
                        'label' => $this->l('Google API Key'),
						'form_group_class' => "google-address ".(Tools::getValue('SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS') ? '': 'hide')."",
                        'name' => 'SPSCO_GOOGLE_API_KEY',
                        'required' => false,
                    ),
					array(
						'type' => 'html',
						'label' => $this->l('Config Fields for Customer'),
						'name' => 'fields_customer',
						'html_content' => $this->getFieldsCustomer('customer')
					),
					array(
                        'type' => 'hidden',
                        'name' => 'SPSCO_FIELDS_SETUP',
                    ),
                ),
                'submit' => array(
					'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
				),
            ),
        );
        $fields_form['setting_shipping'] = array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display Description of Carriers'),
                        'hint' => $this->l('Show description of carriers'),
                        'name' => 'SPSCO_SHOW_DESCRIPTION_CARRIER',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
                        'type' => 'switch',
                        'label' => $this->l('Display Image of Carriers'),
                        'hint' => $this->l('Show image of carriers'),
                        'name' => 'SPSCO_SHOW_IMAGE_CARRIER',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
                        'type' => 'switch',
                        'label' => $this->l('Reload Shipping'),
                        'hint' => $this->l('Reload shipping when changing state'),
                        'name' => 'SPSCO_RELOAD_SHIPPING_BY_STATE',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
                        'type' => 'switch',
                        'label' => $this->l('Require a Postal Code'),
                        'hint' => $this->l('Require a postal code to be entered'),
                        'name' => 'SPSCO_FORCE_NEED_POSTCODE',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
                        'type' => 'switch',
                        'label' => $this->l('Require a City'),
                        'hint' => $this->l('Require a city to be entered'),
                        'name' => 'SPSCO_FORCE_NEED_CITY',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
                ),
                'submit' => array(
					'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
				),
            ),
        );
        $fields_form['setting_payment'] = array(
            'form' => array(
                'input' => array(
					array(
						'type'    => 'select',
						'label'   => $this->l('Default Payment Method'),
						'name'    => 'SPSCO_DEFAULT_PAYMENT_METHOD',
						'hint'    => $this->l('Choose a default payment method'),
						'options' => array(
							'query' => $payment_methods,
							'id'    => 'name',
							'name'  => 'name'
						)
					),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display Images'),
                        'hint' => $this->l('Show images of payment methods'),
                        'name' => 'SPSCO_SHOW_IMAGE_PAYMENT',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
                        'type' => 'switch',
                        'label' => $this->l('Display Description'),
                        'hint' => $this->l('Show detailed description of payment methods'),
                        'name' => 'SPSCO_SHOW_DETAIL_PAYMENT',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					
                ),
                'submit' => array(
					'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
				),
            ),
        );
		$fields_form["setting_review"] = array(
            'form' => array(
                'input' => array(
					array(
                        'type' => 'switch',
                        'label' => $this->l('Display Shopping Cart Before'),
						'hint' => $this->l('Enabling this option will display a summary view of the order before going to the checkout page. If this option is disabled, completing a purchase will lead directly to the modules checkout. '),
                        'name' => 'SPSCO_REDIRECT_DIRECTLY_TO_SP',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Terms of service'),
                        'hint' => $this->l('Require customers to accept or decline terms of service before processing an order.'),
                        'name' => 'PS_CONDITIONS',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
						'type'    => 'select',
						'form_group_class' => "terms-conditions ".((bool)Configuration::get('PS_CONDITIONS') ? '': 'hide')."",
						'hint' => $this->l('Choose the page which contains your store\'s terms and conditions of use.'),
						'label'   => $this->l('Page for the Terms and conditions.'),
						'name'    => 'PS_CONDITIONS_CMS_ID',
						'options' => array(
							'query' => CMS::listCms($this->context->cookie->id_lang),
							'id'    => 'id_cms',
							'name'  => 'meta_title'
						)
					),
					array(
                        'type' => 'switch',
                        'label' => $this->l('Display "Continue Shopping" link'),
                        'hint' => $this->l('Show "Continue Shopping" link'),
                        'name' => 'SPSCO_SHOW_LINK_CONTINUE_SHOPPING',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
                        'type' => 'text',
						'class' => 'fixed-width-xxl',
						'form_group_class' => "link-continue ".((bool)Configuration::get('SPSCO_SHOW_LINK_CONTINUE_SHOPPING') ? '': 'hide')."",
                        'label' => $this->l('Custom URL'),
                        'hint' => $this->l('Custom URL for the "Continue shopping" buttons'),
                        'name' => 'SPSCO_LINK_CONTINUE_SHOPPING',
                    ),
					array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Hook shopping cart'),
                        'hint' => $this->l('Enable hook shopping cart'),
                        'name' => 'SPSCO_ENABLE_HOOK_SHOPPING_CART',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    )
					
                ),
                'submit' => array(
					'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
				),
            ),
        );
		$fields_form["setting_address"] = array(
            'form' => array(
                'input' => array(
					array(
                        'type' => 'switch',
                        'label' => $this->l('First name and Last name for Delivery'),
						'hint' => $this->l('Use the same first name and last name for the customers delivery address. '),
                        'name' => 'SPSCO_USE_SAME_NAME_CONTACT_DA',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
						'type' => 'html',
						'label' => $this->l('Config Fields for Delivery'),
						'name' => 'fields_customer',
						'html_content' => $this->getFieldsCustomer('delivery')
					),
					
                ),
                'submit' => array(
					'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
				),
            ),
        );
		$fields_form["setting_invoice"] = array(
            'form' => array(
                'input' => array(
					array(
                        'type' => 'switch',
                        'label' => $this->l('Invoice Address'),
						'hint' => $this->l('Request invoice address. '),
                        'name' => 'SPSCO_ENABLE_INVOICE_ADDRESS',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
                        'type' => 'switch',
                        'label' => $this->l('Required Invoice '),
						'form_group_class' => "invoice-address ".((bool)Configuration::get('SPSCO_ENABLE_INVOICE_ADDRESS') ? '': 'hide')."",
                        'name' => 'SPSCO_REQUIRED_INVOICE_ADDRESS',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
                        'type' => 'switch',
                        'label' => $this->l('First name and Last name for Invoice'),
						'hint' => $this->l('Use the same first name and last name for the customers invoice address. '),
						'form_group_class' => "invoice-address ".((bool)Configuration::get('SPSCO_ENABLE_INVOICE_ADDRESS') ? '': 'hide')."",
                        'name' => 'SPSCO_USE_SAME_NAME_CONTACT_BA',
                        'required' => false,
                        'is_bool' => true,
                        'values' => array(
                           array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Global'),
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->getTranslator()->trans('No', array(), 'Admin.Global'),
							),
                        )
                    ),
					array(
						'type' => 'html',
						'label' => $this->l('Config Fields for Invoice'),
						'name' => 'fields_customer',
						'html_content' => $this->getFieldsCustomer('invoice')
					),
					
                ),
                'submit' => array(
					'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
				),
            ),
        );
		$lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
		$helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmitGeneral';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm($fields_form);
	}
	
	
	private function sortByFields($field, &$array)
	{
		usort($array, function($a, $b) use($field)  {
			$a = $a[$field];
			$b = $b[$field];
			if ($a == $b) return 0;
			return ($a < $b) ? -1 : 1;
		});

		return true;
	}
	
	private function getFieldsCustomer($object)
    {
		$fieldsUnserialize = $this->getAllFieldsSort(true);
        $html = '';
        $html .='<div class="panel  col-lg-12 spsco-fields-wrap" >
					<div class="spsco-fields-head">
						<div class="spsco-fields-head-inner">
							<div class="row">
								<div class="col-lg-3 col-md-3 col-sm-3 field-id">
									<div class="field-content">
										Name
									</div>
								</div>
								<div class="col-lg-5 col-md-5 col-sm-5 field-description">
									<div class="field-content">
										Description
									</div>	
								</div>
								<div class="col-lg-2 col-md-2 col-sm-2 field-active text-center">
									<div class="field-content">
										Active
									</div>
								</div>
								<div class="col-lg-2 col-md-2 col-sm-2 field-required text-center">
									<div class="field-content">
										Required
									</div>
								</div>
							</div>
						</div>	
					</div>
					<div class="spsco-fields" data-object="'.$object.'">';
						foreach($fieldsUnserialize as $key => $custf){
							if ($custf['object'] == $object) {
								 $html .='<div class="spsco-field '.($custf['id_control'] == 'customer_id' || $custf['id_control'] == 'invoice_id' ||  $custf['id_control'] == 'delivery_id' ? ' hidden ' : '').'" data-id="'.$custf['id_control'].'" id="field_'.$custf['id'].'">
											<div class="row">
												<div class="col-lg-3 col-md-3 col-sm-3 field-id">
													<div class="field-content">
													'.$this->l($custf['name']).'
													</div>
												</div>
												<div class="col-lg-5 col-md-5 col-sm-5 field-description">
													<div class="field-content">
													'.$this->l($custf['description'] , 'setupfields').'
													</div>	
												</div>';
										$html .='<div class="col-lg-2 col-md-2 col-sm-2 field-active text-center">
													<div class="field-content">
														<div class="btn-group ">
															'.$this->changeStatusHtml($object,'changeStatus', $custf['active']).'
														</div>
													</div>	
												</div>';
										if ($custf['id_control'] !== 'customer_passwd'){		
										$html .='<div class="col-lg-2 col-md-2 col-sm-2 field-required text-center">
											<div class="field-content">
												<div class="btn-group ">
													'.$this->changeStatusHtml($object,'changeRequired', $custf['required']).'
												</div>
											</div>
										</div>';
										}		
											$html .='</div>
										</div>';
							}		
						}
			$html .= '</div>';
        $html .= '</div>';
        return $html;
    }
	
	public function postProcessAjax()
    {
        if ((int) Tools::getValue('statusItem'))
            return $this->changeStatus();
		else if ((int) Tools::getValue('updateItemsPosition'))
            return $this->updateItemsPosition();
    }
	
	private function updateItemsPosition()
	{
		$return = [];
        $errors = [];
		$value = [];
		$postions  = Tools::getValue('field');
		$fields = $this->configVars['SPSCO_FIELDS_SETUP'];
		$fieldsUnserialize = unserialize($fields);
		foreach($fieldsUnserialize as &$custf){	
			foreach($postions as $key => $pos){	
				if ($custf['id'] == (int)$pos  ) {
					if (Tools::getValue('object') == 'customer'){
						$custf['row'] = $key;
					}else if (Tools::getValue('object') == 'delivery'){
						$custf['row'] = $key + 12;	
					}else {
						$custf['row'] = $key + 28;	
					}	
				}	
			}
			$value[] = $custf;
		}
		Configuration::updateValue('SPSCO_FIELDS_SETUP', serialize($value));
		$return['success'] = 1;
		$return['value'] = serialize($value);
		$return['message']  = $this->l('Change Position Successful!');
		return $return;
	}
	
	private function changeStatus(){
		$return = [];
        $errors = [];
		$value = [];
		$tmp = 0;
		if (Tools::getValue('object') && Tools::getValue('field')){
			if (Tools::getValue('object')){
				$fields = $this->configVars['SPSCO_FIELDS_SETUP'];
				$fieldsUnserialize = unserialize($fields);
				foreach($fieldsUnserialize as &$custf){
					if (Tools::getValue('field') == $custf['id_control']){
						if (Tools::getValue('type') == 'changeStatus'){
							$custf['active'] = (int) $custf['active'] == 0 ? 1 : 0;
							$tmp = $custf['active'];
						}else{
							$custf['required'] = (int) $custf['required'] == 0 ? 1 : 0;
							$tmp = $custf['required'];
							$custf['classes'] = !$custf['required'] ? str_replace('required','', $custf['classes']) : $custf['classes']. ' required ' ; 
						}
					}
					$value[] = $custf;
				}
				Configuration::updateValue('SPSCO_FIELDS_SETUP', serialize($value));
				$return['success'] = 1;
				$return['value'] = serialize($value);
				$return['html'] = $this->changeStatusHtml(Tools::getValue('object'), Tools::getValue('type'), $tmp);
				if (Tools::getValue('type') == 'changeStatus'){
					$return['message']  = $this->l('Change Active Successful!');
				} else {
					$return['message']  = $this->l('Change Required Successful!');
				}
				
			}
		}
		return $return;
	}
	
	private function changeStatusHtml($object, $action, $status)
    {
        $icon  = ((int) $status == 0 ? 'icon-remove' : 'icon-check');
        $class = ((int) $status == 0 ? 'btn-danger' : 'btn-info');
        $html  = '<a class="btn ' . $class . ' change-tatus" href="#" data-field="'.$object.'" data-action="'.$action.'" title="" ><i class="' . $icon . '"></i></a>';
        return $html;
    }
    
    /**
     * @param null $hookName
     * @param array $configuration
     */
    public function renderWidget($hookName = null, array $configuration = [])
    {
       
    }

    /**
     * @param null $hookName
     * @param array $configuration
     * @return array
     */
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
		$social_login_btn = [];
		$title_block = '';
		if ($hookName == 'displayCustomerLoginFormAfter'){
			$title_block = $this->l('Log in with:');
		}else if ($hookName == 'displayCustomerAccountFormTop'){
			$title_block = $this->l('Register with:');
		}
    }

    /**
     * @param Customer $customer
     * @param $password
     */
	protected function sendConfirmationMail(Customer $customer, $password)
    {
        if (Configuration::get('PS_CUSTOMER_CREATION_EMAIL')) {
            Mail::Send(
                $this->context->language->id,
                'account',
                Mail::l('Welcome!'),
                array('{firstname}' => $customer->firstname,
                    '{lastname}'  => $customer->lastname,
                    '{email}'     => $customer->email,
                    '{passwd}'    => $password
                ),
                $customer->email,
                $customer->firstname.' '.$customer->lastname
            );
        }
    }

    /**
     * @param $customer
     * @param $address_delivery
     * @param $address_invoice
     * @param $password
     * @param $is_set_invoice
     */
	public function createCustomer(&$customer, &$address_delivery, &$address_invoice, $password, $is_set_invoice)
    {
        Hook::exec('actionBeforeSubmitAccount');
        if (count($this->context->controller->errors)) {
            $this->errors = $this->context->controller->errors;
        }
        if (Customer::customerExists($customer->email)) {
            if (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED')) {
                $this->errors[] = sprintf(
                    $this->l('The email %s is already in our database. If the information is correct, please login.'),
                    '<b>'.$customer->email.'</b>'
                );
            } else {
                $customer->is_guest = 1;
            }
        }

        if (!is_null($address_delivery)) {
            if ($this->context->cart->nbProducts() > 0 && !$this->context->cart->isVirtualCart()) {
                $country = new Country($address_delivery->id_country, Configuration::get('PS_LANG_DEFAULT'));
                if (!Validate::isLoadedObject($country)) {
                    $this->errors[] = $this->l('Country cannot be loaded.');
                } elseif ((int) $country->contains_states && !(int) $address_delivery->id_state) {
                    $this->errors[] = $this->l('This country requires you to chose a State.');
                }
            }
        }

        if (!is_null($address_invoice) && $is_set_invoice) {
            $country_invoice = new Country($address_invoice->id_country, Configuration::get('PS_LANG_DEFAULT'));
            if (!Validate::isLoadedObject($country_invoice)) {
                $this->errors[] = $this->l('Country cannot be loaded.');
            } elseif ($this->configVars['SPSCO_ENABLE_INVOICE_ADDRESS']
                && $is_set_invoice
                && (int) $country_invoice->contains_states
                && !(int) $address_invoice->id_state
            ) {
                $this->errors[] = $this->l('This country requires you to chose a State.');
            }
        }

        if (!count($this->errors) && !count($this->warnings)) {
            if (Tools::getIsset('is_new_customer') && Configuration::get('PS_GUEST_CHECKOUT_ENABLED')) {
                $customer->is_guest = Tools::getValue('is_new_customer');
            }
            if (Tools::getIsset('group_customer')) {
                $customer->id_default_group = (int) Tools::getValue('group_customer');
            }

            if (!$customer->add()) {
                $this->errors[] = $this->l('An error occurred while creating your account.');
            } else {
                $customer->cleanGroups();
                if (Tools::getIsset('group_customer')) {
                    $customer->addGroups(array((int) Tools::getValue('group_customer')));
                } else {
                    if (!$customer->is_guest) {
                        $customer->addGroups(array((int) $this->configVars['SPSCO_DEFAULT_GROUP_CUSTOMER']));
                    } else {
                        $customer->addGroups(array((int) Configuration::get('PS_GUEST_GROUP')));
                    }
                }
      
                if (!is_null($address_delivery)) {
                    if (!$this->context->cart->isVirtualCart()) {
                        $address_delivery->id_customer = (int) $customer->id;
                        if ($is_set_invoice) {
                            $address_invoice->id_customer = (int) $customer->id;
                        }
                        if ($this->configVars['SPSCO_USE_SAME_NAME_CONTACT_DA']) {
                            $address_delivery->firstname = $customer->firstname;
                            $address_delivery->lastname  = $customer->lastname;
                        }
                        if (!$address_delivery->save()) {
                            $this->errors[] = $this->l('An error occurred while creating your delivery address.');
                        }
                    }
                }
                if (!is_null($address_invoice) && $is_set_invoice) {
                    if (empty($address_invoice->id_customer)) {
                        $address_invoice->id_customer = $customer->id;
                    }
                    if ($this->configVars['SPSCO_USE_SAME_NAME_CONTACT_BA']) {
                        $address_invoice->firstname = $customer->firstname;
                        $address_invoice->lastname  = $customer->lastname;
                    }
                    if (!$address_invoice->save()) {
                        $this->errors[] = $this->l('An error occurred while creating your billing address.');
                    }

                    if (is_null($address_delivery)) {
                        $address_delivery = $address_invoice;
                    }
                }
                if (is_null($address_delivery) && is_null($address_invoice)) {
                    $id_address_new   = $this->createAddress();
                    $address_delivery = new Address($id_address_new);
                }

                if (!count($this->errors)) {
                    if (!$customer->is_guest) {
                        $this->sendConfirmationMail($customer, $password);
                    }
                    $this->context->cookie->id_customer        = (int) $customer->id;
                    $this->context->cookie->customer_lastname  = $customer->lastname;
                    $this->context->cookie->customer_firstname = $customer->firstname;
                    $this->context->cookie->logged             = 1;
                    $customer->logged                          = 1;
                    $this->context->cookie->is_guest           = $customer->isGuest();
                    $this->context->cookie->passwd             = $customer->passwd;
                    $this->context->cookie->email              = $customer->email;
                    $this->context->customer = $customer;
                    if (Configuration::get('PS_CART_FOLLOWING')
                        && (empty($this->context->cookie->id_cart)
                        || Cart::getNbProducts($this->context->cookie->id_cart) == 0)
                    ) {
                        $this->context->cookie->id_cart = (int) Cart::lastNoneOrderedCart($this->context->customer->id);
                    }
                    if (is_null($address_delivery) && is_null($address_invoice)) {
                        $address_delivery = new Address();
                    }
                    $this->context->cart->id_customer         = (int) $customer->id;
                    $this->context->cart->secure_key          = $customer->secure_key;

                    if (!$is_set_invoice) {
                        $this->updateAddressCart(null, $this->context->cart->id_address_delivery, $address_delivery->id);
                    } else {
                        $this->updateAddressCart('delivery', $this->context->cart->id_address_delivery, $address_delivery->id);
                        $this->updateAddressCart('invoice', $this->context->cart->id_address_invoice, $address_invoice->id);
                    }

                    $this->context->cookie->id_cart = (int) $this->context->cart->id;
                    $this->context->cookie->write();

                    $array_post = array_merge((array) $customer, (array) $address_delivery);

                    foreach ($array_post as $key => $value) {
                        $_POST[$key] = $value;
                    }

                    $recargoequivalencia = $this->isModuleActive('recargoequivalencia');
                    if ($recargoequivalencia) {
                        if (array_key_exists('chkRecargoEquivalencia', $_POST)) {
                            $chkRecargoEquivalencia = Tools::getValue('chkRecargoEquivalencia');
                            if (empty($chkRecargoEquivalencia)) {
                                unset($_POST['chkRecargoEquivalencia']);
                            }
                        }
                    }
                    if ($this->isModuleActive('idxrecargoe')) {
                        $idxrecargoeq = 0;

                        if (in_array('idxrecargoeq', $_POST)) {
                            $idxrecargoeq = Tools::getValue('idxrecargoeq') === 'on' ? 1 : 0;
                        }

                        $_POST['idxrecargoeq'] = $idxrecargoeq;
                    }

                    Hook::exec('actionCustomerAccountAdd', array(
                        '_POST'       => $_POST,
                        'newCustomer' => $customer,
                    ));
                }
            }
        }
    }

    /**
     * @return array
     */
    public function createCustomerAjax()
    {
        $results = array();
        $fields = Tools::jsonDecode(Tools::getValue('fields_sp'));
        $customer         = null;
        $address_delivery = null;
        $address_invoice  = null;
        $password         = null;
        $is_set_invoice   = null;

        $this->validateFields($fields, $customer, $address_delivery, $address_invoice, $password, $is_set_invoice);
        if (!count($this->errors)) {
            $this->createCustomer($customer, $address_delivery, $address_invoice, $password, $is_set_invoice);
            if (!count($this->errors)) {
                $results = array(
                    'isSaved'             => true,
                    'isGuest'             => $customer->is_guest,
                    'id_customer'         => (int) $customer->id,
                    'id_address_delivery' => !empty($address_delivery) ? $address_delivery->id : '',
                    'id_address_invoice'  => !empty($address_invoice) ? $address_invoice->id : '',
                );
            }
        }

        $results['hasError'] = !empty($this->errors);
        $results['errors']   = $this->errors;
        return $results;
    }
	
	public function areProductsAvailable()
    {
        $product = $this->context->cart->checkQuantities(true);

        if (true === $product || !is_array($product)) {
            return true;
        }
		return false;
    }

    /**
     *
     */
	public function getMediaFront()
    {
        $this->context->smarty->assign('spstepcheckout', $this);
        if (!$this->configVars['SPSCO_REDIRECT_DIRECTLY_TO_SP'] || !Tools::getIsset('rc') || Tools::getIsset('checkout')) {
            $this->context->controller->addJqueryUI('ui.datepicker');
            if ($this->configVars['SPSCO_AUTOCOMPLETE_GOOGLE_ADDRESS']) {
                if (!empty($this->configVars['SPSCO_GOOGLE_API_KEY'])) {
                    $google_apy_source = 'https://maps.googleapis.com/maps/api/js?key=';
                    $google_apy_source .= trim($this->configVars['SPSCO_GOOGLE_API_KEY']);
                    $google_apy_source .= '&sensor=false&libraries=places&language='.$this->context->language->iso_code;

                    $this->context->controller->registerJavascript(sha1($google_apy_source), $google_apy_source, array('server' => 'remote'));
                }
            }
            $this->context->controller->addJS($this->_path.'views/js/front/jquery.form-validator.min.js');
            $this->context->controller->addCSS($this->_path.'views/css/front/spstepcheckout.css');
			$this->context->controller->registerStylesheet('modules-spstepcheckout-font-awesome', 'modules/'.$this->name.'/views/css/front/font-awesome.min.css', ['media' => 'all', 'priority' => 150]);
			$this->context->controller->registerJavascript('modules-spstepcheckout-spscovalid', 'modules/'.$this->name.'/views/js/front/spscovalid.js', ['position' => 'bottom', 'priority' => 150]); 
			$this->context->controller->registerJavascript('modules-spstepcheckout-js', 'modules/'.$this->name.'/views/js/front/spstepcheckout.js', ['position' => 'bottom', 'priority' => 350]);
        } else {
			$this->context->controller->registerJavascript('modules-spstepcheckout-js', 'modules/'.$this->name.'/views/js/front/spstepcheckout.js', ['position' => 'bottom', 'priority' => 350]);
        }
    }

    /**
     * @param $params
     */
	public function hookDisplayHeader($params)
    {
		if (!$this->isModuleActive($this->name)) {
            return;
        }
        if ($this->context->controller->php_self == 'order') {
            $this->getMediaFront();
        } else {
            if ($this->context->controller->php_self == 'cart'
                && !Tools::getIsset('ajax')
                && !Tools::getIsset('token')
                && (!Tools::getIsset('action') || Tools::getValue('action') == 'show')
                && $this->context->cart->nbProducts() > 0
            ) {
				$checkMinimal = $this->checkMinimalPurchase();
                if (empty($checkMinimal) && $this->areProductsAvailable()) {
					 
                    Tools::redirect('order');
                }
            }
        }
    }
	
	public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == $this->name)
        {
			$this->context->controller->addJquery();
            $this->context->controller->addJqueryUI('ui.sortable');
			$this->context->controller->addJS($this->_path.'views/js/admin/spco_js.js');
			$this->context->controller->addCSS($this->_path.'views/css/admin/spco_css.css');
        }
    }
}
