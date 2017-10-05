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
if (!defined('_PS_HOST_MODE_'))
{
	require_once('../../config/settings.inc.php');
	require_once('../../images.inc.php');
	require_once('../../classes/Tools.php');
}
require_once(_PS_TOOL_DIR_.'/tcpdf/tcpdf.php');
require_once('WishListpro.php');
require_once(dirname(__FILE__).'/blockwishlistpro.php');
require_once(dirname(__FILE__).'/pdfwl-tcpdf.php');
$context = Context::getContext();
/* Instance of module class for translations*/
$module = new BlockWishListpro();

if ($context->customer->isLogged())
{
	$id_wishlist = Tools::getValue('id_wishlist');
	$id_lang = Tools::getValue('id_lang');
	$currency = $context->currency;

	if (empty($id_wishlist) === false)
	{
		$wishlist = new WishListpro((int)$id_wishlist);
		if (!Validate::isLoadedObject($wishlist))	die(Tools::displayError('cannot find wishlist in database'));
		$nom = $wishlist->name.'.pdf';
		function convertSign($s)
		{
			return str_replace('¥', chr(165), str_replace('£', chr(163), str_replace('€', chr(128), $s)));
		}

		$gather = BlockWishListpro::gatherInfoPdf_tcpdf($id_wishlist, $id_lang);

	/* Close and output PDF document*/
	/* This method has several options, check the source code documentation for more information.*/
		$gather[0]->Output($nom, 'D'); /*D : open with | I : open in browser*/
	}
}
else
	Tools::redirect('authentication.php?controller=authentication&back=/modules/'.BlockWishListpro::MODULENAME.'/mywishlist.php');
?>