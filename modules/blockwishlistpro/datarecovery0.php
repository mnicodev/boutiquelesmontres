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
require_once(dirname(__FILE__).'/../../classes/Tools.php');
require_once(dirname(__FILE__).'/WishListpro.php');
include_once(dirname(__FILE__).'/blockwishlistpro.php');
require_once('../../classes/Tools.php');

	$submit_type = Tools::getValue('typesubmit');
	if (!isset($submit_type))
	exit(Tools::displayError('Invalid sumit'));
	$id_lang = Tools::getValue('id_lang');
	$module = new BlockWishListpro();


/* DO NOT ERASE - for translation purpose
 ->l('imported with success') | ->l('not imported because already exists') | ->l('Confirmation').' | ->l('Recovery carried out') | ->l('Page automatically reloaded in 5 seconds.') | ->l('Table') | ->l('data successfully imported')*/

		/*reset carts (not ordered) before import // otherwise calculation of init quantities false when old carts releasing*/
				/*before reseting calculate quantity by adding cart quantity*/
						/*fields initialization*/
						$wp_table = Db::getInstance()->ExecuteS('
							SELECT *
							FROM `'._DB_PREFIX_.'wishlist_product`
							ORDER BY `id_wishlist`');
						/*add a new field equal to quantity for recovering purpose : quantity_recover = quantity + bought and cart*/
							/*check wether quantity_recover exists and if not exists add the field*/
						$sql = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `'._DB_PREFIX_.'wishlist_product` LIKE `quantity_recover`');
						if (empty($sql))
							Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'wishlist_product` ADD `quantity_recover` INT(10) NOT NULL AFTER `priority`');
							/*1st set quantity_recover = quantity by default*/
						Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'wishlist_product` SET
	`quantity_recover` = `quantity` ');

							/*2nd set quantity_recover = quantity + bought and cart if this is the case*/
						$wp_table_id_wl = Db::getInstance()->ExecuteS('
							SELECT `id_wishlist`
							FROM `'._DB_PREFIX_.'wishlist_product`
							GROUP BY `id_wishlist`');
						$wl_BoughtActualQtyProduct = array();
						$wl_CartBoughtQtyProduct = array();
						foreach ($wp_table_id_wl as $id_wishlist)
						{
							$idwl = $id_wishlist['id_wishlist'];
							$wl_CartBoughtQtyProduct[$idwl] = WishListpro::getCartBoughtQtyProduct_oldversion($idwl);
							$wl_BoughtActualQtyProduct[$idwl] = WishListpro::getBoughtQty_actual_oldversion($idwl);

							foreach ($wp_table as $row)
							{
								if ((int)$row['id_wishlist'] == (int)$idwl)
								{
									foreach ($wl_CartBoughtQtyProduct[$idwl] as $line_product)
									{
										if ($row['id_wishlist_product'] == $line_product['id_wishlist_product'])
										{
											$row['quantity_recover'] = (int)$row['quantity'] + (int)$line_product['cart_quantity'];
											Db::getInstance()->Execute('
											UPDATE `'._DB_PREFIX_.'wishlist_product` SET
											`quantity_recover` = '.(int)$row['quantity_recover'].'
											WHERE `id_wishlist`= '.(int)$row['id_wishlist'].' AND `id_wishlist_product`= '.(int)$row['id_wishlist_product'].'
											');
										break;
										}
									}
								}
							}
						}
				/* end adding cart qty*/

		$wp_table_id_wl = Db::getInstance()->ExecuteS('
			SELECT `id_wishlist`
			FROM `'._DB_PREFIX_.'wishlist_product`
			GROUP BY `id_wishlist`');
			foreach ($wp_table_id_wl as $id_wishlist)
			{
				$idwl = $id_wishlist['id_wishlist'];

				$old_carts = Db::getInstance()->ExecuteS('
				SELECT wp.id_product, wp.id_product_attribute, wpc.id_cart, wp.`id_wishlist_product`
				FROM `'._DB_PREFIX_.'wishlist_product_cart` wpc
				JOIN `'._DB_PREFIX_.'wishlist_product` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
				JOIN `'._DB_PREFIX_.'cart` c ON  (c.`id_cart` = wpc.`id_cart`)
				JOIN `'._DB_PREFIX_.'cart_product` cp ON (wpc.`id_cart` = cp.`id_cart`)
				LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = c.`id_cart`)
				WHERE (wp.`id_wishlist`='.(int)$idwl.' AND o.`id_cart` IS NULL)
				');
				if (isset($old_carts) && $old_carts != false)
					foreach ($old_carts as $old_cart)
					{
						Db::getInstance()->Execute('
							DELETE FROM `'._DB_PREFIX_.'cart_product`
							WHERE id_cart='.(int)$old_cart['id_cart'].' AND id_product='.(int)$old_cart['id_product'].' AND id_product_attribute='.(int)$old_cart['id_product_attribute']
						);
						Db::getInstance()->Execute('
							DELETE FROM `'._DB_PREFIX_.'wishlist_product_cart`
							WHERE id_cart='.(int)$old_cart['id_cart'].' AND `id_wishlist_product`='.(int)$old_cart['id_wishlist_product']
						);
					}
			}
		/* end reset carts*/


		if ($submit_type == 'submitOverwrite' || $submit_type == 'submitImport')
		{
			$table = array('wishlist', 'wishlist_email', 'wishlist_product', 'wishlist_product_cart');

			if (Tools::isSubmit('submitOverwrite'))
			{
				/*table backup in case of submitOverwrite*/
				foreach ($table as $k => $name_table)
				{
					$str1 = _DB_PREFIX_.$table[$k].BlockWishListpro::SUFFIX;
					$str2 = _DB_PREFIX_.$table[$k].BlockWishListpro::SUFFIX.'_bakdd';
					Db::getInstance()->Execute('DROP TABLE IF EXISTS  `'.$str2.'` ');
					$show = Db::getInstance()->ExecuteS('SHOW CREATE TABLE IF NOT EXISTS `'.$str1.'`');
					$show[0]['Create Table'] = str_replace($str1, $str2, $show[0]['Create Table']);
					$create = Db::getInstance()->Execute($show[0]['Create Table']);
					Db::getInstance()->Execute('INSERT INTO  `'.$str2.'`
					SELECT *
					FROM  `'.$str1.'` ');
				}
				/* end backup---------------*/
			}

			/*copy if new doesn't exist and old exists--*/
			foreach ($table as $i => $row)
			{
				if (BlockWishListpro::table_exist(_DB_PREFIX_.$table[$i]) && (WishListpro::checkTableContent(_DB_PREFIX_.$table[$i]) != 0))
				{
					/*copy table*/
					$step0 = Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'`');
$returnsql = BlockWishListpro::table_copy($i, $table);
					$step1 = Db::getInstance()->Execute(BlockWishListpro::table_copy($i, $table));
					$step2 = Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` SELECT * FROM `'._DB_PREFIX_.$table[$i].'`');
					if ($step0 == false || $step1 == false || $step2 == false)
					{
						/*restore backup*/
						foreach ($table as $k => $name_table)
						{
							$str1 = _DB_PREFIX_.$table[$k].BlockWishListpro::SUFFIX.'_bakdd';
							$str2 = _DB_PREFIX_.$table[$k].BlockWishListpro::SUFFIX;
							Db::getInstance()->Execute('DROP TABLE IF EXISTS  `'.$str2.'` ');
							$show = Db::getInstance()->ExecuteS('SHOW CREATE TABLE IF NOT EXISTS `'.$str1.'`');
			$show[0]['Create Table'] = str_replace($str1, $str2, $show[0]['Create Table']);
							$create = Db::getInstance()->Execute($show[0]['Create Table']);
							Db::getInstance()->Execute('INSERT INTO  `'.$str2.'`
							SELECT *
							FROM  `'.$str1.'` ');
						}

						/*end restore backup*/
						die(Tools::displayError('Error when copying old tables.'));
					}

					if ($i == 0)
					{	/*wishlist, add 'published' flag , =1 by default (list published) */
						Db::getInstance()->Execute('
						ALTER TABLE `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` ADD `published` INT(10) NOT NULL AFTER `date_upd`');
						Db::getInstance()->Execute('
						UPDATE `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` SET `published` = 1');
					}

					if ($i == 2)
					{	/*wishlist_product, add quantity_init , quantity_left , alert_qty */
						Db::getInstance()->Execute('
						ALTER TABLE `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` ADD `quantity_rel` INT(10) NOT NULL AFTER `id_product_attribute`, ADD `quantity_init` INT(10) NOT NULL AFTER `quantity`, ADD `quantity_left_rel` INT(10) NOT NULL AFTER `quantity_init`, ADD `quantity_left` INT(10) NOT NULL AFTER `quantity_left_rel`, ADD `alert_qty` INT(10) NOT NULL AFTER `priority`'
						);

						Db::getInstance()->Execute('
						UPDATE `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` SET
						`quantity` = `quantity_recover`
						' 	);

/*OLD						Db::getInstance()->Execute("
						UPDATE `"._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX."` SET
						`quantity_init` = `quantity`
						" 	);
*/
						/*fields initialization*/
						$wp_table = Db::getInstance()->ExecuteS('
							SELECT *
							FROM `'._DB_PREFIX_.'wishlist_product'.BlockWishListpro::SUFFIX.'`
							ORDER BY `id_wishlist`');
						$wp_table_id_wl = Db::getInstance()->ExecuteS('
							SELECT `id_wishlist`
							FROM `'._DB_PREFIX_.'wishlist_product'.BlockWishListpro::SUFFIX.'`
							GROUP BY `id_wishlist`');
						foreach ($wp_table_id_wl as $id_wishlist)
						{
							$idwl = $id_wishlist['id_wishlist'];
							$wl_CartBoughtQtyProduct[$idwl] = WishListpro::getCartBoughtQtyProduct($idwl);
							$wl_BoughtActualQtyProduct[$idwl] = WishListpro::getBoughtQty_actual($idwl);
							$spywl = 0;
							foreach ($wp_table as $row)
							{
								if ((int)$row['id_wishlist'] == (int)$idwl)
								{
									foreach ($wl_CartBoughtQtyProduct[$idwl] as $line_product)
									{
										$spywl += 1;
										if ($row['id_wishlist_product'] == $line_product['id_wishlist_product'])
										{
											$row['quantity_init'] = (int)$row['quantity_recover'];
											$row['quantity_rel'] = $row['quantity_init'];

											Db::getInstance()->Execute('
											UPDATE `'._DB_PREFIX_.$table['2'].BlockWishListpro::SUFFIX.'` SET
											`quantity_init` = '.(int)$row['quantity_init'].',
											`quantity_rel` = '.(int)$row['quantity_rel'].'
											WHERE `id_wishlist`= '.(int)$row['id_wishlist'].' AND `id_wishlist_product`= '.(int)$row['id_wishlist_product'].'
											');
											break;
										}
									}
									foreach ($wl_BoughtActualQtyProduct[$idwl] as $line_product)
									{
										$spywl += 1;
										if ($row['id_wishlist_product'] == $line_product['id_wishlist_product'])
										{
											$row['quantity_left_rel'] = (int)$row['quantity_init'] - (int)$line_product['actual_qty'];
											$row['quantity_left'] = $row['quantity_left_rel'] < 0 ? 0 : $row['quantity_left_rel'];
											Db::getInstance()->Execute('
											UPDATE `'._DB_PREFIX_.$table['2'].BlockWishListpro::SUFFIX.'` SET
											`quantity_left_rel` = '.(int)$row['quantity_left_rel'].',
											`quantity_left` = '.(int)$row['quantity_left'].'
											WHERE `id_wishlist`= '.(int)$row['id_wishlist'].' AND `id_wishlist_product`= '.(int)$row['id_wishlist_product'].'
											' 	);
										break;
										}
									}

									if ((int)$row['quantity_init'] == 0 && (int)$row['quantity'] != 0)
									{
										$row['quantity_init'] = (int)$row['quantity'];
										Db::getInstance()->Execute('
											UPDATE `'._DB_PREFIX_.$table['2'].BlockWishListpro::SUFFIX.'` SET
											`quantity_init` = '.(int)$row['quantity_init'].'
											WHERE `id_wishlist`= '.(int)$row['id_wishlist'].' AND `id_wishlist_product`= '.(int)$row['id_wishlist_product'].'');
										$spywl += 1;
									}
								}
							}
						}
						/*end fields initialization*/
					}

					if ($i == 3)
						/*wishlist_product_cart, add price_wt_init and price_init */
						Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.'` ADD `price_init` FLOAT NOT NULL AFTER `quantity`, ADD `price_wt_init` FLOAT NOT NULL AFTER `price_init`');

					echo $module->l('Table', 'datarecovery0').' '._DB_PREFIX_.$table[$i].BlockWishListpro::SUFFIX.' : '.$module->l('data successfully imported', 'datarecovery0').'<br>';
				}
			}
			echo '<img src="../img/admin/ok.gif" alt="'.$module->l('Confirmation', 'datarecovery0').'" />&nbsp;'.$module->l('Recovery carried out', 'datarecovery0').'.<br />'.$module->l('Page automatically reloaded in 5 seconds.', 'datarecovery0');
		}
?>
