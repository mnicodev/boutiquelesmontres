<?php
/**
* BLOCKWISHLISTPRO Front Office Feature - display products of a list, creator's view
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

/*
* WishList class, WishListpro.php
* WishLists management
* @category classes
*/

if (!defined('_PS_VERSION_'))
	exit;

class WishListpro extends ObjectModel
{
/*dd add from Cart*/
	/** @var integer Customer delivery address ID */
	public $id_address_delivery;
	/** @var integer Customer invoicing address ID */
	public $id_address_invoice;
	/** @var integer Customer currency ID */
	public $id_currency;
	/** @var integer Guest ID */
	public $id_guest;
	/** @var integer Language ID */
	public $id_lang;
	/** @var integer Carrier ID */
	public $id_carrier;
	/** @var boolean True if the customer wants a recycled package */
	public $recyclable = 1;
	/** @var boolean True if the customer wants a gift wrapping */
	public $gift = 0;
	/** @var string Gift message if specified */
	public $gift_message;
	public $_products = null;
	public $__taxCalculationMethod = null;
	public $published;

/*dd end*/
	/** @var integer Wishlist ID */
	public $id;
	/** @var integer Customer ID */
	public $id_customer;
	/** @var integer Token */
	public $token;
	/** @var integer Name */
	public $name;
	/** @var string Object creation date */
	public $date_add;
	/** @var string Object last modification date */
	public $date_upd;
	/** @var string Object last modification date */
	public $id_shop;
	/** @var string Object last modification date */
	public $id_shop_group;

	protected $fieldsSize = array('name' => 64, 'token' => 64);
	protected $fieldsRequired = array('id_customer', 'name', 'token', 'published');
	protected $fieldsValidate = array('id_customer' => 'isUnsignedId', 'name' => 'isMessage',
		'token' => 'isMessage', 'published' => 'isInt',	'id_shop' => 'isUnsignedId', 'id_shop_group' => 'isUnsignedId');
	protected $table = 'wishlist_pro';
	protected $identifier = 'id_wishlist';
	const SUFFIX = '_pro'; /*SUFFIX table, self::SUFFIX in this file*/

	/*----------------------------------------------------------*/
	public function getFields()
	{
		parent::validateFields();
		$fields = array();
		$fields['id_customer'] = (int)($this->id_customer);
		$fields['id_shop'] = (int)($this->id_shop);
		$fields['id_shop_group'] = (int)($this->id_shop_group);
		$fields['token'] = pSQL($this->token);
		$fields['name'] = pSQL($this->name);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		$fields['published'] = pSQL($this->published);
		return ($fields);
	}

