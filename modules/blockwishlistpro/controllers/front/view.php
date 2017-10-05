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
class BlockWishListproViewModuleFrontController extends ModuleFrontController
{
	public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
		include_once($this->module->getLocalPath().'WishListpro.php');
	}

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		$this->assignu();
	}

	/**
	 * Assign wishlist template
	 */
	public function assignu()
	{
		$errors = array();
		$token = Tools::getValue('token');
		$wishlist = WishListpro::getByToken($token);
		$module = new BlockWishListpro();
		$context = Context::getContext();
		$id_lang = (int)Context::getContext()->language->id;
		$ajax = Configuration::get('PS_BLOCK_CART_AJAX');
		$sm_ajax = (isset($ajax) && (int)$ajax == 1) ? '1' : '0';
		$moduleName = 'blockcart';
		$sm_blockcart_is_enable = Module::isEnabled($moduleName) ? '1' : 0;
		$mod_active = WishListpro::is_module_active($moduleName);/*blockcart module, if not active: = 0, if active: = 1*/
		$sm_blockcart_is_active = (isset($mod_active['active']) && (int)$mod_active['active'] == 1) ? '1' : '0';
		$displa = '';
		$resw = array();
		$id_cart = (int)$context->cart->id; /*null or int*/
		if ($id_cart != 0)
		{
			$cart = new Cart($id_cart);
			if (!Validate::isLoadedObject($cart))
			{
				echo Tools::displayError('cannot load object - view (aw)');
				exit();
			}
		}

		if (Tools::getValue('mx_remove_cart') !== false)
		{
			$res_cp = Db::getInstance()->ExecuteS('
				SELECT  * FROM `'._DB_PREFIX_.'cart_product`
				WHERE `id_cart` = '.(int)$id_cart.'
			');
			if (!empty($res_cp))
				Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'wishlist_product_cart'.BlockWishListpro::SUFFIX.'`
				WHERE  `id_cart`='.(int)$id_cart);
			/* remove all products from cart */
			$cart_prodcts = $cart->getProducts();
			foreach ($cart_prodcts as $product)
				$cart->deleteProduct($product['id_product'], $product['id_product_attribute']);
			$displa = $module->l('Products removed from cart. Donations are allowed from now onwards.', 'blockwishlistpro');
		}
		/*check wether cart is not empty and products from another list added previously*/
		if ($id_cart != 0)
			$resw = WishListpro::getWishlistByCartId($id_cart, $wishlist['id_wishlist']);

		if (empty($token) === false)
		{
			WishListpro::checkFields(); /*check if this module is installed over the previous one and add extra fields*/
			if (!isset($wishlist))
				$errors[] = $module->l('cannot find wishlist in database', 'blockwishlistpro');
			if (!count($errors) && (empty($wishlist) === true || $wishlist === false))
				$errors[] = $module->l('Invalid wishlist token', 'view').'/ID/url. '.$module->l('Please check the url of the list', 'view');
			if (!count($errors))
			{
				/*is the list published ?*/
				$publ = WishListpro::getPublishedByToken($token);
				$wishlist['published'] = (int)$publ['published'];

				WishListpro::refreshWishList($wishlist['id_wishlist']);
				$products = WishListpro::getProductByIdCustomer((int)$wishlist['id_wishlist'], (int)$wishlist['id_customer'], $id_lang, null, true);
				$bought = WishListpro::getBoughtProduct((int)$wishlist['id_wishlist']); /*bought or incl identified customer cart*/
				$bought_reel = WishListpro::getBoughtProduct_reel((int)$wishlist['id_wishlist']); /*bought only (excl identified customer cart)*/
				$count_bought = count($bought);
				$count_bought_reel = count($bought_reel);
				$ct_products = count($products);
				for ($i = 0; $i < $ct_products; ++$i)
				{
					$products[$i]['isobj'] = 1;
					$products[$i]['isobj_attr'] = 1;
					$products[$i]['is_attr_exist'] = 1;
					$obj = new Product((int)$products[$i]['id_product'], false, (int)Context::getContext()->language->id);
					if (!Validate::isLoadedObject($obj))
					{
						$products[$i]['isobj'] = 0; /*isobj=0 means a bo cancelled product but still in list*/
						unset($products[$i]);
						continue;
					}
					else
					{
						if ($obj->hasAttributes() > 0)
						{
							if (Product::getProductAttributePrice($products[$i]['id_product_attribute']) === false)
							{
								$products[$i]['is_attr_exist'] = 0;
								unset($products[$i]);
								continue;
							}
						}
						elseif ($products[$i]['id_product_attribute'] != 0)
						{
							/*case of Product with 1 combination when added to list, then the combination has been deleted. Product swithout combination hence*/
							$products[$i]['is_attr_exist'] = 0;
							$products[$i]['isobj_attr'] = 0;
							unset($products[$i]);
							continue;
						}

						$products[$i]['description'] = $obj->description;
						$products[$i]['description_short'] = strip_tags($obj->description_short);
						$products[$i]['price_dd'] = WishListpro::getPriceAw($products[$i]['id_product'], $products[$i]['id_product_attribute']);

						/*dd (bought quantity + identified customer cart quantity)*/
						$products[$i]['bought'] = false;
						$products[$i]['bought_qty'] = 0;

						for ($j = 0, $k = 0; $j < $count_bought; ++$j)
						{
							if ($bought[$j]['id_product'] == $products[$i]['id_product'] &&	$bought[$j]['id_product_attribute'] == $products[$i]['id_product_attribute'])
							{
								$products[$i]['bought'][$k++] = $bought[$j];
								$products[$i]['bought_qty'] = $products[$i]['bought_qty'] + $bought[$j]['quantity'];
							}
						}
						/*dd (actual bought quantity)*/
						$products[$i]['bought_reel'] = false;
						$products[$i]['bought_qty_actual'] = 0;

						for ($j = 0, $k = 0; $j < $count_bought_reel; ++$j)
						{
							if ($bought_reel[$j]['id_product'] == $products[$i]['id_product'] && $bought_reel[$j]['id_product_attribute'] == $products[$i]['id_product_attribute'])
							{
								$products[$i]['bought_reel'][$k++] = $bought_reel[$j];
								$products[$i]['bought_qty_actual'] = $products[$i]['bought_qty_actual'] + $bought_reel[$j]['actual_qty'];
							}
						}
					}
/* img */
					if ($products[$i]['id_product_attribute'] == 0)
					{
						$images = $obj->getImages((int)$this->context->cookie->id_lang);
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
				} /*END for ($i = 0; $i < count($products); ++$i)*/

				$productBoughts = array();
				foreach ($products as $product)
					if (isset($product['bought']) && count($product['bought']))
						$productBoughts[] = $product;

				$stock_management = Configuration::get('PS_STOCK_MANAGEMENT');
				$sm_stock_management = (isset($stock_management) && (int)$stock_management == 1) ? '1' : '0';
				$order_out_of_stock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
				$sm_order_out_of_stock = (isset($order_out_of_stock) && (int)$order_out_of_stock == 1) ? '1' : '0';

				WishListpro::incCounter((int)$wishlist['id_wishlist']);

				/*Are the lists of the customer published ?*/
				$wishlists = WishListpro::getByIdCustomer((int)$wishlist['id_customer']);
				foreach ($wishlists as $i => $wishlist1)
				{
					$publ = WishListpro::getPublishedByToken($wishlist1['token']);
					$wishlists[$i]['published'] = (int)$publ['published'];
				}

				if (version_compare(_PS_VERSION_, '1.5', '>=') && version_compare(_PS_VERSION_, '1.5.1', '<'))
				{
					/*1.5.0.17 */
					$type_medium = 'medium';
					$mediumSize = Image::getSize('medium');
				}
				elseif (version_compare(_PS_VERSION_, '1.5.1', '>=') && version_compare(_PS_VERSION_, '1.5.3.0', '<'))
				{
					/*$type_medium = 'medium_default';
					$mediumSize = Image::getSize('medium_default');*/
					$type_medium = BlockWishListpro::getFormatedName('medium');
					$mediumSize = Image::getSize(BlockWishListpro::getFormatedName('medium'));
				}
				else
				{
					$type_medium = ImageType::getFormatedName('medium');
					$mediumSize = Image::getSize(ImageType::getFormatedName('medium'));
				}

				$this->context->smarty->assign(
					array(
					'isListConflict' => count($resw),
					'tabListConflict' => $resw,
					'displa' => $displa,
					'blockcart_is_active' => $sm_blockcart_is_active,
					'ajax' => $sm_ajax,
					'sm_blockcart_is_enable' => $sm_blockcart_is_enable,
					'order_out_of_stock' => $sm_order_out_of_stock,
					'stock_management' => $sm_stock_management,
					'token' => $token,
					'current_wishlist' => $wishlist,
					'wishlists' => $wishlists,
					'products' => $products,
					'productsBoughts' => $productBoughts,
					'type_medium' => $type_medium,
					'mediumSize' => $mediumSize,
					'themeChoice' => Configuration::get('PS_WISHLISTPRO_FO_THEME'),
					'moduleName' => BlockWishListpro::MODULENAME,
					'enable_on_current_device' => WishListpro::getModuleDeviceEnable('blockcart') /*true: blockcart enabled on current device*/,
					'type_display_default' => $module->type_display_default,
					'type_display_init' => $module->type_display_init
					));
			} /*END if !errors*/
			else
				$this->context->smarty->assign(array('errors_wl' => $errors));

//			$this->context->controller->addJS(_MODULE_DIR_.BlockWishListpro::MODULENAME.'/views/js/ajax-wishlistpro.js');
			$this->context->controller->addJS(_MODULE_DIR_.BlockWishListpro::MODULENAME.'/views/js/jquery/thickbox-modified.js');
			$this->setTemplate('view.tpl');
		} /*END	if (empty($token) === false)*/
	} /*END assignu*/
}
