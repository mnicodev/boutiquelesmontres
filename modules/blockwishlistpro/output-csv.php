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

/*require_once('../../config/config.inc.php');
	$dateFrom = Tools::getValue('date1');
	$dateTo = Tools::getValue('date2');
if ($dateFrom > $dateTo) {$dateTemp = $dateTo; $dateTo = $dateFrom; $dateFrom = $dateTemp;}
$result = WishListpro::getOrdersWithWishlist($dateFrom, $dateTo);
*/
/*--------------------excel et OOcalc + UTF-16LE---------------------*/
header('Content-type: application/vnd.ms-excel; charset=UTF-16LE');
header('Content-disposition: attachment; filename=export.csv');
/*header("Content-disposition: attachment; filename=\"export-".date("Y-m-d").".csv\"");*/
$data = trim(stripcslashes($_REQUEST['csv_text']));
$con = mb_convert_encoding( $data, 'UTF-16LE', 'UTF-8');
echo $con;
/*----------------------fin UTF-16LE--------------------------------*/
?>