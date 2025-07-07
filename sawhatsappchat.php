<?php
/**
 * Copyright since 2007 Alejo A. Sotelo <alejosotelo.com.ar>
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Alejo A. Sotelo
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Alejo A. Sotelo is strictly forbidden.
 * In order to obtain a license, please contact us: soporte@alejosotelo.com.ar
 *
 * @author    Alejo A. Sotelo <soporte@alejosotelo.com.ar>
 * @copyright Copyright (c) since 2007 Alejo Sotelo
 * @license   Commercial License
 */
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Sawhatsappchat extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'sawhatsappchat';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Alejo Sotelo <alejosotelo.com.ar>';
        $this->need_instance = 0;

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('SA Whatsapp Chat');
        $this->description = $this->l('Este módulo agrega un botón de Whatsapp a tu tienda para que tus usuarios inicien un chat con tu empresa.');

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $msgLogged = 'Hola {asesor_nombre}! Soy {cliente_nombre}. Te contacto desde {url}. Quería hacer una consulta:';
        $msgGuest = 'Hola {asesor_nombre}! Te contacto desde {url}. Quería hacer una consulta:';

        Configuration::updateValue('SAWHATSAPPCHAT_PHONE', '');
        Configuration::updateValue('SAWHATSAPPCHAT_MESSAGE_GUEST', 'Hola, estoy en {url}');
        Configuration::updateValue('SAWHATSAPPCHAT_MESSAGE_LOGGED', 'Hola! Soy {cliente_nombre} estoy en {url}');
        Configuration::updateValue('SAWHATSAPPCHAT_MESSAGE_ASESOR_GUEST', $msgGuest);
        Configuration::updateValue('SAWHATSAPPCHAT_MESSAGE_ASESOR_LOGGED', $msgLogged);

        return parent::install() &&
            $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SAWHATSAPPCHAT_PHONE');
        Configuration::deleteByName('SAWHATSAPPCHAT_MESSAGE_GUEST');
        Configuration::deleteByName('SAWHATSAPPCHAT_MESSAGE_LOGGED');
        Configuration::deleteByName('SAWHATSAPPCHAT_MESSAGE_ASESOR_LOGGED');
        Configuration::deleteByName('SAWHATSAPPCHAT_MESSAGE_ASESOR_GUEST');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitSawhatsappchatModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSawhatsappchatModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' =>  $this->l('SA Whatsapp Chat - Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-whatsapp"></i>',
                        'desc' => $this->l('Enter a valid whatsapp phone'),
                        'name' => 'SAWHATSAPPCHAT_PHONE',
                        'label' => $this->l('Whatsapp Phone'),
                        'placeholder' => '5491123456789',
                    ],
                    [
                        'col' => 3,
                        'type' => 'textarea',
                        'prefix' => '<i class="icon icon-text"></i>',
                        'desc' => $this->l('Mensaje que se enviará al dueño del sitio cuando el cliente sea invitado. Variables: {asesor_nombre}, {asesor_email}, {asesor_telefono}, {cliente_nombre}, {dominio} y {url}.'),
                        'name' => 'SAWHATSAPPCHAT_MESSAGE_GUEST',
                        'label' => $this->l('Whatsapp Message (Guest)'),
                        'placeholder' => 'Hola, estoy en {url}',
                    ],
                    [
                        'col' => 3,
                        'type' => 'textarea',
                        'prefix' => '<i class="icon icon-text"></i>',
                        'desc' => $this->l('Mensaje que se enviará al dueño del sitio cuando el cliente esté logueado. Variables: {asesor_nombre}, {asesor_email}, {asesor_telefono}, {cliente_nombre}, {dominio} y {url}.'),
                        'name' => 'SAWHATSAPPCHAT_MESSAGE_LOGGED',
                        'label' => $this->l('Whatsapp Message (Logged In)'),
                        'placeholder' => 'Hola, estoy en {url}',
                    ],
                    [
                        'col' => 3,
                        'type' => 'textarea',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Mensaje que se enviara por whatsapp al asesor comercial cuando el cliente esté logueado. Variables: {asesor_nombre}, {asesor_email}, {asesor_telefono}, {cliente_nombre}, {dominio} y {url}.'),
                        'name' => 'SAWHATSAPPCHAT_MESSAGE_ASESOR_LOGGED',
                        'label' => $this->l('Mensaje whatsapp Asesor (Cliente Logueado)'),
                    ],
                    [
                        'col' => 3,
                        'type' => 'textarea',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Mensaje que se enviara por whatsapp al asesor comercial cuando el cliente sea invitado.  Variables: {asesor_nombre}, {asesor_email}, {asesor_telefono}, {cliente_nombre}, {dominio} y {url}.'),
                        'name' => 'SAWHATSAPPCHAT_MESSAGE_ASESOR_GUEST',
                        'label' => $this->l('Mensaje whatsapp Asesor (Cliente Invitado)'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'SAWHATSAPPCHAT_PHONE' => Configuration::get('SAWHATSAPPCHAT_PHONE', null, null, null, ''),
            'SAWHATSAPPCHAT_MESSAGE_GUEST' => Configuration::get('SAWHATSAPPCHAT_MESSAGE_GUEST', null, null, null, ''),
            'SAWHATSAPPCHAT_MESSAGE_LOGGED' => Configuration::get('SAWHATSAPPCHAT_MESSAGE_LOGGED', null, null, null, ''),
            'SAWHATSAPPCHAT_MESSAGE_ASESOR_LOGGED' => Configuration::get('SAWHATSAPPCHAT_MESSAGE_ASESOR_LOGGED', null, null, null, ''),
            'SAWHATSAPPCHAT_MESSAGE_ASESOR_GUEST' => Configuration::get('SAWHATSAPPCHAT_MESSAGE_ASESOR_GUEST', null, null, null, ''),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookDisplayHeader()
    {
        if (!$this->active) {
            return;
        }

        $whasappLogo = $this->_path . 'views/img/whatsapp-container-48x48.png';

        if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) {
            $whasappLogo = $this->_path . 'views/img/whatsapp-container-48x48.webp';
        }

        Media::addJsDef([
            'sawhatsappchat' => [
                'phone' => Configuration::get('SAWHATSAPPCHAT_PHONE'),
                'message' => $this->getMessage(),
                'logo' => $whasappLogo,
            ],
        ]);

        $this->context->controller->registerStylesheet(
            'module-sawhatsappchat',
            'modules/' . $this->name . '/views/css/front.css',
            [
                'media' => 'all',
                'priority' => 150,
                'version' => $this->version,
            ]
        );

        $this->context->controller->registerJavascript(
            'module-sawhatsappchat',
            'modules/' . $this->name . '/views/js/front.js',
            [
                'position' => 'bottom',
                'priority' => 150,
                'version' => $this->version,
            ]
        );
    }

    protected function getMessage()
    {
        $asesor = $this->findAsesor();

        if ($asesor !== false) {
            return $this->getMessageAsesor($asesor);
        }

        return $this->getMessageDefault();
    }

    protected function findAsesor()
    {
        $existsCart = Validate::isLoadedObject($this->context->cart);
        $cartId = $existsCart ? $this->context->cart->id : null;

        $asesorManager = new AsesorManager($this->context);
        $hasCode = $asesorManager->hasCodeInCookieOrCart($cartId);

        if (!$hasCode) {
            return false;
        }
    
        if ($existsCart && ($link = SavoucherbylinkCart::findByCartId($cartId)) !== false) {
            $code = $link->code;
        } else {
            $code = $this->asesorManager->getCodeInCookie();
        }

        $asesor = SavoucherbylinkAsesor::getInstance()->findAsesorOrCustomerByCode($code);
        $asesor->code = $code;

        if (!$asesor) {
            return false;
        }

        return $asesor;
    }

    protected function getMessageAsesor($asesor)
    {
        $firstname = $this->context->customer->isLogged() ? $this->context->customer->firstname : '';
        $configKey = 'SAWHATSAPPCHAT_MESSAGE_ASESOR_';
        $configKey .= !empty($firstname) ? 'LOGGED' : 'GUEST';
        $host = Tools::getHttpHost();

        $message = Configuration::get($configKey, null, null, null, '');
        $message = str_replace(
            [
                '{asesor_nombre}',
                '{asesor_email}',
                '{asesor_telefono}',
                '{cliente_nombre}',
                '{dominio}',
                '{url}'
            ],
            [
                $asesor->name, 
                $asesor->email, 
                $asesor->whatsapp,
                !empty($firstname) ? ucwords(strtolower($firstname)) : '',
                $host,
                $this->getCurrentUri($asesor->code)
            ],
            $message
        );
        return $message;
    }

    protected function getMessageDefault()
    {
        $firstname = $this->context->customer->isLogged() ? $this->context->customer->firstname : '';
        $configKey = 'SAWHATSAPPCHAT_MESSAGE_';
        $configKey .= !empty($firstname) ? 'LOGGED' : 'GUEST';
        $host = Tools::getHttpHost();

        $message = Configuration::get($configKey, null, null, null, '');
        $message = str_replace(
            [
                '{asesor_nombre}',
                '{asesor_email}',
                '{asesor_telefono}',
                '{cliente_nombre}',
                '{dominio}',
                '{url}'
            ],
            [
                '', 
                '', 
                '',
                !empty($firstname) ? ucwords(strtolower($firstname)) : '',
                $host,
                $this->getCurrentUri()
            ],
            $message
        );
        return $message;
    }

    protected function getCurrentUri($code = '')
    {
        $currentUri = _PS_BASE_URL_SSL_;
        $currentUri .= SymfonyRequest::createFromGlobals()->getRequestUri();

        if (!empty($code)) {
            $currentUri .= (strpos($currentUri, '?') === false ? '?' : '&') . AsesorManager::GET_PARAM_CODE . '=' . $code;
        }

        return $currentUri;
    }
}
