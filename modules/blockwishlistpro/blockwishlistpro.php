<?php
/**
* BLOCKWISHLISTPRO Front Office Feature - display products of a list, creator's view
*
* @author    Denis Deleval / alize-web.fr <contact@alizeweb.fr>
* @copyright Alizé Web
* @license   Non-exclusive license
 * The module is protected by copyright and other intellectual property laws
 * This license does not grant any reseller privileges
 * The module is identified as “Not-For-Resale” and may not be sold or otherwise transferred
 * You may not rent, redistribute, lease, lend, sell the module (even if you modify files), decompile, reverse engineer, or disassemble the module
 * Non exclusive license for one web site
 * Installation and use:  You may install, use, access, display and run one copy of the module for single website only
 * Multiple sites : buy as many licenses as the number of websites
 * You may modify files (.TPL, PHP, JS, CSS) provided with the module for the purpose of enhancing or customizing the product
 * but you cannot sell modified files
*/

if (!defined('_PS_VERSION_'))
	exit;
/* compatible with PS 1.6x / 1.5x (and not PS 1.2x, 1.3x, 1.4x)
*/
class BlockWishListpro extends Module
{
	const INSTALL_SQL_FILE = 'install.sql';
	private $_html = '';
	private $_postErrors = array();
	const MODULENAME = 'blockwishlistpro';
	const SUFFIX = '_pro';

