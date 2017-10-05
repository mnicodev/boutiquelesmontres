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

require_once('../../config/config.inc.php');
require_once('../../init.php');
require_once('WishListpro.php');
include_once('blockwishlistpro.php');
$context = Context::getContext();
$cookie = $context->cookie;

/*Instance of module class for translations*/
$module = new BlockWishListpro();

		/**
		 * To sanitize text /cette fonction sert à nettoyer et enregistrer un texte
		 */
		function Rec($text)
		{
			$text = trim($text); // delete white spaces after & before text
			if (1 === get_magic_quotes_gpc())
				$stripslashes = create_function('$txt', 'return stripslashes($txt);');
			else
				$stripslashes = create_function('$txt', 'return $txt;');
			// magic quotes ?
			$text = $stripslashes($text);
			$text = htmlspecialchars($text, ENT_QUOTES); // converts to string with " and ' as well
			$text = nl2br($text);
			return $text;
		};

if ($context->customer->isLogged())
{
	$id_wishlist = (int)Tools::getValue('id_wishlist');
	if (empty($id_wishlist) === true)
		exit(Tools::displayError('Invalid wishlist'));
	$wishlist = WishListpro::exists($id_wishlist, $cookie->id_customer, true);
	if ($wishlist === false)
		exit(Tools::displayError('Invalid wishlist'));
	$url_list = 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.BlockWishListpro::MODULENAME.'/view.php?token='.$wishlist['token'];
	$wishlist = $wishlist['name'];

	$shop_name = (string)Configuration::get('PS_SHOP_NAME');
	$shop_phone = (string)Configuration::get('PS_SHOP_PHONE');
	$shop_url = 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;
	$shop_logo = 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'img/logo.jpg';
	$filehtml = Tools::getValue('file');
	$filehtml_temp = Tools::getValue('file_temp');

	$customer = new Customer((int)($cookie->id_customer));
	$lastname = $customer->lastname;
	$firstname = $customer->firstname;

	$message_personal = Tools::getValue('message_personal');
	$message_personal = Rec($message_personal);
	$test_message = $message_personal == '' ? 'none' : 'block'; /*to display or not personal message -> CSS parameter in wishlist.html*/
	$message_personal .= '<br />';

	$path_html = dirname(__FILE__).'/mails/'.Language::getIsoById($cookie->id_lang).'/'.$filehtml;
	if (!file_exists($path_html))
		exit('Error, no wishlist.html file in mails/'.Language::getIsoById($cookie->id_lang).'/');
	$id_fic = fopen($path_html, 'r');
	if ($id_fic)
	{
		$content = fread($id_fic, filesize($path_html));
		fclose($id_fic);
		$content = str_replace('{shop_name}', $shop_name, $content);
		$content = str_replace('{shop_phone}', $shop_phone, $content);
		$content = str_replace('{shop_url}', $shop_url, $content);
		$content = str_replace('{shop_logo}', $shop_logo, $content);
		$content = str_replace('{lastname}', $lastname, $content);
		$content = str_replace('{firstname}', $firstname, $content);
		$content = str_replace('{wishlist}', $wishlist, $content);
		$content = str_replace('{message_personal}', $message_personal, $content);
		$content = str_replace('{test_message}', $test_message, $content);
		$content = str_replace('{message}', $url_list, $content);
		$content = str_replace('{path_wlp}', 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.BlockWishListpro::MODULENAME, $content);

		$path_html_temp = dirname(__FILE__).'/mails/'.Language::getIsoById($cookie->id_lang).'/'.$filehtml_temp;
		$id = fopen($path_html_temp, 'w+');
		if ($id)
		{
			fputs($id, $content);
			fclose($id);
			echo 'création fichier réussie';
		}
		else exit( 'Error, cannot open'.$path_html_temp.' !');
	}
	else exit('Error,  cannot open'.$path_html.' !');
}
?>