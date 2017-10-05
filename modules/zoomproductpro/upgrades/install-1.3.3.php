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

function upgrade_module_1_3_3()
{
    Configuration::updateValue('ZPP_OVERRIDE_CSS', '');
    Configuration::updateValue('ZPP_OVERRIDE_JS', '');

    return true;
}
