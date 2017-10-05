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

function upgrade_module_2_0_0()
{
    Configuration::updateValue('ZPP_OVERRIDE_CSS', '');
    Configuration::updateValue('ZPP_OVERRIDE_JS', '');

    Configuration::updateValue('ZPP_ENABLE_360', 0);
    Configuration::updateValue('ZPP_IMAGES_EXTENSION', '.png');

    return true;
}
