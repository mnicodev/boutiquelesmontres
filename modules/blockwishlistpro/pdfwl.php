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

/*
* PDF class, PDF.php
* PDF invoices and document management
* @category classes
*
*/
require_once(dirname(__FILE__).'/../../tools/fpdf/fpdf.php');
require_once(dirname(__FILE__).'/../../classes/Tools.php');
require_once(dirname(__FILE__).'/../../classes/PDF.php');
require_once(dirname(__FILE__).'/../../classes/Currency.php');
require_once(dirname(__FILE__).'/../../classes/Address.php');
require_once(dirname(__FILE__).'/../../classes/State.php');
require_once(dirname(__FILE__).'/../../classes/Customer.php');
require_once(dirname(__FILE__).'/../../classes/Configuration.php');
require_once('WishListpro.php');
include_once('blockwishlistpro.php');

/*ob_end_clean();*/
class PDFWl extends PDF
{
	protected static $order = null;
	protected static $orderReturn = null;
	protected static $orderSlip = null;
	protected static $delivery = null;
	protected static $_priceDisplayMethod;
	protected static $id_wishlist = null;
	/** @var object Order currency object */
/*	private static $currency = null;*/
	protected static $_iso;
	/** @var array Special PDF params such encoding and font */
	protected static $_pdfparams = array();
	protected static $_fpdf_core_fonts = array('courier', 'helvetica', 'helveticab', 'helveticabi', 'helveticai', 'symbol', 'times', 'timesb', 'timesbi', 'timesi', 'zapfdingbats');
/*	/**
	*/
	public function __construct()
	{
		parent::__construct();
	}

	/**
	*  header
	*/
	public function Header()
	{
		$context = Context::getContext();
		$cookie = $context->cookie;
		$id_lang = $cookie->id_lang ? (int)$cookie->id_lang : (int)Configuration::get('PS_LANG_DEFAULT');
		$module = new BlockWishListpro();
		$conf = Configuration::getMultiple(array('PS_SHOP_NAME', 'PS_SHOP_ADDR1', 'PS_SHOP_CODE', 'PS_SHOP_CITY', 'PS_SHOP_COUNTRY', 'PS_SHOP_STATE'));
		$conf['PS_SHOP_NAME'] = isset($conf['PS_SHOP_NAME']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_NAME']) : 'Your company';
		$conf['PS_SHOP_ADDR1'] = isset($conf['PS_SHOP_ADDR1']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_ADDR1']) : 'Your company';
		$conf['PS_SHOP_CODE'] = isset($conf['PS_SHOP_CODE']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_CODE']) : 'Postcode';
		$conf['PS_SHOP_CITY'] = isset($conf['PS_SHOP_CITY']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_CITY']) : 'City';
		$conf['PS_SHOP_COUNTRY'] = isset($conf['PS_SHOP_COUNTRY']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_COUNTRY']) : 'Country';
		$conf['PS_SHOP_STATE'] = isset($conf['PS_SHOP_STATE']) ? Tools::iconv('utf-8', self::encoding(), $conf['PS_SHOP_STATE']) : '';

		if (file_exists(_PS_IMG_DIR_.'logo.jpg'))
			$this->Image(_PS_IMG_DIR_.'logo.jpg', 10, 8, 0, 15);
		$this->SetFont(self::fontname(), 'B', 15);
		$this->Cell(115);
/*		->l('WISHLIST #') /*do not erase !  used to create translation form ! */
		$this->Cell(77, 10, self::$module->l('WISHLIST #', 'pdfwl').' '.sprintf('%06d', self::$id_wishlist), 0, 1, 'R');
		$this->SetFont(self::fontname(), 'B', 14);

		$name = WishListpro::getCreatorID(self::$id_wishlist);
		$wl_info = WishListpro::getByIdCustomer($name[0]['id_customer']);
		/* Display address information */
		$id_address = Address::getFirstCustomerAddressId((int)$name[0]['id_customer']);
		$invoice_address = new Address($id_address);
		$invoiceState = $invoice_address->id_state ? new State($invoice_address->id_state) : false;
		$shop_country = Configuration::get('PS_SHOP_COUNTRY');
		$invoice_customer = new Customer((int)$name[0]['id_customer']);

		foreach ($wl_info as $row)
			if ($row['id_wishlist'] == self::$id_wishlist) $wl_name = $row['name'];
		$this->Cell(115);
		$this->Cell(77, 7, Tools::iconv('utf-8', self::encoding(), $invoice_address->firstname).' '.Tools::iconv('utf-8', self::encoding(), $invoice_address->lastname), 0, 1, 'R');
		$this->SetFont(self::fontname(), 'B', 12);
		$this->Cell(115);
		$this->Cell(77, 5, Tools::iconv('utf-8', self::encoding(), $wl_name), 0, 1, 'R');

		$width = 100;

		$this->SetX(40);
		$this->SetY(30);
		$this->SetFont(self::fontname(), '', 12);
		if (!empty($invoice_address->company))
		{
			$this->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->company), 0, 'L');
			$this->Ln(5);
		}
		$this->SetFont(self::fontname(), 'B', 12);
		$this->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->firstname).' '.Tools::iconv('utf-8', self::encoding(), $invoice_address->lastname), 0, 'L');
		$this->Ln(5);
		$this->SetFont(self::fontname(), '', 11);
		if (!empty($name[0]['email']))
		{
			$this->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $name[0]['email']), 0, 'L');
			$this->Ln(6);
		}
		$this->SetFont(self::fontname(), '', 9);
		$this->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->address1), 0, 'L');
		$this->Ln(4);
		if (!empty($invoice_address->address2))
		{
			$this->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->address2), 0, 'L');
			$this->Ln(4);
		}
		$this->Cell($width, 10, $invoice_address->postcode.' '.Tools::iconv('utf-8', self::encoding(), $invoice_address->city), 0, 'L');
		$this->Ln(4);
		$this->Cell($width, 10, Tools::iconv('utf-8', self::encoding(), $invoice_address->country.($invoiceState ? ' - '.$invoiceState->name : '')), 0, 'L');
		$this->Ln(4);
