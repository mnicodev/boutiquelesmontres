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
require_once('../../config/settings.inc.php');
require_once('../../config/defines.inc.php');
require_once('../../init.php');
require_once('../../classes/Tools.php');
require_once('../../classes/Cookie.php');
require_once('../../classes/Mail.php');
require_once('../../classes/Customer.php');
require_once('WishListpro.php');

	function table_exist($table)
	{
		$runQuery = Db::getInstance()->Execute('SHOW TABLES' );
		/*On crée un nouveau tableau avec toutes les tables*/
		$tables = array();
		while ($row = mysql_fetch_row($runQuery))
			$tables[] = $row[0];
		/*On vérifie si $table est dans le tableau tables*/
		if (in_array($table, $tables))
			return true;
	}
	function table_copy($i, $table, $suffix)
	{
		$sql = array();
		/*wishlist table*/
		if ($i == 0)
			$sql[$i] = 'CREATE TABLE IF NOT EXISTS`'._DB_PREFIX_.$table[$i].$suffix.'` ( `id_wishlist` int( 10 ) unsigned NOT NULL AUTO_INCREMENT ,`id_customer` int( 10 ) unsigned NOT NULL ,`token` varchar( 64 ) NOT NULL ,`name` varchar( 64 ) NOT NULL ,`counter` int( 10 ) unsigned DEFAULT NULL ,`date_add` datetime NOT NULL ,`date_upd` datetime NOT NULL ,
				PRIMARY KEY ( `id_wishlist` ) ) ENGINE = MyISAM DEFAULT CHARSET = utf8';

		/*wishlist_email table*/
		if ($i == 1)
			$sql[$i] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$table[$i].$suffix.'` ( `id_wishlist` int( 10 ) unsigned NOT NULL,`email` varchar( 128 ) NOT NULL,`date_add` datetime NOT NULL ) ENGINE = MyISAM DEFAULT CHARSET = utf8';

		/*wishlist_product table*/
		if ($i == 2)
		{
			$sql[$i] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$table[$i].$suffix.'` ( `id_wishlist_product` int( 10 ) NOT NULL AUTO_INCREMENT ,`id_wishlist` int( 10 ) unsigned NOT NULL ,`id_product` int( 10 ) unsigned NOT NULL ,`id_product_attribute` int( 10 ) unsigned NOT NULL ,`quantity` int( 10 ) unsigned NOT NULL ,`priority` int( 10 ) unsigned NOT NULL ,
				PRIMARY KEY ( `id_wishlist_product` ) ) ENGINE = MyISAM DEFAULT CHARSET = utf8';
		};
		/*wishlist_product_cart*/
		if ($i == 3)
			$sql[$i] = ' CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$table[$i].$suffix.'` ( `id_wishlist_product` int( 10 ) unsigned NOT NULL ,`id_cart` int( 10 ) unsigned NOT NULL ,`quantity` int( 10 ) unsigned NOT NULL ,`date_add` datetime NOT NULL ) ENGINE = MyISAM DEFAULT CHARSET = utf8';

		return $sql[$i];
	}

$table = array('wishlist', 'wishlist_email', 'wishlist_product', 'wishlist_product_cart');
$suffix = '_pro';
/*les nouvelles tables existent-elles ?---------------*/
$spynew = 0;
foreach ($table as $i => $row)
{
	if (table_exist(_DB_PREFIX_.$table[$i].$suffix))
	$spynew += 1;
}
if ($spynew != 0)
	echo 'au moins une nouvelle table est dans la base, en fait $spynew.<br>';

/*les anciennes tables existent-elles ?---------------*/
$spy = 0;
foreach ($table as $i => $row)
{
	if (table_exist(_DB_PREFIX_.$table[$i]))
	$spy += 1;
}
if ($spy != 0)
	echo ' au moins une table est dans la base, en fait $spy.<br>';

/*copy if new doesn't exist and old exists------------*/
foreach ($table as $i => $row)
{
	if (table_exist(_DB_PREFIX_.$table[$i]) && (!table_exist(_DB_PREFIX_.$table[$i].$suffix)))
	{
		/*copy table*/
		$step1 = Db::getInstance()->Execute(table_copy($i, $table, $suffix));
		$step2 = Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.$table[$i].$suffix.'` SELECT * FROM `'._DB_PREFIX_.$table[$i].'`');
		if ($step1 == false || $step2 == false)
			die(Tools::displayError('error when copying old tables. Please install this module without copying old tables'));

		if ($i == 3)
		/*wishlist_product_cart, add price_wt_init and price_init */
			Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$table[$i].$suffix.'` ADD `price_init` FLOAT NOT NULL AFTER `quantity`, ADD `price_wt_init` FLOAT NOT NULL AFTER `price_init`');
		echo '<br> copie de la table '._DB_PREFIX_.$table[$i].$suffix.' effectu&eacute;e <br>';
	}
}

/*foreach ($table as $i => $row) {
	if (table_existe(_DB_PREFIX_.$table[$i])) {echo "la table "._DB_PREFIX_.$table[$i]." est bien dans la base";$spy = 1;} else {echo "la table "._DB_PREFIX_.$table[$i]." n'est pas dans la base";};
	echo "<br />";
}*/
/*
$table="ps_wishlist_product";
if (table_existe($table)) {echo "la table $table est bien dans la base";} else {echo "la table $table n'est pas dans la base";};
*/
?>