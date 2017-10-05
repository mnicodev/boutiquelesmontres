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

/* @since 1.5.0 */
/* Use of TCPDF class */
require_once(_PS_CLASS_DIR_.'/Tools.php');
require_once(_PS_TOOL_DIR_.'/tcpdf/tcpdf.php');
include_once('blockwishlistpro.php');

/** Extend the TCPDF class to create custom Header and Footer*/
class MYPDF extends TCPDF {
	/*Page header*/
	public function Header()
	{
		$module = new BlockWishListpro();
		/* Logo*/
		$image_file = is_file(_PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE')) ? _PS_IMG_DIR_.Configuration::get('PS_LOGO_INVOICE') : _PS_IMG_DIR_.Configuration::get('PS_LOGO');
		/* test file_exists*/
		if (is_file($image_file))
			$this->Image($image_file, 10, 5, 25, 0, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
		/* Set font*/
		$this->SetFont('helvetica', 'B', 20);
		/* Title*/
		$this->Cell(0, 15, $module->l('Gift list report', 'pdfwl-tcpdf'), 0, false, 'C', 0, '', 0, false, 'M', 'M');
	}
	/* Page footer*/
	public function Footer()
	{
		$module = new BlockWishListpro();
		/* Position at 15 mm from bottom*/
		$this->SetY(-15);
		/* Set font*/
		$this->SetFont('helvetica', '', 8);
		/* Page number*/
		$this->Cell(0, 15, $module->l('Page', 'pdfwl-tcpdf').' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
				$arrayConf = array('PS_SHOP_NAME', 'PS_SHOP_ADDR1', 'PS_SHOP_ADDR2', 'PS_SHOP_CODE', 'PS_SHOP_CITY', 'PS_SHOP_COUNTRY', 'PS_SHOP_DETAILS', 'PS_SHOP_PHONE', 'PS_SHOP_STATE');
		$conf = Configuration::getMultiple($arrayConf);
		$conf['PS_SHOP_NAME_UPPER'] = Tools::strtoupper($conf['PS_SHOP_NAME']);
		foreach ($conf as $key => $value)
			$conf[$key] = Tools::iconv('utf-8', 'utf-8', $value);
		foreach ($arrayConf as $key)
			if (!isset($conf[$key]))
				$conf[$key] = '';
		$foot = '<p>'.$conf['PS_SHOP_NAME_UPPER'].' | '.(!empty($conf['PS_SHOP_DETAILS']) ? $module->l('Details:', 'pdfwl-tcpdf').' '.$conf['PS_SHOP_DETAILS'].' - ' : '').(!empty($conf['PS_SHOP_PHONE']) ? $module->l('PHONE:', 'pdfwl-tcpdf').' '.$conf['PS_SHOP_PHONE'] : '').' | '.$conf['PS_SHOP_ADDR1'].(!empty($conf['PS_SHOP_ADDR2']) ? ' '.$conf['PS_SHOP_ADDR2'] : '').' '.$conf['PS_SHOP_CODE'].' '.$conf['PS_SHOP_CITY'].((isset($conf['PS_SHOP_STATE']) && !empty($conf['PS_SHOP_STATE'])) ? (', '.$conf['PS_SHOP_STATE']) : '').' '.((isset($conf['PS_SHOP_STATE']) ? $conf['PS_SHOP_COUNTRY'] : '')).'</p>';
			$this->writeHTML($foot, true, false, true, false, 'T');
	}

	/* to create pdf sheet */
	public static function WlSummary_tcpdf($id_wishlist, $tab, $data_list, $message_donator, $id_currency, $id_lang)
	{
		/* create new PDF document (custom header footer)*/
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		/* set default header data*/
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		/* set header and footer fonts*/
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		/* set default monospaced font*/
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		/*set margins*/
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		/*set auto page breaks*/
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		/*set image scale factor*/
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		/*set some language-dependent strings
		$pdf->setLanguageArray($l);
		set default font subsetting mode
		$pdf->setFontSubsetting(true);*/
		/* set font*/
		$pdf->SetFont('helvetica', '', 9);
		/* Add a page*/
		/* This method has several options, check the source code documentation for more information.*/
		$pdf->AddPage();

		$wishlist = new WishListpro((int)$id_wishlist);
		if (!Validate::isLoadedObject($wishlist))	die(Tools::displayError('cannot find wishlist in database'));
		$name = WishListpro::getCreatorID($id_wishlist);
		/* Image size in table (if php_ c url extension not activated) */

		if (version_compare(_PS_VERSION_, '1.5', '>=') && version_compare(_PS_VERSION_, '1.5.1', '<'))
		{
			/*1.5.0.17 */
			$smallsizewl = Image::getSize('small');
		}
		elseif (version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.3.0', '<'))
		{
			/*$smallsizewl = Image::getSize('small_default');*/
			$smallsizewl = Image::getSize(BlockWishListpro::getFormatedName('small'));
		}
		else
			$smallsizewl = Image::getSize(ImageType::getFormatedName('small'));

		$smallHeight = 34;
		if (isset($smallsizewl) && count($smallsizewl))
			$smallWidth = $smallHeight * $smallsizewl['width'] / $smallsizewl['height'];
		else
			$smallWidth = $smallHeight;

		/* Display address information */
		$id_address = Address::getFirstCustomerAddressId((int)$name[0]['id_customer']);
		$invoice_address = new Address($id_address);
		$first_n = isset($invoice_address->firstname) ? $invoice_address->firstname : '';
		$last_n = isset($invoice_address->lastname) ? $invoice_address->lastname : '';
		$module = new BlockWishListpro(); /*translation with $module->l('text', 'file without extension)*/

		$html_table = '<table cellpadding="3" style="font-weight:bold">
						<tr>
							<td>'.$module->l('List', 'pdfwl-tcpdf').' : '.$wishlist->name.' ('.sprintf('%06d', $id_wishlist).')</td>
						 </tr>
						 <tr>
						 	<td>'.$first_n.' '.$last_n.' ('.(isset($name[0]['email']) ? $name[0]['email'] : '').')</td>
						</tr>
					   </table>';

		$header_table = array(
		array($module->l('Date', 'pdfwl-tcpdf'), 'L', '10%'),
		array($module->l('Donator', 'pdfwl-tcpdf'), 'L', '15%'),
		array('', 'C', '7%'),
		array($module->l('Product', 'pdfwl-tcpdf'), 'L', '31%'),
		array($module->l('Manufacturer', 'pdfwl-tcpdf'), 'L', '23%'),
		array($module->l('Qty', 'pdfwl-tcpdf'), 'C', '7%'),
		array($module->l('Total', 'pdfwl-tcpdf'), 'C', '12%')
		);
		$html_table .= '
		<style>
			table.content_wl {
				border: 1px solid #ddd;
			}
			table.content_wl td {border-bottom: 1px solid #ddd}
		</style>
		<h4>'.$module->l('Detail of gifts', 'pdfwl-tcpdf').'</h4>
		<table cellpadding="3" class="content_wl">
							<tr style="background-color:#f0f0f0;">';
		foreach ($header_table as $row)
			$html_table .= '	<td style="text-align:'.($row[1] == 'C' ? 'center' : 'left').';width:'.$row[2].'">'.$row[0].'</td>';
		$html_table .= '</tr>';

		foreach ($tab as $product)
		{
			$file = file_exists($product['image']) ? fopen($product['image'], 'r') : '';
			$html_table	.= '<tr>';
			$html_table	.=	'<td>'.Tools::iconv('utf-8', 'utf-8', (version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($product['date']) : Tools::displayDate($product['date'], $id_lang, false))).'</td>
							<td>'.($product['donator_name'] != '' ? Tools::iconv('utf-8', 'utf-8', $product['donator_name']) : '---')
						.' '.
						($product['donator_firstname'] != '' ? Tools::iconv('utf-8', 'utf-8', $product['donator_firstname']) : '---').'</td>
									<td>'.($product['image'] && file_exists($product['image']) ? '<img class="in_pdf" src="'.$product['image'].'" height="'.(int)$smallHeight.'" width="'.(int)$smallWidth.'">' : '').'</td>
									<td>'.($product['product_name'] != '' ? Tools::iconv('utf-8', 'utf-8', self::convertSign($product['product_name'])) : '---').'</td>
									<td>'.($product['product_manufacturer_name'] != '' ? Tools::iconv('utf-8', 'utf-8', $product['product_manufacturer_name']) : '---').'</td>
									<td style="text-align:center">'.($product['quantity'] != '' ? Tools::iconv('utf-8', 'utf-8', $product['quantity']) : '---').'</td>
									<td style="text-align:center">'.($product['total'] != '' ? Tools::displayPrice($product['total'], (int)$id_currency, false) : '---').'</td>';
			$html_table	.= '</tr>';
		}

		$html_table	.= '<tr style="text-align:center"><td style="font-weight:bold;text-align:left">'.$module->l('Total', 'pdfwl-tcpdf').'</td><td></td><td></td><td></td><td></td><td colspan="2" style="font-weight:bold">'.($data_list['products']['wl'] != '' ? Tools::displayPrice($data_list['products']['wl'], (int)$id_currency, false) : '---').'</td></tr>';
		$html_table	.= '</table>
						<p>&nbsp;</p><p>&nbsp;</p>';
		$html_msg = '<h4>'.$module->l('Messages of donators', 'pdfwl-tcpdf').'</h4>
						<table cellpadding="3" class="content_wl">
							<tr style="background-color:#f0f0f0;">
								<td style="width:15%">'.$module->l('Date', 'pdfwl-tcpdf').'</td>
								<td style="width:25%">'.$module->l('Donator', 'pdfwl-tcpdf').'</td>
								<td style="width:60%">'.$module->l('Message', 'pdfwl-tcpdf').'</td>
							</tr>';
		foreach ($message_donator as $ord)
		{
			if ($ord['message'] != '')
			{
				$ord['date'] = isset($ord['date']) ? $ord['date'] : '1900-01-01';
				$html_msg .= '<tr>
								<td>'.(version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($ord['date']) : Tools::displayDate($ord['date'], $id_lang, false)).'</td>
								<td>'.($ord['name'] != '' ? Tools::iconv('utf-8', 'utf-8', $ord['name']) : '---').'</td>
								<td>'.nl2br($ord['message']).'</td>
							 </tr>';
			}

		}
		$html_msg .= '</table>';
		$html = $html_table.$html_msg;

/*$name = 'cheque 25﷼';
$pos = strpos($name, '€');
var_dump($pos);
echo 'car 128:'.chr(128);
echo 'utf encode:'.utf8_encode($name);
echo 'utf decode:'.utf8_decode($name);
echo 'htmlentities:'.htmlentities($name);

echo '$name = ';echo $name;
echo 'str_replace $name = ';echo str_replace('€', '&euro;', $name); //ok !!
echo 'convertSign $name = ';echo convertSign($name);
echo 'Tools:inconv $name = ';echo Tools::iconv('utf-8', 'utf-8', self::convertSign($name));
*/
		/* Print text using writeHTMLCell()*/
		$pdf->writeHTML($html, true, false, true, false, '');
		return $pdf;
	}

	protected static function convertSign($s)
	{
		return str_replace('¥', chr(165), str_replace('£', chr(163), str_replace('€', '&euro;', $s)));
	}
}