/*		->l('Tax ID number:') /*do not erase !  used to create translation form ! */
		if (isset($invoice_customer->dni))
			if ($invoice_customer->dni != null)
				$this->Cell($width, 10, $module->l('Tax ID number:', 'pdfwl').' '.Tools::iconv('utf-8', self::encoding(), $invoice_customer->dni), 0, 'L');
		if (!empty($invoice_address->phone_mobile))
			$this->Ln(4);
		$this->Ln(5);
	}
/*--------------------------------------------*/
	/**
	* footer
	*/
	public function Footer()
	{
		$this->SetY(-33);
		$this->SetFont(self::fontname(), '', 7);
		$this->Cell(190, 5, ' '."\n".Tools::iconv('utf-8', self::encoding(), 'P. ').$this->GroupPageNo().' / '.$this->PageGroupAlias(), 'T', 1, 'R');

			$this->Ln(4);
		$this->Ln(9);
		$arrayConf = array('PS_SHOP_NAME', 'PS_SHOP_ADDR1', 'PS_SHOP_ADDR2', 'PS_SHOP_CODE', 'PS_SHOP_CITY', 'PS_SHOP_COUNTRY', 'PS_SHOP_DETAILS', 'PS_SHOP_PHONE', 'PS_SHOP_STATE');
		$conf = Configuration::getMultiple($arrayConf);
		$conf['PS_SHOP_NAME_UPPER'] = Tools::strtoupper($conf['PS_SHOP_NAME']);
		foreach ($conf as $key => $value)
			$conf[$key] = Tools::iconv('utf-8', self::encoding(), $value);
		foreach ($arrayConf as $key)
			if (!isset($conf[$key]))
				$conf[$key] = '';
		$this->SetFillColor(240, 240, 240);
		$this->SetTextColor(0, 0, 0);
		$this->SetFont(self::fontname(), '', 8);
/*		->l('Headquarters:') /*do not erase !  used to create translation form ! */
		$this->Cell(0, 5, $conf['PS_SHOP_NAME_UPPER'].
		(!empty($conf['PS_SHOP_ADDR1']) ? ' - '.self::l('Headquarters:').' '.$conf['PS_SHOP_ADDR1'].(!empty($conf['PS_SHOP_ADDR2']) ? ' '.$conf['PS_SHOP_ADDR2'] : '').' '.$conf['PS_SHOP_CODE'].' '.$conf['PS_SHOP_CITY'].((isset($conf['PS_SHOP_STATE']) && !empty($conf['PS_SHOP_STATE'])) ? (', '.$conf['PS_SHOP_STATE']) : '').' '.$conf['PS_SHOP_COUNTRY'] : ''), 0, 1, 'C', 1);
/*		->l('Details:') ->l('PHONE:') /*do not erase !  used to create translation form ! */
		$this->Cell(0, 5,
		(!empty($conf['PS_SHOP_DETAILS']) ? self::l('Details:').' '.$conf['PS_SHOP_DETAILS'].' - ' : '').
		(!empty($conf['PS_SHOP_PHONE']) ? self::l('PHONE:').' '.$conf['PS_SHOP_PHONE'] : ''), 0, 1, 'C', 1);
	}

