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

header("Content-type: application/vnd.ms-excel; name='excel'; charset=UTF-16LE" );
header('Content-Disposition: filename=export.xls');
/* Fix for crappy IE bug in download.*/
header('Pragma: ');
header('Cache-Control: ');

$ret = stripcslashes($_REQUEST['excel_table']);
$patterns = array();
$patterns[0] = '/Ã©/';
$patterns[1] = '/Ã\s/';
$patterns[2] = '/Â°/';
$patterns[3] = '/Ã¨/';
$patterns[4] = '/Ã¹/';
$patterns[5] = '/Ã´/';
$patterns[6] = '/Ã»/';
$patterns[7] = '/Ã¢/';
$patterns[8] = '/Ã£/';
$patterns[9] = '/&amp;/';
$patterns[10] = '/Â£/';
$patterns[11] = '/â‚¬/';
$patterns[12] = '/Âµ/';
$replacements = array();
$replacements[0] = 'é';
$replacements[1] = 'à';
$replacements[2] = '°';
$replacements[3] = 'è';
$replacements[4] = 'ù';
$replacements[5] = 'ô';
$replacements[6] = 'û';
$replacements[7] = 'â';
$replacements[8] = 'ã';
$replacements[9] = '&';
$replacements[10] = '£';
$replacements[11] = '€';
$replacements[12] = 'µ';

$rt = preg_replace($patterns, $replacements, $ret);
?>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
<body><?php echo $rt;?>
</body>
</html>