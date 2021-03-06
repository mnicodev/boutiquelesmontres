<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ManufacturerController extends ManufacturerControllerCore
{
   

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
    	
    	
        parent::initContent();
        

        if (Validate::isLoadedObject($this->manufacturer) && $this->manufacturer->active && $this->manufacturer->isAssociatedToShop()) {
            $this->productSort();
            $this->assignOne();
            $this->setTemplate(_PS_THEME_DIR_.'manufacturer.tpl');
        } else {
            $this->assignAll();
            $this->setTemplate(_PS_THEME_DIR_.'manufacturer-list.tpl');
        }
    }

    /**
     * Assign template vars if displaying one manufacturer
     */
    protected function assignOne()
    {
    		$p=current(Manufacturer::getProducts($this->manufacturer->id,2,1,1));
    	 $id_cat=(int)$p["id_category_default"];

				while($id_cat!=2) {
					$cat=new Category($id_cat);
					if((int)$cat->id_parent)
						$id_cat=(int)$cat->id_parent;
				}

				
				
        $this->manufacturer->description = Tools::nl2br(trim($this->manufacturer->description));
        $nbProducts = $this->manufacturer->getProducts($this->manufacturer->id, null, null, null, $this->orderBy, $this->orderWay, true);
        $this->pagination((int)$nbProducts);
$this->n=100;
		$products = $this->manufacturer->getProducts($this->manufacturer->id, $this->context->language->id, (int)$this->p, (int)$this->n, $this->orderBy, $this->orderWay);
        $this->addColorsToProductList($products);

		 foreach($products as $product) {
		 	$type=$product["features"][0]["value"];
		 	break;
		 	
		 }
		 
		 foreach($products as $key=>$val) {
				$m=new Manufacturer($val["id_manufacturer"]);
				$products[$key]["marque"]=$m->name;
			}

        $this->context->smarty->assign(array(
            'nb_products' => $nbProducts,
            'type' => strtolower($type),
            'products' => $products,
            'cat'=>$cat,
            'path' => ($this->manufacturer->active ? Tools::safeOutput($this->manufacturer->name) : ''),
            'manufacturer' => $this->manufacturer,
            'comparator_max_item' => Configuration::get('PS_COMPARATOR_MAX_ITEM'),
            'body_classes' => array($this->php_self.'-'.$this->manufacturer->id, $this->php_self.'-'.$this->manufacturer->link_rewrite)
        ));
    }

    /**
     * Assign template vars if displaying the manufacturer list
     */
    protected function assignAll()
    {
        if (Configuration::get('PS_DISPLAY_SUPPLIERS')) {
            $data = Manufacturer::getManufacturers(false, $this->context->language->id, true, false, false, false);
            $nbProducts = count($data);
			$this->pagination($nbProducts);
			$this->n=100;
			$data = Manufacturer::getManufacturers(true, $this->context->language->id, true, $this->p, $this->n, false);
            foreach ($data as &$item) {
				$m=new Manufacturer($item["id_manufacturer"]);
				preg_match("/^https:\/\//",$m->meta_description[1],$rs);
				if($rs[0]) $item["redirect"]=$m->meta_description[1];
				else {
					preg_match("/^http:\/\//",$m->meta_description[1],$rs);
					if($rs[0]) $item["redirect"]=$m->meta_description[1];

				}
            	
                $item['image'] = (!file_exists(_PS_MANU_IMG_DIR_.$item['id_manufacturer'].'-'.ImageType::getFormatedName('medium').'.jpg')) ? $this->context->language->iso_code.'-default' : $item['id_manufacturer'];
            }

            $this->context->smarty->assign(array(
                'pages_nb' => ceil($nbProducts / (int)$this->n),
                'nbManufacturers' => $nbProducts,
                'mediumSize' => Image::getSize(ImageType::getFormatedName('medium')),
                'manufacturers' => $data,
                'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY')
            ));
        } else {
            $this->context->smarty->assign('nbManufacturers', 0);
        }
    }

    /**
     * Get instance of current manufacturer
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }
}
