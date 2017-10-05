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

		/* SSL Management */
		$useSSL = false;
		require_once(dirname(__FILE__).'/../../config/config.inc.php');
		require_once(dirname(__FILE__).'/../../init.php');
		require_once(dirname(__FILE__).'/WishListpro.php');
		require_once(dirname(__FILE__).'/blockwishlistpro.php');
		if (!file_exists(dirname(__FILE__).'/../../config/defines.inc.php'))
			require_once(dirname(__FILE__).'/defines_oldversion.inc.php'); /*old version (<= 1.2)*/
		$context = Context::getContext();
		if ($context->customer->isLogged())
		{
			$module = new BlockWishListpro();
			$id_lang = (int)($context->language->id);
			$language = $context->language->iso_code;
			$action = Tools::getValue('action');
			$id_wishlist = Tools::getValue('id_wishlist');
			$id_product = Tools::getValue('id_product');
			$id_product_attribute = Tools::getValue('id_product_attribute');
			$quantity = Tools::getValue('quantity');
			$priority = Tools::getValue('priority');
			$wishlist = new WishListpro((int)$id_wishlist);
			$token = $wishlist->token; /*to display link to wishlist on emails wishlist form*/
			$refresh = Tools::getValue('refresh');
			$currency_id = isset($context->currency->id) ? $context->currency->id : $context->cookie->id_currency;
			$current_currency = Currency::getCurrencyInstance($currency_id);
			$current_conv_rate = (float)$current_currency->conversion_rate;
			if (empty($id_wishlist) === false)
			{
				if (!strcmp($action, 'update')) /*if true action = update*/
				{
					$tep = WishListpro::updateProduct($id_wishlist, $id_product, $id_product_attribute, $priority, $quantity);

					/* minimal qty check */
					$tepp = explode('|', $tep);
					if (strpos($tepp[0], 'minimal_qty') !== false)
					{
						echo $tep;
						return;
					}

					$resultat1 = Db::getInstance()->getRow('
						SELECT wp.`quantity_init` as quantity_init, wp.`quantity` as quantity1, wp.`quantity_left` as quantity_left, wp.`alert_qty` as alert_qty
							FROM `'._DB_PREFIX_.'wishlist_product'.BlockWishListpro::SUFFIX.'` wp
						WHERE `id_wishlist` = '.(int)$id_wishlist.'
						AND `id_product` = '.(int)$id_product.'
						AND `id_product_attribute` = '.(int)$id_product_attribute);

					if (empty($resultat1) === false && $resultat1 !== false && count($resultat1))
					{
						/*to update left quantity through ajax function (WishlistProductManage), echo ...*/
							$actual_bought = Db::getInstance()->getRow('
							SELECT wp.`id_product`, wp.`id_product_attribute`, SUM(cp.`quantity`) as actual_qty
							FROM `'._DB_PREFIX_.'wishlist_product_cart'.BlockWishListpro::SUFFIX.'` wpc
							JOIN `'._DB_PREFIX_.'wishlist_product'.BlockWishListpro::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
							JOIN `'._DB_PREFIX_.'cart_product` cp ON (cp.`id_cart` = wpc.`id_cart` AND cp.`id_product`=wp.`id_product` AND wp.`id_product_attribute`=cp.`id_product_attribute`)
							JOIN `'._DB_PREFIX_.'cart` ca ON (ca.id_cart = wpc.id_cart)
							LEFT JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = ca.`id_customer`)
							LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.id_cart = ca.id_cart)
							WHERE (wp.id_wishlist='.(int)$id_wishlist.' AND wp.`id_product`='.(int)$id_product.' AND wp.`id_product_attribute`='.(int)$id_product_attribute.' AND o.id_cart IS NOT NULL )
							GROUP BY wp.`id_product`, wp.`id_product_attribute`'
							);

	/*SELECT wp.`id_product`, wp.`id_product_attribute`, SUM(cp.`quantity`) as actual_qty
							FROM `ps_1603wishlist_product_cart_pro` wpc
							JOIN `ps_1603wishlist_product_pro` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
							JOIN `ps_1603cart_product` cp ON (cp.`id_cart` = wpc.`id_cart` AND cp.`id_product`= wp.`id_product` AND wp.`id_product_attribute`= cp.`id_product_attribute`)
							JOIN `ps_1603cart` ca ON (ca.id_cart = wpc.id_cart)
							LEFT JOIN `ps_1603customer` cu ON (cu.`id_customer` = ca.`id_customer`)
							LEFT JOIN `ps_1603orders` o ON (o.id_cart = ca.id_cart)
							WHERE (wp.id_wishlist=1 AND wp.`id_product`=2 AND wp.`id_product_attribute`=7 AND o.id_cart IS NOT NULL )
							GROUP BY wp.`id_product`, wp.`id_product_attribute`*/
/*echo "<br>mwlp resultat1 alert_qty";var_dump($resultat1['alert_qty']);
echo '<br>mwlp actual_bought';var_dump($actual_bought);
echo '<br>mwlp id_wl';var_dump($id_wishlist);
echo '<br>mwlp id_product';var_dump($id_product);
echo '<br>mwlp id_product_attribute';var_dump($id_product_attribute);
echo '<br>mwlp BlockWishListpro::SUFFIX';var_dump(BlockWishListpro::SUFFIX);*/
							if (empty($actual_bought) === false && count($actual_bought))
							{
								if (!$actual_bought['actual_qty'] || empty($actual_bought['actual_qty']))
									$actual_bought['actual_qty'] = 0;
								/*dd if flag alert_qty=1, echo... for ajax alert telling wanted qty can not be inferior to bought qty*/
								if ($resultat1['alert_qty'] == 1)
									echo 'alert|'.$resultat1['quantity_init'].'|'.$resultat1['quantity_left'];
								else
									echo $resultat1['quantity_left'];
							}
							else
							{
								$actual_bought['actual_qty'] = 0;
								echo $quantity;
							}
					}
				}
				else
				{
					if (!strcmp($action, 'delete'))
					{
						/*Bought Quantities :  has the product already been bought ?*/
						$test = WishListpro::getProductBoughtQty_actual($id_wishlist, $id_product, $id_product_attribute);
						if (isset($test))
						{
							if (empty($test)) /*if not bought : remove*/
							{
								WishListpro::removeProduct($id_wishlist, (int)($context->customer->id), $id_product, $id_product_attribute);
								echo 'successful removing';
								return;
							}
							else /*if bought : not remove */
								echo 'impossible';
						}
					}
					$products = WishListpro::getProductByIdCustomer($id_wishlist, $context->customer->id, $id_lang);
					$bought = WishListpro::getBoughtProduct($id_wishlist);
					$bought_reel = WishListpro::getBoughtProduct_reel($id_wishlist); /*ordered by cart*/

					$count_bought = count($bought);
					$count_bought_reel = count($bought_reel);
					$count_products = count($products);
					foreach ($products as $i => $pro)
					{
						$products[$i]['isobj'] = 1;
						$products[$i]['isobj_attr'] = 1; /*product has combination if 1*/
						$products[$i]['is_attr_exist'] = 1; /* combination of the list exists if 1*/
						$obj = new Product((int)$products[$i]['id_product'], false, (int)$id_lang);
						if (!Validate::isLoadedObject($obj))
						{
							$products[$i]['isobj'] = 0; /*isobj=0 means a bo cancelled product but still in list*/
							$products[$i]['cover'] = '';
							$gett = WishListpro::getProductBoughtQty_actual($id_wishlist, $products[$i]['id_product'], $products[$i]['id_product_attribute']);
							$products[$i]['name'] = isset($gett[0]['pdt_order_name']) ? $gett[0]['pdt_order_name'] : '';
							$products[$i]['price_dd'] = '';
						}
						else
						{
							if ($obj->hasAttributes() > 0)
							{
								if (Product::getProductAttributePrice($products[$i]['id_product_attribute']) === false)
									$products[$i]['is_attr_exist'] = 0;
							}
							elseif ($products[$i]['id_product_attribute'] != 0)
							{
							/*case of Product with 1 combination when added to list, then the combination has been deleted. Product swithout combination hence*/
									$products[$i]['is_attr_exist'] = 0;
									$products[$i]['isobj_attr'] = 0;
							}

							$products[$i]['price_dd'] = WishListpro::getPriceAw($products[$i]['id_product'], $products[$i]['id_product_attribute']);

							if ($products[$i]['id_product_attribute'] != 0)
							{
								$combination_imgs = $obj->getCombinationImages((int)$id_lang);
								if ($combination_imgs)
								{
									if (isset($combination_imgs[$products[$i]['id_product_attribute']][0]['id_image']))
									{
										$products[$i]['cover'] = $obj->id.'-'.$combination_imgs[$products[$i]['id_product_attribute']][0]['id_image'];
										/*version 1.4.3 and further : new image directory : /p/3/7/37-medium.jpg (before : /p/1-37-medium.jpg)*/
										if (!file_exists(_PS_PROD_IMG_DIR_.$products[$i]['cover'].'-medium.jpg'))
										{
											$folders = str_split((string)$combination_imgs[$products[$i]['id_product_attribute']][0]['id_image']);
											$products[$i]['cover'] = implode('/', $folders).'/'.$combination_imgs[$products[$i]['id_product_attribute']][0]['id_image'];
										}
									}
									else
									{
									$images = $obj->getImages((int)$id_lang);
										foreach ($images as $k => $image)
										{
											if ($image['cover'])
											{
												$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
												/*version 1.4.3 and further : new image directory : /p/3/7/37-medium.jpg (before : /p/1-37-medium.jpg)*/
												if (!file_exists(_PS_PROD_IMG_DIR_.$products[$i]['cover'].'-medium.jpg'))
												{
													$folders = str_split((string)$image['id_image']);
													$products[$i]['cover'] = implode('/', $folders).'/'.$image['id_image'];
												}
												break;
											}
										}
										if (!isset($products[$i]['cover']))
											$products[$i]['cover'] = Language::getIsoById((int)$id_lang).'-default';
									}
								}
								else
								{ /*in case no image defined for any attributes*/
										$images = $obj->getImages((int)$id_lang);
										foreach ($images as $k => $image)
										{
											if ($image['cover'])
											{
												$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
												/*version 1.4.3 and further : new image directory : /p/3/7/37-medium.jpg (before : /p/1-37-medium.jpg)*/
												if (!file_exists(_PS_PROD_IMG_DIR_.$products[$i]['cover'].'-medium.jpg'))
												{
													$folders = str_split((string)$image['id_image']);
													$products[$i]['cover'] = implode('/', $folders).'/'.$image['id_image'];
												}
												break;
											}
										}
										if (!isset($products[$i]['cover']))
											$products[$i]['cover'] = Language::getIsoById((int)$id_lang).'-default';
								}
							}
							else
							{
								$images = $obj->getImages((int)$id_lang);
								foreach ($images as $k => $image)
									if ($image['cover'])
									{
										$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
										/*version 1.4.3 and further : new image directory : /p/3/7/37-medium.jpg (before : /p/1-37-medium.jpg)*/
										if (!file_exists(_PS_PROD_IMG_DIR_.$products[$i]['cover'].'-medium.jpg'))
										{
										$folders = str_split((string)$image['id_image']);
										$products[$i]['cover'] = implode('/', $folders).'/'.$image['id_image'];
										}
										break;
									}
								if (!isset($products[$i]['cover']))
									$products[$i]['cover'] = Language::getIsoById($id_lang).'-default';
							}
						}
						$products[$i]['bought'] = false;
						for ($j = 0, $k = 0; $j < $count_bought; ++$j)
						{
							if ($bought[$j]['id_product'] == $products[$i]['id_product'] && $bought[$j]['id_product_attribute'] == $products[$i]['id_product_attribute'])
								$products[$i]['bought'][$k++] = $bought[$j];
						}
						/*to calculate bought quantity*/
						$products[$i]['bought_reel'] = false;
						$products[$i]['bought_qty_actual'] = 0;

						for ($j = 0, $k = 0; $j < $count_bought_reel; ++$j)
						{
							if (isset($bought_reel[$j]) && $bought_reel[$j]['id_product'] == $products[$i]['id_product'] &&	$bought_reel[$j]['id_product_attribute'] == $products[$i]['id_product_attribute'])
							{
								$products[$i]['bought_reel'][$k++] = $bought_reel[$j];
								$products[$i]['bought_qty_actual'] = $products[$i]['bought_qty_actual'] + $bought_reel[$j]['actual_qty'];
							}
						}
						if (isset($products[$i]['bought_qty_actual']))
						{
							/*in case of data recovery when installing this module over the former and native one*/
							if ($products[$i]['quantity_init'] == 0)
								$products[$i]['quantity_init'] = $products[$i]['bought_qty_actual'];
							$products[$i]['left'] = $products[$i]['quantity_init'] - $products[$i]['bought_qty_actual'];
						}
						else
							$products[$i]['left'] = $products[$i]['quantity_init'];

						if ($products[$i]['left'] < 0)
							$products[$i]['left'] = 0;
			/*if quantity == 0 AND achat/purchase, then quantity_init == achat  | per product*/

/* img */
						if ($products[$i]['id_product_attribute'] == 0)
						{
							$images = $obj->getImages((int)$context->cookie->id_lang);
							foreach ($images as $k => $image)
							{
								if ($image['cover'])
								{
									$cover = $image;
									$cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($obj->id.'-'.$image['id_image']) : $image['id_image']);
								}
							}
							if (!isset($cover))
								$cover = array('id_image' => $context->language->iso_code.'-default', 'legend' => 'No picture', 'title' => 'No picture');
							$products[$i]['cover'] = $cover['id_image'];
						}
						else
						{
							$combination_imgs = $obj->getCombinationImages($id_lang);
							if ($combination_imgs)
							{
								if (isset($combination_imgs[$products[$i]['id_product_attribute']][0]['id_image']))
									$products[$i]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($products[$i]['id_product'].'-'.$combination_imgs[$products[$i]['id_product_attribute']][0]['id_image']) : $combination_imgs[$products[$i]['id_product_attribute']][0]['id_image']);
								else
								{
									$images = $obj->getImages($id_lang);
									foreach ($images as $k => $image)
									{
										if ($image['cover'])
											$products[$i]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($obj->id.'-'.$image['id_image']) : $image['id_image']);
									}
									if (!isset($products[$i]['cover']))
										$products[$i]['cover'] = Language::getIsoById($id_lang).'-default';
								}
							}
							else
							{
								$images = $obj->getImages($id_lang);
								foreach ($images as $k => $image)
								{
									if ($image['cover'])
										$products[$i]['cover'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($obj->id.'-'.$image['id_image']) : $image['id_image']);
								}
								if (!isset($products[$i]['cover']))
									$products[$i]['cover'] = Language::getIsoById($id_lang).'-default';
							}
						}
						$products[$i]['have_image'] = Product::getCover((int)$products[$i]['id_product']);
					}

					$productBoughts_actual = array();

					foreach ($products as $product)
						{
						if (isset($product['bought_reel']) && count($product['bought_reel']))
							$productBoughts_actual[] = $product;
						}
					/*sort by date dd*/
					$productBoughts_actual_sort = array();

					foreach ($bought_reel as $n => $row)
					{
						$productBoughts_actual_sort[$n]['lastname'] = ( isset($row['lastname']) ? $row['lastname'] : '');
						$productBoughts_actual_sort[$n]['firstname'] = ( isset($row['firstname']) ? $row['firstname'] : '');
						$productBoughts_actual_sort[$n]['date_add'] = ( isset($row['date_add']) ? (version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($row['date_add']) : Tools::displayDate($row['date_add'], $id_lang, false)) : '');

						$productBoughts_actual_sort[$n]['date_upd'] = ( isset($row['date_upd']) ? (version_compare(_PS_VERSION_, '1.5.5.0', '>=') ? Tools::displayDate($row['date_upd']) : Tools::displayDate($row['date_upd'], $id_lang, false)) : '');
						$productBoughts_actual_sort[$n]['date_upd_org'] = ( isset($row['date_upd']) ? $row['date_upd'] : '');

						for ($i = 0; $i < $count_products; $i++)
						{
							if ($row['id_product'] == $products[$i]['id_product'] && $row['id_product_attribute'] == $products[$i]['id_product_attribute'])
							{
							$productBoughts_actual_sort[$n]['quantity'] = ( isset($row['quantity']) ? $row['quantity'] : 0);
							$productBoughts_actual_sort[$n]['actual_qty'] = ( isset($row['actual_qty']) ? $row['actual_qty'] : 0);
							$productBoughts_actual_sort[$n]['id_product'] = ( isset($products[$i]['id_product']) ? $products[$i]['id_product'] : '');
							$productBoughts_actual_sort[$n]['id_product_attribute'] = ( isset($products[$i]['id_product_attribute']) ? $products[$i]['id_product_attribute'] : '');
							$productBoughts_actual_sort[$n]['quantity_init'] = ( isset($products[$i]['quantity_init']) ? $products[$i]['quantity_init'] : 0);
							$productBoughts_actual_sort[$n]['product_quantity'] = ( isset($products[$i]['product_quantity']) ? $products[$i]['product_quantity'] : 0);
							$productBoughts_actual_sort[$n]['name'] = ( isset($products[$i]['name']) ? $products[$i]['name'] : '');
							$productBoughts_actual_sort[$n]['priority'] = ( isset($products[$i]['priority']) ? $products[$i]['priority'] : '');
							$productBoughts_actual_sort[$n]['link_rewrite'] = ( isset($products[$i]['link_rewrite']) ? $products[$i]['link_rewrite'] : '');
							$productBoughts_actual_sort[$n]['category_rewrite'] = ( isset($products[$i]['category_rewrite']) ? $products[$i]['category_rewrite'] : '');
							$productBoughts_actual_sort[$n]['attributes_small'] = ( isset($products[$i]['attributes_small']) ? $products[$i]['attributes_small'] : '');
							$productBoughts_actual_sort[$n]['attribute_quantity'] = ( isset($products[$i]['attribute_quantity']) ? $products[$i]['attribute_quantity'] : '');
							$productBoughts_actual_sort[$n]['price_dd'] = ( isset($products[$i]['price_dd']) ? $products[$i]['price_dd'] : 0);
							$productBoughts_actual_sort[$n]['cover'] = ( isset($products[$i]['cover']) ? $products[$i]['cover'] : '');
							$productBoughts_actual_sort[$n]['have_image'] = Product::getCover((int)$products[$i]['id_product']);
								}
							}
						}
					$date = array();
					foreach ($productBoughts_actual_sort as $i => $row)
						$date[$i] = (isset($row['date_upd_org']) ? $row['date_upd_org'] : '');
					$bool = ((isset($productBoughts_actual_sort) && !empty($productBoughts_actual_sort)) ? array_multisort($date, SORT_DESC, SORT_REGULAR, $productBoughts_actual_sort) : false);

					$total = array();
					$value = WishListpro::getValue($id_wishlist);
		/*old versions (1.2...)*/
					if ($value)
					{
						foreach ($value as $n => $order)
						{
							$t_order = new Order($order['id_order']);
							$currency = new Currency($t_order->id_currency);
							$ratio_cur = (float)$currency->conversion_rate / $current_conv_rate;
							$total['total_products_wt'] = (isset($total['total_products_wt']) ? $total['total_products_wt'] : 0) + (isset($order['total_products_wt']) ? ((float)$t_order->total_products_wt / $ratio_cur) : 0);
							$total['total_discounts'] = (isset($total['total_discounts']) ? $total['total_discounts'] : 0) + ( isset($order['total_discounts']) ? ((float)$t_order->total_discounts / $ratio_cur) : 0);
							$total['total_paid'] = (isset($total['total_paid']) ? $total['total_paid'] : 0) + (isset($order['total_paid']) ? ((float)$t_order->total_paid / $ratio_cur) : 0);
							$total['total_paid_real'] = (isset($total['total_paid_real']) ? $total['total_paid_real'] : 0) + (isset($order['total_paid_real']) ? ((float)$t_order->total_paid_real / $ratio_cur) : 0);
							$total['total_products'] = (isset($total['total_products']) ? $total['total_products'] : 0) + (isset($order['total_products']) ? ((float)$t_order->total_products / $ratio_cur) : 0);
							$total['total_shipping'] = (isset($total['total_shipping']) ? $total['total_shipping'] : 0) + (isset($order['total_shipping']) ? ((float)$t_order->total_shipping / $ratio_cur) : 0);
							$total['total_wrapping'] = (isset($total['total_wrapping']) ? $total['total_wrapping'] : 0) + (isset($order['total_wrapping']) ? ((float)$t_order->total_wrapping / $ratio_cur) : 0);
						}
					}
					else
					{
							$total['total_discounts'] = (isset($total['total_discounts']) ? $total['total_discounts'] / $ratio_cur : 0);
							$total['total_paid'] = (isset($total['total_paid']) ? $total['total_paid'] / $ratio_cur : 0);
							$total['total_paid_real'] = (isset($total['total_paid_real']) ? $total['total_paid_real'] / $ratio_cur : 0);
							$total['total_products'] = (isset($total['total_products']) ? $total['total_products'] / $ratio_cur : 0);
							$total['total_products_wt'] = (isset($total['total_products_wt']) ? $total['total_products_wt'] / $ratio_cur : 0);
							$total['total_shipping'] = (isset($total['total_shipping']) ? $total['total_shipping'] / $ratio_cur : 0);
							$total['total_wrapping'] = (isset($total['total_wrapping']) ? $total['total_wrapping'] / $ratio_cur : 0);
					}
					/*end*/
					$listcart = WishListpro::getListCart($id_wishlist);
					$total_wl = array();
					$total_wl['total_products_wt'] = 0;
					foreach ($listcart as $j => $id_ct)
					{
/*						$cart = new CartWl((int)$id_ct['id_cart']);*/
						$cart = new Cart((int)$id_ct['id_cart']);
						if (!Validate::isLoadedObject($cart))
							die(Tools::displayError());
				/* products of wishlist | with taxes | products and discounts | without shipping */
					}
					$listcart_o = WishListpro::getListOrder($id_wishlist);
					$products_wl = WishListpro::getProductByIdCustomer((int)$id_wishlist, (int)$wishlist->id_customer, (int)$id_lang);
					$total = array();
					$total['list']['products']['mx'] = 0;
					$total['list']['discounts']['mx'] = 0;
					$total['list']['wrapping']['mx'] = 0;
					$total['list']['shipping']['mx'] = 0;
					$total['list']['paid']['mx'] = 0;
					$total['list']['products']['wl'] = 0;
					$total['list']['paid']['wl'] = 0;
					foreach ($listcart_o as $i => $row)
					{
						$t_order = new Order($row['id_order']);
						$currency = new Currency($t_order->id_currency);
						$ratio_cur = (float)$currency->conversion_rate / $current_conv_rate;

						$total['order'][$row['id_order']] = BlockWishListpro::valueDetails_AdminOrders($row['id_order'], $products_wl, (int)$id_wishlist);
						$total['list']['products']['mx'] += $total['order'][$row['id_order']]['products']['mx'] / $ratio_cur;
						$total['list']['discounts']['mx'] += $total['order'][$row['id_order']]['discounts']['mx'] / $ratio_cur;
						$total['list']['wrapping']['mx'] += $total['order'][$row['id_order']]['wrapping']['mx'] / $ratio_cur;
						$total['list']['shipping']['mx'] += $total['order'][$row['id_order']]['shipping']['mx'] / $ratio_cur;
						$total['list']['paid']['mx'] += $total['order'][$row['id_order']]['paid']['mx'] / $ratio_cur;
						$total['list']['products']['wl'] += $total['order'][$row['id_order']]['products']['wl'] / $ratio_cur;
						$total['list']['paid']['wl'] += $total['order'][$row['id_order']]['paid']['wl'] / $ratio_cur;
					}
					$total_wl['total_products_wt'] += $total['list']['products']['wl'];
					$path_html = _PS_ROOT_DIR_.'/modules/'.BlockWishListpro::MODULENAME.'/mails/'.Language::getIsoById($id_lang).'/wishlist.html';
					$id_fic = fopen($path_html, 'r');
					if (!file_exists($path_html))
						die ('Error, no wishlist.html file in mails/'.Language::getIsoById($id_lang).'/');
					$message_default_tab = file($path_html);
					$name = $wishlist->name;

					if (version_compare(_PS_VERSION_, '1.5', '>=') && version_compare(_PS_VERSION_, '1.5.1', '<'))
					{
						/*1.5.0.17 */
						$type_medium = 'medium';
						$mediumSize = Image::getSize('medium');
					}
					elseif (version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.3.0', '<'))
					{
						$type_medium = BlockWishListpro::getFormatedName('medium');
						$mediumSize = Image::getSize(BlockWishListpro::getFormatedName('medium'));
					}
					else
					{
						$type_medium = ImageType::getFormatedName('medium');
						$mediumSize = Image::getSize(ImageType::getFormatedName('medium'));
					}
					$context->smarty->assign(array(
						'products' => $products,
						'productsBoughts_actual' => $productBoughts_actual,
						'productsBoughts_act_st' => $productBoughts_actual_sort,
						'id_wishlist' => $id_wishlist,
						'themeChoice' => Configuration::get('PS_WISHLISTPRO_FO_THEME'),
						'message_default_tab' => $message_default_tab,
						'name' => $name,
						'token' => $token,
						'refresh' => $refresh,
						'modulename' => BlockWishListpro::MODULENAME,
						'type_medium' => $type_medium,
						'mediumSize' => $mediumSize,
						'language' => $language,
						'value' => $value,
						'total' => $total,
						'total_wl' => $total_wl,
						'spy_ps16' => (int)version_compare(_PS_VERSION_, '1.6.0', '>=')
						));
					$context->smarty->display(_PS_ROOT_DIR_.'/modules/blockwishlistpro/views/templates/front/managewishlistpro.tpl');
				}
			}
		}
?>