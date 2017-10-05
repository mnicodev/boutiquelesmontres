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

/**
 * @since 1.5.0
 */
class BlockWishListproSearchlistModuleFrontController extends ModuleFrontController
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
		$this->assignu();
	}
	/**
	 * Assign wishlist template
	 */
	public function assignu()
	{
		$errors = array();
		$customers = array();
		$submit_type = Tools::getValue('searchsubmit');
		if (!isset($submit_type))
			exit(Tools::displayError('Invalid sumit'));
		$request = pSQL(Tools::getvalue('searchname'));
		$module = new BlockWishListpro(); //translation
		$date_format = Context::getContext()->language->date_format_lite;

		$request = str_replace('%', '', $request);
		//search for the name in database : pick up the name of id_customer which is in ps_whishlist and equal to $request
		//select only customers with wishlist
		if (!empty($request))
		{
			$customers = (Db::getInstance()->ExecuteS('
			SELECT cu.`id_customer`, cu.`firstname`, cu.`lastname`, w.`name` as list_name, w.`date_add`, w.`token`, w.`published`
			FROM `'._DB_PREFIX_.'wishlist'.BlockWishListpro::SUFFIX.'` w
			JOIN `'._DB_PREFIX_.'customer` cu
			WHERE w.`id_customer` = cu.`id_customer` AND w.`published` = 1
			and cu.`lastname` LIKE \'%'.pSQL($request).'%\'
			ORDER BY `date_add` DESC
			'));
			if (empty($customers)) $errors[] = $module->l('Sorry, no result for').' <strong>'.$request.'</strong>.<br />';
		}
		else
		{
			$errors[] = $module->l('The search request is empty. Please enter the last name of the creator of the list, or a part of it. For example \'smi\' for \'smith\'');

		}

		$this->context->smarty->assign(array(
			'request' => $request,
			'customers' => $customers,
			'modulename' => BlockWishListpro::MODULENAME,
			'date_format' => $date_format,
			'search_link' => $this->context->link->getModuleLink('blockwishlistpro', 'searchlist'),
			'view_link' => $this->context->link->getModuleLink('blockwishlistpro', 'view'),
			'themeChoice' => Configuration::get('PS_WISHLISTPRO_FO_THEME'),
			'erros_wlp' => $errors
		));
		$this->setTemplate('searchlist.tpl');
	}
}