	public function __construct()
	{
		$this->name = 'blockwishlistpro';
		$this->tab = 'front_office_features';
		$this->version = '2.8.1';
		$this->author = 'Alize Web';
		$this->need_instance = 1;
		$this->bootstrap = false;
		$this->opt_tablefilter = 'picnet_table.filter';
		if (version_compare(_PS_VERSION_, '1.6', '>='))
			$this->bootstrap = true;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
		parent::__construct();

		$this->displayName = $this->l('WishlistPro');
		$this->description = $this->l('Add a block containing the customer\'s wishlists. Information and dashboards about wishlists, PDF and e-mails.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		$this->module_key = 'd2fe0039b98751c5883201da67820634';

		$isHttps = $this->isHTTPS();
		$this->ssl_base = 'http';
		if ($isHttps)
			$this->ssl_base = 'https';

		/*donators'page and managmt page: grid or list mode*/
		$this->type_display_default = 'grid'; /*'grid' or 'list' by default*/
		$this->type_display_init = 'none'; /*Just for the first page visited - 'catg' or 'none' - if 'catg' : check grid/list mode already selected on category page, then if yes apply this choice by default; if 'none', apply the $type_display_default mode*/
	}

	public function install()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<')) //uncorrect version
			die('<div class="alert error"><p>'.$this->l('Sorry, this version of Wishlistpro is not compatible with your Prestashop version').'.</p>
			<p>Wishlistpro '.$this->version.' <-#-> Prestashop '._PS_VERSION_.'</p><br />
			<p>'.$this->l('It seems you did not download the right version from Addons. Actually when you download the module on Addons you have to select the version of the module').'. </p>
			<p>'.$this->l('Please install the right version of the module.').'</p></div>');

		/*check if wishlistpro table already exists and if yes insert 2 fields `id_shop` and `id_shop_group` if need be*/
		$name = _DB_PREFIX_.'wishlist'.self::SUFFIX;
		if (self::table_exist($name, 1))
		{
			if (self::existTableColumn($name, 'id_shop') === false)
				$exec = Db::getInstance()->Execute('
					ALTER TABLE  `'.$name.'` ADD  `id_shop` INT( 11 ) UNSIGNED NOT NULL DEFAULT  \''.(int)Shop::getContextShopID().'\' AFTER  `counter`,	ADD  `id_shop_group` INT( 11 ) UNSIGNED NOT NULL DEFAULT  \''.(int)Shop::getContextShopGroupID().'\' AFTER  `id_shop`');
		}

/* !$this->registerHook('orderConfirmation') */
		$paramFoTheme = 0;
		$paramBoTheme = 0;
		if (version_compare(_PS_VERSION_, '1.6', '>='))
		{
			$paramFoTheme = 1;
			$paramBoTheme = 1;
		}
		if (!parent::install() || !$this->registerHook('displayHome') || !$this->registerHook('displayMyAccountBlock') || !$this->registerHook('displayCustomerAccount') || !$this->registerHook('productActions') ||	!$this->registerHook('cart') ||	!$this->registerHook('displayHeader') ||	!$this->registerHook('actionValidateOrder') || !$this->registerHook('adminCustomers') || !$this->registerHook('displayBackOfficeHeader') || !$this->registerHook('displayBackOfficeTop') || !Configuration::updateValue('PS_WISHLISTPRO_BO_THEME', $paramBoTheme) || !Configuration::updateValue('PS_WISHLISTPRO_FO_THEME', $paramFoTheme) || !Configuration::updateValue('PS_WISHLISTPRO_DISPLAYHOME', 0) || !Configuration::updateValue('PS_WISHLISTPRO_DISPLAYHOMEWIDTH', 12) || !Configuration::updateValue('PS_WISHLISTPRO_ACTIVE', 1) || !Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wishlist_pro`(`id_wishlist` int(10) unsigned NOT NULL auto_increment,
			  `id_customer` int(10) unsigned NOT NULL,
			  `token` varchar(64) character set utf8 NOT NULL,
			  `name` varchar(64) character set utf8 NOT NULL,
			  `counter` int(10) unsigned NULL,
			  `id_shop` int(11) unsigned NOT NULL,
			  `id_shop_group` int(11) unsigned NOT NULL,
			  `date_add` datetime NOT NULL,
			  `date_upd` datetime NOT NULL,
			  `published` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`id_wishlist`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8') || !Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wishlist_email_pro` (
			  `id_wishlist` int(10) unsigned NOT NULL,
			  `email` varchar(128) character set utf8 NOT NULL,
			  `date_add` datetime NOT NULL
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8') || !Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wishlist_product_pro` (
			  `id_wishlist_product` int(10) NOT NULL auto_increment,
			  `id_wishlist` int(10) unsigned NOT NULL,
			  `id_product` int(10) unsigned NOT NULL,
			  `id_product_attribute` int(10) unsigned NOT NULL,
			  `quantity_rel` int(10) NOT NULL,
			  `quantity` int(10) unsigned NOT NULL,
			  `quantity_init` int(10) unsigned NOT NULL,
			  `quantity_left_rel` int(10) NOT NULL,
			  `quantity_left` int(10) unsigned NOT NULL,
			  `priority` int(10) unsigned NOT NULL,
			  `alert_qty` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`id_wishlist_product`)
			) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8') || !Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wishlist_product_cart_pro` (
			`id` int(10) unsigned NOT NULL auto_increment,
			`id_wishlist_product` int(10) unsigned NOT NULL,
			`id_cart` int(10) unsigned NOT NULL,
			`quantity` int(10) unsigned NOT NULL,
			`price_init` FLOAT UNSIGNED NOT NULL,
			`price_wt_init` FLOAT UNSIGNED NOT NULL,
			`date_add` datetime NOT NULL,
 			PRIMARY KEY  (`id`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8') || !Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wishlist_send_pdf_pro` (
			`id_wishlist` int(10) unsigned NOT NULL,
			`id_order` int(10) unsigned NOT NULL,
			`date_send_pdf` datetime NOT NULL
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8') || !Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'wishlist_automatic_sending_pro` (
			`automatic` int(10) unsigned NOT NULL ,
			`copy` int(10) unsigned NOT NULL
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'))
			return false;

		// Hook either on left or right column
		if (version_compare(_PS_VERSION_, '1.6', '>='))
		{
			$theme = new Theme(Context::getContext()->shop->id_theme);
			if (isset($theme->default_right_column) && $theme->default_right_column)
				$this->registerHook('rightColumn');
			else
				$this->registerHook('leftColumn');
		}
		else
			$this->registerHook('leftColumn');


		$result = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'wishlist_automatic_sending'.self::SUFFIX.'`');
		if (empty($result))
			Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'wishlist_automatic_sending'.self::SUFFIX.'` (`automatic`,`copy`)
			VALUES ('.(int)(1).', '.(int)(0).')');

		/* This hook is optional */
		if (version_compare(_PS_VERSION_, '1.5.5.0', '>='))
			$this->registerHook('displayMyAccountBlockfooter');
		return true;
	}
	public function uninstall()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<')) /*uncorrect version*/
			return (Configuration::deleteByName('PS_WISHLISTPRO_ACTIVE') && Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'`') && Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wishlist_email'.self::SUFFIX.'`') && Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'`') && Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'`') && Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wishlist_automatic_sending'.self::SUFFIX.'`') && Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'wishlist_send_pdf'.self::SUFFIX.'`') && parent::uninstall());
		else
			return (Configuration::deleteByName('PS_WISHLISTPRO_ACTIVE') && Configuration::deleteByName('PS_WISHLISTPRO_BO_THEME') && Configuration::deleteByName('PS_WISHLISTPRO_FO_THEME') && Configuration::deleteByName('PS_WISHLISTPRO_DISPLAYHOME') && Configuration::deleteByName('PS_WISHLISTPRO_DISPLAYHOMEWIDTH') && parent::uninstall());
	}

/*automatic email sending after order Validation (better than order confirmation in case of card, paypal payment)*/
	public function hookActionValidateOrder($params)
	{
		/*order id checking*/
		if (!isset($params['order']->id))
			return false;
		$id_order = $params['order']->id;
		if ((int)$id_order < 1)
			return false;
		/*database query*/
		$temp = (Db::getInstance()->ExecuteS('
		SELECT `automatic`, `copy`
		FROM `'._DB_PREFIX_.'wishlist_automatic_sending'.self::SUFFIX.'`
		'));
		if ((int)$temp[0]['automatic'] == 1)
		{
			if ($this->_automatic_mail($id_order, $temp))
				return true;
			return false;
		}
	}


	private function _automatic_mail($id_order, $temp)
	{
		require_once(dirname(__FILE__).'/../../config/config.inc.php');
		require_once(dirname(__FILE__).'/../../init.php');
//		require_once(dirname(__FILE__).'/../../config/settings.inc.php');
/*if (!file_exists(dirname(__FILE__).'/../../config/defines.inc.php')) require_once(dirname(__FILE__).'/defines_oldversion.inc.php'); /*old version (<= 1.2)*/
//else require_once(dirname(__FILE__).'/../../config/defines.inc.php');
//		require_once(dirname(__FILE__).'/../../images.inc.php');
		include_once(dirname(__FILE__).'/WishListpro.php');
		$context = Context::getContext();
		$cookie = $context->cookie;
		$id_lang = (!isset($cookie) || !is_object($cookie)) ? (int)Configuration::get('PS_LANG_DEFAULT') : (int)$context->language->id;
		$module = new BlockWishListpro();
		$link = new Link();
		/*find out wishlist id based on order id*/
		$array_wl = WishListpro::getWishlistId($id_order);
		if (!empty($array_wl))
		{
				$id_wishlist = $array_wl['id_wishlist'];
				$gather = BlockWishListpro::gatherInfoPdf_tcpdf($id_wishlist, $id_lang);
				/*look at the choice in the db*/
				$choice_automatic_copy_pdf_email = (int)$temp[0]['copy'];
				/*send PDF by MAIL*/
				$wishlist = new WishListpro($id_wishlist);
				if (!Validate::isLoadedObject($wishlist))
				die(Tools::displayError('Cannot find wishlist in database'));
				$wishlist1 = WishListpro::exists($id_wishlist, $wishlist->id_customer, true);
				if ($wishlist1 === false)
					exit(Tools::displayError('Invalid wishlist'));
				$customer = new Customer($wishlist->id_customer);
				if (Validate::isLoadedObject($customer))
				{
					$template = 'mail_to_creator';
					$subject = $module->l('Information about wishlist').' '.utf8_encode(html_entity_decode(htmlentities($wishlist1['name'], ENT_COMPAT, 'UTF-8'))).'"';
					$fileAttachment = array();
					$templateVars = array(
						'{lastname}' => $customer->lastname,
						'{firstname}' => $customer->firstname,
						'{wishlist}' => $wishlist1['name'],
						'{shop_phone}' => (string)Configuration::get('PS_SHOP_PHONE'),
/*						'{message}' => 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.BlockWishListpro::MODULENAME.'/view.php?token='.$wishlist1['token']);*/
						'{message}' => $link->getModuleLink('blockwishlistpro', 'view', array('token' => pSQL($wishlist1['token']))));
					$to = $customer->email;
					$toName = $customer->firstname.' '.$customer->lastname;
					$from = (string)Configuration::get('PS_SHOP_EMAIL');
					$fromName = (string)Configuration::get('PS_SHOP_NAME');
					$temp_name = 'point_temp.pdf';
					$fileAttachment['content'] = $gather[0]->Output($temp_name, 'S');
					$name_extension = $module->l('report').'-'.$wishlist1['name'].'-'.$toName;
					/* retrieve '.' from $name_extension to avoid issue with file name extension | .pdf*/
					$name_extension = str_replace('.', '-', $name_extension);
					$name_extension = str_replace(' ', '-', $name_extension);
					$name_extension = $name_extension.'.pdf';
					$fileAttachment['name'] = $name_extension;
					$fileAttachment['mime'] = 'application/pdf';
					$modeSMTP = null;
					$templatePath = dirname(__FILE__).'/mails/';
					$send_mail_creator = Mail::Send($id_lang, $template, $subject, $templateVars, $to, $toName, $from, $fromName, $fileAttachment, $modeSMTP, $templatePath);
				}
				if ($choice_automatic_copy_pdf_email == 1)
				{
					$template = 'mail_to_creator_copy_to_shop';
					$subject = $module->l('Copy - Information about wishlist').' '.$wishlist1['name'].'"';
					$templateVars = array(
						'{lastname}' => $customer->lastname,
						'{firstname}' => $customer->firstname,
						'{wishlist}' => $wishlist1['name'],
						'{shop_phone}' => (string)Configuration::get('PS_SHOP_PHONE'),
/*						'{message}' => 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.BlockWishListpro::MODULENAME.'/view.php?token='.$wishlist1['token']);*/
						'{message}' => $link->getModuleLink('blockwishlistpro', 'view', array('token' => pSQL($wishlist1['token']))));
					$to = $customer->email;
					$to = (string)Configuration::get('PS_SHOP_EMAIL');
					$toName = (string)Configuration::get('PS_SHOP_NAME');
					$from = (string)Configuration::get('PS_SHOP_EMAIL');
					$fromName = (string)Configuration::get('PS_SHOP_NAME');
					$send_mail_creator_copy_to_shop = Mail::Send($id_lang, $template, $subject, $templateVars, $to, $toName, $from, $fromName, $fileAttachment, $modeSMTP, $templatePath);
				}
		} /*end if ! empy array_wl*/
	}

/*-------------------------------------------------------------------------*/
	public function getContent()
	{
		include_once(dirname(__FILE__).'/WishListpro.php');
		if (Tools::getIsset('adminSelectList'))
		{
			$id_customer = Tools::getValue('idCustomer');
			$submi = Tools::getValue('submit');
			$trigger = Tools::getValue('trigger');
			$token = Tools::getValue('token_module');
			$id_empl = Tools::getValue('id_employee');
			$tok = Tools::getAdminToken('AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)$id_empl);
			$id_secondlist_select = Tools::getValue('id_secondlist_select');
			$chain = '';
			if ($token == $tok)
			{
				if ($id_customer != -1)
				{
					$wishlists = WishListpro::getByIdCustomer($id_customer);

					if ($trigger == 'boMangmt')
						$chain = "<select name='".htmlentities($id_secondlist_select, ENT_COMPAT, 'UTF-8')."' id='".htmlentities($id_secondlist_select, ENT_COMPAT, 'UTF-8')."' onchange=\"$('input[type=radio][name=scenario_bo_instore]').prop('checked', false); hidedisplayDiv1Div2(['section_products', 'submitBoinStoreDon', 'submitBoinStoreWithdraw', 'don_detail']);\">";
					else
						$chain = '<select name="'.htmlentities($id_secondlist_select, ENT_COMPAT, 'UTF-8').'" id="'.htmlentities($id_secondlist_select, ENT_COMPAT, 'UTF-8').'" onchange="document.forms[\'listing\'].submit();">';

					$chain .= '<option value="-1" selected disabled class="graytext nodisplay">'.$this->l('Select a list').'</option>';
					if (isset($wishlists))
					{
						foreach ($wishlists as $i => $wishlist)
						{
							$chain .= '<option value="'.(int)$wishlist['id_wishlist'].'">';
							$chain .= htmlentities($wishlist['name'], ENT_COMPAT, 'UTF-8').' </option>';
						}
					}
					$chain .= '</select>';
				}
				else
					$chain = "<select name='".htmlentities($id_secondlist_select, ENT_COMPAT, 'UTF-8')."'><option value='-1'>--</option>
				</select>";
			}
			else
				$chain = 'connection issue - aslt';
			die(Tools::jsonEncode($chain));
		}

		/* products not in list, bo management */
		if (Tools::getIsset('adminSelectPrice'))
		{
			$id_customer = Tools::getValue('idCustomer');
			$submi = Tools::getValue('submit');
			$token = Tools::getValue('token_module');
			$id_empl = Tools::getValue('id_employee');
			$tok = Tools::getAdminToken('AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)$id_empl);
			$countl = (int)Tools::getValue('countl');
			$name = Tools::getValue('name');

			if (isset($id_customer) && $token == $tok)
			{
				$ip_ipa = Tools::getValue('ip_ipa');
				$name = Tools::getValue('name');
				$qty = Tools::getValue('qty');
				$id_lang = (int)Tools::getValue('id_lang');
				$msg_empty_cust = Tools::getValue('msg_empty_cust');
				if ($id_customer <= 0)
				{
					echo "<span class='alert_red'>".htmlentities($msg_empty_cust, ENT_COMPAT, 'UTF-8').'</span>';
					return;
				}
				$cookie = new Cookie('temp'); // global $cookie impossible because this file is outside admin directory but call from admin directory
				$cookie->customer = $id_customer;
				$cookie->id_lang = $id_lang;

				$price_f = '';
				$trimm = explode('-', $ip_ipa);
				$id_product = (int)$trimm[0];
				$id_product_attribute = (int)$trimm[1];

				$obj = new Product((int)$id_product, false, (int)$id_lang);
				if (!Validate::isLoadedObject($obj))
					die (Tools::displayError('Cannot load product object'));
				else
				{
					$price_f = self::getPriceCatalogaw($obj, $id_product_attribute, true);
					$price_f_wot = self::getPriceCatalogaw($obj, $id_product_attribute, false);
				}
			}
			else
				die(Tools::jsonEncode('connection issue'));

			$chain = '';
			$chain .= '
			<tr id="notlist_'.$ip_ipa.'" class="bordertop">
				<td class="pdg4"><span class="synth_select_bo_magmt h4 blockinlineaw">'.$name.'</span></td>
				<td class="align_right">'.Tools::displayPrice($price_f).'</td>
				<td class="align_center">
					<input type="hidden" name="notlist_count" value="'.$countl.'">
					<input type="hidden" name="notlist_pricewt_'.$countl.'" value="'.$price_f.'">
					<input type="hidden" name="notlist_pricewot_'.$countl.'" value="'.$price_f_wot.'">
					<input type="hidden" name="notlist_ipipa_'.$countl.'" value="'.$ip_ipa.'">
					<input type="hidden" name="notlist_name_'.$countl.'" value="'.$name.'">
					<input class="width_50 blockinlineaw align_right" type="text" name="notlist_qty_'.$countl.'" id="ipipa_'.$ip_ipa.'" value="'.$qty.'">
				</td>
				<td class="align_center">
					<a onclick="remove_pdt_bo_mngt(this, \''.$this->l('Do you want to remove this product ?').'\');return false;" href="#"><img src="../img/admin/delete.gif" alt="" title="'.$this->l('Delete').'" /></a>
				</td>
			</tr>';
			die(Tools::jsonEncode($chain));
		}

		$this->_html = $this->headerHTML();
		$this->_html .= '<h2 class="noprint">'.$this->displayName.' - '.$this->version.'</h2>
						<div id="help_guide"'.(version_compare(_PS_VERSION_, '1.6.0', '>=')? "style='margin-top:0'" : '').'>
							<a href="'._MODULE_DIR_.self::MODULENAME.'/readme_'.Language::getIsoById($this->context->language->id).'.pdf" target="_blank" title="'.$this->l('Need help ? Refer to the complete userguide PDF...').'">
							<img src="'._MODULE_DIR_.self::MODULENAME.'/views/img/icon/help2.png" alt="'.$this->l('Help ? Refer to the complete userguide PDF').'" title="'.$this->l('Need help ? Refer to the complete userguide PDF...').'" />
							'.$this->l('Help - User guide').'
							<img src="'._MODULE_DIR_.self::MODULENAME.'/views/img/icon/pdf.gif" alt="'.$this->l('Help ? Refer to the complete userguide PDF').'" title="'.$this->l('Need help ? Refer to the complete userguide PDF...').'" />
							</a>
						</div>
		<hr class="separationd" />';
		if (Tools::isSubmit('submitSettings'))
		{
			$activated = Tools::getValue('activated');
			$temp = Configuration::get('PS_WISHLISTPRO_ACTIVE');
			if ($activated != 0 && $activated != 1)
				$this->_html .= '<div class="alert error alert-warning">'.$this->l('Activate module : Invalid choice.').'</div>';
			else
				Configuration::updateValue('PS_WISHLISTPRO_ACTIVE', (int)$activated);
				if ((int)$activated != (int)$temp)
				$this->_html .= '<div class="conf confirm alert alert-success">'.$this->l('Settings updated').'</div>';
		}
		/*automatic email sending, db update*/
		if (Tools::isSubmit('submitAutomatic'))
		{
			$activate = Tools::getValue('email_gift_crea') ? 1 : 0;
			$copy = Tools::getValue('email_gift_copy') ? 1 : 0;
			/*database query*/
			$temp = (Db::getInstance()->ExecuteS('
			SELECT `automatic`, `copy`
			FROM `'._DB_PREFIX_.'wishlist_automatic_sending'.self::SUFFIX.'`
			'));

			if ($activate != 0 && $activate != 1)
				$this->_html .= '<div class="bootstrap"><div class="alert error alert-warning">'.$this->l('Automatic sending : Invalid choice.').'</div></div>';
			else
				$set_init = Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'wishlist_automatic_sending'.self::SUFFIX.'`
					SET `automatic`='.$activate.',
						`copy`='.$copy.'
				');
			if (((int)$activate != (int)$temp[0]['automatic']) || ((int)$copy != (int)$temp[0]['copy']))
				$this->_html .= '<div class="bootstrap"><div class="conf confirm alert alert-success"> '.$this->l('Settings updated').'</div></div>';
		}
		if (Tools::isSubmit('submitHomeDisplay'))
		{
			$typ = array('homeDisp', 'colWidth');
			$confName = array('PS_WISHLISTPRO_DISPLAYHOME', 'PS_WISHLISTPRO_DISPLAYHOMEWIDTH');
			$text = array($this->l('Display the block on homepage (hook displayHome)'), $this->l('Column width'));
			foreach ($confName as $k => $value)
			{
				$param = Tools::getValue($typ[$k], (int)Tools::getValue($value));
				if ($k != 0 && !in_array($param, array(12,4,3,6,8,9)))
					$this->_html .= '<div class="alert error">'.$this->l('Column width').' : '.$this->l('Invalid choice').'</div>';
				elseif ($k == 0 && !in_array($param, array(0, 1)))
					$this->_html .= '<div class="alert error alert-warning">'.$text[$k].' : '.$this->l('Invalid choice').'</div>';
				else
				{
					Configuration::updateValue($value, (int)$param);
					$this->_html .= '<div class="conf confirm alert alert-success">'.$this->l('Settings updated').' : '.$text[$k].'</div>';
				}
			}
		}

		if (Tools::isSubmit('submitThemeFoBo'))
		{
			$activatedFo = Tools::getValue('themeChoice');
			$tempFo = Configuration::get('PS_WISHLISTPRO_FO_THEME');
			$activatedBo = Tools::getValue('themeBOChoice');
			$tempBo = Configuration::get('PS_WISHLISTPRO_BO_THEME');
			if ($activatedFo != 0 && $activatedFo != 1 && $activatedBo != 0 && $activatedBo != 1)
				$this->_html .= '<div class="alert error alert-warning">'.$this->l('Theme : Invalid choice.').'</div>';
			else
			{
				Configuration::updateValue('PS_WISHLISTPRO_FO_THEME', (int)$activatedFo);
				Configuration::updateValue('PS_WISHLISTPRO_BO_THEME', (int)$activatedBo);

			}
			if (((int)$activatedFo != (int)$tempFo) || ((int)$activatedFo != (int)$tempFo))
				$this->_html .= '<div class="conf confirm alert alert-success">'.$this->l('Settings updated').' : '.$this->l('Theme choice').'</div>';
		}
		/*data recovery vs native module (blockwishlist)*/
		if (Tools::isSubmit('submitOverwrite'))
			$this->_html .= '<div class="conf confirm">'.$this->dataRecover('submitOverwrite').'</div>';
		elseif (Tools::isSubmit('submitImport'))
			$this->_html .= '<div class="conf confirm">'.$this->dataRecover('submitImport').'</div>';

		$this->_displayForm();
		return ($this->_html);
	}

/*-------------------------------------------------------------------------*/
	private function _displayForm()
	{
		include_once(dirname(__FILE__).'/WishListpro.php');
		$check_extra_fields = WishListpro::checkFields(); /*check if this module is installed over the previous one and add extra fields*/
		$this->_displayFormSettings();
		$this->_displayFormView();
	}
/*-------------------------------------------------------------------------*/
	private function _displayFormSettings()
	{
		if (version_compare(_PS_VERSION_, '1.6', '>='))
		{
			$chPlus = '<span id="morep" style="display:none"><i class="icon-plus-square right"></i></span>';
			$chLess = '<span id="lessp"><i class="icon-minus-square right"></i></span>';
		}
		else
		{
			$chPlus = '<img src="'.$this->_path.'views/img/icon/more.gif" width="10" height="10" id="morep" style="display:none" />';
			$chLess = '<img src="'.$this->_path.'views/img/icon/less.gif" width="10" height="10" id="lessp" />';
		}

		$this->_html .= '<div class="conf confirm" id="message_confirm" style="display:none"></div>';
		$this->_html .= '
		<div class="cockpit_select_wide" id="settings">
			<div class="name_block_cockpit_wide">
				<img src="'.$this->_path.'logo.gif" alt="" title="" />&nbsp;'.$this->l('Settings').'
				&nbsp;<a href="'.Tools::htmlentitiesUTF8(Tools::safeOutput($_SERVER['REQUEST_URI'])).'" onclick="visibility(Array(\'activation\'));return false;" >'.$chPlus.$chLess.'</a>
			</div>';
		$this->_html .= '
			<div id="activation">
			<form id="active" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" class="form-horizontal noprint">
				<fieldset>';
		$this->_html .= '<h2><img src="'.$this->_path.'views/img/icon/manage_pdt.gif" alt="" /> '.$this->l('Block module activation').'</h2>
				<div class="panel">
					<div class="panel-heading  thClassic">
							<p><img src="'.$this->_path.'views/img/icon/list-next.gif" style="margin-bottom:3px" alt="" /> '.$this->l('Display the block').'</p>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-5 mg_bottom6">
							<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="'.$this->l('in the right column and "Add to my wishlist" button on product page').'">'.$this->l('in the right column and on product page').'</span>
						</label>
						<div class="col-lg-4 thClassic">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="activated" id="activated_on" value="1" '.(Configuration::get('PS_WISHLISTPRO_ACTIVE') == 1 ?  'checked="checked"' : '').'>
								<label for="activated_on">'.$this->l('Yes').'</label>
								<input type="radio" name="activated" id="activated_off" value="0" '.(Configuration::get('PS_WISHLISTPRO_ACTIVE') == 0 ?  'checked="checked"' : '').'>
								<label for="activated_off">'.$this->l('No').'</label>
								<a class="slide-button btn"></a>
							</span>
						</div>';
						if (version_compare(_PS_VERSION_, '1.6', '<'))
							$this->_html .= '<hr class="separationd" />';
						$this->_html .= '
					</div>
					<div class="panel-footer">
						<button type="submit" value="1" name="submitSettings" class="button btn btn-default pull-right">
						<i class="process-icon-save"></i> '.$this->l('Save').'
						</button>
					</div>
				</div>
				</fieldset>
			</form>';
		/*Activate automatic email sending after purchases*/
			/*Is the automatic sending already activated or disabled ?*/
			$temp = (Db::getInstance()->ExecuteS('
			SELECT `automatic`, `copy`
			FROM `'._DB_PREFIX_.'wishlist_automatic_sending'.self::SUFFIX.'`
			'));
			$automatic_send = 0;
			$automatic_copy = 0;
			if (isset($temp[0]))
			{
				$automatic_send = $temp[0]['automatic'];
				$automatic_copy = $temp[0]['copy'];
			}

		$this->_html .= '<form id="auto_send" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" class="form-horizontal noprint">
			<fieldset id="automatic_sending">';
		$this->_html .= '<h2><img src="'.$this->_path.'views/img/icon/sendtoafriend.png" alt="" /> '.$this->l('Automatic e-mail sending').'</h2>
				<div class="panel">
					<div class="panel-heading">
							<p><img src="'.$this->_path.'views/img/icon/list-next.gif" style="margin-bottom:3px" alt="" /> '.$this->l('After every gift purchase').'</p>
					</div>
					<div class="form-group mg_bottom0">
						<p><em>'.$this->l('Activate automatic e-mail sending after every gift purchase (incl. PDF) ...').'</em></p>
						<label class="control-label col-lg-5 mg_bottom6">
							<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="'.$this->l('Activate automatic e-mail sending to the creator of the list after every gift purchase (incl. PDF)').'" data-original-title="'.$this->l('Activate automatic e-mail sending to the creator of the list after every gift purchase (incl. PDF)').'">'.$this->l('... to the creator of the list').'
							</span>
						</label>
						<div class="col-lg-4 thClassic">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="email_gift_crea" id="crea_on" value="1" '.($automatic_send == 1 ? 'checked="checked" ' : '').'/>
								<label for="crea_on">'.$this->l('Yes').'</label>
								<input type="radio" name="email_gift_crea" id="crea_off" value="0" '.($automatic_send == 0 ?  'checked="checked"' : '').'>
								<label for="crea_off">'.$this->l('No').'</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
					<div class="form-group mg_top6">
						<label class="control-label col-lg-5 mg_bottom6 mg_top6">
							<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="'.$this->l('Select this option to receive a copy of the automatic e-mail sending').'" data-original-title="'.$this->l('Select this option to receive a copy of the automatic e-mail sending').'">'.$this->l('receive a copy').'
							</span>
						</label>
						<div class="col-lg-4 thClassic">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="email_gift_copy" id="copy_crea_on" value="1" '.($automatic_copy == 1 ? 'checked="checked" ' : '').'/>
								<label for="copy_crea_on">'.$this->l('Yes').'</label>
								<input type="radio" name="email_gift_copy" id="copy_crea_off" value="0" '.($automatic_copy == 0 ?  'checked="checked"' : '').'>
								<label for="copy_crea_off">'.$this->l('No').'</label>
								<a class="slide-button btn"></a>
							</span>
						</div>';
						if (version_compare(_PS_VERSION_, '1.6', '<'))
							$this->_html .= '<hr class="separationd" />';
						$this->_html .= '
					</div>

					<div class="panel-footer">
						<button type="submit" value="1" name="submitAutomatic" class="button btn btn-default pull-right">
						<i class="process-icon-save"></i> '.$this->l('Save').'
						</button>
					</div>
			</fieldset>
			</form>';

		/*Activate home display (hook displayHome)*/
		$this->_html .= '<form id="homeDisplay" action="'.$_SERVER['REQUEST_URI'].'" method="post" class="form-horizontal noprint">
			<fieldset>';
		$this->_html .= '<h2><img src="'.$this->_path.'views/img/icon/home.png" alt="" /> '.$this->l('Home display').'</h2>
				<div class="panel">
					<div class="panel-heading">
							<p><img src="'.$this->_path.'views/img/icon/list-next.gif" style="margin-bottom:3px" alt="" /> '.$this->l('Home display activation').'</p>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-5 mg_bottom6">
							<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="'.$this->l('Select YES and the search block will be displayed on Home Page').'">'.$this->l('Display the block on homepage (hook displayHome)').'
							</span>
						</label>
						<div class="col-lg-4 thClassic txtalgnright">
							<span class="switch prestashop-switch fixed-width-lg">
								<input type="radio" name="homeDisp" id="homeDisp_on" value="1" '.(Configuration::get('PS_WISHLISTPRO_DISPLAYHOME') == 1 ?  'checked="checked"' : '').'>
								<label for="homeDisp_on">'.$this->l('Yes').'</label>
								<input type="radio" name="homeDisp" id="homeDisp_off" value="0" '.(Configuration::get('PS_WISHLISTPRO_DISPLAYHOME') == 0 ?  'checked="checked"' : '').'>
								<label for="homeDisp_off">'.$this->l('No').'</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
						<hr class="separationd" />
					</div>
					<div class="panel-heading  thClassic">
							<p><img src="'.$this->_path.'views/img/icon/list-next.gif" style="margin-bottom:3px" alt="" /> '.$this->l('Column width').' ('.$this->l('effective with bootstrap theme').')</p>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-5">
							<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="'.$this->l('Select the width to adjust the size of the block on homepage').'">'.$this->l('Select the width of the block column').'</span>
						</label>
						<div class="col-lg-4 thClassic txtalgnright">
							<div class="radio">
								<label class="t" for="full"> '.$this->l('Full width').'&nbsp;
									<input type="radio" name="colWidth" id="full" value="12" '.(Configuration::get('PS_WISHLISTPRO_DISPLAYHOMEWIDTH') == 12 ? 'checked="checked" ' : '').'/>
								</label>
							</div>
							<div class="radio">
								<label class="t" for="quarter"> '.$this->l('Quarter').' (1/4)&nbsp;<input type="radio" name="colWidth" id="quarter" value="3" '.(Configuration::get('PS_WISHLISTPRO_DISPLAYHOMEWIDTH') == 3 ? 'checked="checked" ' : '').'/>
								</label>
							</div>
							<div class="radio">
								<label class="t" for="third"> '.$this->l('Third').' (1/3)&nbsp;<input type="radio" name="colWidth" id="third" value="4" '.(Configuration::get('PS_WISHLISTPRO_DISPLAYHOMEWIDTH') == 4 ? 'checked="checked" ' : '').'/>
								</label>
							</div>
							<div class="radio">
								<label class="t" for="third2"> '.$this->l('Third').' (2/3)&nbsp;<input type="radio" name="colWidth" id="third2" value="8" '.(Configuration::get('PS_WISHLISTPRO_DISPLAYHOMEWIDTH') == 8 ? 'checked="checked" ' : '').'/>
									</label>
							</div>
							<div class="radio">
								<label class="t" for="quarter3"> '.$this->l('Quarter').' (3/4)&nbsp;<input type="radio" name="colWidth" id="quarter3" value="9" '.(Configuration::get('PS_WISHLISTPRO_DISPLAYHOMEWIDTH') == 9 ? 'checked="checked" ' : '').'/>
								</label>
							</div>
							<div class="radio">
								<label class="t" for="half"> '.$this->l('Half').' (1/2)&nbsp;<input type="radio" name="colWidth" id="half" value="6" '.(Configuration::get('PS_WISHLISTPRO_DISPLAYHOMEWIDTH') == 6 ? 'checked="checked" ' : '').'/>
								</label>
							</div>
						</div>';
						if (version_compare(_PS_VERSION_, '1.6', '<'))
							$this->_html .= '<hr class="separationd" />';
						$this->_html .= '
					</div>

					<div class="panel-footer">
						<button type="submit" value="1" name="submitHomeDisplay" class="button btn btn-default pull-right">
						<i class="process-icon-save"></i> '.$this->l('Save').'
						</button>
					</div>

				</div>
				</fieldset>
			</form>';
		$this->_html .= '<form action="" method="post" class="form-horizontal noprint">
				<fieldset>';
		$this->_html .= '<h2><img src="'.$this->_path.'views/img/icon/color_swatch.png" alt="" /> '.$this->l('Theme choice').'</h2>
				<div class="panel">
					<div class="panel-heading  thClassic">
						<p><img src="'.$this->_path.'views/img/icon/list-next.gif" style="margin-bottom:3px" alt="" /> '.$this->l('Front Office').'</p>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-5">
							<span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" >'.$this->l('Choose the theme applied to buttons').'</span>
						</label>
						<div class="col-lg-4 thClassic txtalgnright">
							<div class="radio">
								<label class="t" for="theme0"><input type="radio" name="themeChoice" id="theme0" value="0" '.(Configuration::get('PS_WISHLISTPRO_FO_THEME') == 0 ? 'checked="checked" ' : '').'/> '.$this->l('Classic').'&nbsp;

								</label>
							</div>
							<div class="radio">
								<label class="t" for="theme1"><input type="radio" name="themeChoice" id="theme1" value="1" '.(Configuration::get('PS_WISHLISTPRO_FO_THEME') == 1 ? 'checked="checked" ' : '').'/> '.$this->l('Modern 1').'  ('.$this->l('Bootstrap compatible').')&nbsp;

								</label>
							</div>
						</div>';
						if (version_compare(_PS_VERSION_, '1.6', '<'))
							$this->_html .= '<hr class="separationd" />';
						$this->_html .= '
					</div>
					<p class="mg_top24"><em>'.$this->l('Do not forget to empty the cache if you want your new choice to be applied (Advanced parameters / Performance').')</em></p>

					<div class="panel-footer">
						<button type="submit" value="1" name="submitThemeFoBo" class="button btn btn-default pull-right">
						<i class="process-icon-save"></i> '.$this->l('Save').'
						</button>
					</div>
				</fieldset>
			</form>';
			$this->_html .= '
			</div>';
		/*---------end  Activate automatic email*/

	/*Data recovery --------------------------------*/
	/*1. Is blockwishlist installed ?*/
	if (Module::isInstalled('blockwishlist'))
	{
		$table = array('wishlist', 'wishlist_email', 'wishlist_product', 'wishlist_product_cart');
		$spy_module = array();
		$test = array();
		/*2.old tables of native blockwishlist module*/
		$spyold = 0;
		$testcontentold = 0;
		foreach ($table as $i => $row)
		{
			/*do they exist ?*/
			if (self::table_exist(_DB_PREFIX_.$table[$i], 1))
			{
				$spyold += 1;
				/*data inside ?*/
				$rc = new ReflectionClass('Db');
					if ($rc->hasMethod('getValue'))
						$test[$i] = WishListpro::checkTableContent(_DB_PREFIX_.$table[$i], 1);
					else
						$test[$i] = WishListpro::checkTableContentOld(_DB_PREFIX_.$table[$i].self::SUFFIX); /*old versions (1.1)*/
				if ($test[$i] != 0) $testcontentold += 1;
			}
		}
		/*3.Do new tables exist ?*/
		$spynew = 0;
		$testcontentnew = 0;
		foreach ($table as $i => $row)
		{
			if (self::table_exist(_DB_PREFIX_.$table[$i].self::SUFFIX, 1))
			{
				$spynew += 1;
				$rc = new ReflectionClass('Db');
					if ($rc->hasMethod('getValue'))
						$test[$i] = WishListpro::checkTableContent(_DB_PREFIX_.$table[$i].self::SUFFIX);
					else
						$test[$i] = WishListpro::checkTableContentOld(_DB_PREFIX_.$table[$i].self::SUFFIX); //old versions (1.1)
				if ($test[$i] != 0) $testcontentnew += 1;
			}
		}
		$this->_html .= '
		<form id="recovery"  method="post" class="noprint">
			<fieldset>
				<div id="recoverydiv" class="cockpit_select">
					<div class="name_block_cockpit_0">'.$this->l('Data recovery').'</div>';
		$this->_html .= '<table class="masterborder titledd">
							<tr>
							<td class="borderdd">'.$this->l('previous module').'<br/>'.$this->l('Wishlist').'<br/>'.$this->l('by Prestashop').'</td>
							<td class="nextdd">&nbsp;&nbsp;<img src="'.$this->_path.'views/img/icon/next.png" />&nbsp;&nbsp;</td>
							<td class="borderdd newdd">'.$this->l('new module').'<br/>'.$this->l('PRO Wishlist').'</td>
							</tr>
							<tr>
							<td class="borderdd '.
								(Module::isInstalled('blockwishlist') ? 'colordd2 ">'.$this->l('installed') : 'colordd3 ">'.$this->l('not installed')).'</td>
							<td class="nextdd"></td>
							<td class="colordd3 borderdd newdd">'.$this->l('installed').'</td>
							</tr>
							<tr>
							<td class="borderdd '.
								(Module::isEnabled('blockwishlist') ?
									'colordd2 ">'.$this->l('enabled') :
									'colordd3 ">'.$this->l('disabled'))
							.'</td>
							<td class="nextdd"></td>
							<td class="newdd '.
								(Module::isEnabled('blockwishlistpro') ?
									'colordd3 ">'.$this->l('enabled') :
									'colordd1 ">'.$this->l('disabled'))
							.'</td>
							</tr>
							<tr>
							<td class="borderdd '.
								($testcontentold == 0 ?
									'">'.$this->l('No Data') :
									'colordd2 ">'.$this->l('Data detected'))
							.'</td>
							<td class="nextdd"></td>
							<td class="newdd '.
								($testcontentnew == 0 ?
									'">'.$this->l('No Data') :
									($testcontentold !== 0 ?
									'colordd1 ">'.$this->l('Data detected') : 'colordd3 ">'.$this->l('Data detected')))
							.'</td>
							</tr></table>';
		if (Module::isInstalled('blockwishlist'))
		{
//		if ($spyold != 0) {
			$id_lang = (int)$this->context->language->id;
			if ($testcontentold != 0)
			{	//previous module data inside
				$this->_html .= '<p><strong style="color:red;font-weight:bold">'.$this->l('The previous wishlist module').'</strong> '.$this->l('(native module by Prestashop)').' <strong style="color:red;font-weight:bold">'.$this->l('should be uninstalled').'</strong>.&nbsp;'.$this->l('Do not forget to back up your database before').'.<br />';

				if ($testcontentnew != 0)
				{	//pro wishlist current data
					$this->_html .= '&nbsp;'.$this->l('Before uninstalling it, do you want to copy (and overwrite) data from the left to the right ?').'<br />';
					$this->_html .= '<input type="submit" class="button" value="'.$this->l('copy and overwrite ').'" name="submitOverwrite"  /><img src="'.$this->_path.'views/img/icon/help.png" alt="'.$this->l('If there are data in the PRO Wishlist module (like wishlists created), they will be overwritten by data of the previous wishlist module').'" title="'.$this->l('If there are data in the PRO Wishlist module (like wishlists created), they will be overwritten by data of the previous wishlist module').'" />
								</p>';
				}
				else
				{						//no data in pro wishlist
					$this->_html .= '&nbsp;'.$this->l('Before uninstalling the previous module, do you want to').' '.$this->l('import data ?').'<br />';
					$this->_html .= '<input type="submit" class="button" value="'.$this->l('import data').'" name="submitImport" /><img src="'.$this->_path.'views/img/icon/help.png" alt="'.$this->l('Data of the previous wishlist module will be imported').'" title="'.$this->l('Data of the previous wishlist module will be imported').'" /><br />';
				}
			}
			else
				$this->_html .= '<p><strong style="color:red;font-weight:bold">'.$this->l('The previous wishlist module').'</strong> '.$this->l('(native module by Prestashop)').' <strong style="color:red;font-weight:bold">'.$this->l('should be uninstalled').'</strong>.&nbsp;';

			$this->_html .= '
					</div>
				</fieldset>
			</form>';
		}
	}

	//------------------------------------------------------------------------

		$this->_html .= '
		<hr class="separationd" />
	</div> ';

	}
	/*-------------------------------------------------------------------------*/
	private function _displayFormView()
	{
		require_once(dirname(__FILE__).'/WishListpro.php');
		include_once(_PS_CLASS_DIR_.'/Language.php');
		include_once(_PS_CLASS_DIR_.'/Tools.php');

		$lkn_module = $this->context->link->getAdminLink('AdminModules').'&configure='.self::MODULENAME.'';
		$token_module = Tools::getAdminToken('AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)$this->context->employee->id);

		$this->_html .= '<script type=\'text/javascript\'>
		// called when selecting a product --> to get the list
		adminSelectList = "'.$lkn_module.'&adminSelectList";
		token_module = "'.$token_module.'";
		id_employee = "'.$this->context->employee->id.'";
		</script>';

		$tab_id = Tab::getCurrentTabId();
		$id_empl = (int)$this->context->employee->id;
		$id_lang = (int)$this->context->language->id;
		$iso_id_lang = Language::getIsoById($id_lang);
		$spy_ps16 = version_compare(_PS_VERSION_, '1.6.0', '>=') ? 1 : 0;

		$link = new Link();
		$linkmodule = rtrim($link->getModuleLink(self::MODULENAME, 'orderswishlists', array(), true));
		$date_format = self::getDateFormatDataTable();

/*date picker*/
		$this->context->controller->addCSS($this->_path.'views/css/style.css', 'all');
		if (version_compare(_PS_VERSION_, '1.6.0.7', '<'))
			$this->context->controller->addCSS($this->_path.'views/css/print.css', 'print');
/*		if ($iso_id_lang != 'en') echo '<script type="text/javascript" src="http://jquery-ui.googlecode.com/svn/trunk/ui/i18n/jquery.ui.datepicker-'.Language::getIsoById($cookie->id_lang).'.js"></script> ' ;*/
/*end date picker*/
		if (version_compare(_PS_VERSION_, '1.6', '<'))
		{
			$this->context->controller->addJqueryUI('ui.datepicker');/*1.5 only*/
			$this->context->controller->addCSS($this->_path.'views/css/bo_theme_classic.css', 'all');
		}
		if (version_compare(_PS_VERSION_, '1.5.3.1', '>='))
		{
			$this->context->controller->addCSS($this->_path.'views/css/bo_dd.css', 'all');
			$this->context->controller->addCSS($this->_path.'views/css/wishlist_dd.css', 'all');
			$this->context->controller->addCSS($this->_path.'views/css/thickbox.css', 'all');
		}
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/jquery/jquery.cluetip.js');
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/jquery/jquery.tablesorter.js');
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/jquery/jquery.tablesorter.pager.js');
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/jquery/jquery.pager.js');
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/jquery/picnet.table.filter.min.js');
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/bo.js');
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/thickbox-params-bo.js');
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/jquery/thickbox-modified.js');
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/ajax-wishlistpro.js');
			$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/doc-ready-wishlistpro.js');

/*dd select customer and wishlist when referring from email-sending (ex summary_table_post) (pdf creation and sending) */
		$id_customer_back = Tools::getIsset(Tools::getValue('customer_back')) ? Tools::getValue('customer_back') : '';
		$id_wishlist_back = Tools::getIsset(Tools::getValue('wishlist_back')) ? Tools::getValue('wishlist_back') : '';
/*end */

/*translations*/
/*only for email-sending (ex summary_table_post).php*/
		$temp1 = $this->l('Information about wishlist');
		$temp2 = $this->l('Copy - Information about wishlist');
		$temp3 = $this->l('Back to cockpit');


/*dd select only customers with wishlist */
			$customers = (Db::getInstance()->ExecuteS('
			SELECT cu.`id_customer`, cu.`email`, cu.`firstname`, cu.`lastname`, w.`date_upd`
			FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w
			JOIN `'._DB_PREFIX_.'customer` cu
			WHERE w.`id_customer` = cu.`id_customer`
			GROUP BY w.`id_customer`
			ORDER BY cu.`lastname` ASC, cu.`firstname` ASC
			'));

/* end dd select only customers with wishlist */
if (count($customers))
{
	if (Tools::getValue('custom') === false && Tools::getValue('id_wishlist') === false)
		$_POST['custom'] = $customers[0]['id_customer'];
	$id_customer = (int)Tools::getValue('custom');
}

		$this->_html .= '<br /><br /><a name="cockpit" id="cockpit"></a>';
		$this->_html .= '
			<div class="cockpit_select_wide">';
		$this->_html .= '
				<div class="name_block_cockpit_wide" id="name_cockpit"><img src="'.$this->_path.'views/img/icon/tab-tools.gif" alt="Cockpit" title="'.$this->l('Manage all the lists').'" />&nbsp;'.$this->l('Cockpit').'</div>';
if (empty($customers))
{
		$this->_html .= '
				<div class="alert_no_data">'.$this->l('No wishlist at this time').'<br />--> '.$this->l('No data').'</div> ';
}
		$this->_html .= '
		<form method="post" id="orders_select" class="mg_top12 col-xs-12 col-md-6'.($spy_ps16 == false ? ' cl_order_view' : '').'" name="orders_select"  onsubmit="return (false);" >
			<fieldset>';
		$this->_html .= '
				<div class="cockpit_select" id="selection_order">';
			$this->_html .= '
					<div class="name_block_cockpit_0">'.$this->l('Orders View').'</div>';
			$this->_html .= '
					<ul>
						<li>
						<input type="radio" name="period_type" value="today" id="today" checked="checked" onClick="PeriodVisibility(\'today\');"/>
						<label for="today" style="float:none;">&nbsp;'.$this->l('today').'</label><br />
						</li>
						<li>
						<input type="radio" name="period_type" value="last 7 days" id="sevendays" onClick="PeriodVisibility(\'sevendays\');" />
						<label for="sevendays" style="float:none;">&nbsp;'.$this->l('last 7 days').'</label><br />
						</li>
						<li>
						<input type="radio" name="period_type" value="last 30 days" id="thirtydays" onClick="PeriodVisibility(\'thirtydays\');"/>
						<label for="thirtydays" style="float:none;">&nbsp;'.$this->l('last 30 days').'</label>
						</li>
						<li>
						<input type="radio" name="period_type" value="selected period" id="period_select" onClick="PeriodVisibility(\'period_select\');" />
						<label for="period_select" style="float:none;">&nbsp;'.$this->l('Select a period (format yyyy-mm-dd)').'</label> <img src="'.$this->_path.'views/img/icon/date.png" alt="'.$this->l('select the calendar to pick up dates').'" title="'.$this->l('select the calendar to pick up dates').'"  onClick="PeriodVisibility(\'calendar\');" />

						<div id="date12" style="display:none">
							'.$this->l('from').' <input type="text" name="date1" id="datepicker" name="datepicker" value="'.date('Y-m-d', time() - 60 * 60 * 24).'" size="12" /> '.$this->l('to').' <input type="text" name="date2" id="datepicker2" name="datepicker2" value="'.$this->l('yyyy-mm-dd').'" onFocus="javascript:document.getElementById(\'datepicker2\').value=document.getElementById(\'datepicker\').value;" size="12"/>
						</div>
						</li>
						<li>
						<input type="radio" name="period_type" value="all" id="allorders"  onClick="PeriodVisibility(\'allorders\');"/>
						<label for="allorders" style="float:none;">&nbsp;'.$this->l('all orders').'</label>
						</li>
					</ul>
			';
/*			$this->_html .= "
					<input type='hidden' name='tabid' value='".$tab_id."' />
					<input type='hidden' name='id_empl' value='".$id_empl."' />
					<input type='hidden' name='token' value='".$token_module."' />
					<input type='hidden' name='id_lang' value='".$id_lang."' />";*/
			$this->_html .= '
					<input class="button" type="submit" name="submit_orders" value=" '.$this->l('Results').' " onClick="form_orders_select(\''.$linkmodule.'\',\''.$id_lang.'\',\''.self::MODULENAME.'\', \''.(int)$this->context->employee->id.'\', \''.$token_module.'\', \''.$tab_id.'\', \''.self::MODULENAME.'\', \''.$this->ssl_base.'\', \''.$date_format.'\')" />
					<span id="loader_orders_select" style="display: none;"><img src="'.$this->_path.'views/img/icon/loader.gif" alt="loading" /></span>';
		$this->_html .= '
			</div>';
		$this->_html .= '</fieldset></form>';

		$this->_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" class="mg_top12 col-xs-12 col-md-6'.($spy_ps16 == false ? ' cl_client_view' : '').'" id="listing" name="listing_name"><fieldset>';
		$this->_html .= '<div class="cockpit_select" id="selection_customer">';
		$this->_html .= '<div class="name_block_cockpit_0">'.$this->l('Customer View').'</div>';

		$this->_html .= '
				<div class="margin-form">';
		$this->_html .= '
					<select id="sel_cust" name="custom" onchange="go_bo_instore(\'loader_list_customers2\', \'id_wishlis\', \'list_store_feed2\', \'sel_cust\');">';

		$id_wishlist = Tools::getValue('id_wishlis');
		$id_customer = Tools::getValue('custom');
		if ($id_customer == false)
			$this->_html .= '<option value="-1" selected="selected">...'.$this->l('Customer').'...</option>';
		else
			$this->_html .= '<option value="-1">...'.$this->l('Customer').'...</option>';

		foreach ($customers as $customer)
		{
			$this->_html .= '<option value="'.(int)$customer['id_customer'].'"';
			if (isset($customer['id_customer']) && (int)$customer['id_customer'] === (int)$id_customer && $id_wishlist)
			{
				if ($id_customer != false)
					$this->_html .= ' selected="selected"';
			}
			$this->_html .= '> '.htmlentities($customer['lastname'], ENT_COMPAT, 'UTF-8').' '.htmlentities($customer['firstname'], ENT_COMPAT, 'UTF-8').' </option>';
		}
		$this->_html .= '
					</select>
					<span id="loader_list_customers2" style="display: none;"><img src="'.$this->_path.'views/img/icon/loader.gif" alt="loading" /></span><br />';

		$wishlists = WishListpro::getByIdCustomer($id_customer);
		if (isset($wishlists))
			if (!count($wishlists))
			{
				$this->_html .= '<p class="alert mg_top12">'.$this->l('No lists for the shop');
				return $this->_html .= '</fieldset></form>';
			}

		$this->_html .= '<div id="list_store_feed2">
					<select name="id_wishlis" id="id_wishlis" onchange="document.forms[\'listing\'].submit();">';

		if ($id_wishlist == false)
			$this->_html .= '<option value="-1" selected="selected">...'.$this->l('list').'...</option>';
		else
			$this->_html .= '<option value="-1">...'.$this->l('list').'...</option>';

		if (isset($wishlists))
			foreach ($wishlists as $wishlist)
			{
				$this->_html .= '<option value="'.(int)$wishlist['id_wishlist'].'"';

				if ($wishlist['id_wishlist'] == $id_wishlist)
					$this->_html .= ' selected="selected"';
				$this->_html .= ' alt="'.htmlentities($wishlist['name'], ENT_COMPAT, 'UTF-8').'" title="'.htmlentities($wishlist['name'], ENT_COMPAT, 'UTF-8').'"> '.htmlentities($wishlist['name'], ENT_COMPAT, 'UTF-8').' </option>';
			}
		$this->_html .= '
					</select></div>
				</div>';
		if (isset($wishlists))
			foreach ($wishlists as $wishlist)
			{
				$tp_arr = array('token'=>pSQL($wishlist['token']));
				$this->_html .= '<input type="hidden" rel="linklist_'.(int)$wishlist['id_wishlist'].'" value="'.$link->getModuleLink('blockwishlistpro', 'view', $tp_arr).'">';
			}

			/*cockpit choices*/
			$this->_html .= '
			<div class="cockpit_choice" id="action_customer">';
			$this->_html .= '
					<ul>
						<li>
							<img src="'.$this->_path.'views/img/icon/pdf.gif" alt="" />
							<a href="#pdf_mail" title="'.$this->l('Open or send by email a PDF summarizing up all the gifts and messages of the list ').'" onClick="resultsVisibility(\'results_pdfmail\',\'results\',\'results_cust\'); return true;">'.$this->l('PDF and MAIL').'</a></li>
						<li>
							<img src="'.$this->_path.'views/img/icon/gold.gif" alt="" />
							<a href="#total_donations" title="'.$this->l('Find out the total donations ').'" onClick="resultsVisibility(\'results_cust\',\'results\',\'results_pdfmail\'); return true;">'.$this->l('Total donations').'</a></li>
						<li>
							<img src="'.$this->_path.'views/img/icon/copy_files.gif" alt="" />
							<a href="#detail_orders" title="'.$this->l('Check the details of every orders and offered products ').'" onClick="resultsVisibility(\'results_cust\',\'results\',\'results_pdfmail\'); return true;">'.$this->l('Detail - Orders').'</a></li>
						<li>
							<img src="'.$this->_path.'views/img/icon/AdminCarts.gif" alt="" />
							<a href="#detail_products" title="'.$this->l('For each product in the list, see the purchased and the remaining quantities as well as the priority').'" onClick="resultsVisibility(\'results_cust\',\'results\',\'results_pdfmail\'); return true;">'.$this->l('Detail - Products of the list').'</a></li>';

if (isset($wishlists))
	$this->_html .= '
						<li>
							<img src="'.$this->_path.'views/img/icon/AdminGroups.gif" alt=""  />
							<a href="javascript:;" target="_blank" title="'.$this->l('Go to the web page of the list as a donator').'" onclick="BoLinkList($(\'#id_wishlis option:selected\').val(),\''.$this->_path.'\', \''.self::MODULENAME.'\');return false;">'.$this->l('List as internet user').'</a></li>';
else
	$this->_html .= '&nbsp;<br />';

			$this->_html .= '
					</ul>
				</div>';
			$this->_html .= '
			</div>';
		$this->_html .= '
		</fieldset></form><hr  style="clear:both; visibility:hidden"/>';
		$this->_html .= '</div>';
		$this->_html .= '<br style="clear:both" />';

	/*----------- orders table -------------------*/
		$this->_html .= ' <div id="results">';
		$this->_html .= '
		<div id="results_area">
		<!--receive data from orders_wishlists.php via ajax ajaxOrdersWishlists -->';
		/* do not erase, for translation purpose : $this->l('Back to cockpit')*/
		$this->_html .= '
	 </div></div>';

	/*-------------------------------------------*/
		if (isset($wishlists) && Validate::isUnsignedId($id_wishlist))
			$this->_displayProducts((int)$id_wishlist);
	}

	/*-----back office--------------------------*/
	public function headerHTML()
	{
		$condition1 = Tools::getValue('controller');
		$condition2 = Tools::getValue('configure');
		if ($condition1 === 'adminmodules' && $condition2 === 'blockwishlistpro')
		{
			$this->context->controller->addCSS(($this->_path).'views/css/bo_dd.css', 'all');
			$this->context->controller->addCSS(($this->_path).'views/css/wishlist_dd.css', 'all');
			$this->context->controller->addCSS(($this->_path).'views/css/thickbox.css', 'all');
		}
	}

	/*----front office------*/
	public function hookHeader()
	{
		$this->page_name = Dispatcher::getInstance()->getController();
		$this->context->controller->addCSS(($this->_path).'views/css/wishlist_dd.css', 'all');
		$this->context->controller->addCSS(($this->_path).'views/css/thickbox.css', 'all');
		if (version_compare(_PS_VERSION_, '1.6.0.0', '>='))
			$this->context->controller->addCSS($this->_path.'views/css/myaccount_wlp.css', 'all');
		if (Configuration::get('PS_WISHLISTPRO_FO_THEME') == 1)
			$this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');

		$plugins_folder = _PS_JS_DIR_.'jquery/plugins/';
		$this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/jquery.scrollTo.js');
		$this->context->controller->addJS(_MODULE_DIR_.self::MODULENAME.'/views/js/ajax-wishlistpro.js');
	}

	/*----display block------*/
	public function hookLeftColumn($params)
	{
		$tplName = 'blockwishlist.tpl';
		if (isset($params['hookTrigger']) && $params['hookTrigger'] == 'dispHome')
			$tplName = 'blockwishlist-home.tpl';

			if (Configuration::get('PS_WISHLISTPRO_ACTIVE') == 0)
				return null;
			require_once(dirname(__FILE__).'/WishListpro.php');
			if ($this->context->customer->isLogged())
			{
				$wishlists = WishListpro::getByIdCustomer($this->context->customer->id);
				if (empty($params['cookie']->id_wishlist) === true || WishListpro::exists($this->context->cookie->id_wishlist, $this->context->customer->id) === false)
				{
					if (!count($wishlists))
						$id_wishlist = false;
					else
					{
						$id_wishlist = (int)$wishlists[0]['id_wishlist'];
						$params['cookie']->id_wishlist = (int)$id_wishlist;
					}
				}
				else
					$id_wishlist = $params['cookie']->id_wishlist;

				/*list of wishlist products still in database (not cancelled)*/
				$list_epur = $id_wishlist == false ? false : WishListpro::getProductByIdCustomer($id_wishlist, $params['cookie']->id_customer, $params['cookie']->id_lang, null, true);
				if ($list_epur != false)
					foreach ($list_epur as $i => $row)
					{
						$obj = new Product((int)$row['id_product'], false, (int)Context::getContext()->language->id);
						if (!Validate::isLoadedObject($obj))
							$list_epur[$i]['isobj'] = 0; /*isobj=0 means a bo cancelled product but still in list*/
						else
							$list_epur[$i]['isobj'] = 1; /*isobj=1 still in list and database*/
					}
				$this->smarty->assign(array(
					'id_wishlist' => $id_wishlist,
					'isLogged' => true,
					'wishlist_products' => $list_epur,
					'wishlists' => $wishlists,
					'ptoken' => Tools::getToken(false)));
			}
			else
				$this->smarty->assign(array('wishlist_products' => false, 'wishlists' => false));

			$this->smarty->assign(array(
				'wishlist_link' => $this->context->link->getModuleLink('blockwishlistpro', 'mywishlist'),
				'themeChoice' => Configuration::get('PS_WISHLISTPRO_FO_THEME'),
				'blockWidth' => Configuration::get('PS_WISHLISTPRO_DISPLAYHOMEWIDTH'),
				'search_link' => $this->context->link->getModuleLink('blockwishlistpro', 'searchlist'),
				'modulename'=> self::MODULENAME,
				'id_lang' => isset($params['cookie']->id_lang) ? (int)$params['cookie']->id_lang : (int)Configuration::get('PS_LANG_DEFAULT')
			));

			if (isset($params['hookTrigger']) && $params['hookTrigger'] == 'dispHome')
				$this->smarty->assign('hookTrigger', 'dispHome');

		$this->smarty->assign(array(
			'themeChoice' => Configuration::get('PS_WISHLISTPRO_FO_THEME'),
			'blockWidth' => Configuration::get('PS_WISHLISTPRO_DISPLAYHOMEWIDTH')
		));

			return ($this->display(__FILE__, 'views/templates/front/'.$tplName));
	}

	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}

	public function hookDisplayHome($params)
	{
		if ($this->page_name === 'index' && Configuration::get('PS_WISHLISTPRO_DISPLAYHOME'))
		{
			$params['hookTrigger'] = 'dispHome';
			return $this->hookLeftColumn($params);
		}
	}

	/*-----product page-------------*/
	public function hookProductActions($params)
	{
			if (Configuration::get('PS_WISHLISTPRO_ACTIVE') == 0)
				return (null);
			$this->hookRightColumn($params);

			$this->context->smarty->assign('id_product', (int)Tools::getValue('id_product'));
			$this->context->smarty->assign('modulename', self::MODULENAME);

			return ($this->display(__FILE__, 'views/templates/front/blockwishlist-extra.tpl'));
	}

	/*----------------------*/
	public function hookDisplayCustomerAccount()
	{
		$this->smarty->assign('in_footer', false); /*to display gift img*/
		return $this->display(__FILE__, 'views/templates/front/my-account.tpl');
	}
	/*------------------------*/
	public function hookMyAccountBlock()
	{
		return $this->hookDisplayMyAccountBlock();
	}

	public function hookDisplayMyAccountBlock()
	{
		if (version_compare(_PS_VERSION_, '1.5.5.0', '>='))
			$this->smarty->assign('in_footer', false);
		else
			$this->smarty->assign('in_footer', true);
		return $this->display(__FILE__, 'views/templates/front/my-account.tpl');
	}

	/*--------------------------*/
	public function hookdisplayMyAccountBlockfooter()
	{
		return $this->display(__FILE__, 'views/templates/front/blockmyaccountfooter.tpl');
	}

	/*-------Back Office--------*/
	public function hookAdminCustomers($params)
	{
		require_once(dirname(__FILE__).'/WishListpro.php');

		$customer = new Customer((int)$params['id_customer']);
		if (!Validate::isLoadedObject($customer))
			die (Tools::displayError());

		$this->_html = '<h2>'.$this->l('Wishlists').'</h2>';

		$wishlists = WishListpro::getByIdCustomer((int)$customer->id);
		if (!count($wishlists))
			$this->_html .= $customer->lastname.' '.$customer->firstname.' '.$this->l('had no wishlist');
		else
		{
			$this->_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="listing">';

			$id_wishlist = (int)Tools::getValue('id_wishlist');
			if (!$id_wishlist)
					$id_wishlist = $wishlists[0]['id_wishlist'];

			$this->_html .= '<span>'.$this->l('Wishlist').': </span> <select name="id_wishlist" onchange="$(\'#listing\').submit();">';
			foreach ($wishlists as $wishlist)
			{
				$this->_html .= '<option value="'.(int)$wishlist['id_wishlist'].'"';
				if ($wishlist['id_wishlist'] == $id_wishlist)
				{
					$this->_html .= ' selected="selected"';
					$counter = $wishlist['counter'];
				}
				$this->_html .= '>'.htmlentities($wishlist['name'], ENT_COMPAT, 'UTF-8').'</option>';
			}
			$this->_html .= '</select>';

			$this->_displayProducts((int)$id_wishlist);

			$this->_html .= '</form><br />';

			return $this->_html;
		}
	}

	/*-----------------------------------------*/
	private function _displayProducts($id_wishlist)
	{
		include_once(dirname(__FILE__).'/WishListpro.php');
		$context = Context::getContext();
		$currency_id = isset($context->currency->id) ? $context->currency->id : $context->cookie->id_currency;
		$current_currency = Currency::getCurrencyInstance($currency_id);
		$current_conv_rate = (float)$current_currency->conversion_rate;
		$id_lang = (int)$this->context->language->id;
		$cookie = $this->context->cookie;
		$wishlist = new WishListpro((int)$id_wishlist);
		$products_wl = WishListpro::getProductByIdCustomer((int)$id_wishlist, (int)$wishlist->id_customer, (int)$id_lang);
		/*-Display | List and Orders details |(extracted from AdminOrders)--*/
		$listcart = WishListpro::getListOrder($id_wishlist);
		$total = array();
		$total['list']['products']['mx'] = 0;
		$total['list']['discounts']['mx'] = 0;
		$total['list']['wrapping']['mx'] = 0;
		$total['list']['shipping']['mx'] = 0;
		$total['list']['paid']['mx'] = 0;
		$total['list']['products']['wl'] = 0;
		$total['list']['paid']['wl'] = 0;

		foreach ($listcart as $row)
		{
			$order = new Order((int)$row['id_order']);
			$currency = new Currency($order->id_currency);
			$ratio_cur = (float)$currency->conversion_rate / $current_conv_rate;
			$total['order'][$row['id_order']] = self::valueDetails_AdminOrders($row['id_order'], $products_wl, (int)$id_wishlist);
			$total['list']['products']['mx'] += $total['order'][$row['id_order']]['products']['mx'] / $ratio_cur;
			$total['list']['discounts']['mx'] += $total['order'][$row['id_order']]['discounts']['mx'] / $ratio_cur;
			$total['list']['wrapping']['mx'] += $total['order'][$row['id_order']]['wrapping']['mx'] / $ratio_cur;
			$total['list']['shipping']['mx'] += $total['order'][$row['id_order']]['shipping']['mx'] / $ratio_cur;
			$total['list']['paid']['mx'] += $total['order'][$row['id_order']]['paid']['mx'] / $ratio_cur;
			$total['list']['products']['wl'] += $total['order'][$row['id_order']]['products']['wl'] / $ratio_cur;
			$total['list']['paid']['wl'] += $total['order'][$row['id_order']]['paid']['wl'] / $ratio_cur;
		}
		/*List level information*/
		$name = WishListpro::getCreatorName($id_wishlist);
		$token1 = $wishlist->token; /*to display link to wishlist on emails wishlist form*/
		$ch = $_SERVER['REQUEST_URI'];
		$customer = new Customer($wishlist->id_customer);
		if (Validate::isLoadedObject($customer))
			$to = $customer->email;
		$lat1 = WishListpro::getListLastOrderMailPDF($id_wishlist);
		$chain_js = '';
		if ($listcart == false)
		$chain_js = $this->l('Sorry, no pdf available because no gifts have been already offered');
		$lat2_spy = WishListpro::getListLastOrder($id_wishlist);
		$lat2_idorder = $lat2_spy ? (int)$lat2_spy['id_order'] : null;
		$msg_confirm = $this->l('Please confirm you want to send the mail to the customer email adress');
		$msg_warning1 = $this->l('* No orders have been made since the last email to that client.');
		$msg_warning2 = $this->l('Do you really want to send this email ?');
		$msg_warning3 = $this->l('for your information the customer email adress is');
		$latest_order_pdfmail = isset($lat1[0]) && isset($lat1[0]['max_order']) ? (int)$lat1[0]['max_order'] : null;
		$lat1_spy = $lat1 == false ? false : true;
		$lat2_spy = $lat2_spy == false ? false : true;
		$msg_confirm = str_replace("'", '&rsquo;', $msg_confirm);
		$msg_warning1 = str_replace("'", '&rsquo;', $msg_warning1);
		$msg_warning2 = str_replace("'", '&rsquo;', $msg_warning2);
		$msg_warning3 = str_replace("'", '&rsquo;', $msg_warning3);


/*area of customer wishlist display div results_cust*/
		$this->_html .= '<div id="results_pdfmail">';
		/*PDF creation form*/
		$this->_html .= '<a name="pdf_mail" id="pdf_mail"></a><br />';
		$this->_html .= '<form action="'.$this->_path.'email-sending.php" method="post" class="noprint" style="margin-bottom:-6px">';
		$this->_html .= '<fieldset style="border-bottom:0">';
			$this->_html .= '
				<legend class="legend_cockpit">';
			$this->_html .= $this->l('PDF and MAIL').' - '.$this->l('List').' '.$this->l(' #').sprintf('%06d', $id_wishlist).' - '.$name[0]['firstname'].' '.$name[0]['lastname'].' - '.$wishlist->name.'&nbsp;&nbsp;';
			$this->_html .= '
					<a href="'.$this->_path.'view.php?token='.$token1.'" target="_blank" title="'.$this->l('Go to the web page of the list as a donator').'">
					<img src="'.$this->_path.'views/img/icon/details.gif" alt="'.$this->l('Go to the web page of the list as a donator').'" /></a>';
			$this->_html .= '
				</legend>';
			$this->_html .= '<a href="#cockpit" class="back_cockpit"><img src="'.$this->_path.'views/img/icon/back(visitors).gif" alt="" />'.$this->l('Back to Cockpit').'</a>';
			$this->_html .= "	<input type='hidden' name='id_list1' value='".$id_wishlist."' />
								<input type='hidden' name='champ1' value='".serialize($total)."' />
								<input type='hidden' name='id_lang' value='".$id_lang."' />
";
			$this->_html .= '<table>';
			$this->_html .= "<tr><td colspan='2'><img src='".$this->_path."views/img/icon/pdf.gif' alt='' /> <b>".$this->l('PDF document with donation details').'</b></td></tr>';
			$this->_html .= "<tr height='6'><td></td></tr>";
		if ($chain_js === '')
		{
			$this->_html .= "<tr><td width='350'><img src='".$this->_path."views/img/icon/list-next.gif' style='margin-bottom:3px' alt='' />".$this->l('Open /read /save PDF').'</td>';
			$this->_html .= '<td width="170" title="'.$this->l('Open /read /save PDF').'"><label class="admin_pdf"><img src="'.$this->_path.'views/img/icon/pdf.gif" alt="" /> <input type="submit" name="submit_summary" value=" '.$this->l('Open PDF').' "/> </label></td>
								<td width="5"></td>
								<td width="225">&nbsp;</td></tr>';
		}
		else
			$this->_html .= '<tr><td><span>'.$chain_js.'</span></td></tr>';
			$this->_html .= '</table>
			</fieldset>
		</form>';
		/*end ------- PDF creation form*/
		/*PDF email sending form*/
		$this->_html .= '<form action="'.$this->_path.'email-sending.php" method="post" onsubmit="javascript:return validation(\''.$to.'\', \''.$id_wishlist.'\', \''.$lat1_spy.'\', \''.$lat2_spy.'\', \''.$lat2_idorder.'\', \''.$msg_confirm.'\', \''.$msg_warning1.'\', \''.$msg_warning2.'\', \''.$msg_warning3.'\');" class="mg_top24 noprint">
			<fieldset  id="pdf_sending">';
			$this->_html .= "
								<input type='hidden' name='id_list2' value='".$id_wishlist."' />
								<input type='hidden' name='id_lang' value='".$id_lang."' />";
			$this->_html .= '<table>';
			$this->_html .= "<tr><td colspan='2'><img src='".$this->_path."views/img/icon/pdf.gif' alt='' /><img src='".$this->_path."views/img/icon/sendtoafriend.png' alt=''/> <b>".$this->l('PDF + E-MAIL').'</b></td></tr>';

			$this->_html .= "<tr height='6'><td></td></tr>";
		if ($chain_js === '')
		{
			$this->_html .= '<tr><td><a class="thickbox admin_pdf" href="'.$this->_path.'mails/'.Language::getIsoById($this->context->language->id).'/mail_to_creator.html?height=460&width=600&KeepThis=true&TB_iframe=true" style="width:260px; padding-bottom:0; height:20px" title="'.$this->l('Logo of your shop and parameters into {} will be personalized when you send PDF').'"> '.$this->l('Find out the email template').' <img src="'.$this->_path.'views/img/icon/slip.gif" alt="" /></a></td></tr>';

			$this->_html .= "<tr><td width='350'><img src='".$this->_path."views/img/icon/list-next.gif' style='margin-bottom:3px' alt='' />".$this->l('Send the PDF to the creator of the list by e-mail').'</td>';
			$this->_html .= '	<td width="170" title="'.$this->l('Send the PDF to the creator of the list by e-mail').'"><label class="admin_pdf"><img src="'.$this->_path.'views/img/icon/pdf.gif" alt="" /> <img src="'.$this->_path.'views/img/icon/sendtoafriend.png" alt=""/> <input type="submit" name="submit_mail" value=" '.$this->l('Send PDF').' " /></label></td>
								<td width="5"></td>
								<td width="270" title="'.$this->l('Select this option to receive a sending confirmation by e-mail').'"> <label class="admin_pdf"><input type="checkbox" name="copy_mail" value="yes" /> '.$this->l('Receive a copy of the e-mail').' </label> </td>
							</tr>';
		}
		else
			$this->_html .= '<tr><td><span>'.$chain_js.'</span></td></tr>';


			$this->_html .= '<tr><td></td></tr>';
			$this->_html .= '</table>
			</fieldset>
		</form>';
		/*end  PDF email sending form*/

	$this->_html .= '</div> ';

		$this->_html .= '<div id="results_cust">';
			$this->_html .= '<br /><a name="total_donations" id="total_donations"></a>
			<form action="" class="noprint mg_bottom12">';
			$this->_html .= '
				<fieldset id="don">';
			$this->_html .= '
				<legend class="total_donation legend_cockpit">'.$this->l('Total Donations').' - '.$this->l('List').' '.$this->l(' #').sprintf('%06d', $id_wishlist).' - '.$name[0]['firstname'].' '.$name[0]['lastname'].' - '.$wishlist->name.'&nbsp;&nbsp;<a href="'.$this->_path.'view.php?token='.$token1.'" target="_blank"><img src="'.$this->_path.'views/img/icon/details.gif" alt="'.$this->l('Go to the web page of the list as a donator').'" title="'.$this->l('Go to the web page of the list as a donator').'"/></a>
				</legend>';
			$this->_html .= '<a href="#cockpit" class="back_cockpit"><img src="'.$this->_path.'views/img/icon/back(visitors).gif" alt="" />'.$this->l('Back to Cockpit').'</a>';

				if (isset($cookie->id_currency) && $cookie->id_currency)
					$temp_currency = new Currency((int)$cookie->id_currency);
				else
					$temp_currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));

				$this->_html .= '
					<div style="margin: 6px 0 2px 20px; clear:both">
						<table class="table" cellspacing="0" cellpadding="0">
							<tr><td width="280px">'.$this->l('Products').'</td><td align="right">'.Tools::displayPrice($total['list']['products']['mx'], $temp_currency, false).'</td><td>|</td><td width="280px" align="left"><b>'.$this->l('incl. products from wishlist').'</b></td><td align="right"><b>'.Tools::displayPrice($total['list']['products']['wl'], $temp_currency, false).'</b></td></tr>
							'.($total['list']['discounts']['mx'] > 0 ? '<tr><td>'.$this->l('Discounts').'</td><td align="right">-'.Tools::displayPrice($total['list']['discounts']['mx'], $temp_currency, false).'</td></tr>' : '').'
							'.($total['list']['wrapping']['mx'] > 0 ? '<tr><td>'.$this->l('Wrapping').'</td><td align="right">'.Tools::displayPrice($total['list']['wrapping']['mx'], $temp_currency, false).'</td></tr>' : '').'
							<tr><td>'.$this->l('Shipping').'</td><td align="right">'.Tools::displayPrice($total['list']['shipping']['mx'], $temp_currency, false).'</td><td></td><td></td><td></td></tr>';
				$this->_html .= '
							<tr><td style="font-size:14px">'.$this->l('Total').'</td><td align="right" style="font-size:14px">'.Tools::displayPrice($total['list']['paid']['mx'], $temp_currency, false).'</td><td></td><td></td><td></td></tr>
						</table>
					</div>';
				$this->_html .= '
				</fieldset>';
				$this->_html .= '
			</form><br />';

		/* order level information*/
				$this->_html .= '
			<form action="" class="noprint mg_bottom12">';
		$this->_html .= '<a name="detail_orders" id="detail_orders"></a>
			<fieldset id="orderd">';
			$this->_html .= '
				<legend class="order_detail legend_cockpit">'.$this->l('Detail - Orders').' - '.$this->l('List').' '.$this->l(' #').sprintf('%06d', $id_wishlist).' - '.$name[0]['firstname'].' '.$name[0]['lastname'].' - '.$wishlist->name.'&nbsp;&nbsp;<a href="'.$this->_path.'view.php?token='.$token1.'" target="_blank"><img src="'.$this->_path.'views/img/icon/details.gif" alt="'.$this->l('Go to the web page of the list as a donator').'" title="'.$this->l('Go to the web page of the list as a donator').'"/></a>
				</legend>';
			$this->_html .= '<a href="#cockpit" class="back_cockpit"><img src="'.$this->_path.'views/img/icon/back(visitors).gif" alt="" />'.$this->l('Back to Cockpit').'</a>';
			/* detail orders section*/
			foreach ($listcart as $row)
			{
				$total_detail = $this->viewDetails_AdminOrders($row['id_order'], $products_wl, $total, (int)$id_wishlist);
				$total['order_detail'][$row['id_order']] = $total_detail;
			}
			$this->_html .= '
			</fieldset>		';

				$this->_html .= '
			</form>';

		/*Products level information*/
		$this->viewDetailsProducts ($id_wishlist, $products_wl, $name);

		$this->_html .= '
			</div>'; /*end of div results_cust*/
	}

	/*----wishlist summary data  to create pdf table ---*/
	public static function data_wl_summary($total)
	{
	$data_wl = array();
	foreach ($total['order_detail'] as $i => $order_line)
		{
				foreach ($order_line as $j => $order_pdt_concat)
					$data_wl[$i.'-'.$j] = $order_pdt_concat;
		}
	return $data_wl;
	}

	/*---------------------------------------------------------------------*/
	/*-get info from Order (from AdminOrders class, viewDetails function)--*/
	/* for PDF 															*/
	public static function getDetails_AdminOrders($id_order, $products_wl, $total, $id_wishlist)
	{
		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
			die(Tools::displayError());

		$context = Context::getContext();
		$id_lang = (int)$context->language->id;
		$customer = new Customer($order->id_customer);
		$carrier = new Carrier($order->id_carrier);
		$history = $order->getHistory($context->language->id);
		$products = $order->getProducts();
		$customizedDatas = Product::getAllCustomizedDatas((int)$order->id_cart);
		Product::addCustomizationPrice($products, $customizedDatas);
		$messages = Message::getMessagesByOrderId($order->id, true);
		$states = OrderState::getOrderStates((int)$context->language->id);
		$currency_id = isset($context->currency->id) ? $context->currency->id : $context->cookie->id_currency;
		$current_currency = Currency::getCurrencyInstance($currency_id);
		$current_conv_rate = (float)$current_currency->conversion_rate;
		$currency = new Currency($order->id_currency);
		$ratio_cur = (float)$currency->conversion_rate / $current_conv_rate;
		$currentLanguage = new Language($id_lang);
		$currentState = $order->getCurrentState();
		$total_detail = array();

		foreach ($products as $k => $product)
		{
	/*Supplier name addition  */
			$temp = Db::getInstance()->getRow('
					SELECT s.`name`
					FROM '._DB_PREFIX_.'supplier s
					JOIN '._DB_PREFIX_.'product p ON (p.`id_supplier` = s.`id_supplier`)
					WHERE p.`id_product` = '.(int)$product['product_id'].'
					GROUP BY s.`name`'
					);
			$product['s_name'] = $temp['name'];
	/*Manufacturer name addition  */
			$temp = Db::getInstance()->getRow('
					SELECT m.`name`
					FROM '._DB_PREFIX_.'manufacturer m
					JOIN '._DB_PREFIX_.'product p ON (p.`id_manufacturer` = m.`id_manufacturer`)
					WHERE p.`id_product` = '.(int)$product['product_id'].'
					GROUP BY m.`name`'
					);
			$product['m_name'] = $temp['name'];

			/* dd Is the product of the order featured in the wishlist ?*/
			/* && has been bought via Offer button (wishlist page), not via Buy button (product page) ? 22/11/2012 * detected with getProductBoughtQty_actual*/
			$bought = array();
			$bought = WishListpro::getProductBoughtQty_actual($id_wishlist, $product['product_id'], $product['product_attribute_id'], $id_order);
			$product['in_wl'] = false;
			foreach ($products_wl as $j => $row2)
			{
				if ($product['product_attribute_id'] == $row2['id_product_attribute'] && $product['product_id'] == $row2['id_product'] && !empty($bought))
					$product['in_wl'] = true;
			}
			$id_idattr = $product['product_id'].'-'.$product['product_attribute_id'];
			$total_detail[$id_idattr]['wl'] = $product['in_wl'];
			$total_detail[$id_idattr]['attributes_small'] = (isset($product['attributes_small']) ? $product['attributes_small'] : '');
			/*image + product/attribute exist ?*/
			$product['isobj'] = 1;
			$product['isobj_attr'] = 1;
			$product['is_attr_exist'] = 1;
			$product['pdt_order_name'] = '';
			$obj = new Product((int)$product['product_id'], false, $id_lang);
			if (!Validate::isLoadedObject($obj))
			{
				$product['isobj'] = 0; /*isobj=0 means a bo cancelled product but still in list*/
				$products[$k]['cover'] = $context->language->iso_code.'-default';
			}
			else
			{
				if ($products[$k]['product_attribute_id'] != 0)
				{
					$combination_imgs = $obj->getCombinationImages($id_lang);
					if ($combination_imgs)
					{
						if (isset($combination_imgs[$products[$k]['product_attribute_id']][0]['id_image']))
							$products[$k]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($products[$k]['id_product'].'-'.$combination_imgs[$products[$k]['product_attribute_id']][0]['id_image']) : $combination_imgs[$products[$k]['product_attribute_id']][0]['id_image']);
						else
						{
							$images = $obj->getImages($id_lang);
							foreach ($images as $j => $image)
							{
								if ($image['cover'])
									$products[$k]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($obj->id.'-'.$image['id_image']) : $image['id_image']);
							}
							if (!isset($products[$k]['cover']))
								$products[$k]['cover'] = Language::getIsoById($id_lang).'-default';
						}
					}
					else
					{
						$images = $obj->getImages($id_lang);
						foreach ($images as $p => $image)
						{
							if ($image['cover'])
								$products[$k]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($obj->id.'-'.$image['id_image']) : $image['id_image']);
						}
						if (!isset($products[$k]['cover']))
							$products[$k]['cover'] = Language::getIsoById($id_lang).'-default';
					}
				}
				else
				{
					$images = $obj->getImages($id_lang);
					foreach ($images as $t => $image)
						if ($image['cover'])
						{
							$products[$k]['cover'] = $obj->id.'-'.$image['id_image'];
							break;
						}
				}
				if (!isset($products[$k]['cover']))
					$products[$k]['cover'] = $context->language->iso_code.'-default';
			}
			$link = new Link();

			if (version_compare(_PS_VERSION_, '1.5', '>=') && version_compare(_PS_VERSION_, '1.5.1', '<'))
				$type_small = 'small';
			elseif (version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.3.0', '<'))
				$type_small = self::getFormatedName('small');
/*				$type_small = Image::getSize(BlockWishListpro::getFormatedName('small'));
*/				/*$type_small = 'small_default';*/
			else
				$type_small = ImageType::getFormatedName('small');

			if (!$context->link)
				$link_img = 'http://'.$link->getImageLink($obj->link_rewrite, $products[$k]['cover'], $type_small);
			else
				$link_img = $context->link->getImageLink($obj->link_rewrite, $products[$k]['cover'], $type_small);

			/* to prevent tcpdf error when no image attached to product */
			if (function_exists('get_headers'))
				$file_headers = @get_headers($link_img);
			$url_exists = isset($file_headers) && is_array($file_headers) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $file_headers[0]) : false;
			$total_detail[$id_idattr]['image'] = $url_exists == 1 ? $link_img : '';

			/* Customization display
			$this->displayCustomizedDatas($customizedDatas, $product, $currency, $image, $tokenCatalog, $k);*/

			/* Normal display */
			if ($product['product_quantity'] > $product['customizationQuantityTotal'])
			{
				/* dd variables setting for synthesis table */
				$rc = new ReflectionClass('Product');
				if ($rc->hasMethod('getTaxCalculationMethod'))
				{
					$total_detail[$id_idattr]['up'] = $order->getTaxCalculationMethod() == PS_TAX_EXC ? $product['product_price'] : $product['product_price_wt'];
					$total_detail[$id_idattr]['total'] = ($order->getTaxCalculationMethod() == PS_TAX_EXC ? $product['product_price'] : $product['product_price_wt']) * ((int)$product['product_quantity'] - $product['customizationQuantityTotal']);
				}
				else
				{
					$total_detail[$id_idattr]['up'] = (int)Configuration::get('PS_TAX') == 0 ? $product['product_price'] : $product['product_price_wt'];
					$total_detail[$id_idattr]['total'] = ((int)Configuration::get('PS_TAX') == 0 ? $product['product_price'] : $product['product_price_wt']) * ((int)$product['product_quantity'] - $product['customizationQuantityTotal']);
				}
				/* multi currency shop*/
				$total_detail[$id_idattr]['up'] = (float)$total_detail[$id_idattr]['up'] / $ratio_cur;
				$total_detail[$id_idattr]['total'] = (float)$total_detail[$id_idattr]['total'] / $ratio_cur;

				$total_detail[$id_idattr]['quantity'] = (int)$product['product_quantity'] - $product['customizationQuantityTotal'];
				$total_detail[$id_idattr]['refunded'] = $order->hasBeenPaid() ? (int)$product['product_quantity_refunded'] : '';
				$total_detail[$id_idattr]['returned'] = $order->hasBeenPaid() ? (int)$product['product_quantity_return'] : '';
				$total_detail[$id_idattr]['product_name'] = $product['product_name'];
				$total_detail[$id_idattr]['product_reference'] = $product['product_reference'];
				$total_detail[$id_idattr]['product_supplier_reference'] = $product['product_supplier_reference'];
				$total_detail[$id_idattr]['product_supplier_name'] = $product['s_name'];
				$total_detail[$id_idattr]['product_manufacturer_name'] = $product['m_name'];
				$total_detail[$id_idattr]['donator_name'] = $customer->lastname;
				$total_detail[$id_idattr]['donator_firstname'] = $customer->firstname;
				$total_detail[$id_idattr]['date'] = $order->date_add;
			}
		}
	if (!isset($total_detail))
		$total_detail = array();
	return ($total_detail);
	}
	/*------------------end function getDetails_AdminOrders----------------*/

	/*---------------------------------------------------------------------*/
	/*----info from Order (from AdminOrders class, viewDetails function)---*/
	public function viewDetails_AdminOrders($id_order, $products_wl, $total, $id_wishlist)
	{
		require_once(_PS_CLASS_DIR_.'/Customer.php');
		require_once(_PS_CLASS_DIR_.'/Language.php');
		require_once(_PS_CLASS_DIR_.'/Link.php');
		require_once(_PS_CLASS_DIR_.'/Product.php');
		require_once(_PS_CLASS_DIR_.'/Tools.php');

		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
			die(Tools::displayError());

		$context = Context::getContext();
		$id_lang = (int)$context->language->id;
		$customer = new Customer($order->id_customer);
		$carrier = new Carrier($order->id_carrier);
		$history = $order->getHistory($id_lang);
		$products = $order->getProducts();
		$customizedDatas = Product::getAllCustomizedDatas((int)$order->id_cart);
		Product::addCustomizationPrice($products, $customizedDatas);
		$discounts = $order->getCartRules();
		$messages = Message::getMessagesByOrderId($order->id, true);
		$states = OrderState::getOrderStates($id_lang);
		$currency = new Currency($order->id_currency);
		$currentLanguage = new Language($id_lang);
		$currency_id = isset($context->currency->id) ? $context->currency->id : $context->cookie->id_currency;
		$current_currency = Currency::getCurrencyInstance($currency_id);
		$current_conv_rate = (float)$current_currency->conversion_rate;

		$link = new Link();
		$row = array_shift($history);

		/*display order header*/
		$tokenOrders = Tools::getAdminToken('AdminOrders'.(int)Tab::getIdFromClassName('AdminOrders').(int)$context->employee->id);
		$this->_html .= '
	<div class="clearfix">';
		$this->_html .= '
			<p class="copy_legend">'.$this->l('Order').$this->l(' #').sprintf('%06d', $order->id).' - '.$this->l('Donor').' : '.$customer->firstname.' '.$customer->lastname.'
			&nbsp;&nbsp;<a target="_blank" href="?controller=adminorders&id_order='.$order->id.'&vieworder&token='.$tokenOrders.'"><img src="'.$this->_path.'views/img/icon/information.png" alt="'.$this->l('Go to  the administrator web page of the order').'" title="'.$this->l('Go to  the administrator web page of the order').'"/></a></p>
		';
			/* Display shipping infos */
			$this->_html .= '
			<div style="float: left; width: 250px; margin-left: 22px; font-size:88%">
				'.$this->l('Carrier:').' '.($carrier->name == '0' ? Configuration::get('PS_SHOP_NAME') : $carrier->name).'<br />
			</div>';
			/* Display date of order */
			$this->_html .= '
			<div style="float: left; width: 250px; margin-left: 80px;text-align:right">
				'.$this->l('Date:').' '.(version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($order->date_add) : Tools::displayDate($order->date_add, $id_lang, false)).'<br />
			</div>';

			/* Display summary order */
			$incl_wl_1 = Tools::displayPrice((float)$total['order'][$order->id]['products']['wl'], (int)$order->id_currency, false);
			$this->_html .= '
			<br />
			<div style="margin: 2px 0 2px 20px; clear:both">
				<table class="table" cellspacing="0" cellpadding="0" style="font-size:100%">
					<tr><td width="200px">'.$this->l('Products').'</td><td align="right" width="120px">'.Tools::displayPrice($order->getTotalProductsWithTaxes(), $currency, false).'</td><td>|</td><td width="200px" align="left"><b>'.$this->l('including products from wishlist').'</b></td><td align="right" width="120px"><b>'.$incl_wl_1.'</b></td></tr>
						'.($order->total_discounts > 0 ? '<tr><td>'.$this->l('Discounts').'</td><td align="right">-'.Tools::displayPrice($order->total_discounts, $currency, false).'</td></tr>' : '').'
						'.($order->total_wrapping > 0 ? '<tr><td>'.$this->l('Wrapping').'</td><td align="right">'.Tools::displayPrice($order->total_wrapping, $currency, false).'</td></tr>' : '').'
					<tr><td>'.$this->l('Shipping').'</td><td align="right">'.Tools::displayPrice($order->total_shipping, $currency, false).'</td><td></td><td></td><td></td></tr>';
			$this->_html .= '
					<tr><td style="font-size:14px;border-bottom:none">'.$this->l('Total').'</td><td align="right" style="font-size:14px;border-bottom:none">'.Tools::displayPrice($order->total_paid, $currency, false).'</font></td><td></td><td></td><td></td></tr>
				</table>
			</div>';
			$this->_html .= '
			<div class="clear" style="margin:0"></div>';

		/*List of products*/
		$this->_html .= '
			<br />
			<div class="mg_top12">';
		$this->_html .= '
				<table cellspacing="0" cellpadding="0" class="table table-condensed">';
		$this->_html .= '
					<tr id="order_product">
						<th align="center" style="width: 60px">&nbsp;</th>
						<th style="width: 160px">'.$this->l('Product').'</th>
						<th style="width: 40px; text-align: center">'.$this->l('In wishlist ?').'</th>
						<th style="width: 80px; text-align: center">'.$this->l('UP').' <sup>*</sup></th>
						<th style="width: 20px; text-align: center">'.$this->l('Qty').'</th>
						'.($order->hasBeenPaid() ? '<th style="width: 20px; text-align: center">'.$this->l('Refunded').'</th>' : '').'
						'.($order->hasBeenDelivered() ? '<th style="width: 20px; text-align: center">'.$this->l('Returned').'</th>' : '').'
						<th style="width: 90px; text-align: center">'.$this->l('Total').' <sup>*</sup></th>
						';
		$this->_html .= '
					</tr>';
		$tokenCatalog = Tools::getAdminToken('AdminProducts'.(int)Tab::getIdFromClassName('AdminProducts').(int)$context->employee->id);

		foreach ($products as $k => $product)
		{
			/*Supplier name addition  */
			$temp = Db::getInstance()->getRow('
					SELECT s.`name`
					FROM '._DB_PREFIX_.'supplier s
					JOIN '._DB_PREFIX_.'product p ON (p.`id_supplier` = s.`id_supplier`)
					WHERE p.`id_product` = '.(int)$product['product_id'].'
					GROUP BY s.`name`'
					);
			$product['s_name'] = $temp['name'];
			/*Manufacturer name addition  */
			$temp = Db::getInstance()->getRow('
					SELECT m.`name`
					FROM '._DB_PREFIX_.'manufacturer m
					JOIN '._DB_PREFIX_.'product p ON (p.`id_manufacturer` = m.`id_manufacturer`)
					WHERE p.`id_product` = '.(int)$product['product_id'].'
					GROUP BY m.`name`'
					);
			$product['m_name'] = $temp['name'];

			// dd Is the product of the order featured in the wishlist ?
			// && has been bought via Offer button (wishlist page), not via Buy button (product page) ? 22/11/2012 * detected with getProductBoughtQty_actual
			$bought = array();
			$bought = WishListpro::getProductBoughtQty_actual($id_wishlist, $product['product_id'], $product['product_attribute_id'], $id_order);
			$product['in_wl'] = false;
			foreach ($products_wl as $j => $row2)
			{
				if ($product['product_attribute_id'] == $row2['id_product_attribute'] && $product['product_id'] == $row2['id_product'] && !empty($bought))
					$product['in_wl'] = true;
			}
//image + product or attribute still in database ?
			$product['isobj'] = 1;
			$product['isobj_attr'] = 1;
			$product['is_attr_exist'] = 1;
			$obj = new Product((int)$product['product_id'], false, $id_lang);
			if (!Validate::isLoadedObject($obj))
			{
				$product['isobj'] = 0; //isobj=0 means a bo cancelled product but still in list
				$link_img = '';
//				continue;
			}
			else
			{
				if ($obj->hasAttributes() > 0)
				{
					if ((int)$product['product_attribute_id'] === 0)
					//Has the product id w/o attr (when it was added to the list) received new attributes afterwards ?
						$product['isobj_attr'] = 0; //isobj_attr =0 means that the product still exists but attributes have been added since the add in the list. So attr 0 is not available anymore.

					else
					{ /*Does the attribute still exist ?*/
						if (Product::getProductAttributePrice($product['product_attribute_id']) === false)
							$product['is_attr_exist'] = 0;

					}
				}
				if ($products[$k]['product_attribute_id'] != 0)
				{
					$combination_imgs = $obj->getCombinationImages($id_lang);
					if ($combination_imgs)
					{
						if (isset($combination_imgs[$products[$k]['product_attribute_id']][0]['id_image']))
							$products[$k]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($products[$k]['id_product'].'-'.$combination_imgs[$products[$k]['product_attribute_id']][0]['id_image']) : $combination_imgs[$products[$k]['product_attribute_id']][0]['id_image']);
						else
						{
							$images = $obj->getImages($id_lang);
							foreach ($images as $j => $image)
							{
								if ($image['cover'])
									$products[$k]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($obj->id.'-'.$image['id_image']) : $image['id_image']);
							}
							if (!isset($products[$k]['cover']))
								$products[$k]['cover'] = Language::getIsoById($id_lang).'-default';
						}
					}
					else
					{
						$images = $obj->getImages($id_lang);
						foreach ($images as $p => $image)
						{
							if ($image['cover'])
								$products[$k]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($obj->id.'-'.$image['id_image']) : $image['id_image']);
						}
						if (!isset($products[$k]['cover']))
							$products[$k]['cover'] = Language::getIsoById($id_lang).'-default';
					}
				}
				else
				{
					$images = $obj->getImages($id_lang);
					foreach ($images as $t => $image)
						if ($image['cover'])
						{
							$products[$k]['cover'] = $obj->id.'-'.$image['id_image'];
							break;
						}
				}
				if (!isset($products[$k]['cover']))
					$products[$k]['cover'] = $context->language->iso_code.'-default';
			$link = new Link();

			if (version_compare(_PS_VERSION_, '1.5', '>=') && version_compare(_PS_VERSION_, '1.5.1', '<'))
				$type_small = 'small';
			elseif (version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.3.0', '<'))
				/*$type_small = Image::getSize(BlockWishListpro::getFormatedName('small'));*/
				$type_small = 'small'.'_'.'default';
			else
				$type_small = ImageType::getFormatedName('small');

			if (!$context->link)
				$link_img = 'http://'.$link->getImageLink($obj->link_rewrite, $products[$k]['cover'], $type_small);
			else
				$link_img = $context->link->getImageLink($obj->link_rewrite, $products[$k]['cover'], $type_small);
			}

				/*Customization display*/
			$this->displayCustomizedDatas($customizedDatas, $product, $currency, $image, $tokenCatalog, $k, $order);

			/*Normal display*/
			if ($product['product_quantity'] > $product['customizationQuantityTotal'])
				{
					/*dd variables setting for synthesis table*/
					$rc = new ReflectionClass('Product');
		$this->_html .= '		<tr>
									<td align="center">'.($link_img ? '<img src="'.$link_img.'" style="float:left;" alt="'.htmlentities($product['product_name'], ENT_COMPAT, 'UTF-8').' " />': '').' </td>
									<td><a class="link_cyber" target="_blank" href="index.php?controller=adminproducts&id_product='.$product['product_id'].'&updateproduct&token='.$tokenCatalog.'" title="'.$this->l('Look at the Product data sheet for more details').'">
										<span class="productName"';

										if ($product['in_wl'] === false)
											$this->_html .= 'style="color:red"';
		$this->_html .= '				>'.$product['product_name'].($product['isobj'] === 0 ? ' - <span class="alert_red">'.$this->l('The product has been removed from database since the gift').'</span>' : (($product['isobj_attr'] === 1 && $product['is_attr_exist'] === 0) ? '<br /><span class="alert_red">'.$this->l('The product still exists but the combination has been removed from database since the gift'): ($product['isobj_attr'] === 0 && $product['id_product_attribute'] != 0 ? '<br /><span class="alert_red">'.$this->l('The product still exists but a combination has been added from database since the gift'):''))).'</span><br />
										'.($product['product_reference'] ? $this->l('Ref:').' '.$product['product_reference'] : '')
										.(($product['product_reference'] && $product['product_supplier_reference']) ? ' / '.$product['product_supplier_reference'] : '')
										.'</a></td>
									<td align="center">';
									if ($product['in_wl'] === false)
										$this->_html .= '<span style="color:red">'.$this->l('no').' <img src="'.$this->_path.'views/img/icon/comment.gif" title="'.$this->l('This product is not part of the list but was ordered along with some products from the list').' ('.$this->l('or has been deleted from database since it was ordered').')."/></span>';
									else
										$this->_html .= $this->l('yes');
		$this->_html .= '</td>
									<td align="center">';
					if ($rc->hasMethod('getTaxCalculationMethod'))
		$this->_html .= Tools::displayPrice($order->getTaxCalculationMethod() == PS_TAX_EXC ? $product['product_price'] : $product['product_price_wt'], $currency, false);
					else
		$this->_html .= Tools::displayPrice((int)Configuration::get('PS_TAX') == 0 ? $product['product_price'] : $product['product_price_wt'], $currency, false);

		$this->_html .= '</td>
									<td align="center" class="productQuantity">'.((int)$product['product_quantity'] - $product['customizationQuantityTotal']).'</td>
									'.($order->hasBeenPaid() ? '<td align="center" class="productQuantity">'.(int)$product['product_quantity_refunded'].'</td>' : '').'
									'.($order->hasBeenDelivered() ? '<td align="center" class="productQuantity">'.(int)$product['product_quantity_return'].'</td>' : '').'
									<td align="center">';
					if ($rc->hasMethod('getTaxCalculationMethod'))
		$this->_html .= Tools::displayPrice(Tools::ps_round($order->getTaxCalculationMethod() == PS_TAX_EXC ? $product['product_price'] : $product['product_price_wt'], 2) * ((int)$product['product_quantity'] - $product['customizationQuantityTotal']), $currency, false);
					else
//ps_round doesn't exist in old versions (1.2...)
		$this->_html .= Tools::displayPrice(round((int)Configuration::get('PS_TAX') == 0 ? $product['product_price'] : $product['product_price_wt'], 2) * ((int)$product['product_quantity'] - $product['customizationQuantityTotal']), $currency, false);
		$this->_html .= '</td>

								</tr>';
							// new calculation of total order if product is not in wishlist
//							if ($product['in_wl'] === false) { $total_order_wl= $total_order_wl-($order->getTaxCalculationMethod() == PS_TAX_EXC ? $product['product_price'] : $product['product_price_wt']);}
//
				}
		}
		$rc = new ReflectionClass('Product'); //old versions (1.2...)
		$this->_html .= '
				</table>
				<div style="margin:15px auto 24px auto;"><sup>*</sup> '.$this->l('According to the group of this customer, prices are printed:').' ';
					if ($rc->hasMethod('getTaxCalculationMethod'))
		$this->_html .= ($order->getTaxCalculationMethod() == PS_TAX_EXC ? $this->l('tax excluded.') : $this->l('tax included.'));
					else
		$this->_html .= ((int)Configuration::get('PS_TAX') == 0 ? $this->l('tax excluded.') : $this->l('tax included.'));


		$this->_html .= (!Configuration::get('PS_ORDER_RETURN') ? '<br />'.$this->l('Merchandise returns are disabled') : '').'
				<br /></div>';
					if (count($discounts))
					{
		$this->_html .= '
					<div style="float:right; width:280px; margin-top:15px;">
					<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
						<tr>
							<th><img src="../img/admin/coupon.gif" alt="'.$this->l('Discounts').'" />'.$this->l('Discount name').'</th>
							<th align="center" style="width: 100px">'.$this->l('Value').'</th>
						</tr>';

						foreach ($discounts as $discount)
						{
		$this->_html .= '
						<tr>
							<td>'.$discount['name'].'</td>
							<td align="center">- '.Tools::displayPrice($discount['value'], $currency, false).'</td>
						</tr>';
						}
				$this->_html .= '
					</table>
					</div>';
					};
		$this->_html .= '
			</div>';
