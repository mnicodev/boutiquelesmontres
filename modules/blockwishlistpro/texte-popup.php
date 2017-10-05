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
include_once(dirname(__FILE__).'/blockwishlistpro.php');

$module = new BlockWishListpro();
$context = Context::getContext();
$cookie = $context->cookie;

$tab_text = array();

$texte	= Tools::getValue('idText', 'vide');
$obj = new Product((int)$texte, false, (int)$cookie->id_lang);
if (!Validate::isLoadedObject($obj))
	var_dump('error object');
else
{
	$tab_text[0] = $obj->name;
	$tab_text[1] = $obj->description;
	$tab_text[2] = $obj->description_short;
}
?>
<?php
if ($texte == 'vide')
	echo $module->l('No description !', 'texte-popup');
else
{
	foreach ($tab_text as $i => $row)
	{
			$row = trim($row);
			echo $row;
			echo '<br>';
			if ($i == 0)
				echo '<hr>';
			if ($i == 1)
				echo '<br>';
	}
}
?>