	public function delete()
	{
		$context = Context::getContext();
		$cookie = $context->cookie;

		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'wishlist_email'.self::SUFFIX.'` WHERE `id_wishlist` = '.(int)$this->id);
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` WHERE `id_wishlist` = '.(int)$this->id);
		if (isset($cookie->id_wishlist))
			unset($cookie->id_wishlist);

		return (parent::delete());
	}

	/**
	 * Increment counter
	 *
	 * @return boolean succeed
	 */
	public static function incCounter($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT `counter`
		  FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'`
		WHERE `id_wishlist` = '.(int)$id_wishlist);
		if ($result == false || !count($result) || empty($result) === true)
			return (false);
		return (Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` SET
		`counter` = '.(int)($result['counter'] + 1).'
		WHERE `id_wishlist` = '.(int)$id_wishlist));
	}

	public static function isExistsByNameForUser($name)
	{
		if (Shop::getContextShopID())
			$shop_restriction = 'AND id_shop = '.(int)Shop::getContextShopID();
		elseif (Shop::getContextShopGroupID())
			$shop_restriction = 'AND id_shop_group = '.(int)Shop::getContextShopGroupID();
		else
			$shop_restriction = '';

		$context = Context::getContext();
		return Db::getInstance()->getValue('
		SELECT COUNT(*) AS total
		FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'`
		WHERE `name` = \''.pSQL($name).'\'
		AND `id_customer` = '.(int)$context->customer->id.'
		'.$shop_restriction
		);
	}
	public static function isExistsByNameForUserOld($name) /*version 1.1*/
	{
		if (Shop::getContextShopID())
			$shop_restriction = 'AND id_shop = '.(int)Shop::getContextShopID();
		elseif (Shop::getContextShopGroupID())
			$shop_restriction = 'AND id_shop_group = '.(int)Shop::getContextShopGroupID();
		else
			$shop_restriction = '';

		$context = Context::getContext();
		return Db::getInstance()->getRow('
		SELECT COUNT(*) AS total
		FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'`
		WHERE `name` = \''.pSQL($name).'\'
		AND `id_customer` = '.(int)$context->customer->id.'
		'.$shop_restriction
		);
	}

	/**
	 * Return true if wishlist exists else false
	 *
	 *  @return boolean exists
	 */
	public static function exists($id_wishlist, $id_customer, $return = false)
	{
		if (!Validate::isUnsignedId($id_wishlist) || !Validate::isUnsignedId($id_customer))
			die (Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT `id_wishlist`, `name`, `token`
		  FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'`
		WHERE `id_wishlist` = '.(int)$id_wishlist.'
		AND `id_customer` = '.(int)$id_customer);
		if (empty($result) === false && $result != false && count($result))
		{
			if ($return === false)
				return (true);
			else
				return ($result);
		}
		return (false);
	}

	/**
	 * Get ID wishlist by Token
	 * @return array Results
	 */
	public static function getByToken($token)
	{
		if (Shop::getContextShopID())
			$shop_restriction = 'AND w.`id_shop` = '.(int)Shop::getContextShopID();
		elseif (Shop::getContextShopGroupID())
			$shop_restriction = 'AND w.`id_shop_group` = '.(int)Shop::getContextShopGroupID();
		else
			$shop_restriction = '';

		if (!Validate::isMessage($token))
			die (Tools::displayError());
		return (Db::getInstance()->getRow('
		SELECT w.`id_wishlist`, w.`name`, w.`id_customer`, c.`firstname`, c.`lastname`
		  FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w
		INNER JOIN `'._DB_PREFIX_.'customer` c ON c.`id_customer` = w.`id_customer`
		WHERE `token` = \''.pSQL($token).'\'
		'.$shop_restriction.'
		'));
	}

	/**
	 * Get Wishlists by Customer ID
	 * @return array Results
	 */
	public static function getByIdCustomer($id_customer)
	{
		if (!$id_customer)
		return;
		if (Shop::getContextShopID())
			$shop_restriction = 'AND id_shop = '.(int)Shop::getContextShopID();
		elseif (Shop::getContextShopGroupID())
			$shop_restriction = 'AND id_shop_group = '.(int)Shop::getContextShopGroupID();
		else
			$shop_restriction = '';

		if (!Validate::isUnsignedId($id_customer))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT w.`id_wishlist`, w.`name`, w.`token`, w.`date_add`, w.`date_upd`, w.`counter`
		FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w
		WHERE `id_customer` = '.(int)$id_customer.'
		'.$shop_restriction.'
		ORDER BY w.`name` ASC'));
	}

	/**
	 * Get Wishlists number products by Customer ID - wp.quantity = left quantity incl not yet payed cart
	 *
	 * @return array Results
	 */
	public static function getInfosByIdCustomer($id_customer)
	{
		if (Shop::getContextShopID())
			$shop_restriction = 'AND id_shop = '.(int)Shop::getContextShopID();
		elseif (Shop::getContextShopGroupID())
			$shop_restriction = 'AND id_shop_group = '.(int)Shop::getContextShopGroupID();
		else
			$shop_restriction = '';

		if (!Validate::isUnsignedId($id_customer))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT SUM(wp.`quantity`) AS nbProducts, wp.`id_wishlist`
		  FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
		INNER JOIN `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w ON (w.`id_wishlist` = wp.`id_wishlist`)
		WHERE w.`id_customer` = '.(int)$id_customer.'
		'.$shop_restriction.'
		GROUP BY w.`id_wishlist`
		ORDER BY w.`name` ASC'));
	}

	/** DD List of carts in wishlist n° id_wishlist
	 * Return id carts
	 * @return Array results
	 */
	public static function getListCart($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT wpc.`id_cart`
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.id_cart = wpc.id_cart)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		WHERE (wp.`id_wishlist`='.(int)$id_wishlist.' AND o.`id_cart` IS NOT NULL)
		GROUP BY wpc.`id_cart`'
		));
	}
	/**
	 * Get email from wishlist
	 *
	 * @return Array results
	 */
	public static function getEmail($id_wishlist, $id_customer)
	{
		if (!Validate::isUnsignedId($id_wishlist) || !Validate::isUnsignedId($id_customer))
			die (Tools::displayError());
		if (Shop::getContextShopID())
			$shop_restriction = 'AND id_shop = '.(int)Shop::getContextShopID();
		elseif (Shop::getContextShopGroupID())
			$shop_restriction = 'AND id_shop_group = '.(int)Shop::getContextShopGroupID();
		else
			$shop_restriction = '';

		return (Db::getInstance()->ExecuteS('
		SELECT we.`email`, we.`date_add`
		  FROM `'._DB_PREFIX_.'wishlist_email'.self::SUFFIX.'` we
		INNER JOIN `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w ON w.`id_wishlist` = we.`id_wishlist`
		WHERE we.`id_wishlist` = '.(int)$id_wishlist.'
		AND w.`id_customer` = '.(int)$id_customer.'
		'.$shop_restriction.''));
	}

	/** DD List of orders in wishlist n° id_wishlist
	 *
	 * @return Array results
	 */
	public static function getListOrder($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT wpc.`id_cart`, o.`id_order`
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		WHERE (wp.id_wishlist='.(int)$id_wishlist.' AND o.`id_cart` IS NOT NULL)
		GROUP BY wpc.`id_cart`, o.`id_order`
		ORDER BY o.`id_order` DESC
		'));
	}


	public static function refreshWishList($id_wishlist)
	{
		/*reset old carts*/
		$old_carts = Db::getInstance()->ExecuteS('
		SELECT wp.id_product, wp.id_product_attribute, wpc.id_cart, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(c.date_upd) AS timecart
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` c ON  (c.id_cart = wpc.id_cart)
		JOIN `'._DB_PREFIX_.'cart_product` cp ON (wpc.id_cart = cp.id_cart)
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.id_cart = c.id_cart)
		WHERE (wp.id_wishlist='.(int)$id_wishlist.' AND o.id_cart IS NULL)
		HAVING timecart  >= 3600*1');

		if (isset($old_carts) && $old_carts != false)
			foreach ($old_carts as $old_cart)
				Db::getInstance()->Execute('
					DELETE FROM `'._DB_PREFIX_.'cart_product`
					WHERE id_cart='.(int)$old_cart['id_cart'].' AND id_product='.(int)$old_cart['id_product'].' AND id_product_attribute='.(int)$old_cart['id_product_attribute']
				);

		/*res : wishlist products with cart id, quantity in cart, and quantity in wishlist cart*/
		$res = Db::getInstance()->ExecuteS('
			SELECT wp.`id_wishlist_product`, cp.`quantity` AS cart_quantity, wpc.`quantity` AS wish_quantity, wpc.`id_cart`, wp.`quantity` as quantity, wp.`quantity_left` as quantity_left, wp.`quantity_init` as quantity_init
			FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
			JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
			JOIN `'._DB_PREFIX_.'cart` c ON (c.`id_cart` = wpc.`id_cart`)
			JOIN `'._DB_PREFIX_.'cart_product` cp ON (cp.`id_cart` = wpc.`id_cart` AND cp.`id_product` = wp.`id_product` AND cp.`id_product_attribute` = wp.`id_product_attribute`)
			WHERE wp.`id_wishlist`='.(int)$id_wishlist
		);
		/*dd difference between wish cart and order*/
		/*Updating of wishcart according to the actual order cart : You order less or more than you put in the wish cart. ( > becomes <>)*/
		if (isset($res) && $res != false)
		{
			foreach ($res as $refresh)
				if ((int)$refresh['wish_quantity'] != (int)$refresh['cart_quantity'])
				{
					Db::getInstance()->Execute('
						UPDATE `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'`
						SET `quantity`='.(int)$refresh['cart_quantity'].'
						WHERE `id_wishlist_product`='.(int)$refresh['id_wishlist_product'].' AND `id_cart`='.(int)$refresh['id_cart']
					);
				}
		}

		/*freshwish : products added in the wishlist cart but cancelled afterwards (when ordering ... or in cart block)*/
		/*delete the line in wishlist_product_cart*/
		$freshwish = Db::getInstance()->ExecuteS('
			SELECT  wpc.`id_cart`, wpc.`id_wishlist_product`, wp.`id_product`, wp.`id_product_attribute`
			FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
			JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wpc.`id_wishlist_product` = wp.`id_wishlist_product`)
			JOIN `'._DB_PREFIX_.'cart` c ON (c.`id_cart` = wpc.`id_cart`)
			LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.id_cart = wpc.id_cart)
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
			WHERE (wp.`id_wishlist` = '.(int)$id_wishlist.' AND ((od.`product_id` IS NULL AND od.`product_attribute_id` IS NULL)))
			');

		if (isset($freshwish) && $freshwish != false)
		{
			foreach ($freshwish as $prodcustomer)
			{
				self::update_qty_wlproduct($id_wishlist, $prodcustomer['id_product'], $prodcustomer['id_product_attribute']);
/*check if there is a cart in [cart_product]. If yes and only one line in wishlist_product_cart and same ip ipa, do not delete*/
				$res_cp = Db::getInstance()->ExecuteS('
					SELECT  * FROM `'._DB_PREFIX_.'cart_product`
					WHERE `id_cart` = '.(int)$prodcustomer['id_cart'].'
				');
				$spy = 0;
				foreach ($res_cp as $line_cp)
				{
					if ($prodcustomer['id_cart'] == $line_cp['id_cart'] && $prodcustomer['id_product'] == $line_cp['id_product'] && $prodcustomer['id_product_attribute'] == $line_cp['id_product_attribute'])
						$spy = 1;/*spy pour delete apres boucle foreach*/
				}
				if ($spy == 0)
						Db::getInstance()->Execute('
							DELETE FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'`
							WHERE `id_wishlist_product`='.(int)$prodcustomer['id_wishlist_product'].' AND `id_cart`='.(int)$prodcustomer['id_cart']);
			}
		}

		/*Update quantity fields in wishlist_product */
		/*idem $res : quantities put in carts grouped by product*/
		$res_group_by = Db::getInstance()->ExecuteS('
			SELECT wp.`id_wishlist`, wp.`id_wishlist_product`,wp.`id_product`, wp.`id_product_attribute`, SUM(cp.`quantity`) AS cart_quantity, SUM(wpc.`quantity`) AS wish_quantity, MAX(wp.`quantity_rel`) as quantity_rel, MAX(wp.`quantity`) as quantity, MAX(wp.`quantity_left`) as quantity_left, MAX(wp.`quantity_init`) as quantity_init
			FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
			JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
			JOIN `'._DB_PREFIX_.'cart` c ON (c.`id_cart` = wpc.`id_cart`)
			JOIN `'._DB_PREFIX_.'cart_product` cp ON (cp.`id_cart` = wpc.`id_cart` AND cp.`id_product` = wp.`id_product` AND cp.`id_product_attribute` = wp.`id_product_attribute`)
			WHERE wp.`id_wishlist`='.(int)$id_wishlist.'
			GROUP BY wp.`id_wishlist`,wp.`id_wishlist_product`,wp.`id_product`,wp.`id_product_attribute` '
		);

			foreach ($res_group_by as $product_detail)
			{
				$product_detail['quantity_rel'] = $product_detail['quantity_init'] - $product_detail['cart_quantity'];
				$product_detail['quantity'] = $product_detail['quantity_rel'] >= 0 ? $product_detail['quantity_rel'] : 0;
$ordered_only = self::getProductBoughtQty_actual($id_wishlist, $product_detail['id_product'], $product_detail['id_product_attribute']);

				if (empty($ordered_only))
					$ordered_only[0]['actual_qty'] = 0;
				$product_detail['quantity_left_rel'] = $product_detail['quantity_init'] - $ordered_only[0]['actual_qty'];
				$product_detail['quantity_left'] = $product_detail['quantity_left_rel'] >= 0 ? $product_detail['quantity_left_rel'] : 0;

				Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'`
					SET `quantity_rel`= '.$product_detail['quantity_rel'].',
					`quantity`= '.$product_detail['quantity'].',
					`quantity_left_rel`= '.$product_detail['quantity_left_rel'].',
					`quantity_left`= '.$product_detail['quantity_left'].'
					WHERE `id_wishlist_product`='.(int)$product_detail['id_wishlist_product'].' AND `id_wishlist`='.(int)$id_wishlist
					);
			}
	}

	/**
	 * Get Wishlist products by Customer ID
	 * Param $quantity = true : get only products with left quantity. Note that if a product is in a cart but not already ordered, it might not appear if the quantity in cart = left quantity
	 * @return array Results
	 */
	public static function getProductByIdCustomer($id_wishlist, $id_customer, $id_lang, $id_product = null, $quantity = false)
	{
		if (!Validate::isUnsignedId($id_customer) || !Validate::isUnsignedId($id_lang) || !Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
if (Shop::getContextShopID())
	$shop_restriction = 'AND id_shop = '.(int)Shop::getContextShopID();
elseif (Shop::getContextShopGroupID())
	$shop_restriction = 'AND id_shop_group = '.(int)Shop::getContextShopGroupID();
else
	$shop_restriction = '';

		$products = Db::getInstance()->ExecuteS('
		SELECT wp.`id_product`, wp.`quantity`, wp.`quantity_init`, sk_av.`quantity` AS product_quantity, sk_av.`out_of_stock` AS product_out_of_stock, p.`quantity` AS product_quantityOLD, p.`reference`, pl.`name`, wp.`id_product_attribute`, wp.`priority`, pl.link_rewrite, cl.link_rewrite AS category_rewrite
	  	FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
		JOIN `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w
			ON w.`id_wishlist` = wp.`id_wishlist`
		LEFT OUTER JOIN `'._DB_PREFIX_.'product` p
			ON p.`id_product` = wp.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'product_shop` product_shop
			ON (product_shop.id_product = p.id_product AND product_shop.id_shop = '.(int)Shop::getContextShopID().')

		LEFT OUTER JOIN `'._DB_PREFIX_.'stock_available` sk_av
			ON (sk_av.`id_product` = wp.`id_product` AND sk_av.`id_product_attribute`=0
		'.StockAvailable::addSqlShopRestriction(null, null, 'sk_av').')
		LEFT OUTER JOIN `'._DB_PREFIX_.'product_lang` pl
			ON (pl.`id_product` = wp.`id_product`'.Shop::addSqlRestrictionOnLang('pl').' AND pl.`id_lang` = '.(int)$id_lang.')

		LEFT OUTER JOIN `'._DB_PREFIX_.'product_shop` pr_s
			ON pr_s.`id_product` = wp.`id_product`'.Shop::addSqlRestrictionOnLang('pr_s').'
		LEFT OUTER JOIN `'._DB_PREFIX_.'category_lang` cl
			ON cl.`id_category` = pr_s.`id_category_default` AND cl.id_lang='.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').'

		WHERE w.`id_customer` = '.(int)$id_customer.'

		AND wp.`id_wishlist` = '.(int)$id_wishlist.
		($id_product !== null ? ' AND wp.`id_product` = '.(int)$id_product : '').
		($quantity == true ? ' AND wp.`quantity` != 0': '').'
		ORDER BY wp.`priority` ASC, pl.`name` ASC '
		);

		/*LEFT OUTER : Add in this array products which have been cancelled since the creation and boughts  : if a product has been cancelled, it is not in product table anymore and hence not in this $products array. But has to be considered as part of the list*/

		if (empty($products) === true || !count($products))
			return array();
		$cpt_products = count($products);
		for ($i = 0; $i < $cpt_products; ++$i)
		{
			if (isset($products[$i]['id_product_attribute']) && Validate::isUnsignedInt($products[$i]['id_product_attribute']))
			{
				$result = Db::getInstance()->ExecuteS('
				SELECT al.`name` AS attribute_name, sk_av.`quantity` AS "attribute_quantity", pa.`quantity` AS "attribute_quantityOLD"
				  FROM `'._DB_PREFIX_.'product_attribute_combination` pac
				LEFT JOIN `'._DB_PREFIX_.'stock_available` sk_av ON (sk_av.`id_product_attribute`=pac.`id_product_attribute`
				'.StockAvailable::addSqlShopRestriction(null, null, 'sk_av').')
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
				WHERE pac.`id_product_attribute` = '.(int)$products[$i]['id_product_attribute']);
				$products[$i]['attributes_small'] = '';
				if ($result)
					foreach ($result as $k => $row)
						$products[$i]['attributes_small'] .= $row['attribute_name'].', ';
				$products[$i]['attributes_small'] = rtrim($products[$i]['attributes_small'], ', ');
				if (isset($result[0]))
					$products[$i]['attribute_quantity'] = $result[0]['attribute_quantity'];
			}
			else
				$products[$i]['attribute_quantity'] = $products[$i]['product_quantity'];
		}
		return $products;
	}

	/**
	 * Add product to ID wishlist
	 *
	 * @return boolean succeed
	 */
	public static function addProduct($id_wishlist, $id_customer, $id_product, $id_product_attribute, $quantity)
	{
		if (!Validate::isUnsignedId($id_wishlist) || !Validate::isUnsignedId($id_customer) || !Validate::isUnsignedId($id_product) || !Validate::isUnsignedId($quantity))
			die (Tools::displayError());

/*----------DD initial quantity---------*/
			$updat = Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
				SET	wp.`quantity_init` = wp.`quantity_init` + '.(int)$quantity.'
				WHERE wp.`id_wishlist` = '.(int)$id_wishlist.'
				AND wp.`id_product` = '.(int)$id_product.'
				AND wp.`id_product_attribute` = '.(int)$id_product_attribute);
/*----------------end DD----------------*/
		$result = Db::getInstance()->getRow('
		SELECT wp.`quantity`, wp.`quantity_init`
		  FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
		JOIN `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w ON (w.`id_wishlist` = wp.`id_wishlist`)
		WHERE wp.`id_wishlist` = '.(int)$id_wishlist.'
		AND w.`id_customer` = '.(int)$id_customer.'
		AND wp.`id_product` = '.(int)$id_product.'
		AND wp.`id_product_attribute` = '.(int)$id_product_attribute);

		/*If product has attribute, minimal quantity is set with minimal quantity of attribute*/
		$product = new Product((int)$id_product);
		$minimal_quantity = ($id_product_attribute) ? Attribute::getAttributeMinimalQty($id_product_attribute) : $product->minimal_quantity;
		if ((int)($quantity + $result['quantity']) < (int)$minimal_quantity)
			return sprintf(Tools::displayError('You must add %d minimum quantity', !Tools::getValue('ajax')), $minimal_quantity);

		if (empty($result) === false && count($result))  //product already registered in wishlist, no change in quantity_init
		{
			if (($result['quantity'] + $quantity) <= 0)
				return (self::removeProduct($id_wishlist, $id_customer, $id_product, $id_product_attribute));
			else
				return (Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` SET
				`quantity` = '.(int)($quantity + $result['quantity']).'
				WHERE `id_wishlist` = '.(int)$id_wishlist.'
				AND `id_product` = '.(int)$id_product.'
				AND `id_product_attribute` = '.(int)$id_product_attribute));
		}
		else
		{
			/*new product in wishlist, quantity_init=quantity	*/
			return (Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` (`id_wishlist`, `id_product`, `id_product_attribute`, `quantity_rel`, `quantity`, `quantity_init`, `quantity_left_rel`, `quantity_left`, `priority`, `alert_qty`) VALUES(
			'.(int)$id_wishlist.',
			'.(int)$id_product.',
			'.(int)$id_product_attribute.',
			0,
			'.(int)$quantity.',
			'.(int)$quantity.',
			0,
			0,
			1,
			0
			)'));
		}
		return true;
	}

	/**
	 * Update product to wishlist
	 * @return boolean succeed
	 */
	public static function updateProduct($id_wishlist, $id_product, $id_product_attribute, $priority, $new_quantity)
	{
		if (!Validate::isUnsignedId($id_wishlist) || !Validate::isUnsignedId($id_product) || !Validate::isUnsignedId($new_quantity) || $priority < 0 || $priority > 4)
			die (Tools::displayError());

/*----------DD initial quantity---------*/
			if ((int)$new_quantity < 0)
				$new_quantity = - (int)$new_quantity;
			else
				$new_quantity = (int)$new_quantity;

			$alert_qty = 0; /*if 1, ajax alert : wanted qty can not be inferior to bought qty*/
			$resul = Db::getInstance()->getRow('
				SELECT wp.`quantity_init` as quantity_init, wp.`quantity` as quantity1,wp.`quantity_rel`,wp.`quantity_left`
		 			FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
				WHERE `id_wishlist` = '.(int)$id_wishlist.'
				AND `id_product` = '.(int)$id_product.'
				AND `id_product_attribute` = '.(int)$id_product_attribute);

			$bought_and_cart = Db::getInstance()->getRow('
				SELECT wp.`id_product`, wp.`id_product_attribute`, SUM(wpc.`quantity`) as qty
				FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
				JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
				JOIN `'._DB_PREFIX_.'cart` ca ON (ca.id_cart = wpc.id_cart)
				JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = ca.`id_customer`)
				WHERE (wp.`id_wishlist` = '.(int)$id_wishlist.' AND wp.`id_product`='.(int)$id_product.' AND wp.`id_product_attribute`='.(int)$id_product_attribute.')
				GROUP BY wp.`id_product`, wp.`id_product_attribute`'
				);

			$actual_bought = Db::getInstance()->getRow('
				SELECT wp.`id_product`, wp.`id_product_attribute`, SUM(od.`product_quantity`) as actual_qty
				FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
				JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
				JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
				JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
				JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
				WHERE (wp.id_wishlist='.(int)$id_wishlist.' AND wp.`id_product`='.(int)$id_product.' AND wp.`id_product_attribute`='.(int)$id_product_attribute.' AND o.`id_cart` IS NOT NULL )
				GROUP BY wp.`id_product`, wp.`id_product_attribute`'
				);
	/*dd difference between updated wanted quantity and the previous one, calculation of new quantity */
			if (empty($resul) === false && count($resul))
			{
				/*If product has attribute, minimal quantity is set with minimal quantity of attribute*/
				$product = new Product((int)$id_product);
				$minimal_quantity = ($id_product_attribute) ? Attribute::getAttributeMinimalQty($id_product_attribute) : $product->minimal_quantity;
				if ((int)$new_quantity < (int)$minimal_quantity)
					return 'minimal_qty|'.sprintf(Tools::displayError('The minimal quantity is %d', !Tools::getValue('ajax')), $minimal_quantity);

				if (empty($actual_bought) === false && count($actual_bought))
				{	/*wanted quantity can not be changed below bought quantity*/
					if ($resul['quantity_init'] == 0 && $new_quantity < (int)$actual_bought['actual_qty'])
					{
						$new_quantity = (int)$actual_bought['actual_qty'];
						$alert_qty = 1;
					}
					elseif ($new_quantity < (int)$actual_bought['actual_qty'])
					{
						$new_quantity = $resul['quantity_init'];
						$alert_qty = 1;
					}
				}
				if (empty($actual_bought) === true || !count($actual_bought))
					$actual_bought['actual_qty'] = 0;
/*OLD*/
				$diff = (int)$new_quantity - (int)$resul['quantity_init'];
/*				$new_quantity = (int)$resul['quantity1'] + $diff;*/
				if (empty($bought_and_cart) === false && count($bought_and_cart))
				/*$new_quantity = (int)$quantity - (int)$bought_and_cart['qty'];*/
					$quantity_rel = (int)$new_quantity - (int)$bought_and_cart['qty'];
				else
					$quantity_rel = (int)$new_quantity;

				$quantity = $quantity_rel >= 0 ? $quantity_rel : 0;
			}
			else
				$quantity_rel  = (int)$new_quantity; /*$new_quantity  should never happen*/
			$quantity_left_rel = (int)$new_quantity - (int)$actual_bought['actual_qty'];
			$quantity_left = $quantity_left_rel >= 0 ? $quantity_left_rel : 0;
			if ($quantity_rel > (int)$quantity_left)
			{
				$quantity_rel = (int)$quantity_left_rel;
				$quantity = $quantity_rel >= 0 ? $quantity_rel : 0;
			}
			/*in case of quantity in cart + decrease in wanted quantity + release of cart quantity)*/
	/*dd update of wanted quantity : quantity_init */
				$updatewl = Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` SET
				`quantity_init` = '.(int)$new_quantity.',
				`priority` = '.(int)$priority.',
				`quantity` = '.(int)$quantity.',
				`quantity_rel` = '.(int)$quantity_rel.',
				`quantity_left` = '.(int)$quantity_left.',
				`quantity_left_rel` = '.(int)$quantity_left_rel.',
				`alert_qty` = '.(int)$alert_qty.'
				WHERE `id_wishlist` = '.(int)$id_wishlist.'
				AND `id_product` = '.(int)$id_product.'
				AND `id_product_attribute` = '.(int)$id_product_attribute);
				return $updatewl;
	}

	/**
	 * Remove product from wishlist
	 *
	 * @return boolean succeed
	 */
	public static function removeProduct($id_wishlist, $id_customer, $id_product, $id_product_attribute)
	{
		if (!Validate::isUnsignedId($id_wishlist) || !Validate::isUnsignedId($id_customer) || !Validate::isUnsignedId($id_product))
			die (Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT w.`id_wishlist`, wp.`id_wishlist_product`
		FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w
		LEFT JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.`id_wishlist` = w.`id_wishlist`)
		WHERE w.`id_customer` = '.(int)$id_customer.'
		AND wp.`id_product` = '.(int)$id_product.'
		AND wp.`id_product_attribute` = '.(int)$id_product_attribute.'
		AND w.`id_wishlist` = '.(int)$id_wishlist);
		if (empty($result) === true || $result === false || !count($result) || $result['id_wishlist'] != $id_wishlist)
			return false;

		/*Delete product in wishlist_product_cart*/
		Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'`
		WHERE `id_wishlist_product` = '.(int)$result['id_wishlist_product']);
		return Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'`
		WHERE `id_wishlist` = '.(int)$id_wishlist.'
		AND `id_product` = '.(int)$id_product.'
		AND `id_product_attribute` = '.(int)$id_product_attribute);
	}

	/**
	 * Return bought product by ID wishlist
	 *
	 * @return Array results
	 */
	public static function getBoughtProduct($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT wp.`id_product`, wp.`id_product_attribute`, wpc.`quantity`, wpc.`date_add`, cu.`lastname`, cu.`firstname`
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = ca.`id_customer`)
		WHERE wp.`id_wishlist` = '.(int)$id_wishlist));
	}
	/**
	 * Return bought or cart product by ID wishlist : quantity in cart (ordered or not) group by product
	 *
	 * @return Array results
	 */
	public static function getCartBoughtQtyProduct($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT wp.`id_wishlist_product`, wp.`id_product`, wp.`id_product_attribute`, SUM(wpc.`quantity`) as cart_quantity
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		WHERE wp.`id_wishlist` = '.(int)$id_wishlist.'
		GROUP BY wp.`id_wishlist_product`,wp.`id_wishlist`,wp.`id_product`, wp.`id_product_attribute` '
		));
	}
	/**
	 * concerns native module and not the pro new one : Return bought or cart product by ID wishlist : quantity in cart (ordered or not) group by product
	 *
	 * @return Array results
	 */
	public static function getCartBoughtQtyProduct_oldversion($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT wp.`id_wishlist_product`, wp.`id_product`, wp.`id_product_attribute`, SUM(wpc.`quantity`) as cart_quantity
		FROM `'._DB_PREFIX_.'wishlist_product_cart` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		WHERE wp.`id_wishlist` = '.(int)$id_wishlist.'
		GROUP BY wp.`id_wishlist_product`,wp.`id_wishlist`,wp.`id_product`, wp.`id_product_attribute` '
		));
	}

	/** DD details actuals bought products, excl products in wish carts
	 * Return bought product detailed by bought cart, for a specific ID wishlist
	 *
	 * @return Array results
	 */
	public static function getBoughtProduct_reel($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
/*
		return (Db::getInstance()->ExecuteS('
		SELECT wp.`id_product`, wp.`id_product_attribute`, wpc.`quantity`, cp.`quantity` as actual_qty, wpc.`date_add`,  cu.`lastname`, cu.`firstname`, ca.`date_upd`
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart_product` cp ON (cp.`id_cart` = wpc.`id_cart` && cp.`id_product`=wp.`id_product` && wp.`id_product_attribute`=cp.`id_product_attribute`)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		LEFT JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = ca.`id_customer`)
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		WHERE (wp.`id_wishlist`='.(int)$id_wishlist.' && o.`id_cart` IS NOT NULL )'
		));
*/
		return (Db::getInstance()->ExecuteS('
		SELECT wp.`id_product`, wp.`id_product_attribute`, wpc.`quantity`, od.`product_quantity` as actual_qty, wpc.`date_add`,  cu.`lastname`, cu.`firstname`, ca.`date_upd`, o.`id_cart`, od.`product_name` as pdt_order_name
		FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
		JOIN `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.id_cart = wpc.id_cart)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		LEFT JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = ca.`id_customer`)
		WHERE (wp.`id_wishlist`='.(int)$id_wishlist.' AND o.`id_cart` IS NOT NULL )'
		));

}

	/** DD actuals bought products, excl products in wish carts, per id_product and id_product_attribute (group by)
	 * Return global bought quantity product for a specific ID wishlist
	 *
	 * @return Array results
	 */
	public static function getBoughtQty_actual($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('

		SELECT wp.`id_product`, wp.`id_product_attribute`, SUM(od.`product_quantity`) as actual_qty, wp.`id_wishlist_product`
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		WHERE (wp.`id_wishlist`='.(int)$id_wishlist.' AND o.`id_cart` IS NOT NULL )
		GROUP BY wp.`id_product`, wp.`id_product_attribute`'
		));
}
	/** Concerns native version only, not pro one | DD actuals bought products, excl products in wish carts, per id_product and id_product_attribute (group by)
	 * Return global bought quantity product for a specific ID wishlist
	 *
	 * @return Array results
	 */
	public static function getBoughtQty_actual_oldversion($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('

		SELECT wp.`id_product`, wp.`id_product_attribute`, SUM(od.`product_quantity`) as actual_qty, wp.`id_wishlist_product`
		FROM `'._DB_PREFIX_.'wishlist_product_cart` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		WHERE (wp.`id_wishlist`='.(int)$id_wishlist.' AND o.`id_cart` IS NOT NULL )
		GROUP BY wp.`id_product`, wp.`id_product_attribute`'
		));
}
/*----------------------------------------------------------------------------------------*/
	/** DD actual bought quantity for a specific product, excl products in wish carts, per id_product and id_product_attribute (group by)
	 * Return global bought quantity  for a product and specific ID wishlist
	 * @return Array results
	 */
	public static function getProductBoughtQty_actual($id_wishlist, $id_product, $id_product_attribute, $id_order = null)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('

		SELECT wp.`id_product`, wp.`id_product_attribute`, SUM(od.`product_quantity`) as actual_qty, od.`product_name` as pdt_order_name
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		WHERE (
			wp.`id_wishlist`='.(int)$id_wishlist.'
			AND o.`id_cart` IS NOT NULL
			AND wp.`id_product`='.(int)$id_product.'
			AND wp.`id_product_attribute`='.(int)$id_product_attribute.
			($id_order !== null ? ' AND o.`id_order` = '.(int)$id_order : '').
			')
		GROUP BY wp.`id_product`, wp.`id_product_attribute`'
		));
}


	/** DD orders and value of actuals bought products, excl products in wish carts but including mixed carts (products out of the list but in the same cart as wishlist products
	 * Return orders and paid amounts, for a specific ID wishlist
	 * @return Array results
	 */
	public static function getValue($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT cu.`lastname`, cu.`firstname`, o.`date_upd`, o.`id_order`,o.`total_discounts`, o.`total_paid`,o.`total_paid_real`, o.`total_shipping`, o.`total_products_wt`, o.`total_products`, o.`total_wrapping`
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		LEFT JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = ca.`id_customer`)
		WHERE (wp.`id_wishlist`='.(int)$id_wishlist.' AND o.`id_cart` IS NOT NULL)
		GROUP BY cu.`lastname`, cu.`firstname`, o.`date_upd`, o.`id_order`'
		));
}

	/**
	 * Add bought product
	 * @return boolean succeed
	 */
	public static function addBoughtProduct($id_wishlist, $id_product, $id_product_attribute, $id_cart, $quantit)
	{
		/*quantity=1 given by ajax_wishlist dd*/
		if (!Validate::isUnsignedId($id_wishlist) || !Validate::isUnsignedId($id_product) || !Validate::isUnsignedId($quantit))
			die (Tools::displayError());

		$resultt = Db::getInstance()->getRow('
			SELECT `quantity_rel`,`quantity`, `id_wishlist_product`
		  FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
			WHERE `id_wishlist` = '.(int)$id_wishlist.'
			AND `id_product` = '.(int)$id_product.'
			AND `id_product_attribute` = '.(int)$id_product_attribute);
		if (!count($resultt)) return false;

		/*get price when adding product in cart*/
		$price = Product::getPriceStatic((int)$id_product, true, (isset($id_product_attribute) ? (int)$id_product_attribute : null), 2, null, false, true, 1, false, null, null, null);
		$price_wt = Product::getPriceStatic((int)$id_product, false, (isset($id_product_attribute) ? (int)$id_product_attribute : null), 2, null, false, true, 1, false, null, null, null);


			Db::getInstance()->Execute('
			SELECT *
			FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'`
			WHERE `id_wishlist_product`='.(int)$resultt['id_wishlist_product'].' AND `id_cart`='.(int)$id_cart
			);

		if (Db::getInstance()->NumRows() > 0)
			$result2 = Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'`
				SET `quantity`=`quantity` + '.(int)$quantit.'
				WHERE `id_wishlist_product`='.(int)$resultt['id_wishlist_product'].' AND `id_cart`='.(int)$id_cart
				);
		else											/*if product not already in wishlist cart*/
			$result2 = Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'`
				(`id_wishlist_product`, `id_cart`, `quantity`, `price_init`, `price_wt_init`, `date_add`) VALUES(
				'.(int)$resultt['id_wishlist_product'].',
				'.(int)$id_cart.',
				'.(int)$quantit.',
				'.(float)($price).',
				'.(float)($price_wt).',
				\''.pSQL(date('Y-m-d H:i:s')).'\')');

		if ($result2 === false)
			return (false);
/*dd as we can add in wishlist cart more than wanted quantity, decreasing quantity must be limited to 0*/
/*dd*/	$rt = (int)$resultt['quantity'] - (int)$quantit;
/*dd*/	if ($rt < 0)
			$rt = 0;

		return self::update_qty_wlproduct($id_wishlist, $id_product, $id_product_attribute);
	}

	/**
	 * Add email to wishlist
	 *
	 * @return boolean succeed
	 */
	public static function addEmail($id_wishlist, $email)
	{
		if (!Validate::isUnsignedId($id_wishlist) || !Validate::isEmail($email))
		{
			Tools::displayError('Error id_wishlist or email validation / addEmail function');
			return false;
		}
		return (Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'wishlist_email'.self::SUFFIX.'` (`id_wishlist`, `email`, `date_add`) VALUES(
		'.(int)$id_wishlist.',
		\''.pSQL($email).'\',
		\''.pSQL(date('Y-m-d H:i:s')).'\')'));
	}


