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
/*require_once('../../config/settings.inc.php');
if (!file_exists(dirname(__FILE__).'/../../config/defines.inc.php')) require_once(dirname(__FILE__).'/defines_oldversion.inc.php'); /*old version (<= 1.2)*/
/*else require_once('../../config/defines.inc.php');
if (!defined('_PS_HOST_MODE_'))
{
	require_once('../../init.php');
	require_once('../../classes/Tools.php');
	require_once('../../classes/Cookie.php');
	require_once('../../classes/Mail.php');
	require_once('../../classes/Customer.php');
}*/
require_once('WishListpro.php');
require_once('./blockwishlistpro.php');

$module = new BlockWishListpro();
$context = Context::getContext();
$id_lang = (int)Tools::getValue('id_lang');
$id_empl = (int)Tools::getValue('id_empl');

$className = 'AdminCustomers';
$tok_customeradmin = Tools::getAdminToken($className.(int)Tab::getIdFromClassName('AdminCustomers').(int)$id_empl);
$partial_link_admincustomer = Dispatcher::getInstance()->createUrl('AdminCustomers', $id_lang, array('token' => $tok_customeradmin), false);

/*------------export to csv file------------------------------*/
$period_type = Tools::getValue('period_type');
$date1 = Tools::getValue('date1');
$date2 = Tools::getValue('date2');
$id_lang = (int)Tools::getValue('id_lang');