$this->_html .= '<br />
			<div class="mg10-auto-10-0" style="background-color:#9A96B1; height:10px"></div>
		</div>';
	}

/*-----extract from AdminOrders, valuation, to refresh prices total without JScript ----*/
	public static function valueDetails_AdminOrders($id_order, $products_wl, $id_wishlist)
	{
		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
			die(Tools::displayError());

		$context = Context::getContext();
		$customer = new Customer($order->id_customer);
		$products = $order->getProducts();
		$customizedDatas = Product::getAllCustomizedDatas((int)$order->id_cart);
		Product::addCustomizationPrice($products, $customizedDatas);
		$currency = new Currency($order->id_currency);
		$total = array();

		/* Display summary order */
		$total['products']['mx'] = (float)$order->getTotalProductsWithTaxes();
		$total['discounts']['mx'] = $order->total_discounts > 0 ? (float)$order->total_discounts : '';
		$total['wrapping']['mx'] = $order->total_wrapping > 0 ? (float)$order->total_wrapping : '';
		$total['shipping']['mx'] = $order->total_shipping > 0 ? (float)$order->total_shipping : '';
		$total['paid']['mx'] = (float)$order->total_paid;
		/*intialization*/
		$total['paid']['wl'] = $total['paid']['mx'];
		$total['products']['wl'] = $total['products']['mx'];
		foreach ($products as $k => $product)
		{
			/* dd Is the product of the order featured in the wishlist ?*/
			/* && has been bought via Offer button (wishlist page), not via Buy button (product page) ? 22/11/2012 * detected with getProductBoughtQty_actual*/
			$bought = array();
			$bought = WishListpro::getProductBoughtQty_actual($id_wishlist, $product['product_id'], $product['product_attribute_id'], $id_order);
			$product['in_wl'] = false;
			foreach ($products_wl as $j => $row2)
			{
				if ($product['product_attribute_id'] == $row2['id_product_attribute'] && $product['product_id'] == $row2['id_product'] && !empty($bought))
					$product['in_wl'] = true;
			}

			/* Normal display*/
			if ($product['product_quantity'] > $product['customizationQuantityTotal'])
			{
				/* new calculation of total order if product is not in wishlist*/
				if ($product['in_wl'] === false)
				{
					/* total['paid'] and total['products'] are taxes included*/
					$total['paid']['wl'] = $total['paid']['wl'] - (float)$product['product_price_wt'] * $product['product_quantity'];
					$total['products']['wl'] = $total['products']['wl'] - (float)$product['product_price_wt'] * $product['product_quantity'];
				}
			}
		}
	return $total;
	}

	/*-----list of wishlist products : name | bought qty | left qty | priority */
	/*dd change in native method : bought quantity */
	public function viewDetailsProducts($id_wishlist, $products, $name)
	{
/*		global $cookie;*/
		$context = Context::getContext();
		$id_lang = (int)$context->language->id;
		$bought = WishListpro::getBoughtProduct($id_wishlist);
		$wishlist = new WishListpro((int)$id_wishlist);
		if (!Validate::isLoadedObject($wishlist))
			Tools::displayError('cannot find wishlist in databaseXX');
		$token1 = $wishlist->token; /*to display link to wishlist on emails wishlist form*/
		$bought_reel = WishListpro::getBoughtProduct_reel($id_wishlist); /* ordered by cart */
		$cpt_products = count($products);
		$cpt_bought = count($bought);
		$cpt_bought_reel = count($bought_reel);
		for ($i = 0; $i < $cpt_products; ++$i)
		{
			$products[$i]['isobj'] = 1;
			$products[$i]['isobj_attr'] = 1; /*product has combination if 1*/
			$products[$i]['is_attr_exist'] = 1; /* combination of the list exists if 1*/
			$products[$i]['pdt_order_name'] = '';
			$obj = new Product((int)$products[$i]['id_product'], false, $id_lang);
			if (!Validate::isLoadedObject($obj))
			{
				$products[$i]['isobj'] = 0; /*isobj=0 means a bo cancelled product but still in list*/
				$products[$i]['cover'] = '';
				$gett = WishListpro::getProductBoughtQty_actual($id_wishlist, $products[$i]['id_product'], $products[$i]['id_product_attribute']);
				$products[$i]['bought_qty_actual'] = isset($gett[0]['actual_qty']) ? (int)$gett[0]['actual_qty'] : 0;
				$products[$i]['left'] = $products[$i]['quantity_init'] - $products[$i]['bought_qty_actual'];
				$products[$i]['pdt_order_name'] = isset($gett[0]['pdt_order_name']) ? $gett[0]['pdt_order_name'] : '';
				continue;
			}
			else
			{
				if ($obj->hasAttributes() > 0)
				{
					if (Product::getProductAttributePrice($products[$i]['id_product_attribute']) === false)
						$products[$i]['is_attr_exist'] = 0;
				}
				else
				{
				/*case of Product with 1 combination when added to list, then the combination has been deleted. Product swithout combination hence*/
						$products[$i]['is_attr_exist'] = 0;
						$products[$i]['isobj_attr'] = 0;
				}

/*
				if ($obj->hasAttributes() > 0)
				{
					if ((int)$products[$i]['id_product_attribute'] === 0)
						$products[$i]['isobj_attr'] = 0;
					else
					{
						if (Product::getProductAttributePrice($products[$i]['id_product_attribute']) === false)
							$products[$i]['is_attr_exist'] = 0;

					}
				}
*/
				if ($products[$i]['id_product_attribute'] != 0)
				{
					$combination_imgs = $obj->getCombinationImages($id_lang);
					if ($combination_imgs)
					{
						if (isset($combination_imgs[$products[$i]['id_product_attribute']][0]['id_image']))
							$products[$i]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($products[$i]['id_product'].'-'.$combination_imgs[$products[$i]['id_product_attribute']][0]['id_image']) : $combination_imgs[$products[$i]['id_product_attribute']][0]['id_image']);
						else
						{
							$images = $obj->getImages($id_lang);
							foreach ($images as $k => $image)
							{
								if ($image['cover'])
									$products[$i]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($obj->id.'-'.$image['id_image']) : $image['id_image']);
							}
							if (!isset($products[$i]['cover']))
								$products[$i]['cover'] = Language::getIsoById($id_lang).'-default';
						}
					}
					else
					{
						$images = $obj->getImages($id_lang);
						foreach ($images as $k => $image)
						{
							if ($image['cover'])
								$products[$i]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($obj->id.'-'.$image['id_image']) : $image['id_image']);
						}
						if (!isset($products[$i]['cover']))
							$products[$i]['cover'] = Language::getIsoById($id_lang).'-default';
					}
				}
				else
				{
					$images = $obj->getImages($id_lang);
					foreach ($images as $k => $image)
						if ($image['cover'])
						{
							$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
							break;
						}
				}
				if (!isset($products[$i]['cover']))
					$products[$i]['cover'] = $context->language->iso_code.'-default';
			}

			$products[$i]['bought'] = false;
			for ($j = 0, $k = 0; $j < $cpt_bought; ++$j)
			{
				if ($bought[$j]['id_product'] == $products[$i]['id_product'] &&	$bought[$j]['id_product_attribute'] == $products[$i]['id_product_attribute'])
					$products[$i]['bought'][$k++] = $bought[$j];
			}

	/*dd from managewishlist php to calculate bought quantity*/
			$products[$i]['bought_reel'] = false;
			$products[$i]['bought_qty_actual'] = 0;

			for ($j = 0, $k = 0; $j < $cpt_bought_reel; ++$j)
			{
				if ($bought_reel[$j]['id_product'] == $products[$i]['id_product'] && $bought_reel[$j]['id_product_attribute'] == $products[$i]['id_product_attribute'])
				{
					$products[$i]['bought_reel'][$k++] = $bought_reel[$j];
					$products[$i]['bought_qty_actual'] = $products[$i]['bought_qty_actual'] + $bought_reel[$j]['actual_qty'];
					$products[$i]['pdt_order_name'] = isset($bought_reel[$j]['pdt_order_name']) ? $bought_reel[$j]['pdt_order_name'] : '';
				}
			}
			if (isset($products[$i]['bought_qty_actual']))
				$products[$i]['left'] = $products[$i]['quantity_init'] - $products[$i]['bought_qty_actual'];
			else
				$products[$i]['left'] = $products[$i]['quantity_init'];
			if ($products[$i]['left'] < 0)
				$products[$i]['left'] = 0;
		}

		$productBoughts_actual = array();
		foreach ($products as $product)
		{
			if (isset($product['bought_reel']) && count($product['bought_reel']))
				$productBoughts_actual[] = $product;
		}
		$this->_html .= '
		<br /><br /><a name="detail_products" id="detail_products"></a>
		<form action="" class="noprint">
			<fieldset>';
			$this->_html .= '
				<legend class="legend_cockpit">'.$this->l('Detail Products').' - '.$this->l('List').' '.$this->l(' #').sprintf('%06d', $id_wishlist).' - '.$name[0]['firstname'].' '.$name[0]['lastname'].' - '.$wishlist->name.'&nbsp;&nbsp;<a href="'.$this->_path.'view.php?token='.$token1.'" target="_blank"><img src="'.$this->_path.'views/img/icon/details.gif" alt="'.$this->l('Go to the web page of the list as a donator').'" title="'.$this->l('Go to the web page of the list as a donator').'"/></a>
				</legend>';
			$this->_html .= '<a href="#cockpit" class="back_cockpit"><img src="'.$this->_path.'views/img/icon/back(visitors).gif" alt="" />'.$this->l('Back to Cockpit').'</a>';

		$this->_html .= '
			<table class="table" id="detailwl1">
				<thead>
					<tr>
						<th class="first_item" style="width:600px;">'.$this->l('Product').'</th>
						<th class="first_item" style="width:100px;">'.$this->l('Bought Quantity').'</th>
						<th class="item" style="text-align:center;width:100px;">'.$this->l('Left Quantity').'</th>
						<th class="item" style="text-align:center;width:100px;">'.$this->l('Priority').'</th>
					</tr>
				</thead>
				<tbody>';
		$priority = array($this->l('Top'), $this->l('Very Important'), $this->l('Important'), $this->l('Medium'), $this->l('Low'));

		$tokenCatalog = Tools::getAdminToken('AdminProducts'.(int)Tab::getIdFromClassName('AdminProducts').(int)$context->employee->id);
		foreach ($products as $product)
		{
			if ($product['isobj'] === 0)
				$link_img = null;
			else
			{
				$link = new Link();
				$link_img = '';

				if (version_compare(_PS_VERSION_, '1.5', '>=') && version_compare(_PS_VERSION_, '1.5.1', '<'))
					$type_small = 'small';
				elseif (version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.3.0', '<'))
					/*$type_small = Image::getSize(BlockWishListpro::getFormatedName('small'));*/
				$type_small = 'small'.'_'.'default';
				else
					$type_small = ImageType::getFormatedName('small');

				if (!$context->link)
					$link_img = 'http://'.$link->getImageLink($product['link_rewrite'], $product['cover'], $type_small);
				else
					$link_img = $context->link->getImageLink($product['link_rewrite'], $product['cover'], $type_small);
			}

				$this->_html .= '
				<tr>
					<td class="first_item">
						<a href="index.php?controller=adminproducts&id_product='.$product['id_product'].'&updateproduct&token='.$tokenCatalog.'" target="_blank"  title="'.$this->l('Look at the Product data sheet for more details').'" class="inlin-blok fl_aw">
							'.($link_img ? '<img src="'.$link_img.'" style="float:left;" alt="'.htmlentities($product['name'], ENT_COMPAT, 'UTF-8').'" />' : '<span style="display:inline-block;width:46px"></span>').'
						</a>
						<div class="mg_left12 fl_aw width70p">
						<a href="index.php?controller=adminproducts&id_product='.$product['id_product'].'&updateproduct&token='.$tokenCatalog.'" target="_blank" class="link_cyber"  title="'.$this->l('Look at the Product data sheet for more details').'">'.$product['name'].'</a> '.$product['reference'];
				if (isset($product['attributes_small']))
					$this->_html .= '  <i>'.htmlentities($product['attributes_small'], ENT_COMPAT, 'UTF-8').'</i>';
				if ($product['isobj'] === 0)
					$this->_html .= '<span class="alert_red">[id_product '.$product['id_product'].($product['id_product_attribute'] > 0 ? ' / id_product_attribute '.$product['id_product_attribute'].']<br/>' : '').$this->l('The product has been removed from database since the gift').'.<span>';
				else
				{
					if ($product['isobj_attr'] === 0 && $product['id_product_attribute'] != 0)
					{
						$this->_html .= '<br /><span class="alert_red">[id_product '.$product['id_product'].' / id_product_attribute '.$product['id_product_attribute'].']<br />'.$this->l('This product/combination has been removed from database since its addition').'.<span>'.($product['pdt_order_name'] ? '<br />('.$product['pdt_order_name'].')' : '');
						$this->_html .= '<br /><span class="alert_red">[id_product '.$product['id_product'].']<br />'.$this->l('The product still exists, but without any combinations').'.';
					}
					elseif ($product['isobj_attr'] === 1 && $product['is_attr_exist'] === 0)
					{
						if ($product['id_product_attribute'] == 0)
							$this->_html .= '<br /><span class="alert_red">[id_product '.$product['id_product'].']<br />'.$this->l('When the product was added to the list, no combinations were assigned to the product').'. '.$this->l('Meanwhile, some combinations have been added!').'<span>'.($product['pdt_order_name'] ? '<br />('.$product['pdt_order_name'].')' : '');
						else
							$this->_html .= '<br /><span class="alert_red">[id_product '.$product['id_product'].' / id_product_attribute '.$product['id_product_attribute'].']<br />'.$this->l('This product/combination has been removed from database since its addition').'.<br /><span>'.($product['pdt_order_name'] ? '<br />('.$product['pdt_order_name'].')' : '');
					}
				}
/*				if ($product['isobj'] === 1 && $product['isobj_attr'] === 1 && $product['is_attr_exist'] === 0)
				if ($product['isobj'] === 1 && $product['isobj_attr'] === 0 && $product['is_attr_exist'] === 1)
					$this->_html .= '<br /><span class="alert_red">'.$this->l('The product still exists but a combination has been added from database since the gift').'<span>'.($product['pdt_order_name'] ? '<br />('.$product['pdt_order_name'].')' : '');
*/
				$this->_html .= '
						</div>
					</td>
					<td class="item" style="text-align:center;';
						if ((int)$product['bought_qty_actual'] != 0) $this->_html .= 'font-weight:bold; color:black;';
				$this->_html .= '
								">'.(int)$product['bought_qty_actual'].'</td>
					<td class="item" style="text-align:center;">'.(int)$product['left'].'</td>
					<td class="item" style="text-align:center;">'.$priority[(int)$product['priority']].'</td>
				</tr>';
				}
		$this->_html .= '
				</tbody>
			</table>
		</fieldset>
	</form>';
	}