/*-------------------------------------------*/
	/** DD List of orders with wishlist with date_add between date1 and date2
	 * @return Array results
	 */
	public static function getOrdersWithWishlist($date1 = false, $date2 = false)
	{
		$req = (Db::getInstance()->ExecuteS('
		SELECT o.`date_add`,o.`id_order`, o.`date_add`, o.`id_customer` as id_donator,
			( SELECT car.`name`
								  FROM `'._DB_PREFIX_.'carrier` car
								 WHERE ( o.`id_carrier`= car.`id_carrier`)
			  GROUP BY car.`id_carrier`) as carrier,
			cu.`lastname`,cu.`firstname`, wp.`id_wishlist`, w.`name` as name_wl, w.`id_customer` as id_creator,
			( SELECT cu.`lastname`
								  FROM `'._DB_PREFIX_.'customer` cu

								 WHERE ( w.`id_customer`= cu.`id_customer`)
			  GROUP BY cu.`id_customer`) as lastname_wl,
			( SELECT cu.`firstname`
								  FROM `'._DB_PREFIX_.'customer` cu

								 WHERE ( w.`id_customer`= cu.`id_customer`)
			  GROUP BY cu.`id_customer`) as firstname_wl
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
		JOIN `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w ON (wp.`id_wishlist` = w.`id_wishlist`)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		LEFT JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = ca.`id_customer`)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		WHERE o.`id_cart` IS NOT NULL AND o.`date_add` BETWEEN '.' \''.$date1.' 00:00:00\' AND \''.$date2.' 23:59:59\' '.'
		GROUP BY o.`id_order`
		ORDER BY o.`id_order` DESC
		'));
		return $req;
		}

	/** DD Id of the latest order in wishlist
	 * @return string results
	 */
	public static function getListLastOrder($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		$temp = (Db::getInstance()->ExecuteS('
		SELECT o.`id_order`
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		WHERE (wp.`id_wishlist`='.(int)$id_wishlist.' AND o.`id_cart` IS NOT NULL)
		GROUP BY o.`id_order`
		ORDER BY o.`id_order` DESC
		'));
		if (isset($temp[0])) return $temp[0];
	}
	/** DD Id of the latest order send by mail in pdf format
	 *
	 * @return Array results
	 */
	public static function getListLastOrderMailPDF($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		$temp = (Db::getInstance()->ExecuteS('
		SELECT `id_wishlist`, MAX(`id_order`) as max_order
		FROM `'._DB_PREFIX_.'wishlist_send_pdf'.self::SUFFIX.'` wspdf
		WHERE (wspdf.id_wishlist='.(int)$id_wishlist.' AND wspdf.`id_order` IS NOT NULL)
		GROUP BY `id_wishlist`
		ORDER BY max_order DESC
		'));
		return $temp;
	}
/*------------------------------------------*/
	/** DD Id of the latest wishlist of an order
	 *
	 * @return string results
	 */
	public static function getWishlistId($id_order)
	{
		if (!Validate::isUnsignedId($id_order))
			die (Tools::displayError());
		$temp = (Db::getInstance()->ExecuteS('
		SELECT wp.`id_wishlist`
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.id_wishlist_product = wpc.id_wishlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_cart` = ca.`id_cart`)
		JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order` AND od.`product_id` = wp.`id_product` AND od.`product_attribute_id` = wp.`id_product_attribute`)
		WHERE (o.`id_order`= '.(int)$id_order.' AND o.`id_cart` IS NOT NULL)
		GROUP BY wp.`id_wishlist`
		ORDER BY wp.`id_wishlist` DESC
		'));
		if (isset($temp[0])) return $temp[0];
	}

/*------------------------------------------*/
	/** DD get customer name of wishlist creator  with n° id_wishlist
	 *
	 * @return Array results
	 */
	public static function getCreatorName($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT cu.`firstname`, cu.`lastname`
		FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w
		LEFT JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = w.`id_customer`)
		WHERE w.`id_wishlist`='.(int)$id_wishlist.'
		'));
	}
	/** DD get customer ID and email of wishlist creator  with n° id_wishlist
	 *
	 * @return Array results
	 */
	public static function getCreatorID($id_wishlist)
	{
		if (!Validate::isUnsignedId($id_wishlist))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT cu.`firstname`, cu.`lastname`,cu.`id_customer`,cu.`email`
		FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w
		LEFT JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = w.`id_customer`)
		WHERE w.`id_wishlist`='.(int)$id_wishlist.'
		'));
	}

	/** DD get messages of an order when clicking gift wrapping during order process
	 *
	 * @return Array results
	 */
	public static function getMessageGift($id_order)
	{
		if (!Validate::isUnsignedId($id_order))
			die (Tools::displayError());
		// list of carts of this order
		$gif_mess = Db::getInstance()->ExecuteS('
		SELECT o.`gift_message`
		FROM `'._DB_PREFIX_.'orders` o
		WHERE o.`id_order`='.(int)$id_order.'
		');
		return $gif_mess;
	}

	/** DD check wether extra fiels (quantity_init, quantity_left, alert_qty) already exist and add them if not
	 *
	 * @return Array results
	 */
	public static function checkFields()
	{
		/*quantity_init*/
		$resul_init = Db::getInstance()->Execute('
			SELECT wp.`quantity_init`
				FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
			');
		/*quantity_left*/
		$resul_left = Db::getInstance()->Execute('
			SELECT wp.`quantity_left`
				FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
			');
		/*alert_qty*/
		$resul_alert = Db::getInstance()->Execute('
			SELECT wp.`alert_qty`
				FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
			');

		if ($resul_init === false || $resul_left === false || $resul_alert === false)
		{
			/*fields creation*/
			$init = Db::getInstance()->Execute('
				ALTER TABLE `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'`
				ADD `quantity_init` INTEGER DEFAULT "0"
				');
			$left = Db::getInstance()->Execute('
				ALTER TABLE `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'`
				ADD `quantity_left` INTEGER DEFAULT "0"');
			$alert = Db::getInstance()->Execute('
				ALTER TABLE `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'`
				ADD `alert_qty` INTEGER DEFAULT "0"');

			/*list by list*/
			$list = Db::getInstance()->ExecuteS('
				SELECT wp.`id_wishlist`, SUM(wp.`quantity`)
				FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
				GROUP BY wp.`id_wishlist`
			');
			/*bought products*/
			foreach ($list as $nb_list)
			{
				$id_wishlist = $nb_list['id_wishlist'];
				$bought = self::getBoughtQty_actual($id_wishlist);
				/*field initialization - quantity_init*/
				$resul_wl = Db::getInstance()->ExecuteS('
				SELECT wp.`id_product`, wp.`id_product_attribute`, wp.`quantity`
					FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
					WHERE wp.`id_wishlist`='.(int)$id_wishlist.'
				');
				foreach ($resul_wl as $k => $prdt)
				{
					$cpt = 0;
					foreach ($bought as $bght_prdt)
					{
						if ($prdt['id_product'] == $bght_prdt['id_product'] && $prdt['id_product_attribute'] == $bght_prdt['id_product_attribute'])
						{
							$resul_wl[$k]['quantity_init'] = $prdt['quantity'] + $bght_prdt['actual_qty'];
							$resul_wl[$k]['quantity_left'] = $prdt['quantity'];
							$cpt = 1;
						}
					}
					if ($cpt != 1)
					{
						$resul_wl[$k]['quantity_init'] = $prdt['quantity'];
						$resul_wl[$k]['quantity_left'] = $prdt['quantity'];
					}
					$set_init = Db::getInstance()->Execute('
						UPDATE `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
						SET wp.`quantity_init`='.$resul_wl[$k]['quantity_init'].',
							wp.`quantity_left`='.$resul_wl[$k]['quantity_left'].'
						WHERE wp.`id_product`='.$prdt['id_product'].'
						AND wp.`id_product_attribute`='.$prdt['id_product_attribute'].'
						AND wp.`id_wishlist` = '.$id_wishlist.'
					');
				}
			}/*end offoreach ($list as $nb_list)*/
		}
	}

	/**
	 * Check if there is content in parameter table
	 * @return nb or rows getRow : concerns old versions (1.1) with no defined getValue method
	 */
	public static function checkTableContentOld($table)
	{
		return (Db::getInstance()->getRow('
		SELECT COUNT(*)
		FROM `'.$table.'`
		'));
	}
	/**
	 * Check if there is content in parameter table
	 * @return array with the nb of rows | getValue
	 */
	public static function checkTableContent($table)
	{
		return (Db::getInstance()->getValue('
		SELECT COUNT(*)
		FROM `'.$table.'`
		'));
	}

		/*Update quantity fields in wishlist_product  table*/

	public static function update_qty_wlproduct($id_wishlist, $id_product, $id_product_attribute)
	{
		/*idem $res : quantities put in carts by product (grouped by product)*/
		$res_group_by = Db::getInstance()->ExecuteS('
			SELECT wp.`id_wishlist`,wp.`id_wishlist_product`,wp.`id_product`,wp.`id_product_attribute`, SUM(cp.`quantity`) AS cart_quantity, SUM(wpc.`quantity`) AS wish_quantity, MAX(wp.`quantity_rel`) as quantity_rel, MAX(wp.`quantity`) as quantity, MAX(wp.`quantity_left`) as quantity_left, MAX(wp.`quantity_init`) as quantity_init
			FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
			JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
			LEFT JOIN `'._DB_PREFIX_.'cart` c ON (c.`id_cart` = wpc.`id_cart`)
			LEFT JOIN `'._DB_PREFIX_.'cart_product` cp ON (cp.`id_cart` = wpc.`id_cart` AND cp.`id_product` = wp.`id_product` AND cp.`id_product_attribute` = wp.`id_product_attribute`)
			WHERE wp.`id_wishlist`='.(int)$id_wishlist.' AND wp.`id_product`='.(int)$id_product.' AND wp.`id_product_attribute`='.(int)$id_product_attribute.'
			GROUP BY wp.`id_wishlist`,wp.`id_wishlist_product`,wp.`id_product`,wp.`id_product_attribute` '
		);

			foreach ($res_group_by as $product_detail)
			{
				$ordered_only = self::getProductBoughtQty_actual($id_wishlist, $product_detail['id_product'], $product_detail['id_product_attribute']);
				if (empty($ordered_only))
					$ordered_only[0]['actual_qty'] = 0;

				$product_detail['quantity_rel'] = $product_detail['quantity_init'] - $product_detail['cart_quantity'];
				$product_detail['quantity'] = $product_detail['quantity_rel'] >= 0 ? $product_detail['quantity_rel'] : 0;

				$product_detail['quantity_left_rel'] = $product_detail['quantity_init'] - $ordered_only[0]['actual_qty'];

				$product_detail['quantity_left'] = $product_detail['quantity_left_rel'] >= 0 ? $product_detail['quantity_left_rel'] : 0;

				Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'`
					SET `quantity_rel`= '.(int)$product_detail['quantity_rel'].',
					`quantity`= '.(int)$product_detail['quantity'].',
					`quantity_left_rel`= '.(int)$product_detail['quantity_left_rel'].',
					`quantity_left`= '.(int)$product_detail['quantity_left'].'
					WHERE `id_wishlist_product`='.(int)$product_detail['id_wishlist_product'].' AND `id_wishlist`='.(int)$id_wishlist
					);
			}
	}

	/**
	 * is a module active
	 * @return array
	 */
	public static function is_module_active($moduleName)
	{
			if (!empty($moduleName))
		{
			$moduleNameList = array();
			$results = Db::getInstance()->getRow('SELECT `id_module`, `active`, `name` FROM `'._DB_PREFIX_.'module` WHERE `name` = \''.$moduleName.'\'');
/*array $results  'id_module' => string '9' | 'active' => string '0' if not active  | 'name' => string 'blockcart'*/
			if (!$results)
				$moduleNameList['active'] = 0;
			else
				$moduleNameList['active'] = isset($results['active']) ? $results['active'] : 0;
		}
	return $moduleNameList;
	}

	/**
	 * Check whether  ID wishlist by Token is published
	 *
	 * @return array Results
	 */
	public static function getPublishedByToken($token)
	{
		if (!Validate::isMessage($token))
			die (Tools::displayError());
		return (Db::getInstance()->getRow('
		SELECT w.`published`,w.`id_wishlist`
		  FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w
		INNER JOIN `'._DB_PREFIX_.'customer` c ON c.`id_customer` = w.`id_customer`
		WHERE w.`token` = \''.pSQL($token).'\''));
	}

	/**
	 * Get list by Customer ID
	 * @return array Results
	 */
	public static function getByIdList($id_list)
	{
		if (Shop::getContextShopID())
			$shop_restriction = 'AND id_shop = '.(int)Shop::getContextShopID();
		elseif (Shop::getContextShopGroupID())
			$shop_restriction = 'AND id_shop_group = '.(int)Shop::getContextShopGroupID();
		else
			$shop_restriction = '';

		if (!Validate::isUnsignedId($id_list))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT w.`id_wishlist`, w.`token`
		FROM `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w
		WHERE `id_wishlist` = '.(int)$id_list.'
		'.$shop_restriction.'
		ORDER BY w.`name` ASC'));
	}

	/**
	* check wether the id cart is also the id cart of a wishlist cart
	* @return boolean
	*/
	public static function isCartInWishlist($id_cart)
	{
		$result = Db::getInstance()->getRow('
		SELECT wpc.`id_cart`
			FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		WHERE `id_cart` = '.(int)$id_cart.'
		GROUP BY wpc.`id_cart`
		');
		return $result;
	}
	/**
	* One cart must be dedicated to ONE list only.
	* check wether the id cart is dedicated to the current list
	* @return boolean
	*/
	public static function getWishlistByCartId($id_cart, $currentListId)
	{
		$result = Db::getInstance()->ExecuteS('
			SELECT wp.`id_wishlist`, w.`name`, w.`token`
			FROM `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp
			JOIN `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
			JOIN `'._DB_PREFIX_.'wishlist'.self::SUFFIX.'` w ON (wp.`id_wishlist` = w.`id_wishlist`)
			WHERE wpc.`id_cart`='.(int)$id_cart.' AND wp.`id_wishlist` !='.(int)$currentListId.'
			GROUP BY wp.`id_wishlist`
		');
		return $result;
	}
	/**
	 * Return  cart product by ID Cart : quantity in product_cart
	 *
	 * @return Array results
	 */
	public static function getListCartProduct($id_cart)
	{
		if (!Validate::isUnsignedId($id_cart))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT wp.`id_wishlist_product`, wp.`id_product`, wp.`id_product_attribute`, wpc.`quantity`
		FROM `'._DB_PREFIX_.'wishlist_product_cart'.self::SUFFIX.'` wpc
		JOIN `'._DB_PREFIX_.'wishlist_product'.self::SUFFIX.'` wp ON (wp.`id_wishlist_product` = wpc.`id_wishlist_product`)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.`id_cart` = wpc.`id_cart`)
		WHERE wpc.`id_cart` = '.(int)$id_cart.'
		GROUP BY wp.`id_product`, wp.`id_product_attribute` '
		));
	}

	/**
	 * Return  price (if combination or not, take into account price display)
	 *
	 * @return float
	 */
	public static function getPriceAw($ip, $ipa = null)
	{
		$obj = new Product((int)$ip, false, (int)Context::getContext()->language->id);
		if (!Validate::isLoadedObject($obj))
			return false;
		$rc = new ReflectionClass('Product');
		if ($rc->hasMethod('initPricesComputation'))
		{
			Product::initPricesComputation();
			$priceDisplay = Product::getTaxCalculationMethod();
		}
		$price = 0;
		if ($obj->hasAttributes() == null)
			$ipa = null;

/*+  `price_display_method` 1 tax ecl, 0 tax incl -> $priceDisplay*/
		if ($rc->hasMethod('initPricesComputation'))
		{
			if (!$priceDisplay || $priceDisplay == 2)
				$price = $obj->getPrice(true, $ipa, 2);
			else
				$price = $obj->getPrice(false, $ipa, 2);
		}
		else /*old version (1.2)*/
			$price = $obj->getPrice(true, (int)$ipa);

		return (float)$price;
	}

	/* check if module is enable in the current device
		return true if yes, else false
	*/
	public static function getModuleDeviceEnable($moduleToCheck)
	{
		if (version_compare(_PS_VERSION_, '1.6', '<'))
		return true;

		$context = Context::getContext();
		$sql = new DbQuery();
		$sql->select('m.`id_module`, m.`name` as module, ms.`enable_device`');
		$sql->from('module', 'm');
		$sql->join(Shop::addSqlAssociation('module', 'm', true, 'module_shop.enable_device & '.(int)Context::getContext()->getDevice()));
			$sql->innerJoin('module_shop', 'ms', 'ms.`id_module` = m.`id_module`');
		$sql->where('m.name = \''.$moduleToCheck.'\'');

/* empty : blockart disabled for all devices or current device; */
/* if isset $result[0] :
	mobile enable if $result[0]['enable_device'] & Context::DEVICE_MOBILE =! 0
	tablet enable if $result[0]['enable_device'] & Context::DEVICE_TABLET =! 0
	computer enable if $result[0]['enable_device'] & Context::DEVICE_COMPUTER =! 0
*/
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		$enable_on_current = 0;
		if (!empty($result))
		switch ($context->getDevice())
		{
			case '4':
				$enable_on_current = $result[0]['enable_device'] & Context::DEVICE_MOBILE;
				break;
			case '2':
				$enable_on_current = $result[0]['enable_device'] & Context::DEVICE_TABLET;
				break;
			case '1':
				$enable_on_current = $result[0]['enable_device'] & Context::DEVICE_COMPUTER;
				break;

			default:
			$enable_on_current = 0;
				break;
		}
		return $enable_on_current !== 0 ? true : false;
	}
}