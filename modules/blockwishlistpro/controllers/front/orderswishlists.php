<?php
/**
* BLOCKWISHLISTPRO Front Office Feature - display products of a list, creator view
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

/*CORS cross domain allowed*/
$isHTTPS = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| $_SERVER['SERVER_PORT'] == 443;
header('Access-Control-Allow-Origin: '.($isHTTPS ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']);

class BlockWishListproOrderswishlistsModuleFrontController extends ModuleFrontController
{
public function __construct()
{
	parent::__construct();
	$this->context = Context::getContext();
	include_once($this->module->getLocalPath().'WishListpro.php');
}

public function initContent()
{
	parent::initContent();
	$this->assignaut();
}

/**
 * Assign wishlist template
 */
public function assignaut()
{
//	require_once('../../config/config.inc.php');
	// if (!defined('_PS_HOST_MODE_'))
	// 	require_once('../../../../init.php');
//	require_once('../../WishListpro.php');
//	require_once('../../../BlockWishListpro.php');
	$error = '';
	$chain = '';
	$id_empl = (int)Tools::getValue('id_empl');
	$context = Context::getContext();
	$id_lang = (int)(Tools::getValue('id_lang'));
	$tabid = Tools::getValue('tabid');
	$className = 'AdminCustomers';
	$tok_customeradmin = Tools::getAdminToken($className.(int)Tab::getIdFromClassName('AdminCustomers').(int)$id_empl);
	$partial_link_admincustomer = Dispatcher::getInstance()->createUrl('AdminCustomers', $id_lang, array('token' => $tok_customeradmin), false);
	$className = 'AdminModules';
	$cookie = $context->cookie;
	$token = Tools::getValue('token');
	$tokenAdmin = Tools::getAdminToken($className.(int)$tabid.(int)$id_empl);
	if (Configuration::get('PS_TOKEN_ENABLE') == 1 && strcmp($token, $tokenAdmin))
		$error = Tools::displayError('invalid token');

	if (!Tools::strlen($error) && empty($token) === false)
	{
		$module = new BlockWishListpro();
		$period_type = Tools::getValue('period_type');
		$date1 = Tools::getValue('date1');
		$date2 = Tools::getValue('date2');

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

		switch ($period_type)
		{
			case 'all':
				$period_type_transl = $module->l('all', 'orderswishlists');
				break;
			case 'selected period':
				$period_type_transl = $module->l('selected period', 'orderswishlists');
				break;
			case 'last 7 days':
				$period_type_transl = $module->l('last 7 days', 'orderswishlists');
				break;
			case 'last 30 days':
				$period_type_transl = $module->l('last 30 days', 'orderswishlists');
				break;
			case 'today':
				$period_type_transl = $module->l('today', 'orderswishlists');
				break;
		}

		/*table to export to excel format without filter and footer lines*/
		$chain .= '<div id="output_excel" class="back_blockwl submit_action">';
		$chain .= '	<form action="'._MODULE_DIR_.BlockWishListpro::MODULENAME.'/output-excel.php" method="post" target="_blank" onsubmit="$(\'#excel_table\').val($(\'<div>\').append( $(\'#exportformat\').eq(0).clone() ).html() );">';
		$chain .= '		<input type="hidden" name="excel_table" id="excel_table" />';
		$chain .= '	<input type="submit" id="submit_excel" class="submit_action" value="'.$module->l('Export EXCEL', 'orderswishlists').'" />';
		$chain .= '	</form>
			</div>';

		$chain .= '<div id="output_print" class="back_blockwl submit_action">
			<form method="post">
				<input type="submit" id="submit_print" value="'.$module->l('Print', 'orderswishlists').'" onclick="javascript:print_wl_table(\'table_div\');" />
			</form>
		</div>';

		$chain .= '<br style="clear:both" /><br />';
		$chain .= '<h1 id="h1">'.$module->l('Orders of wishlists over a period', 'orders_wishlists').'</h1>';
		$chain .= '<h2>';
		$chain .= $module->l('Type of period:', 'orderswishlists').' <span class="bg1"> '.$period_type_transl.'</span></h2>';
		if ($dateFrom == '1900-01-01')
			$dateFrom = '... ';
		else
			$dateFrom = (version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($dateFrom) : Tools::displayDate($dateFrom, $cookie->id_lang, false, '-'));
		if (empty($dateTo))
			$dateTo = '... ';

		$chain .= '<h3 id="h3">'.$module->l('From', 'orderswishlists').'&nbsp;<span class="bg1">'.$dateFrom.'</span>&nbsp;&nbsp;&nbsp;'.$module->l('to', 'orderswishlists').'&nbsp;<span class="bg1">'.(version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($dateTo) : Tools::displayDate($dateTo, $id_lang, false, '-')).'</span></h3>';

		$chain .= '<div id="col_width">';
		$chain .= '	<span>'.$module->l('Created on', 'orderswishlists').' '.(version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate(date('Y-m-d')) : Tools::displayDate(date('Y-m-d'), $id_lang, false, '-')).'</span>';
		$chain .= '	<span id="colwidth">';
			$chain .= '	<a id="cleanfilters">'.$module->l('Clear Filters', 'lists_wishlists').'</a><span>&nbsp;&nbsp;</span>';
			$chain .= '	<span id="rowcount"></span><span>&nbsp;'.$module->l('lines', 'orderswishlists').')</span>';
		$chain .= '</div>';

		$cls_head = '';
		$cls_select = '';
		$styl_width_10 = '';
		$cls_head = 'header';
		$styl_width_10 = 'style="width:10%"';

		$chain .= '<div id="table_div">';
		$chain .= '<table id="table2" class="tablesorter" cellspacing="0" cellpadding="0">
				<thead>';
		$chain .= '<tr>
			<th class="'.$cls_head.' id="date_0" '.$styl_width_10.'> '.$module->l('Date of order', 'orderswishlists').'</th>
			<th class="'.$cls_head.' center" id="order_print_0" '.$styl_width_10.'>'.$module->l('Order', 'orderswishlists').'</th>
			<th class="'.$cls_head.'" id="cust_print_0">'.$module->l('Customer name', 'orderswishlists').'</th>
			<th class="'.$cls_head.'" filter-type="ddl" id="carrier_print_0">'.$module->l('Carrier', 'orderswishlists').'</th>
			<th class="'.$cls_head.' center" id="nwl_print_0">'.$module->l('Wishlist', 'orderswishlists').'</th>
			<th class="'.$cls_head.'" id="wlname_print_0">'.$module->l('Wishlist name', 'orderswishlists').'</th>
			<th class="'.$cls_head.'" id="creatorname_print_0">'.$module->l('Wishlist creator name', 'orderswishlists').'</th>
			</tr>
		</thead>
		<tbody>';

		if (!empty($result))
			foreach ($result as $i => $line)
			{
				$ch_do = $module->l('Go to Back office / Customers', 'orderswishlists').' / '.$module->l('Donator', 'orderswishlists').' '.$line['lastname'].' '.$line['firstname'];
				$ch_cre = $module->l('Go to Back office / Customers', 'orderswishlists').' / '.$module->l('Creator', 'orderswishlists').' '.$line['lastname_wl'].' '.$line['firstname_wl'];
				$dispdat = version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($line['date_add']) : Tools::displayDate($line['date_add'], $id_lang, false);
			$chain .= '
			<tr>
				<td>'.$dispdat.'</td>
				<td class="center">'.$line['id_order'].'</td>
				<td>
					<a href="'.$partial_link_admincustomer.'&id_customer='.(int)$line['id_donator'].'&viewcustomer"	alt="'.$ch_do.'" title="'.$ch_do.'"	target="_blank">
					'.$line['lastname'].' '.$line['firstname'].'
					</a>
				</td>
				<td>'.$line['carrier'].'</td>
				<td class="center">'.$line['id_wishlist'].'</td>
				<td>'.$line['name_wl'].'</td>
				<td>
					<a href="
					'.$partial_link_admincustomer.'&id_customer='.(int)$line['id_creator'].'&viewcustomer" alt="'.$ch_cre.'"
					title="'.$ch_cre.'"	target="_blank">
					'.$line['lastname_wl'].' '.$line['firstname_wl'].'
					</a>
				</td>
			</tr>';
			}

		if (empty($result))
			$chain .= '
				<tr>
					<td></td>
					<td class="center"></td>
					<td></td>
					<td></td>
					<td class="center"></td>
					<td></td>
					<td></td>
				</tr>';

		$chain .= '
		</tbody>';

		$chain .= '
		<tfoot>
		<tr>
			<th colspan="8" id="pagerOne" class="pagerOne">
				<img src="'._MODULE_DIR_.BlockWishListpro::MODULENAME.'/views/img/icon/first.png" class="first" height="9" width="9" />
				<img src="'._MODULE_DIR_.BlockWishListpro::MODULENAME.'/views/img/icon/prev.png" class="prev" height="11" width="9" />
				<input type="text" class="pagedisplay" disabled="disabled" size="5"/>
				<img src="'._MODULE_DIR_.BlockWishListpro::MODULENAME.'/views/img/icon/next.png" class="next" height="11" width="9" />
				<img src="'._MODULE_DIR_.BlockWishListpro::MODULENAME.'/views/img/icon/last.png" class="last" height="9" width="9" />
				<select class="pagesize">
					<option  value="50">50</option>
					<option value="100">100</option>
					<option value="200"  selected="selected">200</option>
					<option value="500">500</option>
					<option value="2000">2000</option>
					<option value="10000">10000</option>
				</select> / '.$module->l('page', 'orderswishlists').'
			</th>
		</tr>
		</tfoot>';

		$chain .= '
			</table>
		</div>';

		// table to export to CSV format without filter and footer lines
		$chain .= "
		<table cellspacing='1' cellpadding='0' id='exportformat' style='visibility:hidden'>
		<thead>
			<tr>";
		$chain .= '
				<th>'.$module->l('Date of order', 'orderswishlists').'</th>
				<th>'.$module->l('Order', 'orderswishlists').'</th>
				<th>'.$module->l('Customer name', 'orderswishlists').'</th>
				<th>'.$module->l('Carrier', 'orderswishlists').'</th>
				<th>'.$module->l('Wishlist', 'orderswishlists').'</th>
				<th>'.$module->l('Wishlist name', 'orderswishlists').'</th>
				<th>'.$module->l('Wishlist creator name', 'orderswishlists').'</th>
			</tr>
		</thead>
		<tbody>';

		if (!empty($result))
		foreach ($result as $i => $line)
		{
			$dispdat = version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($line['date_add']) : Tools::displayDate($line['date_add'], $id_lang, false);

			$chain .= '
			<tr>
				<td>'.$dispdat.'</td>
				<td>'.$line['id_order'].'</td>
				<td>'.$line['lastname'].' '.$line['firstname'].'</td>
				<td>'.$line['carrier'].'</td>
				<td>'.$line['id_wishlist'].'</td>
				<td>'.$line['name_wl'].'</td>
				<td>'.$line['lastname_wl'].' '.$line['firstname_wl'].'</td>
			</tr>';
		}

		if (empty($result))
			$chain .= '
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>';

		$chain .= '
			</tr>
			</tbody>
			</table>';

		$chain .= '<a href="#cockpit" class="back_cockpit bottomright noprint"><img src="'._MODULE_DIR_.BlockWishListpro::MODULENAME.'/views/img/icon/back(visitors).gif" height="16" width="16" style="text-decoration:none; margin:3px 6px 0 3px" alt="" />'.$module->l('Back to cockpit', 'orderswishlists').'</a>';
	} /*end test if (!Tools::strlen($error) && empty($token) === false)*/
	die(Tools::jsonEncode($chain));
} /*end f° assignu*/
}