<?php
/**
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * We are experts and professionals in PrestaShop
 *
 * @category  PrestaShop
 * @category  Module
 * @author    PresTeamShop.com <support@presteamshop.com>
 * @copyright 2011-2016 PresTeamShop
 * @license   see file: LICENSE.txt
 */

class AdminActionsZPP extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        $module_name = 'zoomproductpro';

        if (!Tools::isSubmit('token')
            || Tools::encrypt($module_name.'/index') != Tools::getValue('token')
            || !Module::isInstalled($module_name)
        ) {
            $params = array(
                'token' => Tools::getAdminTokenLite('AdminModules'),
                'configure' => $module_name
            );
            $url = Dispatcher::getInstance()->createUrl('AdminModules', $this->context->language->id, $params);

            Tools::redirectAdmin($url);
        }

        $action = Tools::getValue('action');
        if (Tools::isSubmit('action')) {
            $action = Tools::getValue('action');
            $module = Module::getInstanceByName($module_name);

            if (method_exists($module, $action)) {
                define('_PTS_SHOW_ERRORS_', true);

                $data_type = 'json';
                if (Tools::isSubmit('dataType')) {
                    $data_type = Tools::getValue('dataType');
                }

                switch ($data_type) {
                    case 'html':
                        die($module->$action());
                    case 'json':
                        $response = $module->jsonEncode($module->$action());
                        die($response);
                    default:
                        die('Invalid data type.');
                }
            } else {
                die('403 Forbidden');
            }
        } else {
            die('403 Forbidden');
        }
    }
}
