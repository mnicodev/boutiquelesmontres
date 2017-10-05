<?php
/**
* BLOCKWISHLISTPRO Front Office Feature - display products of a list, creator's view
*
* @author    Denis Deleval / alize-web.fr <contact@alizeweb.fr>
* @copyright Alizé Web 2011-2014
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

/* @since 1.5.0 */
class BlockWishListproMyWishListModuleFrontController extends ModuleFrontController
{
	public $display_column_left = true; /*display left column with usual blocks*/
	public $auth = true;
/*	public $php_self = 'mywishlist';
	public $authRedirection = 'mywishlist';*/

	public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
		include_once($this->module->getLocalPath().'WishListpro.php');
	}

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		$this->assign();
	}

	/**
	 * Assign wishlist template
	 */
	public function assign()
	{
		$errors = array();

		if ($this->context->customer->isLogged())
		{
			$module = new BlockWishListpro();
			$id_lang = (int)$this->context->language->id;
			$add = Tools::getIsset('add');
			$add = (empty($add) === false ? 1 : 0);
			$new = Tools::getValue('new');
			$new = (empty($new) === false ? 1 : 0);
			$delete = Tools::getValue('deleted');
			$delete = (empty($delete) === false ? 1 : 0);
			$id_wishlist = Tools::getValue('id_wishlist');

			if (Tools::isSubmit('submitWishlist'))
			{
				if (Configuration::get('PS_TOKEN_ACTIVATED') == 1 && strcmp(Tools::getToken(), Tools::getValue('token')))
					$errors[] = $this->module->l('Invalid token', 'mywishlist');
				if (!count($errors))
				{
					$name = Tools::getValue('name');
					if (empty($name))
						$errors[] = $this->module->l('you must specify a name', 'mywishlist');
					if (!Validate::isMessage($name))
						$errors[] = $this->module->l('you must specify a name without special characters', 'mywishlist');
				}
				if (!count($errors))
				{
					$rc = new ReflectionClass('Db');
						if ($rc->hasMethod('getValue'))
						{
								if (WishListpro::isExistsByNameForUser($name))
									$errors[] = $name.' : '.$this->module->l('this name is already used by an other list', 'mywishlist');
						}
						else
						{
								if (WishListpro::isExistsByNameForUserOld($name)) //old version 1.1
									$errors[] = $name.' : '.$this->module->l('this name is already used by an other list', 'mywishlist');
						}

					if (!count($errors))
					{
						$wishlist = new WishListpro();
						$wishlist->id_shop = (int)$this->context->shop->id;
						$wishlist->id_shop_group = (int)$this->context->shop->id_shop_group;
						$wishlist->name = pSQL($name);
						$wishlist->id_customer = (int)$this->context->customer->id;
						$wishlist->published = 1;
						list($us, $s) = explode(' ', microtime());
						srand($s * $us);
						$wishlist->token = Tools::strtoupper(Tools::substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.$this->context->customer->id), 0, 16));
						$wishlist->add();

//echo '<br>4.mywishlist wishlist->id_shop_group : ';var_dump($wishlist->id_shop_group);

/*						Mail::Send(
							$this->context->language->id,
							'wishlink',
							Mail::l('Your wishlist\'s link', $this->context->language->id),
							array(
							'{wishlist}' => $wishlist->name,
							'{message}' => Tools::getProtocol().htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/blockwishlist/view.php?token='.$wishlist->token),
							$this->context->customer->email,
							$this->context->customer->firstname.' '.$this->context->customer->lastname,
							NULL,
							(string)Configuration::get('PS_SHOP_NAME'),
							NULL,
							NULL,
							$this->module->getLocalPath().'mails/');*/
					}
				}
			}
/*			if ($add) {
				WishListpro::addCardToWishlist((int)($this->context->customer->id), (int)Tools::getValue('id_wishlist'), $id_lang); }*/
			if ((int)$delete === 1 && empty($id_wishlist) === false)
			{	/*die('{"hasError" : true, "errors" : ["hookcart params"]}');		*/
				$wishlist = new WishListpro((int)$id_wishlist);

				if (Validate::isLoadedObject($wishlist))
					if (!WishListpro::getBoughtProduct_reel($id_wishlist))
						$wishlist->delete();
					else
					{
						echo 'already_bought';
						return;  /*-> popup via ajaxwishlistpro*/
						/*$errors[] = Tools::displayError('already bought products, cannot delete the wishlist');*/
					}
				else
				{
						echo 'can`t delete this whislist';
						return;
						/*$errors[] = Tools::displayError('can`t delete this whislist');*/
				}
			}
		/*dd - to formate date to correct country display */
			$d_wishlists = WishListpro::getByIdCustomer((int)$this->context->customer->id);
			foreach ($d_wishlists as $i => $d_wl)
			{
				$d_wishlists[$i]['date_add'] = version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($d_wl['date_add']) : Tools::displayDate($d_wl['date_add'], $id_lang, false);
/*				$d_wishlists[$i]['date_add'] = Tools::displayDate($d_wl['date_add'], $id_lang,false);*/
				$listcart = WishListpro::getListOrder($d_wishlists[$i]['id_wishlist']);
				$d_wishlists[$i]['is_gift']	 = $listcart == false ? false : true;
			/* to check or uncheck the checkbox to publish/unpublish the wishlist*/
				$req = Db::getInstance()->getRow('
				SELECT w.`published`, w.`id_wishlist`
					FROM `'._DB_PREFIX_.'wishlist'.BlockWishListpro::SUFFIX.'` w
				WHERE `id_wishlist` = '.(int)$d_wishlists[$i]['id_wishlist'].'
				');
				$d_wishlists[$i]['published'] = $req['published'];
			}
			$spy_breadcrumb = version_compare(_PS_VERSION_, '1.6.0', '>=') ? 1 : 0;
			$this->context->smarty->assign(array(
				'id_customer' => (int)$this->context->customer->id,
				'errors' => $errors,
				'err_dd' => $errors,
				'modulename' => BlockWishListpro::MODULENAME,
				'view_link' => $this->context->link->getModuleLink('blockwishlistpro', 'view'),
				'nbProducts' => WishListpro::getInfosByIdCustomer((int)$this->context->customer->id),
				'wishlists' => $d_wishlists,
				'spy_breadcrumb' => $spy_breadcrumb,
				'themeChoice' => Configuration::get('PS_WISHLISTPRO_FO_THEME'),
				'displayList' => (bool)Configuration::get('PS_GRID_PRODUCT'),
				'type_display_default' => $module->type_display_default,
				'type_display_init' => $module->type_display_init,
				'id_lang' => $id_lang
			));
			$this->setTemplate('mywishlist.tpl');
		}
		else
		{
			$url = $this->context->link->getModuleLink(''.BlockWishListpro::MODULENAME.'', 'mywishlist');
			$prefix = $this->context->link->getPageLink('authentication');
			Tools::redirect('Location: '.$prefix.'?back='.urlencode($url));
			exit;
		}
	}
}