$dateTo = date('Y-m-d');
if ($period_type == 'last 7 days')
	$dateFrom = date ('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 7, date('Y')));
elseif ($period_type == 'last 30 days')
	$dateFrom = date ('Y-m-d', mktime(0, 0, 0, date('m') - 1, date('d') + 1, date('Y')));
if ($period_type == 'today')
	$dateFrom = $dateTo;
if ($period_type == 'selected period')
{
	$dateFrom = $date1;
	$dateTo = $date2;
}
if ($period_type == 'all' || !isset($dateFrom))
{
	$dateFrom = '1900-01-01';
	$dateTo = date('Y-m-d');
}

if ($dateFrom > $dateTo)
{
	$dateTemp = $dateTo;
	$dateTo = $dateFrom;
	$dateFrom = $dateTemp;
}

$result = WishListpro::getOrdersWithWishlist($dateFrom, $dateTo);

/* translation - do NOT erase !!!!!!!!!!!!
->l('today') ->l('last 7 days') ->l('last 30 days') ->l('selected period') ->l('all') ->l('Created on') ->l('Orders of wishlists over a period') ->l('Type of period:') ->l('From') ->l('to') ->l('Date of order') ->l('Order') ->l('Customer name') ->l('Wishlist') ->l('Carrier') ->l('Wishlist name') ->l('Wishlist creator name') ->l('Select...') ->l('Clear Filters') ->l('Colums width:') ->l('lines') ->l('page') ->l('Back to cockpit')
*/
/* table to export to excel format without filter and footer lines */
/*echo '<div id="output_csv" class="back_blockwl submit_action">
	<form action="'._MODULE_DIR_.BlockWishListpro::MODULENAME.'/output-csv.php"  method="post">
		<input type="hidden" name="csv_text" id="csv_text" />
		<input type="submit" id="submit_csv" value="Export CSV" onclick="getCSVData(\'csv_text\');" />
	</form>
</div>';*/ /*nok with 1.5*/

echo '<div id="output_excel" class="back_blockwl submit_action mg_right12">';
echo '	<form action="'._MODULE_DIR_.BlockWishListpro::MODULENAME.'/output-excel.php" method="post" target="_blank" onsubmit="$(\'#excel_table\').val( $(\'<div>\').append( $(\'#exportformat\').eq(0).clone() ).html() );">';
echo '		<input type="hidden" name="excel_table" id="excel_table" />';
echo '	<input type="submit" id="submit_excel" class="submit_action" value="Export EXCEL" />';
echo '	</form>
	</div>';

echo '<div id="output_print" class="back_blockwl submit_action">
	<form method="post">
		<input type="submit" id="submit_print" value="Print" onclick="javascript:print_wl_table(\'table_div\');" />
	</form>
</div>';

echo '<br style="clear:both" />';
echo '<h1 id="h1">'.$module->l('Orders of wishlists over a period', 'orders_wishlists').'</h1>';

echo '<h2>';

echo $module->l('Type of period:', 'orders_wishlists').' <span class="bg1"> '.$module->l($period_type, 'orders_wishlists').'</span></h2>';
if ($dateFrom == '1900-01-01')
	$dateFrom = '... ';
else
	$dateFrom = version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($dateFrom) : Tools::displayDate($dateFrom, $id_lang, false);
if (empty($dateTo))
	$dateTo = '... ';
else
	$dateTo = version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($dateTo) : Tools::displayDate($dateTo, $id_lang, false);

echo '<h3 id="h3">'.$module->l('From', 'orders_wishlists').'&nbsp;<span class="bg1">'.$dateFrom.'</span>&nbsp;&nbsp;'.$module->l('to', 'orders_wishlists').'&nbsp;<span class="bg1">'.$dateTo.'</span></h3>';

echo '<div id="col_width">';
echo '	<span>'.$module->l('Created on', 'orders_wishlists').' '.(version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate(date('Y-m-d')) : Tools::displayDate(date('Y-m-d'), $id_lang, false)).'</span>';

echo '	<span id="colwidth">';
echo '	<a id="cleanfilters">'.$module->l('Clear Filters', 'orders_wishlists').'</a><span>&nbsp;&nbsp;</span>';
echo '	<span id="rowcount"></span><span>&nbsp;'.$module->l('lines', 'orders_wishlists').')</span>';
echo '</div>';
?>
<div id="table_div">
<table id="table2" class="tablesorter" cellspacing="0" cellpadding="0">
<thead>
	<tr>
		<th class="header" id="date" style="width:10%"><?php echo $module->l('Date of order', 'orders_wishlists') ?></th>
		<th class="header center" id="order_print" style="width:10%"><?php echo $module->l('Order', 'orders_wishlists') ?> </th>
		<th class="header" filter-type="ddl" id="cust_print"><?php echo $module->l('Customer name', 'orders_wishlists') ?></th>
		<th class="header" id="carrier_print"><?php echo $module->l('Carrier', 'orders_wishlists') ?></th>
		<th class="header center" filter-type="ddl" id="nwl_print"><?php echo $module->l('Wishlist', 'orders_wishlists') ?> </th>
		<th class="header" filter-type="ddl" id="wlname_print"><?php echo $module->l('Wishlist name', 'orders_wishlists') ?></th>
		<th class="header" filter-type="ddl" id="creatorname_print"><?php echo $module->l('Wishlist creator name', 'orders_wishlists') ?></th>
	</tr>
</thead>
<tbody>
<?php
if (!empty($result))
	foreach ($result as $i => $line)
		{
			$ch_do = $module->l('Go to Back office / Customers', 'orders_wishlists').' / '.$module->l('Donator', 'orders_wishlists').' '.$line['lastname'].' '.$line['firstname'];
			$ch_cre = $module->l('Go to Back office / Customers', 'orders_wishlists').' / '.$module->l('Creator', 'orders_wishlists').' '.$line['lastname_wl'].' '.$line['firstname_wl'];
			$dispdat = version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($line['date_add']) : Tools::displayDate($line['date_add'], $id_lang, false);
		?>
	<tr>
		<td><?php echo $dispdat ?></td>
		<td class="center"><?php echo $line['id_order'] ?></td>
		<td><a href="
			<?php
			echo $partial_link_admincustomer.'&id_customer='.(int)$line['id_donator'].'&viewcustomer';
			?>"
			alt="<?php
			echo $ch_do;
			?>" title="
			<?php
			echo $ch_do;
			?>" target="_blank">
			<?php echo $line['lastname'].' '.$line['firstname'] ?>
			</td>
		<td>
			<?php echo $line['carrier'] ?>
		</td>
		<td class="center">
			<?php echo $line['id_wishlist'] ?>
		</td>
		<td>
			<?php echo $line['name_wl'] ?>
		</td>
		<td><a href="
			<?php echo $partial_link_admincustomer.'&id_customer='.(int)$line['id_creator'].'&viewcustomer';?>
			" alt="
			<?php echo $ch_cre;?>
			" title="
			<?php echo $ch_cre;?>
			" target="_blank">
			<?php echo $line['lastname_wl'].' '.$line['firstname_wl'];
			?></a></td>
	</tr>
	<?php
		}
	?>

<?php
if (empty($result))
{
?>
	<tr>
		<td></td>
		<td class="center"></td>
		<td></td>
		<td></td>
		<td class="center"></td>
		<td></td>
		<td></td>
	</tr>
<?php
}
?>

</tbody>

<tfoot>
	<tr>
		<th colspan="8" id="pagerOne" class="pagerOne">
			<img src="<?php echo _MODULE_DIR_.BlockWishListpro::MODULENAME ?>/views/img/icon/first.png" class="first" height="9" width="9" />
			<img src="<?php echo _MODULE_DIR_.BlockWishListpro::MODULENAME ?>/views/img/icon/prev.png" class="prev" height="11" width="9" />
			<input type="text" class="pagedisplay" disabled="disabled" size="5"/>
			<img src="<?php echo _MODULE_DIR_.BlockWishListpro::MODULENAME ?>/views/img/icon/next.png" class="next" height="11" width="9" />
			<img src="<?php echo _MODULE_DIR_.BlockWishListpro::MODULENAME ?>/views/img/icon/last.png" class="last" height="9" width="9" />
			<select class="pagesize">
						<option  value="10">10</option>
						<option value="20">20</option>
						<option value="30"  selected="selected">30</option>
						<option  value="40">40</option>
						<option  value="50">50</option>
						<option  value="100">100</option>
			</select> / <?php echo $module->l('page', 'orders_wishlists') ?>
		</th>
	</tr>
</tfoot>

</table>
</div>


<!-- // table to export to CSV format without filter and footer lines // -->
<table cellspacing='1' cellpadding='0' id='exportformat' class="hidden_wl">
<thead>
	<tr>
		<th><?php echo $module->l('Date of order', 'orders_wishlists') ?></th>
		<th><?php echo $module->l('Order', 'orders_wishlists') ?></th>
		<th><?php echo $module->l('Customer name', 'orders_wishlists') ?></th>
		<th><?php echo $module->l('Carrier', 'orders_wishlists') ?></th>
		<th><?php echo $module->l('Wishlist', 'orders_wishlists') ?></th>
		<th><?php echo $module->l('Wishlist name', 'orders_wishlists') ?></th>
		<th><?php echo $module->l('Wishlist creator name', 'orders_wishlists') ?></th>
	</tr>
</thead>
<tbody>
<?php
foreach ($result as $i => $line)
	{
		$dispdat = version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($line['date_add']) : Tools::displayDate($line['date_add'], $id_lang, false);

	?>
<tr>
	<td><?php echo $dispdat ?></td>
	<td><?php echo $line['id_order']?></td>
	<td><?php echo $line['lastname'].' '.$line['firstname']?></td>
	<td><?php echo $line['carrier']?></td>
	<td><?php echo $line['id_wishlist']?></td>
	<td><?php echo $line['name_wl'] ?></td>
	<td><?php echo $line['lastname_wl'].' '.$line['firstname_wl']?></td>
</tr>
<?php
}
?>

<?php if (empty($result))
{
?>
	<tr>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
	</tr>
<?php
}
?>

</tr></tbody>
</table>


<a href="#cockpit" class="back_cockpit bottomright noprint"><img src="<?php echo _MODULE_DIR_.BlockWishListpro::MODULENAME ?>/views/img/icon/back(visitors).gif" height="16" width="16" style="text-decoration:none; margin:3px 6px 0 3px" alt="" /><?php echo $module->l('Back to cockpit', 'orders_wishlists') ?></a>