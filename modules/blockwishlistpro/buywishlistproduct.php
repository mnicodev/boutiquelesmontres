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
require_once(dirname(__FILE__).'/blockwishlistpro.php');

$error = '';
$module = new BlockWishListpro();/*for translation*/

$context = Context::getContext();
$cart = $context->cart;
$id_cart = $cart->id;

$token = Tools::getValue('token');
$id_product = (int)Tools::getValue('id_product');
$id_product_attribute = (int)Tools::getValue('id_product_attribute');
$quantity = (int)Tools::getValue('quantity');

if (Configuration::get('PS_TOKEN_ENABLE') == 1 && strcmp(Tools::getToken(false), Tools::getValue('static_token')))
	$error = Tools::displayError('invalid token');

if (! Tools::strlen($error) && empty($token) === false && empty($id_product) === false)
{
	$wishlist = WishListpro::getByToken($token);
	if ($wishlist !== false)
		WishListpro::addBoughtProduct($wishlist['id_wishlist'], $id_product, $id_product_attribute, $id_cart, $quantity);
}
else
	$error = $module->l('You need to login', 'buywishlistproduct');

if (empty($error) === false)
	echo $error;
?>