/*--------------------------------------------*/

/*parent methods with "private" status, need to redefine them | extracted from PHP class*/
	public static function encoding()
	{
		return (isset(self::$_pdfparams[self::$_iso]) && is_array(self::$_pdfparams[self::$_iso]) && self::$_pdfparams[self::$_iso]['encoding']) ? self::$_pdfparams[self::$_iso]['encoding'] : 'iso-8859-1';
	}
	public static function embedfont()
	{
		return (((isset(self::$_pdfparams[self::$_iso]) && is_array(self::$_pdfparams[self::$_iso]) && self::$_pdfparams[self::$_iso]['font']) && !in_array(self::$_pdfparams[self::$_iso]['font'], self::$_fpdf_core_fonts)) ? self::$_pdfparams[self::$_iso]['font'] : false);
	}
	public static function fontname()
	{
		$font = self::embedfont();
		return $font ? $font : 'Arial';
	}
/*----------------------------------------------------------------------*/
	/* to create pdf sheet */
	public static function WlSummary($id_wishlist, $tab, $data_list, $message_donator, $id_currency = null, $id_lang)
	{
		self::$id_wishlist = $id_wishlist;
		$context = Context::getContext();
		$cookie = $context->cookie;
		$module = new BlockWishListpro();

		$pdf = new PDFWl('P', 'mm', 'A4');
		$pdf->SetAutoPageBreak(true, 35);
		$pdf->StartPageGroup();
		$pdf->AliasNbPages();
		$pdf->k = 3; 									/*factor scale*/

		$pdf->AddPage();
		$pdf->Ln(3);
/*		->l('Date') ->l('Donator') ->l('Product') ->l('Manufacturer') ->l('Qty') ->l('Total') /*do not erase !  used to create translation form ! */
		$header = array(
			array($module->l('Date', 'pdfwl'), 'C'),
			array($module->l('Donator', 'pdfwl'), 'L'),
			array('', 'C'),
			array($module->l('Product', 'pdfwl'), 'L'),
			array($module->l('Manufacturer', 'pdfwl'), 'L'),
			array($module->l('Qty', 'pdfwl'), 'C'),
			array($module->l('Total', 'pdfwl'), 'C')
		);
		$w = array(17,37,20,60,30, 10, 17); 		/*columns width*/
		$align = array('C', 'L', 'C', 'L', 'L', 'C', 'R');
		$w_mess = array(18,37,135); 		/*messages section, columns width*/
		$align_mess = array('C', 'L', 'L');
		$width = 30;
		$pdf->SetFont(self::fontname(), 'B', 12);
/*		->l('Gifts')  /*do not erase !  used to create translation form ! */
		$pdf->Cell($width, 10, $module->l('Gifts', 'pdfwl'), 0, 1, 'L');
		$pdf->Ln(1);
		$pdf->SetFont(self::fontname(), 'B', 8);
		$pdf->SetFillColor(240, 240, 240);
		$cpt_header = count($header);
		for ($i = 0; $i < $cpt_header; $i++)
			if ($i == 2) $pdf->Cell($w[$i], 5, $header[$i][0], 'LTB', 0, $header[$i][1], 1);
			elseif ($i == 3) $pdf->Cell($w[$i], 5, $header[$i][0], 'TBR', 0, $header[$i][1], 1);
			else
			$pdf->Cell($w[$i], 5, $header[$i][0], 1, 0, $header[$i][1], 1);
		$pdf->Ln();
		$pdf->SetFont(self::fontname(), '', 8);

/*Table de x lignes et 7 colonnes*/
$pdf->SetWidths($w);
$pdf->SetAligns($align);

srand(microtime() * 1000000);
$ct_header = count($header);
$header_mess = array(
		array($module->l('Date', 'pdfwl'), 'C'),
		array($module->l('Donator', 'pdfwl'), 'L'),
		array($module->l('Message', 'pdfwl'), 'J')
);
$ct_header_mess = count($header_mess);

	foreach ($tab as $i => $product)
	{
		$pdf->Row(array(
		Tools::iconv('utf-8', self::encoding(), Tools::displayDate($product['date'], $cookie->id_lang, false, '-')),
		($product['donator_name'] != '' ? Tools::iconv('utf-8', self::encoding(), $product['donator_name']) : '---')
		.' '.
		($product['donator_firstname'] != '' ? Tools::iconv('utf-8', self::encoding(), $product['donator_firstname']) : '---'),
		(file_exists($product['file_img']) ? $product['file_img'] : _PS_MODULE_DIR_.BlockWishListpro::MODULENAME.'views/img/none.jpg'), ($product['product_name'] != '' ? str_replace('&euro;', chr(128), Tools::iconv('utf-8', self::encoding(), self::convertSign($product['product_name']))) : '---'),
		($product['product_manufacturer_name'] != '' ? Tools::iconv('utf-8', self::encoding(), $product['product_manufacturer_name']) : '---'),
		($product['quantity'] != '' ? Tools::iconv('utf-8', self::encoding(), $product['quantity']) : '---'),
		($product['total'] != '' ? self::convertSign(Tools::displayPrice($product['total'], (int)$id_currency, true, false)) : '---')
		));
	}

		for ($i = 0; $i < $ct_header; $i++)
			{
			if ($i == 0)
				$pdf->Cell($w[$i], 5, '', 'LTB', 0, $header[$i][1], 1);
			elseif ($i > 0 && $i < count($header) - 1)
				$pdf->Cell($w[$i], 5, '', 'TB', 0, $header[$i][1], 1);
			else
				$pdf->Cell($w[6], 5, ($data_list['products']['wl'] != '' ? self::convertSign(Tools::displayPrice($data_list['products']['wl'], (int)$id_currency, true, false)) : '---'), 1, 'TBR', $header[$i][1], 1);
			}

/*Messages of donators*/
		$pdf->Ln(10);
		$pdf->SetFont(self::fontname(), 'B', 12);
/*		->l('Messages of donators')  /*do not erase !  used to create translation form ! */
		$pdf->Cell($width, 10, $module->l('Messages of donators', 'pdfwl'), 0, 1, 'L');
	$pdf->Ln(1);
/*		->l('Donator') ->l('Message')  /*do not erase !  used to create translation form ! */
	$pdf->SetFont(self::fontname(), 'B', 8);
	$pdf->SetFillColor(240, 240, 240);
	for ($i = 0; $i < $ct_header_mess; $i++)
		$pdf->Cell($w_mess[$i], 5, $header_mess[$i][0], 1, 0, $header_mess[$i][1], 1);
	$pdf->Ln();
	$pdf->SetFont(self::fontname(), '', 8);

$pdf->SetWidths_mess($w_mess);
$pdf->SetAligns_mess($align_mess);
	foreach ($message_donator as $i => $ord)
	{
		if ($ord['message'] != '')
		{
			$ord['date'] = isset($ord['date']) ? $ord['date'] : '1900-01-01';
			$pdf->Row_message(array(

				Tools::displayDate($ord['date'], $id_lang, false, '-'),
				($ord['name'] != '' ? Tools::iconv('utf-8', self::encoding(), $ord['name']) : '---'),
				($ord['message'])
				));
		}
	}
		return $pdf;
	}

