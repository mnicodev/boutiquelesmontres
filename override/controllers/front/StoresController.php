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

class StoresController extends StoresControllerCore
{
   
	/**
     * Display the Xml for showing the nodes in the google map
     */
    protected function displayAjax()
    {
        $stores = $this->getStores();
        $parnode = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><markers></markers>');

        foreach ($stores as $store) {
            $other = '';
            $newnode = $parnode->addChild('marker');
            $newnode->addAttribute('name', $store['name']);
            $address = $this->processStoreAddress($store);

            //$other .= $this->renderStoreWorkingHours($store);
            $newnode->addAttribute('addressNoHtml', strip_tags(str_replace('<br />', ' ', $address)));
            $newnode->addAttribute('address', $address);
            $newnode->addAttribute('other', $other);
            $newnode->addAttribute('phone', $store['phone']);
            $newnode->addAttribute('id_store', (int)$store['id_store']);
            $newnode->addAttribute('has_store_picture', file_exists(_PS_STORE_IMG_DIR_.(int)$store['id_store'].'.jpg'));
            $newnode->addAttribute('lat', (float)$store['latitude']);
            $newnode->addAttribute('lng', (float)$store['longitude']);
            if (isset($store['distance'])) {
                $newnode->addAttribute('distance', (int)$store['distance']);
            }
        }

        header('Content-type: text/xml');
        die($parnode->asXML());
    }
    

    /**
     * Assign template vars for classical stores
     */
    protected function assignStores()
    {
    	 $stores = Db::getInstance()->executeS('
		SELECT s.*, cl.name country, st.iso_code state
		FROM '._DB_PREFIX_.'store s
		'.Shop::addSqlAssociation('store', 's').'
		LEFT JOIN '._DB_PREFIX_.'country_lang cl ON (cl.id_country = s.id_country)
		LEFT JOIN '._DB_PREFIX_.'state st ON (st.id_state = s.id_state)
		WHERE s.active = 1 AND cl.id_lang = '.(int)$this->context->language->id);

        $addresses_formated = array();

        foreach ($stores as &$store) {
            $address = new Address();
            $address->country = Country::getNameById($this->context->language->id, $store['id_country']);
            $address->address1 = $store['address1'];
            $address->address2 = $store['address2'];
            $address->postcode = $store['postcode'];
            $address->city = $store['city'];

            $addresses_formated[$store['id_store']] = AddressFormat::getFormattedLayoutData($address);

            $store['has_picture'] = file_exists(_PS_STORE_IMG_DIR_.(int)$store['id_store'].'.jpg');
            if ($working_hours = $this->renderStoreWorkingHours($store)) {
                $store['working_hours'] = $working_hours;
            }
        }
        
        $this->context->smarty->assign('hasStoreIcon', file_exists(_PS_IMG_DIR_.Configuration::get('PS_STORES_ICON')));

        $distance_unit = Configuration::get('PS_DISTANCE_UNIT');
        if (!in_array($distance_unit, array('km', 'mi'))) {
            $distance_unit = 'km';
        }

        $this->context->smarty->assign(array(
            'distance_unit' => $distance_unit,
            'simplifiedStoresDiplay' => false,
           /* 'stores' => $this->getStores(),*/
            'stores' => $stores,
            'addresses_formated' => $addresses_formated,
        ));
    }

   
}
