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

class ZoomProductProCore extends Module
{
    public $config_vars       = array();
    public $prefix_module     = '';
    protected $configure_vars = array();
    protected $errors         = array();
    protected $warnings       = array();
    protected $html           = '';
    protected $smarty;
    protected $cookie;
    protected $success;
    protected $params_back;
    public $globals;

    const CODE_SUCCESS = 0;
    const CODE_ERROR   = -1;

    public function __construct($name = null, $context = null)
    {
        $this->errors      = array();
        $this->warnings    = array();
        $this->params_back = array();
        $this->globals     = new stdClass();
        $this->fillGlobalVars();

        parent::__construct($name, $context);

        $file_smarty_config = _PS_ROOT_DIR_.'/config/smarty.config.inc.php';
        if (is_file($file_smarty_config)) {
            if (is_writable($file_smarty_config)) {
                $content = Tools::file_get_contents($file_smarty_config);

                if (!strstr($content, 'escapePTS')) {
                    $content .= '
                        //CODE MODULES PRESTEAMSHOP - PLEASE NOT REMOVE
                        //--------------------------------------------------------------------------------------------------------
                        smartyRegisterFunction($smarty, "modifier", "escape", "escapePTS");
                        function escapePTS($string, $esc_type = "html", $char_set = null, $double_encode = true, $as_html = false)
                        {
                            $smarty_escape = SMARTY_PLUGINS_DIR."modifier.escape.php";
                            include_once $smarty_escape;

                            if (!$as_html && is_callable("smarty_modifier_escape")) {
                                $string = call_user_func("smarty_modifier_escape", $string, $esc_type, $char_set, $double_encode);
                            } else {
                                $string = html_entity_decode($string);
                            }

                            return $string;
                        }
                        //--------------------------------------------------------------------------------------------------------
                    ';
                    file_put_contents($file_smarty_config, $content);
                }
            }
        }

        $this->smarty = &$this->context->smarty;
        $this->cookie = &$this->context->cookie;

        $this->fillConfigVars();
        $this->checkModulePTS();
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->config_vars)) {
            Configuration::updateValue($name, $value);
            $this->config_vars[$name] = $value;
        } else {
            $this->{$name} = $value;
        }
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->config_vars)) {
            return $this->config_vars[$name];
        }
    }

    public function install()
    {
        foreach ($this->configure_vars as $config) {
            if (!Configuration::updateValue($config['name'], $config['default_value'], $config['is_html'])) {
                return false;
            }
        }

        //install tab actions for BackOffice.
        if (!Tab::getIdFromClassName('AdminActions'.$this->prefix_module)) {
            $name_tab = array();
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                $name_tab[$lang['id_lang']] = $this->displayName;
            }

            $tab = new Tab();
            $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentModules');
            $tab->class_name = 'AdminActions'.$this->prefix_module;
            $tab->module = $this->name;
            $tab->name = $name_tab;
            $tab->save();
        }

        if (!parent::install() || !$this->executeFileSQL('install')) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        foreach ($this->configure_vars as $config) {
            Configuration::deleteByName($config['name']);
        }

        if (!parent::uninstall() || !$this->executeFileSQL('uninstall')) {
            return false;
        }

        return true;
    }

    private function fillGlobalVars()
    {
        $this->globals->type_control = (object) array(
                'select'   => 'select',
                'textbox'  => 'textbox',
                'textarea' => 'textarea',
                'radio'    => 'radio',
                'checkbox' => 'checkbox'
        );

        $this->globals->lang               = new stdClass();
        $this->globals->lang->type_control = array(
            'select'   => $this->l('List'),
            'textbox'  => $this->l('Textbox'),
            'textarea' => $this->l('Textarea'),
            'radio'    => $this->l('Radio button'),
            'checkbox' => $this->l('Checkbox')
        );
    }

    protected function displayForm()
    {
        if (!array_key_exists('JS_FILES', $this->params_back)) {
            $this->params_back['JS_FILES'] = array();
        }
        if (!array_key_exists('CSS_FILES', $this->params_back)) {
            $this->params_back['CSS_FILES'] = array();
        }

        //add anothers scripts
        if (version_compare(_PS_VERSION_, '1.6') < 0) {
            array_unshift(
                $this->params_back['JS_FILES'],
                $this->_path.'views/js/lib/bootstrap/pts/bootstrap.min.js'
            );

            if (version_compare(_PS_VERSION_, '1.5') < 0) {
                //add jquery in lower version than 1.5
                array_unshift(
                    $this->params_back['JS_FILES'],
                    $this->_path.'views/js/lib/jquery/jquery.min/jquery.min.js'
                );
            }

            //add bootstrap files if issen't 1.6
        }

        array_push($this->params_back['CSS_FILES'], $this->_path.'views/css/lib/jquery/plugins/growl/jquery.growl.css');
        array_push($this->params_back['JS_FILES'], $this->_path.'views/js/lib/jquery/plugins/growl/jquery.growl.js');

        //own bootstrap
        array_push($this->params_back['CSS_FILES'], $this->_path.'views/css/lib/bootstrap/pts/pts-bootstrap.css');

        //switch
        array_push($this->params_back['CSS_FILES'], $this->_path.'views/css/lib/simple-switch/simple-switch.css');

        //back
        array_push($this->params_back['JS_FILES'], $this->_path.'views/js/admin/configure.js');
        array_push($this->params_back['JS_FILES'], $this->_path.'views/js/lib/pts/tools.js');
        array_push($this->params_back['CSS_FILES'], $this->_path.'views/css/admin/configure.css');
        array_push($this->params_back['CSS_FILES'], $this->_path.'views/css/lib/pts/tools.css');
        array_push($this->params_back['CSS_FILES'], $this->_path.'views/css/lib/pts/pts-menu.css');

        //icons
        array_push(
            $this->params_back['CSS_FILES'],
            $this->_path.'views/css/lib/font-awesome/font-awesome.css'
        );

        $iso = Language::getIsoById((int) Configuration::get('PS_LANG_DEFAULT'));

        $server_name = Tools::strtolower($_SERVER['SERVER_NAME']);
        $server_name = str_ireplace('www.', '', $server_name);

        $url_store = $this->getUrlStore().$this->context->shop->getBaseURI().'modules/'.$this->name;

        $this->params_back = array_merge(array(
            'MODULE_DIR'                         => $this->_path,
            'MODULE_IMG'                         => $this->_path.'views/img/',
            'MODULE_NAME'                        => $this->name,
            'MODULE_TPL'                         => _PS_ROOT_DIR_.'/modules/'.$this->name.'/',
            'CONFIGS'                            => $this->config_vars,
            'ISO_LANG'                           => $iso,
            'GLOBALS'                            => $this->globals,
            'VERSION'                            => $this->version,
            'SUCCESS_CODE'                       => self::CODE_SUCCESS,
            'ERROR_CODE'                         => self::CODE_ERROR,
            'SERVER_NAME'                        => $server_name,
            'MODULE_PATH_ABSOLUTE'               => dirname(__FILE__).'/',
            'URL_STORE'                          => $url_store,
            'ACTION_URL' => Tools::safeOutput($_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING'],
            'WARNINGS'                           => $this->warnings,
            'ACTIONS_CONTROLLER_URL' => $this->context->link->getAdminLink('AdminActions'.$this->prefix_module),
            $this->prefix_module.'_STATIC_TOKEN' => Tools::encrypt($this->name.'/index'),
            ), $this->params_back);

        $this->smarty->assign('paramsBack', $this->params_back);
    }

    private function executeFileSQL($file_name)
    {
        if (!file_exists(dirname(__FILE__).'/../sql/'.$file_name.'.sql')) {
            return true;
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__).'/../sql/'.$file_name.'.sql')) {
            return false;
        }

        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);

        foreach ($sql as $query) {
            if (!Db::getInstance()->Execute(trim($query))) {
                return false;
            }
        }

        return true;
    }

    protected function addFrontOfficeJS($path)
    {
        if (version_compare(_PS_VERSION_, '1.5') >= 0) {
            $this->context->controller->addJS($path);
        } elseif (method_exists('Tools', 'addJS')) {
            Tools::addJS($path);
        }
    }

    protected function addFrontOfficeCSS($path, $media)
    {
        if (version_compare(_PS_VERSION_, '1.5') >= 0) {
            $this->context->controller->addCSS($path, $media);
        } elseif (method_exists('Tools', 'addCSS')) {
            Tools::addCSS($path);
        }
    }

    protected function fillConfigVars()
    {
        $languages = Language::getLanguages(false);
        foreach ($this->configure_vars as $config) {
            if (isset($config['is_bool']) && $config['is_bool']) {
                $this->config_vars[$config['name']] = (bool)Configuration::get($config['name']);
            } else {
                $this->config_vars[$config['name']] = '';
                $value_conf = Configuration::get($config['name']);

                if (!empty($value_conf) || $config['is_html']) {
                    $this->config_vars[$config['name']] = $value_conf;

                    if ($this->config_vars[$config['name']] === false) {
                        $this->config_vars[$config['name']] = array();
                        foreach ($languages as $language) {
                            $this->config_vars[$config['name']][$language['id_lang']] = Configuration::get(
                                $config['name'],
                                $language['id_lang']
                            );
                        }
                    }
                }
            }
        }
        $this->config_vars[$this->prefix_module.'_RM'] = Configuration::get($this->prefix_module.'_RM');
    }

    public function jsonDecode($json, $assoc = false)
    {
        if (function_exists('json_decode')) {
            return Tools::jsonDecode($json, $assoc);
        } else {
            include_once dirname(__FILE__).'/../lib/JSON.php';
            $pear_json = new Services_JSON(($assoc) ? SERVICES_JSON_LOOSE_TYPE : 0);

            return $pear_json->decode($json);
        }
    }

    public function jsonEncode($data)
    {
        if (function_exists('json_encode')) {
            return Tools::jsonEncode($data);
        } else {
            include_once dirname(__FILE__).'/../lib/JSON.php';
            $pear_json = new Services_JSON();

            return $pear_json->encode($data);
        }
    }

    protected function displayErrors($return = true)
    {
        if (count($this->errors)) {
            $html = '
    		<div class="alert alert-warning">
    			<ol>';
            foreach ($this->errors as $error) {
                $html .= '<li>'.$error.'</li>';
            }
            $html .= '
    			</ol>
    		</div>';

            if ($return) {
                $this->html = $html;
            } else {
                echo $html;
            }
        }
    }

    protected function displayWarnings($return = true)
    {
        if (count($this->warning)) {
            $html = '
    		<div class="alert alert-warning">
    			<ol>';
            foreach ($this->warning as $warning) {
                $html .= '<li>'.$warning.'</li>';
            }
            $html .= '
    			</ol>
    		</div>';

            if ($return) {
                $this->html = $html;
            } else {
                echo $html;
            }
        }
    }

    protected function sendEmail(
        $email,
        $subject,
        $values = array(),
        $template_name = 'default',
        $email_from = null,
        $to_name = null,
        $lang = null,
        $file_attachment = null
    ) {
        if ($lang == null) {
            $lang = (int) Configuration::get('PS_LANG_DEFAULT');
        }
        if ($email_from == null) {
            $email_from = (string) Configuration::get('PS_SHOP_EMAIL');
        }

        return Mail::Send(
            $lang,
            $template_name,
            $subject,
            $values,
            $email,
            $to_name,
            $email_from,
            null,
            $file_attachment,
            null,
            _PS_MODULE_DIR_.$this->name.'/mails/'
        );
    }

    protected function updateVersion($module)
    {
        $registered_version = Configuration::get($this->prefix_module.'_VERSION');

        if ($registered_version != $this->version) {
            $list = array();

            $upgrade_path = _PS_MODULE_DIR_.$module->name.'/upgrades/';

            // Check if folder exist and it could be read
            if (file_exists($upgrade_path) && ($files = scandir($upgrade_path))) {
                // Read each file name
                foreach ($files as $file) {
                    if (!in_array($file, array('.', '..', '.svn', 'index.php'))) {
                        $tab          = explode('-', $file);
                        $file_version = basename($tab[1], '.php');
                        // Compare version, if minor than actual, we need to upgrade the module
                        if (count($tab) == 2 && version_compare($registered_version, $file_version) < 0) {
                            $list[] = array(
                                'file'             => $upgrade_path.$file,
                                'version'          => $file_version,
                                'upgrade_function' => 'upgrade_module_'.str_replace('.', '_', $file_version));
                        }
                    }
                }
            }

            usort($list, array($this, 'moduleVersionSort'));

            foreach ($list as $num => $file_detail) {
                include $file_detail['file'];

                // Call the upgrade function if defined
                if (function_exists($file_detail['upgrade_function'])) {
                    $file_detail['upgrade_function']($module);
                }

                unset($list[$num]);
            }

            Configuration::updateValue($this->prefix_module.'_VERSION', $this->version);

            $this->fillConfigVars();
        }
    }

    public function checkModulePTS()
    {
        return true;
    }

    public function isVisible()
    {
        $display_module = true;
        $enable_debug = $this->config_vars[$this->prefix_module.'_ENABLE_DEBUG'];

        if ($enable_debug) {
            $display_module = false;
            $my_ip = Tools::getRemoteAddr();
            $ip_debug = $this->config_vars[$this->prefix_module.'_IP_DEBUG'];
            $array_ips_debug = explode(',', $ip_debug);

            if (in_array($my_ip, $array_ips_debug)) {
                $display_module = true;
            }
        }

        return $display_module;
    }

    protected function copyOverride($file)
    {
        $source = _PS_MODULE_DIR_.$this->name.'/public/'.$file;
        $dest   = _PS_ROOT_DIR_.'/'.$file;

        $path_dest = dirname($dest);

        if (!is_dir($path_dest)) {
            if (!mkdir($path_dest, 0777, true)) {
                return false;
            }
        }

        if (@copy($source, $dest)) {
            $path_cache_file = _PS_ROOT_DIR_.'/cache/class_index.php';
            if (file_exists($path_cache_file)) {
                unlink($path_cache_file);
            }

            return true;
        }

        return false;
    }

    protected function existOverride($filename, $key = false)
    {
        $file = _PS_ROOT_DIR_.'/'.$filename;

        if (file_exists($file)) {
            if ($key) {
                $file_content = Tools::file_get_contents($file);
                if (preg_match($key, $file_content) > 0) {
                    return true;
                }

                return false;
            }

            return true;
        }

        return false;
    }

    public function isModuleActive($name_module, $function_exist = false)
    {
        if (Module::isInstalled($name_module)) {
            $module = Module::getInstanceByName($name_module);
            if (Validate::isLoadedObject($module) && $module->active) {
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

        return false;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getUrlStore()
    {
        return (Configuration::get('PS_SSL_ENABLED') ? Tools::getShopDomainSsl(true) : Tools::getShopDomain(true));
    }

    public static function getServerIpAddress()
    {
        $server_addr = $_SERVER['SERVER_ADDR'];
        if ($server_addr === '::1') {
            $hostname    = php_uname('n');
            $server_addr = gethostbyname($hostname);
        }

        return $server_addr;
    }

    private function moduleVersionSort($a, $b)
    {
        return version_compare($a['version'], $b['version']);
    }

    /**
     * Customize save data from form.
     * @param type $option
     * @param string $config_var_value
     */
    protected function saveCustomConfigValue($option, &$config_var_value)
    {
        switch ($option['name']) {
            case 'custom':
                $config_var_value = '';
                break;
        }
    }

    /**
     * @internal This method is not editable, use <b>saveCustomConfigValue</b> if necessary
     * @param type $option
     */
    protected function saveConfigValue($option)
    {
        $config_var_name = $this->prefix_module.'_'.$option['name'];
        $config_var_name = Tools::strtoupper($config_var_name);

        if (array_key_exists($config_var_name, $this->config_vars)) {
            $index = array_search($config_var_name, array_keys($this->config_vars));
            if (isset($option['multilang'])) {
                $languages        = Language::getLanguages(false);
                $config_var_value = array();

                foreach ($languages as $language) {
                    $config_var_value[$language['id_lang']] = Tools::getValue($option['name'].'_'.$language['id_lang']);
                }
            } else {
                $config_var_value = Tools::getValue($option['name'], null);
            }

            switch ($option['type']) {
                case $this->globals->type_control->checkbox:
                    $config_var_value = (int) ((is_null($config_var_value)) ? false : true);
                    break;
                case $this->globals->type_control->select:
                    if (isset($option['multiple']) && $option['multiple']) {
                        if (is_array($config_var_value) && count($config_var_value)) {
                            $config_var_value = implode(',', $config_var_value);
                        } else {
                            $config_var_value = '';
                        }
                    }
                    break;
                default:
                    $config_var_value = (is_null($config_var_value)) ? '' : $config_var_value;
                    break;
            }

            //call function to save some options by custom restrictions or data treatment
            $this->saveCustomConfigValue($option, $config_var_value);
            $is_html = (array_key_exists('is_html', $this->configure_vars[$index])) ? $this->configure_vars[$index]['is_html'] : false;

            //save value
            if (!Configuration::updateValue($config_var_name, $config_var_value, $is_html)) {
                $this->errors[] = $this->l('An error occurred while trying update').': '.$option['label'];
            }

            //if dependencies
            if (isset($option['depends']) && is_array($option['depends']) && count($option['depends'])) {
                foreach ($option['depends'] as $dependency_option) {
                    $this->saveConfigValue($dependency_option);
                }
            }
        }
    }

    /**
     * Save data configuration from post form.
     * @param type $form
     */
    protected function saveFormData($form)
    {
        if (isset($form['options']) && is_array($form['options']) && count($form['options'])) {
            foreach ($form['options'] as $option) {
                $this->saveConfigValue($option);
            }
            $this->fillConfigVars();
        }
    }

    public function writeLog($error = null)
    {
        $name_error = Tools::getValue('name_error', 'Internal error');
        $code_error = Tools::getValue('code_error', '000');
        $error      = Tools::getValue('error', $error);
        $data_sent  = Tools::getValue('data_sent');

        $name_log = date('Ymd').'_error.log';

        $file_log = fopen(dirname(__FILE__).'/../log/'.$name_log, 'a+');
        fwrite($file_log, '['.$code_error.'] '.$name_error."\n".$error."\n\n".$data_sent."\n");
        fwrite($file_log, '----------------------------------------------------------------'."\n\n");
        fclose($file_log);

        return 'An internal error has occurred. Please inform the administrator of the store, thank you.';
    }

    protected function truncateChars($text, $limit, $ellipsis = '...')
    {
        if (Tools::strlen($text) > $limit) {
            $text = trim(Tools::substr($text, 0, $limit)).$ellipsis;
        }

        return $text;
    }
}
