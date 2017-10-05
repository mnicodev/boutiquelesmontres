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

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/WishListpro.php');
include_once(dirname(__FILE__).'/blockwishlistpro.php');

$errors = array();
$context = Context::getContext();
$cookie = $context->cookie;
/* Instance of module class for translations*/
$module = new BlockWishListpro();

$action = Tools::getValue('action');
$id_wishlist = (int)Tools::getValue('id_wishlist');
$id_product = (int)Tools::getValue('id_product');
$quantity = (int)Tools::getValue('quantity');
$id_product_attribute = (int)Tools::getValue('id_product_attribute');
if (Configuration::get('PS_TOKEN_ENABLE') == 1 && strcmp(Tools::getToken(false), Tools::getValue('token')) && $context->customer->isLogged() === true)
	$errors[] = Tools::displayError('invalid token');
if ($context->customer->isLogged())
{
	if ($id_wishlist && WishListpro::exists($id_wishlist, $cookie->id_customer) === true)
		$cookie->id_wishlist = (int)$id_wishlist;
	if (empty($cookie->id_wishlist) === true || $cookie->id_wishlist == false)
		$context->smarty->assign('error', true);
	if (($action == 'add' || $action == 'delete') && empty($id_product) === false)
	{
		/*is cookie->id_wishlist an id of a current wishlist ? (case when deleting the sole wishlist and afterwards adding a product to wishlist)*/
		$wl_exists = WishListpro::getByIdCustomer($context->customer->id);

		if ((int)$cookie->id_wishlist == 0)
			$cookie->id_wishlist = $wl_exists[0]['id_wishlist'];

		if (!isset($cookie->id_wishlist) || $cookie->id_wishlist == '' || empty($wl_exists))
			die('create_list'); /* v1_122 : need to create a list before adding products */

		if ($action == 'add' && $quantity)
		{
			$temp_aw = WishListpro::addProduct($cookie->id_wishlist, $cookie->id_customer, $id_product, $id_product_attribute, $quantity);
			if ($temp_aw === true)
				echo "<span style='display:none'>adding_ok</span>"; /*style : do not display on right column block*/
			else
				echo '<!--|minimal_qty|'.$temp_aw.'|-->';
		}
		else if ($action == 'delete')
		{
			/*Bought Quantities :  has the product already been bought ?*/
			$test = WishListpro::getProductBoughtQty_actual($cookie->id_wishlist, $id_product, $id_product_attribute);
			if (isset($test))
			{
				if (empty($test)) /*if not bought : remove*/
					WishListpro::removeProduct($cookie->id_wishlist, (int)($context->customer->id), $id_product, $id_product_attribute);
				else /*if bought : not remove */
					echo "<span style='display:none'>impossible_to_delete</span>"; /*style : do not display on right column block*/
			}
		}
	}
	/*list of wishlist products still in database (not cancelled)*/
	$id_lang = (int)Context::getContext()->language->id;
	$list_epur = $id_wishlist == false ? false : WishListpro::getProductByIdCustomer($id_wishlist, (int)($context->customer->id), (int)$id_lang, null, true);
	foreach ($list_epur as $i => $row)
	{
		$obj = new Product((int)$row['id_product'], false, (int)Context::getContext()->language->id);
		if (!Validate::isLoadedObject($obj))
			$list_epur[$i]['isobj'] = 0; /*isobj=0 means a bo cancelled product but still in list*/
		else
			$list_epur[$i]['isobj'] = 1; /*isobj=1 still in list and database*/
	}

	$context->smarty->assign('products', $list_epur);
	$context->smarty->assign('modulename', BlockWishListpro::MODULENAME);
	$context->smarty->assign('static_token', Tools::getValue('token'));


/* in case of older versions (1.2 for ex) method Tools::file_exists_cache($file) NOT DEFINED. need to use method_exists*/
		$rc = new ReflectionClass('Tools');
		if ($rc->hasMethod('file_exists_cache'))
		{
			if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.BlockWishListpro::MODULENAME.'/views/templates/front/blockwishlist-ajax.tpl'))
				$context->smarty->display(_PS_THEME_DIR_.'modules/'.BlockWishListpro::MODULENAME.'/views/templates/front/blockwishlist-ajax.tpl');
			elseif (Tools::file_exists_cache(dirname(__FILE__).'/views/templates/front/blockwishlist-ajax.tpl'))
				$context->smarty->display(dirname(__FILE__).'/views/templates/front/blockwishlist-ajax.tpl');
			else
				echo Tools::displayError('No template found');
		}
		else
			$context->smarty->display(dirname(__FILE__).'/views/templates/front/blockwishlist-ajax.tpl');

}
else
{
	$errors[] = Tools::displayError('You need to be logged to manage your wishlist');
	echo 'not_logged';
}

if (count($errors))
{
	$context->smarty->assign('errors', $errors);
	$context->smarty->display(_PS_THEME_DIR_.'errors.tpl');
}