/*--------------http://www.fpdf.org/fr/script/script3.php----------------------------------*/
/*saut de ligne automatique et hauteur de ligne homogène*/
var $widths;
var $aligns;
public function SetWidths($w)
{
/*Tableau des largeurs de colonnes*/
	$this->widths = $w;
}

public function SetAligns($a)
{
	/*Tableau des alignements de colonnes*/
	$this->aligns = $a;
}
var $widths_mess;
var $aligns_mess;
public function SetWidths_mess($w_me)
{
	/*Tableau des largeurs de colonnes*/
	$this->widths_mess = $w_me;
}

public function SetAligns_mess($a_mess)
{
	/*Tableau des alignements de colonnes*/
	$this->aligns_mess = $a_mess;
}

public function Row($data)
{
	/*Calcule la hauteur de la ligne*/
	$nb = 0;
	$h2 = 0;
	$width_img = 10; /*image width setting*/
	$ct_data = count($data);
	for ($i = 0; $i < $ct_data; $i++)
	{
		if ($i !== 2)
		{
			$nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
			$h1 = $this->k * $nb * 1.2;						/*1.2 padding-bottom and linesize*/
		}
		else
		{
			$a = getimagesize($data[$i]); /*array width, height*/
			$h2 = $a[1] * ($width_img / $a[0]) * 1.5;
		}
		$h = max($h1, $h2);
	}
	/*Effectue un saut de page si nécessaire*/
	$this->CheckPageBreak($h);
	/*Dessine les cellules*/
	for ($i = 0; $i < $ct_data; $i++)
	{
		$w = $this->widths[$i];
		$a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
		/*Sauve la position courante*/
		$x = $this->GetX();
		$y = $this->GetY();
		/*Imprime le texte/*
	/*        $this->setY($this->GetY()-0.5);*/
		if ($i !== 2 && $i !== 3)
		{
		/*Dessine le cadre*/
		$this->Rect($x, $y, $w, $h);
		$this->MultiCell($w, $this->k * 1.3, $data[$i], 0, $a); /*1.3 padding-top*/
		}
		elseif ($i == 2)
		{
			$this->Cell($w, $h, '', 'TB');		/* to draw borders cell*/
			/*check this image, get info*/
				$pos = strrpos($data[$i], '.');
				if ($pos)
				{
					$type = Tools::substr($data[$i], $pos + 1);
					$type = Tools::strtolower($type);
					if ($type == 'jpeg')
						$type = 'jpg';
					$mtd = '_parse'.$type;
					if (method_exists($this, $mtd))
						$this->Image($data[$i], $x + 1, $y + 1, $width_img, 0);
				}
		}
		else
		{
			$this->MultiCell($w, $this->k * 1.3, $data[$i], 'T', $a); /*1.3 padding-top*/
			$this->Rect($x, $y + $h, $w, 0);		/*border bottom because Rect... doesn't work : draw left border ! */
		}

		/*Repositionne à droite*/
		$this->SetXY($x + $w, $y);
	}
	/*Va à la ligne*/
	$this->Ln($h);
}

