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
 * @revision  19
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'/zoomproductpro/classes/ZoomProductProCore.php';

class ZoomProductPro extends ZoomProductProCore
{
    const VERSION = '2.0.1';
    const ADDONS               = true;

    public $translation_dir;
    public $dir_360;

    protected $configure_vars = array(
        array('name' => 'ZPP_VERSION', 'default_value' => self::VERSION, 'is_html' => false, 'is_bool' => false),
        array('name' => 'ZPP_IDENTIFIER_IMAGE', 'default_value' => '#bigpic', 'is_html' => false, 'is_bool' => false),
        array('name' => 'ZPP_WIDTH', 'default_value' => 600, 'is_html' => false, 'is_bool' => false),
        array('name' => 'ZPP_HEIGHT', 'default_value' => 600, 'is_html' => false, 'is_bool' => false),
        array('name' => 'ZPP_VIEW_GALLERY', 'default_value' => true, 'is_html' => false, 'is_bool' => true),
        array('name' => 'ZPP_FULL_SCREEN', 'default_value' => false, 'is_html' => false, 'is_bool' => true),
        array('name' => 'ZPP_WATERMARK', 'default_value' => false, 'is_html' => false, 'is_bool' => true),
        array('name' => 'ZPP_POSTFIX_NAME_IMAGES', 'default_value' => '_default', 'is_html' => false, 'is_bool' => false),

        array('name' => 'ZPP_OVERRIDE_CSS', 'default_value' => '', 'is_html' => false, 'is_bool' => false),
        array('name' => 'ZPP_OVERRIDE_JS', 'default_value' => '', 'is_html' => false, 'is_bool' => false),
        array('name' => 'ZPP_ENABLE_360', 'default_value' => 0, 'is_html' => false, 'is_bool' => true),
        array('name' => 'ZPP_IMAGES_EXTENSION', 'default_value' => '.png', 'is_html' => false, 'is_bool' => false),
    );

    public function __construct()
    {
        $this->prefix_module = 'ZPP';
        $this->name = 'zoomproductpro';
        $this->tab = 'front_office_features';
        $this->version = '2.0.1';
        $this->author = 'PresTeamShop';
        $this->module_key = '8eda376045b513f303ee06c5498d8d95';
        $this->bootstrap = true;

        parent::__construct();

        $this->name_file = Tools::substr(basename(__FILE__), 0, Tools::strlen(basename(__FILE__)) - 4);
        $this->dir_360 = _PS_ROOT_DIR_.'/zoomproductpro_360/';

        $this->displayName = 'Zoom Product Pro';
        $this->description = $this->l('Provide high quality Zoom and give more details on photos to high demanding customers with a easy and powerful gallery.');
        $this->confirmUninstall = $this->l('Are you sure you want unistall ?');
        $this->translation_dir = realpath(_PS_MODULE_DIR_.$this->name.'/translations');
        
        //update version
        if (Module::isInstalled($this->name)) {
            $this->updateVersion($this);
        }
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('header')
        ) {
            return false;
        }

        Configuration::updateValue(
            'ZPP_POSTFIX_NAME_IMAGES',
            version_compare('1.5.1', _PS_VERSION_) >= 0 ? '' : '_default'
        );

        if (!is_dir($this->dir_360)) {
            mkdir($this->dir_360, 0777);
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        $forms = $this->getHelperForm();
        if (is_array($forms)
            && count($forms)
            && isset($forms['forms'])
            && is_array($forms['forms'])
            && count($forms['forms'])
        ) {
            foreach ($forms['forms'] as $key => $form) {
                if (Tools::isSubmit('form-'.$key)) {
                    $this->smarty->assign('CURRENT_FORM', $key);
                    //save form data in configuration
                    $this->saveFormData($form);

                    //show message
                    $this->smarty->assign('show_saved_message', true);
                    break;
                }
            }
        }

        $this->displayErrors();
        $this->displayForm();

        return $this->html;
    }