/*-----extract from AdminOrders, add $order parameter, used in viewDetails() ----*/
	private function displayCustomizedDatas(&$customizedDatas, &$product, &$currency, &$image, $tokenCatalog, $id_order_detail, $order)
	{
		/*cancel dd		$order = $this->loadObject();*/
		if (is_array($customizedDatas) && isset($customizedDatas[(int)$product['product_id']][(int)$product['product_attribute_id']]))
		{
			$this->_html .= '
			<tr>
				<td align="center">'.(isset($image['id_image']) ? ImageManager::thumbnail(_PS_IMG_DIR_.'p/'.(int)$product['product_id'].'-'.(int)$image['id_image'].'.jpg',
				'product_mini_'.(int)$product['product_id'].(isset($product['product_attribute_id']) ? '_'.(int)$product['product_attribute_id'] : '').'.jpg', 45, 'jpg') : '--').'</td>
				<td><a href="index.php?tab=AdminCatalog&id_product='.$product['product_id'].'&updateproduct&token='.$tokenCatalog.'">
					<span class="productName">'.$product['product_name'].' - '.$this->l('customized').'</span><br />
					'.($product['product_reference'] ? $this->l('Ref:').' '.$product['product_reference'] : '')
					.(($product['product_reference'] && $product['product_supplier_reference']) ? ' / '.$product['product_supplier_reference'] : '')
					.'</a></td>
				<td align="center">'.Tools::displayPrice($product['product_price_wt'], $currency, false).'</td>
				<td align="center" class="productQuantity">'.$product['customizationQuantityTotal'].'</td>
				'.($order->hasBeenPaid() ? '<td align="center" class="productQuantity">'.$product['customizationQuantityRefunded'].'</td>' : '').'
				'.($order->hasBeenDelivered() ? '<td align="center" class="productQuantity">'.$product['customizationQuantityReturned'].'</td>' : '').'
				<td align="center" class="productQuantity"> - </td>
				<td align="center">'.Tools::displayPrice($product['total_customization_wt'], $currency, false).'</td>
				<td align="center" class="cancelCheck">--</td>
			</tr>';
			foreach ($customizedDatas[(int)$product['product_id']][(int)$product['product_attribute_id']] as $customizationId => $customization)
			{
/*23/7/13*/		$this->_html .= '<tr>';
/*23/7/13*/		if (isset($customization['datas']))
				{
					$this->_html .= '
					<td colspan="2">';
						foreach ($customization['datas'] as $type => $datas)
							if ($type == _CUSTOMIZE_FILE_)
							{
								$i = 0;
								$this->_html .= '<ul style="margin: 4px 0px 4px 0px; padding: 0px; list-style-type: none;">';
								foreach ($datas as $data)
									$this->_html .= '<li style="display: inline; margin: 2px;">
											<a href="displayImage.php?img='.$data['value'].'&name='.(int)($order->id).'-file'.++$i.'" target="_blank"><img src="'._THEME_PROD_PIC_DIR_.$data['value'].'_small" alt="" /></a>
										</li>';
								$this->_html .= '</ul>';
							}
							elseif ($type == _CUSTOMIZE_TEXTFIELD_)
							{
								$i = 0;
								$this->_html .= '<ul style="margin: 0px 0px 4px 0px; padding: 0px 0px 0px 6px; list-style-type: none;">';
								foreach ($datas as $data)
									$this->_html .= '<li>'.($data['name'] ? $data['name'] : $this->l('Text #').++$i).$this->l(':').' '.$data['value'].'</li>';
								$this->_html .= '</ul>';
							}
/*23/7/13*/		$this->_html .= '</td>';
				}
/*23/7/13*/		if (isset($customization['quantity']))
				{
					$this->_html .= '	<td align="center">-</td>
						<td align="center" class="productQuantity">'.$customization['quantity'].'</td>
						'.($order->hasBeenPaid() ? '<td align="center">'.$customization['quantity_refunded'].'</td>' : '').'
						'.($order->hasBeenDelivered() ? '<td align="center">'.$customization['quantity_returned'].'</td>' : '').'
						<td align="center">-</td>
						<td align="center">'.Tools::displayPrice(Tools::ps_round($product['product_price'], 2) * (1 + ($product['tax_rate'] * 0.01)) * ($customization['quantity']), $currency, false).'</td>
						<td align="center" class="cancelCheck">
							<input type="hidden" name="totalQtyReturn" id="totalQtyReturn" value="'.(int)$customization['quantity_returned'].'" />
							<input type="hidden" name="totalQty" id="totalQty" value="'.(int)$customization['quantity'].'" />
							<input type="hidden" name="productName" id="productName" value="'.$product['product_name'].'" />';
					if ((!$order->hasBeenDelivered() || Configuration::get('PS_ORDER_RETURN')) && (int)($customization['quantity_returned'] < (int)$customization['quantity']))
						$this->_html .= '
							<input type="checkbox" name="id_customization['.$customizationId.']" id="id_customization['.$customizationId.']" value="'.$id_order_detail.'" onchange="setCancelQuantity(this, \''.$customizationId.'\', \''.$customization['quantity'].'\')" '.(((int)($customization['quantity_returned'] + $customization['quantity_refunded']) >= (int)$customization['quantity']) ? 'disabled="disabled" ' : '').'/>';
					else
						$this->_html .= '--';
					$this->_html .= '
						</td>
						<td class="cancelQuantity">';
					if ((int)($customization['quantity_returned'] + $customization['quantity_refunded']) >= (int)$customization['quantity'])
						$this->_html .= '<input type="hidden" name="cancelCustomizationQuantity['.$customizationId.']" value="0" />';
					elseif (!$order->hasBeenDelivered() || Configuration::get('PS_ORDER_RETURN'))
						$this->_html .= '
							<input type="text" id="cancelQuantity_'.$customizationId.'" name="cancelCustomizationQuantity['.$customizationId.']" size="2" onclick="selectCheckbox(this);" value="" /> ';
					$this->_html .= ($order->hasBeenDelivered() ? (int)$customization['quantity_returned'].'/'.((int)$customization['quantity'] - (int)$customization['quantity_refunded']) : ($order->hasBeenPaid() ? (int)$customization['quantity_refunded'].'/'.(int)$customization['quantity'] : '')).'
						</td>';
				}
				$this->_html .= '</tr>';
			}
		}
	return $this->_html;
	}
