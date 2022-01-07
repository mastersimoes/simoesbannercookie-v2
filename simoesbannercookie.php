<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SimoesBannerCookie extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'simoesbannercookie';
        $this->tab = 'content_management';
        $this->version = '1.7.8';
        $this->author = 'Lienard Simoes';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Módulo de Banner de Cookie');
        $this->description = $this->l('prueba de banner de cookie by Lienard Simoes');

        $this->confirmUninstall = $this->l('¿Estas seguro de que quieres desinstalar este modulo?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Is method required for install module in prestashop
     */
    public function install()
    {
        return parent::install() && $this->registerHook('displayFooter') && $this->registerHook('displayHeader') && $this->ConfigurationValues(NULL);
    }

    /**
     * Is method required for uninstall module in prestashop
     */
    public function uninstall()
    {
        return parent::uninstall() && $this->ClearConfiguration() && $this->UndoHooks();
    }

    /**
     * Delete Configurations vars for uninstall module
     */
    public function ClearConfiguration()
    {
        return Configuration::deleteByName('MessageTitle') &&
            Configuration::deleteByName('MessageText') &&
            Configuration::deleteByName('ButtonTextAccept') &&
            Configuration::deleteByName('ColorAcceptButton') &&
            Configuration::deleteByName('ColorTitle') &&
            Configuration::deleteByName('ColorText');
    }

    /**
     * Unregister Hooks for uninstall module
     */
    public function UndoHooks()
    {
        return $this->unregisterHook('displayFooter') && $this->unregisterHook('displayHeader');
    }

    /**
     * this method is for add files to header of the module.
     * registerStylesheet add Css custom.
     * registerJavascript add Css custom.
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->registerStylesheet('modules-simoesbannercookie', 'modules/' . $this->name . '/views/css/simoesbannercookie.css', ['media' => 'all', 'priority' => 10]);
        $this->context->controller->registerJavascript('modules-simoesbannercookie', 'modules/' . $this->name . '/views/js/jquery.js', ['position' => 'bottom', 'priority' => 10]);
        $this->context->controller->registerJavascript('modules-simoesbannercookie', 'modules/' . $this->name . '/views/js/simoesbannercookie.js', ['position' => 'bottom', 'priority' => 11]);
    }

    /**
     * this method is for add files to footer of the module.
     */
    public function HookDisplayFooter()
    {
        $this->context->smarty->assign([
            'MessageTitle' => Configuration::get('MessageTitle'), // this is messagge title modal, example: Important information
            'MessageText' => Configuration::get('MessageText'), // this is message main modal, example: Cookies guarantee the proper functioning of our services, you accept the use of cookies.
            'ButtonTextAccept' => Configuration::get('ButtonTextAccept'), // this is message of button accept, example: Accept
            'ColorAcceptButton' => Configuration::get('ColorAcceptButton'), // this is class background color of button accept.
            'ColorTitle' => Configuration::get('ColorTitle'), // this is class text background color of messagge title modal.
            'ColorText' => Configuration::get('ColorText'), // this is class text background color of message main modal.
        ]);

        return $this->display(__FILE__, 'banner.tpl');
    }

    /**
     * this method is for set Configuration vars of the module.
     */
    public function ConfigurationValues($values)
    {
        if (is_null($values)) {
            return
                Configuration::updateValue('MessageTitle', 'Información importante.') &&
                Configuration::updateValue('MessageText', 'Las cookies garantizan el buen funcionamiento de nuestros servicios, aceptas el uso de cookies.') &&
                Configuration::updateValue('ButtonTextAccept', 'Aceptar') &&
                Configuration::updateValue('ColorAcceptButton', 'btn-primary') &&
                Configuration::updateValue('ColorTitle', 'text-dark') &&
                Configuration::updateValue('ColorText', 'text-dark');
        } else {
            $result = true;
            foreach ($values as $key => $value) {
                $result = $result && Configuration::updateValue($key, $value);
            }
            return $result;
        }
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = null;
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submit' . $this->name . 'Module')) == true) {
            $this->postProcess();
            $output = $this->displayConfirmation($this->l('Configuracion Actualizada.'));
        }

        $this->context->smarty->assign('module_dir', $this->_path);

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
        $helper->submit_action = 'submit' . $this->name . 'Module';
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
                    'title' => $this->trans('Settings', [], 'Admin.Global'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon-font"></i>',
                        'desc' => $this->l('Introduzca el texto del titulo que se mostrara en el anuncio'),
                        'name' => 'MessageTitle',
                        'required' => 'required',
                        'label' => $this->l('Titulo del anuncio'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Color de texto del titulo'),
                        'name' => 'ColorTitle',
                        'required' => 'required',
                        'options' => [
                            'query' => [
                                ['key' => 'text-primary', 'name' => 'Azul'],
                                ['key' => 'text-dark', 'name' => 'Negro'],
                                ['key' => 'text-secondary', 'name' => 'Gris'],
                                ['key' => 'text-success', 'name' => 'Verde'],
                                ['key' => 'text-danger', 'name' => 'Rojo'],
                                ['key' => 'text-warning', 'name' => 'Amarillo'],
                                ['key' => 'text-info', 'name' => 'Cian']
                            ],
                            'id' => 'key',
                            'name' => 'name'
                        ],
                        'desc' => 'Por favor, indica el color que deseas usar para el texto del titulo del anuncio.'
                    ],
                    [
                        'col' => 4,
                        'type' => 'textarea',
                        'required' => 'required',
                        'desc' => $this->l('Introduzca el texto del mensaje que se mostrara en el anuncio'),
                        'name' => 'MessageText',
                        'label' => $this->l('Mensaje del anuncio'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Color de texto del mensaje'),
                        'name' => 'ColorText',
                        'required' => 'required',
                        'options' => [
                            'query' => [
                                ['key' => 'text-primary', 'name' => 'Azul'],
                                ['key' => 'text-dark', 'name' => 'Negro'],
                                ['key' => 'text-secondary', 'name' => 'Gris'],
                                ['key' => 'text-success', 'name' => 'Verde'],
                                ['key' => 'text-danger', 'name' => 'Rojo'],
                                ['key' => 'text-warning', 'name' => 'Amarillo'],
                                ['key' => 'text-info', 'name' => 'Cian']
                            ],
                            'id' => 'key',
                            'name' => 'name'
                        ],
                        'desc' => 'Por favor, indica el color que deseas usar para el texto del mensaje del anuncio.'
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'required' => 'required',
                        'prefix' => '<i class="icon-italic"></i>',
                        'desc' => $this->l('Introduzca el texto del boton de aceptar.'),
                        'name' => 'ButtonTextAccept',
                        'label' => $this->l('Texto del boton aceptar'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Color del boton de aceptar'),
                        'required' => 'required',
                        'name' => 'ColorAcceptButton',
                        'options' => [
                            'query' => [
                                ['key' => 'btn-primary', 'name' => 'Azul'],
                                ['key' => 'btn-dark', 'name' => 'Negro'],
                                ['key' => 'btn-secondary', 'name' => 'Gris'],
                                ['key' => 'btn-success', 'name' => 'Verde'],
                                ['key' => 'btn-danger', 'name' => 'Rojo'],
                                ['key' => 'btn-warning', 'name' => 'Amarillo'],
                                ['key' => 'btn-info', 'name' => 'Cian']
                            ],
                            'id' => 'key',
                            'name' => 'name'
                        ],
                        'desc' => 'Por favor, indica el color que deseas usar para el boton de aceptar.'
                    ]
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
            'MessageTitle' => Configuration::get('MessageTitle', 'Información importante.'),
            'ColorTitle' => Configuration::get('ColorTitle', 'text-dark'),
            'MessageText' => Configuration::get('MessageText', 'Las cookies garantizan el buen funcionamiento de nuestros servicios, aceptas el uso de cookies.'),
            'ColorText' => Configuration::get('ColorText', 'text-dark'),
            'ButtonTextAccept' => Configuration::get('ButtonTextAccept', 'Aceptar'),
            'ColorAcceptButton' => Configuration::get('ColorAcceptButton', 'btn-primary'),
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
}
