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
require_once('WishListpro.php');
require_once('blockwishlistpro.php');

	$choice_pdf = Tools::getValue('submit_summary') ? Tools::getValue('submit_summary') : '';
	$choice_pdf_email = Tools::getValue('submit_mail') ? Tools::getValue('submit_mail') : '';
	$choice_copy_mail = Tools::getValue('copy_mail') ? Tools::getValue('copy_mail') : '';
	$id_lang = Tools::getValue('id_lang') ? Tools::getValue('id_lang') : '';

	if (Tools::getValue('submit_summary'))
		Tools::getValue('id_list1') ? $id_wishlist = Tools::getValue('id_list1') : die (Tools::displayError('List ID is missing'));
	if (Tools::getValue('submit_mail'))
		Tools::getValue('id_list2') ? $id_wishlist = Tools::getValue('id_list2') : die (Tools::displayError('List ID is missing'));

	$gather = BlockWishListpro::gatherInfoPdf_tcpdf($id_wishlist, $id_lang);

	/* click summary table to open PDF*/
	if (!empty($choice_pdf))
	{
		if (ob_get_contents())
			ob_end_clean(); /*dd+ 1.5*/
		$gather[0]->Output('wishlist_summary.pdf', 'D');
	}
	/* click summary table to send PDF by MAIL*/
	if (!empty($choice_pdf_email))
	{
		$wishlist = new WishListpro($id_wishlist);
		if (!Validate::isLoadedObject($wishlist))
		die(Tools::displayError('Cannot find wishlist in database'));
		$wishlist1 = WishListpro::exists($id_wishlist, $wishlist->id_customer, true);
		if ($wishlist1 === false)
			exit(Tools::displayError('Invalid wishlist'));
		$module = new BlockWishListpro();
		$customer = new Customer($wishlist->id_customer);
		if (Validate::isLoadedObject($customer))
		{
			$template = 'mail_to_creator';
			$fileAttachment = array();
			/* Instance of module class for translations*/
			$subject = $module->l('Information about wishlist').' '.$wishlist1['name'].'"';
			$templateVars = array(
						'{lastname}' => $customer->lastname,
						'{firstname}' => $customer->firstname,
						'{wishlist}' => $wishlist1['name'],
						'{shop_phone}' => (string)Configuration::get('PS_SHOP_PHONE'),
						'{message}' => 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.BlockWishListpro::MODULENAME.'/view.php?token='.$wishlist1['token']);

			$to = $customer->email;
			$toName = $customer->firstname.' '.$customer->lastname;
			$from = (string)Configuration::get('PS_SHOP_EMAIL');
			$fromName = (string)Configuration::get('PS_SHOP_NAME');

			$temp_name = 'point_temp.pdf';
			$fileAttachment['content'] = $gather[0]->Output($temp_name, 'S');
/* retrieve '.' from $name_extension to avoid issue with file name extension | .pdf*/
			$wishlist1['name'] = str_replace('.', '-', $wishlist1['name']);
			$wishlist1['name'] = str_replace(' ', '-', $wishlist1['name']);
			$name_extension = $module->l('report').'-'.$wishlist1['name'].'-'.$toName.'.pdf';

			$fileAttachment['name'] = $name_extension;
			$fileAttachment['mime'] = 'application/pdf';
			$modeSMTP = null;
			$templatePath = dirname(__FILE__).'/mails/';

		$send_mail_creator = Mail::Send($id_lang, $template, $subject, $templateVars, $to, $toName, $from, $fromName, $fileAttachment, $modeSMTP, $templatePath);
		/*confirmation message only for non automatic sending*/
		if (!empty($choice_pdf_email))
		{
			echo '<link type="text/css" rel="stylesheet" href="'._MODULE_DIR_.BlockWishListpro::MODULENAME.'/views/css/wishlist_dd.css" />';
			if ($send_mail_creator)
			{
				if (isset($gather[1]))  /*gather[1] = latest order in pdf*/
				{
				/*sending date uptdate with last order id
				//test if the order already exists in the table
				 //latest order communicated in pdf mail*/
					$latest_order_pdfmail = WishListpro::getListLastOrderMailPDF($id_wishlist);
					if (empty($latest_order_pdfmail) == false)
					{
						if ($latest_order_pdfmail[0]['max_order'] != false)
						{
							if ((int)$latest_order_pdfmail[0]['max_order'] !== (int)$gather[1])
							{
								$tble_updt = Db::getInstance()->Execute('
								INSERT INTO `'._DB_PREFIX_.'wishlist_send_pdf'.BlockWishListpro::SUFFIX.'`
								(`id_wishlist`, `id_order`,`date_send_pdf`)
									VALUES	(
											'.(int)$id_wishlist.',
											'.(int)$gather[1].',
											\''.pSQL(date('Y-m-d H:i:s')).'\'
											)');
							}
						}
					}
					else
					{
							$tble_updt = Db::getInstance()->Execute('
							INSERT INTO `'._DB_PREFIX_.'wishlist_send_pdf'.BlockWishListpro::SUFFIX.'`
							(`id_wishlist`, `id_order`,`date_send_pdf`)
								VALUES	(
										'.(int)$id_wishlist.',
										'.(int)$gather[1].',
										\''.pSQL(date('Y-m-d H:i:s')).'\'
										)');
					}
				}

	/* do not erase, for translation purpose : $this->l('Email sent to') $this->l('email address') $this->l('Email has not been sent') */
					echo "<div style='font-weight:normal; margin:20px auto 2px 16px; text-align:left; width:70%; border:1px solid #DFD5C3; background-color:#FFFFF0'>&nbsp;".$module->l('Email sent to', 'email-sending').' '.$toName.', '.$module->l('email address', 'email-sending').' '.$to.'.</div>';
			}
			else
				echo "<div style='font-weight:normal; margin:20px auto 2px 16px; text-align:left; width:70%; border:1px solid #DFD5C3; background-color:#FFFFF0'>&nbsp;".$module->l('Email has not been sent', 'email-sending').'.</div>';
		}

		if ($choice_copy_mail == 'yes')
			{
				$template = 'mail_to_creator_copy_to_shop';
	/* do not erase, for translation purpose : $this->l('Copy - Information about wishlist')*/
				$subject = $module->l('Copy - Information about wishlist', 'email-sending').' '.$wishlist1['name'].'"';
				$templateVars = array(
							'{lastname}' => $customer->lastname,
							'{firstname}' => $customer->firstname,
							'{wishlist}' => $wishlist1['name'],
							'{message}' => 'http://'.htmlentities($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.BlockWishListpro::MODULENAME.'/view.php?token='.$wishlist1['token']);

				$to = (string)Configuration::get('PS_SHOP_EMAIL');
				$toName = (string)Configuration::get('PS_SHOP_NAME');
				$from = (string)Configuration::get('PS_SHOP_EMAIL');
				$fromName = (string)Configuration::get('PS_SHOP_NAME');


				$send_mail_creator_copy_to_shop = Mail::Send($id_lang, $template, $subject, $templateVars, $to, $toName, $from, $fromName, $fileAttachment, $modeSMTP, $templatePath);

				if ($send_mail_creator_copy_to_shop) /*confirmation message only for non automatic sending*/
					echo "<div style='font-weight:normal; margin:8px auto 16px 16px; text-align:left; width:70%; border:1px solid #DFD5C3; background-color:#FFFFF0'>&nbsp;".$module->l('Email sent as a copy to', 'email-sending').' '.$toName.', '.$module->l('email address', 'email-sending').' '.$to.'.</div>';
				elseif (!$send_mail_creator_copy_to_shop)
					echo "<div style='font-weight:normal; margin:8px auto 16px 16px; text-align:left; width:70%; border:1px solid #DFD5C3; background-color:#FFFFF0'>&nbsp;".$module->l('Email copy has not been sent', 'email-sending').'.</div>';

			}
		}
		/*to come back to wishlist admin page, only for non automatic sending*/
			echo '<a href="javascript:history.back()" class="back_blockwl">
					<img src="views/img/icon/back(visitors).gif" style="text-decoration:none; margin:3px 6px 0 3px" alt="'.$module->l('Back to cockpit', 'email-sending').'"  />'.$module->l('Back to cockpit', 'email-sending').'
					</a>
					';
	}
?>