/*------end extract----*/

	/*---------------------------------------------------------------------*/
	/*
	* Display Error from controler
	*/
	public function errorLogged()
	{
		return $this->l('You need to be logged to manage your wishlist');
	}

	/* tables already exist ? yes -> previous module has been installed*/
	public static function table_exist($table, $option = null)
	{
			if (_PS_VERSION_ >= 1.5)
				$runQuery = Db::getInstance()->ExecuteS('SHOW TABLES'); /*array*/
			else
				$runQuery = Db::getInstance()->Execute('SHOW TABLES'); /*resource*/
			/*new table with all database tables*/
			$tables = array();
			if (_PS_VERSION_ >= 1.5)
			{
				foreach ($runQuery as $row)
				{
					$tables[] = $row;
					if (array_search($table, $row))
						return true;
				}
				return false;
			}
			else
				while ($row = mysql_fetch_row($runQuery))
					$tables[] = $row[0];

			/*$table  ? in table with all database tables*/
			if (in_array($table, $tables))
				{
					if ($option === null)
						echo '*la table '.$table.' est dans la base';
					return true;
				}
			else
				return false;
	}

	/*------------------------------------------------------------------------*/
	public static function table_copy($i, $table)
	{
			/*wishlist table*/
			$sql = array();
			if ($i == 0 && version_compare(_PS_VERSION_, '1.6.0.9', '<'))
				$sql[$i] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$table[$i].self::SUFFIX.'` ( `id_wishlist` int( 10 ) unsigned NOT NULL auto_increment, `id_customer` int( 10 ) unsigned NOT NULL ,`token` varchar(64) character set utf8 NOT NULL ,`name` varchar(64) character set utf8 NOT NULL ,`counter` int( 10 ) unsigned NULL ,`id_shop` int(11) unsigned default 1, `id_shop_group` int(11) unsigned default 1, `date_add` datetime NOT NULL ,`date_upd` datetime NOT NULL, PRIMARY KEY ( `id_wishlist` ) ) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8';
			else /*new field default*/
				$sql[$i] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$table[$i].self::SUFFIX.'` ( `id_wishlist` int( 10 ) unsigned NOT NULL auto_increment, `id_customer` int( 10 ) unsigned NOT NULL ,`token` varchar(64) character set utf8 NOT NULL ,`name` varchar(64) character set utf8 NOT NULL ,`counter` int( 10 ) unsigned NULL ,`id_shop` int(11) unsigned default 1, `id_shop_group` int(11) unsigned default 1, `date_add` datetime NOT NULL ,`date_upd` datetime NOT NULL, `default` int(10) unsigned default 0, PRIMARY KEY ( `id_wishlist` ) ) ENGINE = '._MYSQL_ENGINE_.' DEFAULT CHARSET = utf8';

			/*wishlist_email table*/
			if ($i == 1)
				$sql[$i] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$table[$i].self::SUFFIX.'` ( `id_wishlist` int( 10 ) unsigned NOT NULL,`email` varchar( 128 ) NOT NULL,`date_add` datetime NOT NULL ) ENGINE = MyISAM DEFAULT CHARSET = utf8';
			/*wishlist_product table*/
			if ($i == 2)
			{
				$str1 = _DB_PREFIX_.$table[$i];
				$str2 = _DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX;
				Db::getInstance()->Execute('DROP TABLE IF EXISTS  `'.$str2.'` ');
				$show = Db::getInstance()->ExecuteS('SHOW CREATE TABLE `'.$str1.'`');
				$show[0]['Create Table'] = str_replace($str1, $str2, $show[0]['Create Table']);
				$sql[$i] = $show[0]['Create Table'];
			}
			/*wishlist_product_cart*/
			if ($i == 3)
				$sql[$i] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$table[$i].self::SUFFIX.'` ( `id_wishlist_product` int( 10 ) unsigned NOT NULL ,`id_cart` int( 10 ) unsigned NOT NULL ,`quantity` int( 10 ) unsigned NOT NULL ,`date_add` datetime NOT NULL ) ENGINE = MyISAM DEFAULT CHARSET = utf8';
			return $sql[$i];
		}
	/*---------------------------------------------------------------------*/
	/*
	* gathering information to create pdf
	*/
	public static function gatherInfoPdf_tcpdf($id_wishlist, $id_lang0)
	{
	include_once(dirname(__FILE__).'/WishListpro.php');
	include_once(dirname(__FILE__).'/pdfwl-tcpdf.php');
	require_once(_PS_TOOL_DIR_.'/tcpdf/tcpdf.php');
	/* Instance of module class for translations */
	$module = new BlockWishListpro();
	$context = Context::getContext();
	$id_lang = ((int)$id_lang0 >= 0) ? (int)$id_lang0 : (int)Configuration::get('PS_LANG_DEFAULT');
	$wishlist = new WishListpro((int)$id_wishlist);
	if (!Validate::isLoadedObject($wishlist))
		die(Tools::displayError('cannot find wishlist in database'));
	$products = WishListpro::getProductByIdCustomer((int)$id_wishlist, (int)$wishlist->id_customer, $id_lang);

	/*to gather information about List and its Orders  |(extracted from blockwishlist)*/
	$listcart = WishListpro::getListOrder($id_wishlist);
	if ($listcart == false)
		{
			echo $module->l('Sorry, no pdf available because no gifts have been already offered');
			echo '. ';
			echo $module->l('To return to the previous page, please click on the back arrow of your browser');
			echo '.';
			die('');
		}
	$products_wl = $products;
	$currency_id = isset($context->currency->id) ? $context->currency->id : $context->cookie->id_currency;
	$current_currency = Currency::getCurrencyInstance($currency_id);
	$current_conv_rate = (float)$current_currency->conversion_rate;

	$message_donator = array();
	$max_id_order = 0;
	$total = array();
	$total['list']['products']['mx'] = 0;
	$total['list']['discounts']['mx'] = 0;
	$total['list']['wrapping']['mx'] = 0;
	$total['list']['shipping']['mx'] = 0;
	$total['list']['paid']['mx'] = 0;
	$total['list']['products']['wl'] = 0;
	$total['list']['paid']['wl'] = 0;

	foreach ($listcart as $i => $row)
	{
		$order = new Order((int)$row['id_order']);
		if (!Validate::isLoadedObject($order))
			die(Tools::displayError('-obj order-'));
		$currency = new Currency($order->id_currency);
		$ratio_cur = (float)$currency->conversion_rate / $current_conv_rate;

		$total['order'][$row['id_order']] = self::valueDetails_AdminOrders($row['id_order'], $products_wl, (int)$id_wishlist);
		$total['list']['products']['mx'] += $total['order'][$row['id_order']]['products']['mx'] / $ratio_cur;
		$total['list']['discounts']['mx'] += $total['order'][$row['id_order']]['discounts']['mx'] / $ratio_cur;
		$total['list']['wrapping']['mx'] += $total['order'][$row['id_order']]['wrapping']['mx'] / $ratio_cur;
		$total['list']['shipping']['mx'] += $total['order'][$row['id_order']]['shipping']['mx'] / $ratio_cur;
		$total['list']['paid']['mx'] += $total['order'][$row['id_order']]['paid']['mx'] / $ratio_cur;
		$total['list']['products']['wl'] += $total['order'][$row['id_order']]['products']['wl'] / $ratio_cur;
		$total['list']['paid']['wl'] += $total['order'][$row['id_order']]['paid']['wl'] / $ratio_cur;
	}
	/*to gather information about total[order_detail]*/
	foreach ($listcart as $i => $row)
	{
		$total_detail_temp = isset($row['id_order']) ? self::getDetails_AdminOrders($row['id_order'], $products_wl, $total, (int)$id_wishlist) : array('');

		/*to cancel products in order but not in wishlist*/
		foreach ($total_detail_temp as $i => $prd)
		{
			if (isset($prd['wl']))
			{
				if ($prd['wl'] == false)
					unset($total_detail_temp[$i]);
			}
		}
		$total_detail_temp = array_values($total_detail_temp); /*reindexation*/
		$total['order_detail'][$row['id_order']] = $total_detail_temp;

		/*Message of donator*/
		$first_mess = Message::getMessagesByOrderId((int)$row['id_order']);

		$message_donator[$row['id_order']]['message'] = '';
		foreach ($first_mess as $i => $r)
			{
				$message_donator[$row['id_order']]['date'] = isset($r['date_add']) ? $r['date_add'] : '-';
				$message_donator[$row['id_order']]['message'] .= $r['message'].(!empty($r['message']) ? "\n" : '');
/*				$message_donator[$row['id_order']]['message'] .= html_entity_decode($r['message'].(!empty($r['message']) ?"\n" : ''));  								/*problème d'accents : in message table : f&eacute;licitations &agrave;*/
			}
		$order = new Order((int)$row['id_order']);
		if (!Validate::isLoadedObject($order))
			die(Tools::displayError());
		$customer = new Customer($order->id_customer);
		if (Validate::isLoadedObject($customer))
			$message_donator[$row['id_order']]['name'] = $customer->firstname.' '.$customer->lastname;
		/*Message of donator written when clicking gift wrapping (order process)*/
		$gift_mess = WishListpro::getMessageGift(($row['id_order']));
		$message_donator[$row['id_order']]['gift_message'] = '';

		foreach ($gift_mess as $i => $rw)
				$message_donator[$row['id_order']]['gift_message'] .= ($rw['gift_message'].($rw['gift_message'] != '' ? "\n" : ''));
/*				$message_donator[$row['id_order']]['gift_message'] .= utf8_decode($rw['gift_message'].($rw['gift_message'] != '' ? "\n" : '')); //problème d'accents : in orders table : J'espère */

		$message_donator[$row['id_order']]['message'] .= $message_donator[$row['id_order']]['gift_message'];
		/*max of orders to update wishli_send_pdf table*/
		$max_id_order = max($max_id_order, (int)$row['id_order']);
	}
		$data_wl = self::data_wl_summary($total);/*to reorganise data to a simple list | summary data  to create pdf table*/
		$data_list = $total['list'];
		$ttt = array();
		$ttt[0] = MYPDF::WlSummary_tcpdf($id_wishlist, $data_wl, $data_list, $message_donator, $currency_id, $id_lang);
		$ttt[1] = $max_id_order;
		return $ttt;
	}
	/**
	 * Count the number of columns in a table
	 * return  integer if not empty, false if table is empty
	 */
	public static function countTableColumn($table)
	{
		$quer = Db::getInstance()->getRow('
		SELECT *
		FROM `'.$table.'`
		');
		if (isset($quer) && $quer !== false)
			return count($quer);
		return false;
	}

	/*
	 * does the field exist in a table
	 * return true if yes
	 */
	public static function existTableColumn($table, $column)
	{
		$exist_table = Db::getInstance()->ExecuteS('
		SHOW COLUMNS FROM `'.$table.'` LIKE \''.$column.'\'
		');
		return !empty($exist_table);
	}

	/**
	 * Recover old data from native blockwishlist module
	 *
	 */
	private function dataRecover($submit_type)
	{
		require_once(dirname(__FILE__).'/WishListpro.php');
		$in_ch = '';
		$wl_CartBoughtQtyProduct = array();
		$wl_BoughtActualQtyProduct = array();
		/*reset carts (not ordered) before import // otherwise calculation of init quantities false when old carts releasing
				//before reseting calculate quantity by adding cart quantity
						//fields initialization*/
						$wp_table = Db::getInstance()->ExecuteS('
							SELECT *
							FROM `'._DB_PREFIX_.'wishlist_product`
							ORDER BY `id_wishlist`');
						/*add a new field equal to quantity for recovering purpose : quantity_recover = quantity + bought and cart
							//check wether quantity_recover exists and if not exists add the field*/
						$sql = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `'._DB_PREFIX_.'wishlist_product` LIKE \'quantity_recover\'');
						if (empty($sql))
							Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'wishlist_product` ADD `quantity_recover` INT(10) NOT NULL AFTER `priority`');
							/*1st set quantity_recover = quantity by default*/
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'wishlist_product` SET
	`quantity_recover` = `quantity` ');

							/*2nd set quantity_recover = quantity + bought and cart if this is the case*/
						$wp_table_id_wl = Db::getInstance()->ExecuteS('
							SELECT `id_wishlist`
							FROM `'._DB_PREFIX_.'wishlist_product`
							GROUP BY `id_wishlist`');
						foreach ($wp_table_id_wl as $id_wishlist)
						{
							$idwl = $id_wishlist['id_wishlist'];
							$wl_CartBoughtQtyProduct[$idwl] = WishListpro::getCartBoughtQtyProduct_oldversion($idwl);
							$wl_BoughtActualQtyProduct[$idwl] = WishListpro::getBoughtQty_actual_oldversion($idwl);

							foreach ($wp_table as $row)
							{
								if ((int)$row['id_wishlist'] == (int)$idwl)
								{
									foreach ($wl_CartBoughtQtyProduct[$idwl] as $line_product)
									{
										if ($row['id_wishlist_product'] == $line_product['id_wishlist_product'])
										{
											$row['quantity_recover'] = (int)$row['quantity'] + (int)$line_product['cart_quantity'];
											Db::getInstance()->Execute('
											UPDATE `'._DB_PREFIX_.'wishlist_product` SET
											`quantity_recover` = '.(int)$row['quantity_recover'].'
											WHERE `id_wishlist`= '.(int)$row['id_wishlist'].' AND `id_wishlist_product`= '.(int)$row['id_wishlist_product'].'
											' 	);
										break;
										}
									}
								}
							}
						}
				/*end adding cart qty*/

		$wp_table_id_wl = Db::getInstance()->ExecuteS('
			SELECT `id_wishlist`
			FROM `'._DB_PREFIX_.'wishlist_product`
			GROUP BY `id_wishlist`');
			foreach ($wp_table_id_wl as $id_wishlist)
			{
				$idwl = $id_wishlist['id_wishlist'];

				$old_carts = Db::getInstance()->ExecuteS('
				SELECT wp.id_product, wp.id_product_attribute, wpc.id_cart, wp.`id_wishlist_product`
				FROM `'._DB_PREFIX_.'wishlist_product_cart` wpc
				JOIN `'._DB_PREFIX_.'wishlist_product` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
				JOIN `'._DB_PREFIX_.'cart` c ON  (c.`id_cart` = wpc.`id_cart`)
				JOIN `'._DB_PREFIX_.'cart_product` cp ON (wpc.`id_cart` = cp.`id_cart`)
				LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = c.`id_cart`)
				WHERE (wp.`id_wishlist`='.(int)$idwl.' AND o.`id_cart` IS NULL)
				');
				if (isset($old_carts) && $old_carts != false)
					foreach ($old_carts as $old_cart)
					{
						Db::getInstance()->Execute('
							DELETE FROM `'._DB_PREFIX_.'cart_product`
							WHERE id_cart='.(int)$old_cart['id_cart'].' AND id_product='.(int)$old_cart['id_product'].' AND id_product_attribute='.(int)$old_cart['id_product_attribute']
						);
						Db::getInstance()->Execute('
							DELETE FROM `'._DB_PREFIX_.'wishlist_product_cart`
							WHERE id_cart='.(int)$old_cart['id_cart'].' AND `id_wishlist_product`='.(int)$old_cart['id_wishlist_product']
						);
					}
			}
		/*end reset carts*/

		$table = array('wishlist', 'wishlist_email', 'wishlist_product', 'wishlist_product_cart');

		if ($submit_type == 'submitOverwrite')
		{
			/*table backup in case of submitOverwrite
			//backup*/
			foreach ($table as $k => $name_table)
			{
				if (BlockWishListpro::table_exist(_DB_PREFIX_.$table[$k].BlockWishListpro::SUFFIX))
				{
					$str1 = _DB_PREFIX_.$table[$k].BlockWishListpro::SUFFIX;
					$str2 = _DB_PREFIX_.$table[$k].BlockWishListpro::SUFFIX.'_bakdd';
					Db::getInstance()->Execute('DROP TABLE IF EXISTS  `'.$str2.'` ');
					$show = Db::getInstance()->ExecuteS('SHOW CREATE TABLE `'.$str1.'`');
					$show[0]['Create Table'] = str_replace($str1, $str2, $show[0]['Create Table']);
					$create = Db::getInstance()->Execute($show[0]['Create Table']);
					Db::getInstance()->Execute('INSERT INTO  `'.$str2.'`
					SELECT *
					FROM  `'.$str1.'` ');
				}
			}
			/*end backup---------------*/
		}

	/*copy if new doesn't exist and old exists---*/
		foreach ($table as $i => $row)
		{
			if (BlockWishListpro::table_exist(_DB_PREFIX_.$table[$i]))
			{
				/*copy table*/
				$step0 = Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'`');

				$step1 = Db::getInstance()->Execute(BlockWishListpro::table_copy($i, $table));
				$step2 = Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` SELECT * FROM `'._DB_PREFIX_.$table[$i].'`');
				if ($step0 == false || $step1 == false || $step2 === false)
				{
					/*restore backup*/
					foreach ($table as $k => $name_table)
					{
						$str1 = _DB_PREFIX_.$table[$k].BlockWishListpro::SUFFIX.'_bakdd';
						$str2 = _DB_PREFIX_.$table[$k].BlockWishListpro::SUFFIX;
						Db::getInstance()->Execute('DROP TABLE IF EXISTS  `'.$str2.'` ');
						$show = Db::getInstance()->ExecuteS('SHOW CREATE TABLE `'.$str1.'`');
		$show[0]['Create Table'] = str_replace($str1, $str2, $show[0]['Create Table']);
						$create = Db::getInstance()->Execute($show[0]['Create Table']);
						Db::getInstance()->Execute('INSERT INTO  `'.$str2.'`
						SELECT *
						FROM  `'.$str1.'` ');
					}

					/*end restore backup*/
					die(Tools::displayError('Error when copying old tables.'));
				}

				if ($i == 0)
				{	/*wishlist, add 'published' flag , =1 by default (list published)*/
					Db::getInstance()->Execute('
					ALTER TABLE `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` ADD `published` INT(10) NOT NULL DEFAULT  \'1\' AFTER `date_upd`'
					);
				}

				if ($i == 2)
				{	/*wishlist_product, add quantity_init , quantity_left , alert_qty */
					Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` ADD `quantity_rel` INT(10) NOT NULL AFTER `id_product_attribute`, ADD `quantity_init` INT(10) NOT NULL AFTER `quantity`, ADD `quantity_left_rel` INT(10) NOT NULL AFTER `quantity_init`, ADD `quantity_left` INT(10) NOT NULL AFTER `quantity_left_rel`, ADD `alert_qty` INT(10) NOT NULL AFTER `priority`');

					Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` SET
					`quantity` = `quantity_recover`
					' 	);

					/*fields initialization*/
					$wp_table = Db::getInstance()->ExecuteS('
						SELECT *
						FROM `'._DB_PREFIX_.'wishlist_product'.BlockWishListpro::SUFFIX.'`
						ORDER BY `id_wishlist`');
					$wp_table_id_wl = Db::getInstance()->ExecuteS('
						SELECT `id_wishlist`
						FROM `'._DB_PREFIX_.'wishlist_product'.BlockWishListpro::SUFFIX.'`
						GROUP BY `id_wishlist`');
					foreach ($wp_table_id_wl as $id_wishlist)
					{
						$idwl = $id_wishlist['id_wishlist'];
						$wl_CartBoughtQtyProduct[$idwl] = WishListpro::getCartBoughtQtyProduct($idwl);
						$wl_BoughtActualQtyProduct[$idwl] = WishListpro::getBoughtQty_actual($idwl);
						$spywl = 0;
						foreach ($wp_table as $row)
						{
							if ((int)$row['id_wishlist'] == (int)$idwl)
							{
								foreach ($wl_CartBoughtQtyProduct[$idwl] as $line_product)
								{
									$spywl += 1;
									if ($row['id_wishlist_product'] == $line_product['id_wishlist_product'])
									{
										$row['quantity_init'] = (int)$row['quantity_recover'];
										$row['quantity_rel'] = $row['quantity_init'];
										Db::getInstance()->Execute('
										UPDATE `'._DB_PREFIX_.$table['2'].BlockWishListpro::SUFFIX.'` SET
										`quantity_init` = '.(int)$row['quantity_init'].',
										`quantity_rel` = '.(int)$row['quantity_rel'].'
										WHERE `id_wishlist`= '.(int)$row['id_wishlist'].' AND `id_wishlist_product`= '.(int)$row['id_wishlist_product'].'
										' 	);
									break;
									}
								}
								foreach ($wl_BoughtActualQtyProduct[$idwl] as $line_product)
								{
									$spywl += 1;
									if ($row['id_wishlist_product'] == $line_product['id_wishlist_product'])
									{
										$row['quantity_left_rel'] = (int)$row['quantity_init'] - (int)$line_product['actual_qty'];
										$row['quantity_left'] = $row['quantity_left_rel'] < 0 ? 0 : $row['quantity_left_rel'];
										Db::getInstance()->Execute('
										UPDATE `'._DB_PREFIX_.$table['2'].BlockWishListpro::SUFFIX.'` SET
										`quantity_left_rel` = '.(int)$row['quantity_left_rel'].',
										`quantity_left` = '.(int)$row['quantity_left'].'
										WHERE `id_wishlist`= '.(int)$row['id_wishlist'].' AND `id_wishlist_product`= '.(int)$row['id_wishlist_product'].'
										' 	);
									break;
									}
								}

								if ((int)$row['quantity_init'] == 0 && (int)($row['quantity'] != 0))
								{
									$row['quantity_init'] = (int)$row['quantity'];
									Db::getInstance()->Execute('
										UPDATE `'._DB_PREFIX_.$table['2'].BlockWishListpro::SUFFIX.'` SET
										`quantity_init` = '.(int)$row['quantity_init'].'
										WHERE `id_wishlist`= '.(int)$row['id_wishlist'].' AND `id_wishlist_product`= '.(int)$row['id_wishlist_product'].'
										' 	);

									$spywl += 1;
								}
							}
						}
					}
					/*end fields initialization*/
				}

				if ($i == 3)
					/*wishlist_product_cart, add price_wt_init and price_init */
					Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` ADD `price_init` FLOAT NOT NULL AFTER `quantity`, ADD `price_wt_init` FLOAT NOT NULL AFTER `price_init`');

				$in_ch .= $this->l('Table').' '._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.' : '.$this->l('data successfully imported').'<br />';
			}
		}
		$in_ch .= $this->l('Recovery carried out').'.<br />';
		return $in_ch;
	}

	/*retro compat 1.5.1-1.5.2*/
	public static function getFormatedName($name)
	{
		$theme_name = Context::getContext()->shop->theme_name;
		$name_without_theme_name = str_replace(array('_'.$theme_name, $theme_name.'_'), '', $name);
		//check if the theme name is already in $name if yes only return $name
		if (strstr($name, $theme_name) && self::getByNameNType($name))
			return $name;
		else if (ImageType::getByNameNType($name_without_theme_name.'_'.$theme_name, 'products'))
			return $name_without_theme_name.'_'.$theme_name;
		else
			return $theme_name.'_'.$name_without_theme_name;
	}

	public function isHTTPS()
	{
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| $_SERVER['SERVER_PORT'] == 443;
	}

	public static function getDateFormatDataTable()
	{
		$context = Context::getContext();
		$date_format = $context->language->date_format_lite;
		if (Tools::strtolower($date_format) == 'd/m/y')
			$date_format = 'DD/MM/YYYY';
		elseif (Tools::strtolower($date_format) == 'm/d/y')
			$date_format = 'MM/DD/YYYY';
		else
		{
			$leng = Tools::strlen($date_format);
			$ch = '';
			for ($i = 0; $i < $leng; ++$i)
			{
				$chx = Tools::strtolower(Tools::substr($date_format, $i, 1));
				if (in_array($chx, array('d', 'm', 'y')))
				{
					$ch .= Tools::strtoupper($chx.$chx);
					if (Tools::strtolower($chx) == 'y')
						$ch .= Tools::strtoupper($chx.$chx);
				}
				else
					$ch .= $chx;
			}
			$date_format = $ch;
		}
		return $date_format;
	}
}