public function Row_message($data)
{
	/*Calcule la hauteur de la ligne*/
	$nb = 0;
	$ct_data = count($data);
	for ($i = 0; $i < $ct_data; $i++)
	{
		$nb = max($nb, $this->NbLines($this->widths_mess[$i], $data[$i]));
		$h = $this->k * $nb * 1.4 + 4;				/*1.2 padding-bottom and linesize*/
	}
	/*Effectue un saut de page si nécessaire*/
	$this->CheckPageBreak($h);
	/*Dessine les cellules*/
	for ($i = 0; $i < $ct_data; $i++)
	{
		$w = $this->widths_mess[$i];
		$a = isset($this->aligns_mess[$i]) ? $this->aligns_mess[$i] : 'L';
		/*Sauve la position courante*/
		$x = $this->GetX();
		$y = $this->GetY();
		/*Imprime le texte*/
		/*Dessine le cadre*/
		$this->Rect($x, $y, $w, $h);
		$this->MultiCell($w, $this->k * 1.6, $data[$i], 0, $a); /*1.5 padding-top*/
	/*function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)*/
		/*Repositionne à droite*/
		$this->SetXY($x + $w, $y);
	}
	/*Va à la ligne*/
	$this->Ln($h);
}

public function CheckPageBreak($h)
{
	/*Si la hauteur h provoque un débordement, saut de page manuel*/
	if ($this->GetY() + $h > $this->PageBreakTrigger)
		$this->AddPage($this->CurOrientation);
}

public function NbLines($w, $txt)
{
	/*Calcule le nombre de lignes qu'occupe un MultiCell de largeur w*/
	$cw = &$this->CurrentFont['cw'];
	if ($w == 0)
		$w = $this->w - $this->rMargin - $this->x;
	$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
	$s = str_replace("\r", '', $txt);
	$nb = Tools::strlen($s);
	if ($nb > 0 && $s[$nb - 1] == '\n')
		$nb--;
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0;
	$nl = 1;
	while ($i < $nb)
	{
		$c = $s[$i];
		if ($c == '\n')
		{
			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			$nl++;
			continue;
		}
		if ($c == ' ')
			$sep = $i;
		$l += $cw[$c];
		if ($l > $wmax)
		{
			if ($sep == -1)
			{
				if ($i == $j)
					$i++;
			}
			else
				$i = $sep + 1;
			$sep = -1;
			$j = $i;
			$l = 0;
			$nl++;
		}
		else
			$i++;
	}
	return $nl;
}
/*--------------------------------------------*/

	protected static function convertSign($s)
	{
		return str_replace('¥', chr(165), str_replace('£', chr(163), str_replace('€', chr(128), $s)));

	}
}