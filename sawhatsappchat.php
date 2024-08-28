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
        $this->version = '1.0.1';
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
        Configuration::updateValue('SAWHATSAPPCHAT_PHONE', '');
        Configuration::updateValue('SAWHATSAPPCHAT_MESSAGE', '');

        return parent::install() &&
            $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SAWHATSAPPCHAT_PHONE');
        Configuration::deleteByName('SAWHATSAPPCHAT_MESSAGE');

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

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
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
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ],
                'input' => [
                    // array(
                    //     'type' => 'switch',
                    //     'label' => $this->l('Live mode'),
                    //     'name' => 'SAWHATSAPPCHAT_LIVE_MODE',
                    //     'is_bool' => true,
                    //     'desc' => $this->l('Use this module in live mode'),
                    //     'values' => array(
                    //         array(
                    //             'id' => 'active_on',
                    //             'value' => true,
                    //             'label' => $this->l('Enabled')
                    //         ),
                    //         array(
                    //             'id' => 'active_off',
                    //             'value' => false,
                    //             'label' => $this->l('Disabled')
                    //         )
                    //     ),
                    // ),
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
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-text"></i>',
                        'desc' => $this->l('Enter a message. Use %s to replace the current page url'),
                        'name' => 'SAWHATSAPPCHAT_MESSAGE',
                        'label' => $this->l('Whatsapp Message'),
                        'placeholder' => 'Hola, estoy en %s',
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
            'SAWHATSAPPCHAT_MESSAGE' => Configuration::get('SAWHATSAPPCHAT_MESSAGE', null, null, null, ''),
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
        $whasappLogo = $this->_path . 'views/img/whatsapp-container-48x48.png';

        if (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) {
            $whasappLogo = $this->_path . 'views/img/whatsapp-container-48x48.webp';
        }

        Media::addJsDef([
            'sawhatsappchat' => [
                'phone' => Configuration::get('SAWHATSAPPCHAT_PHONE'),
                'message' => Configuration::get('SAWHATSAPPCHAT_MESSAGE'),
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
}