    protected function displayForm()
    {
        //helper form
        $helper_form = $this->getHelperForm();
        //extra tabs for PresTeamShop
        $this->getExtraTabs($helper_form);

        $this->params_back = array(
            'HELPER_FORM' => $helper_form,
            'array_label_translate' => $this->getTranslations(),
            'iso_lang_backoffice_shop'  => Language::getIsoById($this->context->employee->id_lang),
            'LANGUAGES' => Language::getLanguages(false),
            'ADDONS'  => self::ADDONS,
            'code_editors' => $this->codeEditors(),
            'id_lang'  => $this->context->employee->id_lang,
            'exist_FAQS_json' =>  file_exists(_PS_MODULE_DIR_.$this->name.'/docs/FAQs.json'),
            'MODULE_PREFIX' => $this->prefix_module,
            'STATIC_TOKEN' => Tools::getAdminTokenLite('AdminModules')
        );
        parent::displayForm();

        $this->html .= $this->display(__FILE__, 'views/templates/admin/header.tpl');
        $this->html .= $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    private function getHelperTabs()
    {
        $tabs = array(
            'settings' => array(
                'label' => $this->l('Settings', $this->name_file),
                'href' => 'settings'
            )
        );

        return $tabs;
    }

    private function getSettingsForm()
    {
        $options = array(
            'identifier_image' => array(
                'name' => 'identifier_image',
                'prefix' =>'txt',
                'label' => $this->l('Identifier image', $this->name_file),
                'type' => $this->globals->type_control->textbox,
                'value' => $this->config_vars['ZPP_IDENTIFIER_IMAGE']
            ),
            'width' => array(
                'name' => 'width',
                'prefix' => 'txt',
                'label' => $this->l('Width', $this->name_file),
                'type' => $this->globals->type_control->textbox,
                'value' => $this->config_vars['ZPP_WIDTH']
            ),
            'height' => array(
                'name' => 'height',
                'prefix' => 'txt',
                'label' => $this->l('Height', $this->name_file),
                'type' => $this->globals->type_control->textbox,
                'value' => $this->config_vars['ZPP_HEIGHT']
            ),
            'postfix_name_images' => array(
                'name' => 'postfix_name_images',
                'prefix' => 'txt',
                'label' => $this->l('Postfix of the name images', $this->name_file),
                'type' => $this->globals->type_control->textbox,
                'value' => $this->config_vars['ZPP_POSTFIX_NAME_IMAGES']
            ),
            'view_gallery' => array(
                'name' => 'view_gallery',
                'prefix' => 'chk',
                'label' => $this->l('Show gallery image', $this->name_file),
                'type' => $this->globals->type_control->checkbox,
                'check_on' => $this->config_vars['ZPP_VIEW_GALLERY'],
                'label_on' => $this->l('YES', $this->name_file),
                'label_off' => $this->l('NO', $this->name_file)
            ),
            'full_screen' => array(
                'name' => 'full_screen',
                'prefix' => 'chk',
                'label' => $this->l('Full screen', $this->name_file),
                'type' => $this->globals->type_control->checkbox,
                'check_on' => $this->config_vars['ZPP_FULL_SCREEN'],
                'label_on' => $this->l('YES', $this->name_file),
                'label_off' => $this->l('NO', $this->name_file)
            ),
            'watermark' => array(
                'name' => 'watermark',
                'prefix' => 'chk',
                'label' => $this->l('Are you using watermark yours images?', $this->name_file),
                'type' => $this->globals->type_control->checkbox,
                'check_on' => $this->config_vars['ZPP_WATERMARK'],
                'label_on' => $this->l('YES', $this->name_file),
                'label_off' => $this->l('NO', $this->name_file),
                'tooltip' => array(
                    'warning' => array(
                        'title' => $this->l('Warning', $this->name_file),
                        'content' => $this->l('You must perform additional configuration for proper operation. See user guide', $this->name_file)
                    )
                )
            ),
            'enable_360' => array(
                'name' => 'enable_360',
                'prefix' => 'chk',
                'label' => $this->l('Enable 360', $this->name_file),
                'type' => $this->globals->type_control->checkbox,
                'check_on' => $this->config_vars['ZPP_ENABLE_360'],
                'label_on' => $this->l('YES', $this->name_file),
                'label_off' => $this->l('NO', $this->name_file),
                'depends' => array(
                    'images_extension' => array(
                        'name' => 'images_extension',
                        'prefix' => 'txt',
                        'label' => $this->l('Extension of images'),
                        'type' => $this->globals->type_control->textbox,
                        'value' => $this->config_vars['ZPP_IMAGES_EXTENSION'],
                        'hidden_on' => $this->config_vars['ZPP_ENABLE_360'],
                        'tooltip' => array(
                            'warning' => array(
                                'title' => $this->l('Warning', $this->name_file),
                                'content' => $this->l('example: .png', $this->name_file)
                            )
                        )
                    )
                )
            ),
        );

        $form = array(
            'tab' => 'settings',
            'method' => 'post',
            'actions' => array(
                'save' => array(
                    'label' => $this->l('Save', $this->name_file),
                    'class' => 'save-settings',
                    'icon' => 'save'
                )
            ),
            'options' => $options
        );

        return $form;
    }

    private function getHelperForm()
    {
        $tabs = $this->getHelperTabs();
        $settings = $this->getSettingsForm();

        $form = array(
            'tabs' => $tabs,
            'forms' => array(
                'settings' => $settings
            )
        );

        return $form;
    }

     /**
     * Extra tabs for PresTeamShop
     * @param type $helper_form
     */
    private function getExtraTabs(&$helper_form)
    {
        $helper_form['tabs']['translate'] = array(
            'label'   => $this->l('Translate'),
            'href'    => 'translate',
            'icon'    => 'globe'
        );

        $helper_form['tabs']['code_editors'] = array(
            'label'   => $this->l('Code Editors'),
            'href'    => 'code_editors',
            'icon'    => 'code'
        );

        if (file_exists(_PS_MODULE_DIR_.$this->name.'/docs/FAQs.json')) {
            $helper_form['tabs']['faqs'] = array(
                'label' => $this->l('FAQs'),
                'href' => 'faqs',
                'icon' => 'question-circle'
            );
        }

        $helper_form['tabs']['suggestions']    = array(
            'label'   => $this->l('Suggestions'),
            'href'    => 'suggestions',
            'icon'    => 'pencil'
        );

        //another modules
        $helper_form['tabs']['another_modules'] = array(
            'label' => $this->l('Another modules'),
            'href'  => 'another_modules',
            'icon'  => 'cubes',
        );

        //carousel
        $protocol = 'http://';
        if (Configuration::get('PS_SSL_ENABLED')) {
            $protocol = 'https://';
        }

        if (!self::ADDONS) {
            $carousel_json = Tools::file_get_contents($protocol.'www.presteamshop.com/products_presteamshop.json');
            $carousel      = $this->jsonDecode($carousel_json);
            foreach ($carousel as &$module) {
                if (Module::isInstalled($module->name)) {
                    $module->active = true;
                } else {
                    $module->active = false;
                }
            }

            $this->smarty->assign('ANOTHER_MODULES', $carousel);
        }
    }

    /* global tabs */
    public function getTranslations()
    {
        if (isset($this->context->cookie->id_lang)) {
            $id_lang = $this->context->cookie->id_lang;
        } else {
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
        }

        $iso_code_selected = Language::getIsoById($id_lang);
        if (Tools::isSubmit('iso_code')) {
            $iso_code_selected = Tools::getValue('iso_code');
        }

        $array_translate = $this->readFile($this->name, 'en');

        if (sizeof($array_translate)) {
            $array_translate_lang_selected  = $this->readFile($this->name, $iso_code_selected, true);
            if (Tools::isSubmit('iso_code')) {
                foreach ($array_translate_lang_selected as &$items_array_translate_lang) {
                    if (in_array('', $items_array_translate_lang)) {
                        $items_array_translate_lang['empty_elements'] = true;
                    }
                }

                return array('message_code' => self::CODE_SUCCESS, 'data' => $array_translate_lang_selected);
            }
            
            foreach ($array_translate as $key_page => $translate_en) {
                foreach ($translate_en as $md5 => $label) {
                    $label = $label;
                    if (!empty($md5) && !empty($key_page)) {
                        $array_translate[$key_page][$md5]['lang_selected'] = '';
                        if (sizeof($array_translate_lang_selected)
                            && isset($array_translate_lang_selected[$key_page][$md5])
                        ) {
                            $array_translate[$key_page][$md5]['lang_selected'] = $array_translate_lang_selected[$key_page][$md5];
                           
                            if (empty($array_translate[$key_page][$md5]['lang_selected'])) {
                                $array_translate[$key_page]['empty_elements'] = true;
                            }
                        } else {
                            $array_translate[$key_page]['empty_elements'] = true;
                        }
                    }
                }
            }
        }

        return $array_translate;
    }

    public function readFile($module, $iso_code, $detail = false)
    {
        $file_name = basename($this->translation_dir.'/'.$iso_code.'.php');
        $file_path = $this->translation_dir.'/'.$file_name;
        if (!file_exists($file_path)) {
            return array();
        }

        $file = fopen($file_path, 'r') or exit($this->l('Unable to open file'));

        $array_translate = array();

        while (!feof($file)) {
            $line =  fgets($file);
            $line_explode = explode('=', $line);

            $search_string = strpos($line_explode[0], '<{'.$module.'}prestashop>');

            if (array_key_exists(1, $line_explode) && $search_string) {
                $file_md5 = str_replace("$"."_MODULE['<{".$module."}prestashop>", '', $line_explode[0]);
                $file_md5 = str_replace("']", '', trim($file_md5));

                $explode_file_md5 = explode('_', $file_md5);
                $md5 = array_pop($explode_file_md5);
                $file_name = join('_', $explode_file_md5);


                $label_title = $file_name;
                $description_lang = str_replace(';', '', $line_explode[1]);
                $description_lang = str_replace("'", '', trim($description_lang));

                if ($detail) {
                    $array_translate[$label_title][$md5] = $description_lang;
                } else {
                    $array_translate[$label_title][$md5] = array(
                        $iso_code => str_replace("'", '', $description_lang)
                    );
                }
            }
        }
        fclose($file);

        return $array_translate;
    }

    public function saveTranslations()
    {
        $data_translation = Tools::getValue('array_translation');
        $iso_code_selected = Tools::getValue('lang');

        $file_name = basename($this->translation_dir.'/'.$iso_code_selected.'.php');
        $file_path = $this->translation_dir.'/'.$file_name;

        if (!file_exists($file_path)) {
            touch($file_path);
        }

        if (is_writable($file_path)) {
            $line = '';

            $line .= '<?php'."\n";
            $line .= 'global $_MODULE;'."\n";
            $line .= '$_MODULE = array();'."\n";

            foreach ($data_translation as $key => $value) {
                foreach ($value as $data) {
                    $data['key_translation'] = trim($data['key_translation']);
                    $data['value_translation'] = trim($data['value_translation']);

                    $line .= '$_MODULE[\'<{'.$this->name.'}prestashop>'.$key.'_';
                    $line .= $data['key_translation'].'\']  = \'';
                    $line .= str_replace("'", "\'", $data['value_translation']).'\';'."\n";
                }
            }
            if (!file_put_contents($file_path, $line)) {
                return array(
                    'message_code' => self::CODE_ERROR,
                    'message' => $this->l('An error has occurred while attempting to save the translations')
                );
            } else {
                return array(
                    'message_code' => self::CODE_SUCCESS,
                    'message' => $this->l('The translations have been successfully saved')
                );
            }
        } else {
            return array(
                'message_code' => self::CODE_ERROR,
                'message' => $this->l('An error has occurred while attempting to save the translations')
            );
        }
    }

    public function shareTranslation()
    {
        $iso_code = Tools::getValue('iso_code');
        $file_name = basename($this->translation_dir.'/'.$iso_code.'.php');
        $file_path = $this->translation_dir.'/'.$file_name;

        if (file_exists($file_path)) {
            $file_attachment = array();
            $file_attachment['content'] = Tools::file_get_contents($file_path);
            $file_attachment['name'] = $iso_code.'.php';
            $file_attachment['mime'] = 'application/octet-stream';

            $sql = 'SELECT id_lang FROM '._DB_PREFIX_.'lang WHERE iso_code = "en"';
            $id_lang = DB::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

            if (empty($id_lang)) {
                $sql = 'SELECT id_lang FROM '._DB_PREFIX_.'lang WHERE iso_code = "es"';
                $id_lang = DB::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            }

            $data = Mail::Send(
                $id_lang,
                'test',
                $_SERVER['SERVER_NAME'].' '.$this->l('he shared a translation with you'),
                array(),
                'info@presteamshop.com',
                null,
                null,
                null,
                $file_attachment,
                null,
                _PS_MAIL_DIR_,
                null,
                $this->context->shop->id
            );

            if ($data) {
                return array(
                    'message_code' => self::CODE_SUCCESS,
                    'message' => $this->l('Your translation has been sent, we will consider it for future upgrades of the module')
                );
            }
        }

        return array(
            'message_code' => self::CODE_ERROR,
            'message' => $this->l('An error has occurred to attempt send the translation')
        );
    }

    public function downloadFileTranslation()
    {
        $iso_code = Tools::getValue('iso_code');
        $file_name = basename($this->translation_dir.'/'.$iso_code.'.php');
        $file_path = $this->translation_dir.'/'.$file_name;

        if (file_exists($file_path)) {
            header("Content-Disposition: attachment; filename=".$iso_code.'.php');
            header("Content-Type: application/octet-stream");
            header("Content-Length: ".filesize($file_path));
            readfile($file_path);
            exit;
        }
    }

    public function codeEditors()
    {
        $code_editors = array(
            'css' => array(
                array(
                    'filepath' => realpath(_PS_MODULE_DIR_.$this->name.'/views/css/front/override.css'),
                    'filename' => 'override',
                    'content' => Configuration::get('ZPP_OVERRIDE_CSS')
                )
            ),
            'javascript' => array(
                array(
                    'filepath' => realpath(_PS_MODULE_DIR_.$this->name.'/views/js/front/override.js'),
                    'filename' => 'override',
                    'content' => Configuration::get('ZPP_OVERRIDE_JS')
                )
            )
        );

        return $code_editors;
    }

    public function saveContentCodeEditors()
    {
        $content = urldecode(Tools::getValue('content'));
        $filepath = urldecode(Tools::getValue('filepath'));

        if (!in_array(basename($filepath), array('override.css', 'override.js'))) {
            return array('message_code' => self::CODE_ERROR);
        }

        if (!file_exists($filepath)) {
            touch($filepath);
        } elseif (is_writable($filepath)) {
            $filetype = pathinfo($filepath);
            if ($filetype['extension'] === 'css') {
                Configuration::updateValue('ZPP_OVERRIDE_CSS', $content);
            } elseif ($filetype['extension'] === 'js') {
                Configuration::updateValue('ZPP_OVERRIDE_JS', $content);
            }

            $this->fillConfigVars();

            file_put_contents($filepath, $content);
        }

        return array('message_code' => self::CODE_SUCCESS, 'message' => $this->l('The code was successfully saved'));
    }
    /* hooks */
    public function hookHeader()
    {
        if ((Tools::getIsset('controller') && Tools::getValue('controller') != 'product')
            || !Tools::getIsset('controller')
        ) {
            return;
        }

        $id_product = Tools::getValue('id_product');

        $product = new ProductCore($id_product, false, $this->context->language->id);
        $images = $product->getImages($this->context->language->id);

        $ids_thumbnails = array();
        foreach ($images as $image) {
            $ids_thumbnails[] = $id_product.'-'.$image['id_image'];
        }

        $this->smarty->assign(array(
            'zoomproductpro_dir' => $this->_path,
            'zoomproductpro_img' => $this->_path.'views/img/',
            'zoomproductpro_zoom_assets' => $this->_path.'views/js/lib/zoom_assets/',
            'CONFIGS' => $this->config_vars,
            'ids_thumbnails' => $ids_thumbnails,
            'ZPP_STATIC_TOKEN' => Tools::encrypt('zoomproductpro/index'),
            'link_rewrite_product' => $product->link_rewrite,
            'zoomproductpro_360' => $this->dir_360,
            'ACTIONS_CONTROLLER_URL'=> $this->context->link->getModuleLink($this->name, 'actions'),
        ));

        $this->addFrontOfficeCSS(_PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.css', 'all');
        $this->addFrontOfficeCSS(($this->_path).'views/css/front/'.$this->name.'.css', 'all');
        $this->addFrontOfficeCSS(($this->_path).'views/css/front/override.css', 'all');
        $this->addFrontOfficeCSS(($this->_path).'views/css/lib/bootstrap/pts/pts-bootstrap.css', 'all');

        $this->addFrontOfficeJS(_PS_JS_DIR_.'jquery/plugins/fancybox/jquery.fancybox.js');
        $this->addFrontOfficeJS(($this->_path).'views/js/lib/zoom_assets/jquery.smoothZoom.js');
        $this->addFrontOfficeJS(($this->_path).'views/js/lib/pts/tools.js');
        $this->addFrontOfficeJS(($this->_path).'views/js/lib/bootstrap/pts/bootstrap.min.js');
        $this->addFrontOfficeJS(($this->_path).'views/js/lib/jquery/plugins/reel/jquery.reel.js');
        $this->addFrontOfficeJS(($this->_path).'views/js/front/'.$this->name.'.js');
        $this->addFrontOfficeJS(($this->_path).'views/js/front/override.js');

        return $this->display(__FILE__, 'views/templates/hook/header.tpl');
    }

    public function getThumbnailsProduct()
    {
        $id_product = Tools::getValue('id_product');
        $id_lang = $this->context->language->id;
        $product = new Product($id_product, false, $id_lang);
        $images = $product->getImages($id_lang);

        $ids_thumbnails = array();
        foreach ($images as $image) {
            $ids_thumbnails[] = $this->context->link->getImageLink(
                $product->link_rewrite,
                $id_product.'-'.$image['id_image'],
                'medium'
            );
        }

        return array('message_code' => self::CODE_SUCCESS, 'data' => $ids_thumbnails);
    }

    public function verify360ProductImages()
    {
        $ext = $this->config_vars['ZPP_IMAGES_EXTENSION'];

        $id_product = Tools::getValue('id_product');
        $dir_product = $this->dir_360.$id_product;
        
        if (is_dir($dir_product)) {
            $total_imagenes = count(glob($dir_product."/{*".$ext."}", GLOB_BRACE));

            if ($total_imagenes > 1) {
                $dir_store = $this->getUrlStore().$this->context->shop->getBaseURI();
                $dir_images = $dir_store.'zoomproductpro_360/'.$id_product.'/';
                list($width, $height) = getimagesize($dir_images.'01'.$ext);

                return array(
                    'message_code' => self::CODE_SUCCESS,
                    'image' => '01'.$ext,
                    'dir_images' => $dir_images,
                    'frames' => $total_imagenes - 1,
                    'width_img' => $width,
                    'height_img' => $height
                );
            }
        }

        return array('message_code' => self::CODE_ERROR);
    }
}
