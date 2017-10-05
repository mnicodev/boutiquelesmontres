<?php
/**
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * We are experts and professionals in PrestaShop
 *
 * @category  PrestaShop
 * @category  Module
 * @author    PresTeamShop.com <support@presteamshop.com>
 * @copyright 2011-2016 PresTeamShop
 * @license   see file: LICENSE.txt
 */

function upgrade_module_2_0_0($object)
{
    //install tab actions for BackOffice.
    if (!Tab::getIdFromClassName('AdminActions'.$object->prefix_module)) {
        $name_tab = array();
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $name_tab[$lang['id_lang']] = 'AdminActions'.$object->prefix_module;
        }

        $tab = new Tab();
        $tab->class_name = 'AdminActions'.$object->prefix_module;
        $tab->module = $object->name;
        $tab->name = $name_tab;
        $tab->save();
    }

    if (file_exists(dirname(__FILE__).'/../log/get_logs.php')) {
        unlink(dirname(__FILE__).'/../log/get_logs.php');
    }
    if (file_exists(dirname(__FILE__).'/../actions.php')) {
        unlink(dirname(__FILE__).'/../actions.php');
    }

    return true;
}
