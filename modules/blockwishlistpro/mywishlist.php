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

/* SSL Management */
$useSSL = false;

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/WishListpro.php');
include_once(dirname(__FILE__).'/blockwishlistpro.php');
$context = Context::getContext();
Tools::displayFileAsDeprecated();

/*init front controller in order to use Tools::redirect*/
$controller = new FrontController();
$controller->init();
Tools::redirect('index.php?fc=module&module=blockwishlistpro&controller=mywishlist');

/*Instance of module class for translations*/
$module = new BlockWishListpro();