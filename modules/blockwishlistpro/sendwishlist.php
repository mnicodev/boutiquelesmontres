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

require_once('../../config/config.inc.php');
require_once('../../init.php');
require_once('WishListpro.php');
require_once('blockwishlistpro.php');
$context = Context::getContext();
$module = new BlockWishListpro();

if (Configuration::get('PS_TOKEN_ENABLE') == 1 && strcmp(Tools::getToken(false), Tools::getValue('token')) && $context->customer->isLogged() === true)
	exit(Tools::displayError('invalid token'));

if ($context->customer->isLogged())
{
/*-------------------------------------------------------------------------------------
		 * to clean and save a text / nettoyer et enregistrer un texte
		 */
		function Rec($text)
		{
			$text = trim($text); /*delete white spaces after & before text*/
			if (1 === get_magic_quotes_gpc())
				$stripslashes = create_function('$txt', 'return stripslashes($txt);');
			else
				$stripslashes = create_function('$txt', 'return $txt;');
			/*magic quotes ?*/
			$text = $stripslashes($text);
			$text = htmlspecialchars($text, ENT_QUOTES); /*converts to string with " and ' as well*/
			$text = nl2br($text);
			return $text;
		}
		/*
		 * Check e-mail syntax
		 */
		function IsEmail($mail)
		{
			$nonascii = "\x80-\xff"; # Les caractères Non-ASCII ne sont pas permis
			$nqtext = "[^\\\\$nonascii\015\012\"]";
			$qchar = "\\\\[^$nonascii]";
			$protocol = '(?:mailto:)';
			$normuser = '[a-zA-Z0-9][a-zA-Z0-9_.-]*';
			$quotedstring = "\"(?:$nqtext|$qchar)+\"";
			$user_part = "(?:$normuser|$quotedstring)";
			$dom_mainpart = '[a-zA-Z0-9][a-zA-Z0-9._-]*\\.';
			$dom_subpart = '(?:[a-zA-Z0-9][a-zA-Z0-9._-]*\\.)*';
			$dom_tldpart = '[a-zA-Z]{2,6}';
			$domain_part = "$dom_subpart$dom_mainpart$dom_tldpart";
			$regex = "$protocol?$user_part\@$domain_part";
			return preg_match("/^$regex$/", $mail);
		}
/*------------------------------------------------------------------------*/
	$id_wishlist = (int)Tools::getValue('id_wishlist');
	$obj = new WishListpro($id_wishlist);
	if (empty($id_wishlist) === true)
		exit(Tools::displayError('Invalid wishlist'));

	$message_personal = Tools::getValue('message_personal');
	$message_personal = Rec($message_personal);
	$test_message = $message_personal == '' ? 'none' : 'block'; /*to display or not personal message -> CSS parameter in wishlist.html*/
	$message_personal .= '<br />';


	for ($i = 1; $i <= 10; ++$i)
	{
		$to = Tools::getValue('email'.$i);
		if ($to === '' || $to == false)
			continue;
		$to = str_replace(' ', '', Rec($to));
		$to = (IsEmail($to)) ? $to : '';

		$wishlist = WishListpro::exists($id_wishlist, $context->customer->id, true);

		if ($wishlist === false)
			exit(Tools::displayError('Invalid wishlist'));
		if (WishListpro::addEmail($id_wishlist, $to) === false)
			Tools::displayError('Wishlist/addEmail send error');

		$shop_phon = (string)(Configuration::get('PS_SHOP_PHONE')) == '' ? '...': (string)(Configuration::get('PS_SHOP_PHONE'));
		$customer = new Customer((int)($context->customer->id));

		$messagefrom = $module->l('Message from ', 'sendwishlist');
		$link = new Link();
		if (Validate::isLoadedObject($customer))
		$mail_s = Mail::Send((int)($context->language->id), 'wishlist', $messagefrom.$customer->lastname.' '.$customer->firstname,
			array(
			'{lastname}' => $customer->lastname,
			'{firstname}' => $customer->firstname,
			'{wishlist}' => $wishlist['name'],
			'{message_personal}' => $message_personal,
			'{test_message}' => $test_message,
			'{shop_phone}' => $shop_phon,
			'{path_wlp}' => 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.BlockWishListpro::MODULENAME,
			'{message}' => $link->getModuleLink(BlockWishListpro::MODULENAME, 'view', array('token' => pSQL($obj->token)))),
			$to, '', $customer->email, $customer->firstname.' '.$customer->lastname, null, null, dirname(__FILE__).'/mails/');

/*		$mail_s = Mail::Send((int)($cookie->id_lang), 'wishlist', $messagefrom.$customer->lastname.' '.$customer->firstname,
			array(
			'{lastname}' => $customer->lastname,
			'{firstname}' => $customer->firstname,
			'{wishlist}' => $wishlist['name'],
			'{message_personal}' => $message_personal,
			'{test_message}' => $test_message,
			'{shop_phone}' => $shop_phon,
			'{path_wlp}' => 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.BlockWishListpro::MODULENAME,
			'{message}' => 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.BlockWishListpro::MODULENAME.'/view.php?token='.$wishlist['token']),
			$to, '', $customer->email, $customer->firstname.' '.$customer->lastname, null, null, dirname(__FILE__).'/mails/');
*/
		if (!$mail_s || $mail_s == false)
			echo 'nok';
	}
}