{*
* We offer the best and most useful modules PrestaShop and modifications for your online store.
*
* We are experts and professionals in PrestaShop
*
* @category  PrestaShop
* @category  Module
* @author    PresTeamShop.com <support@presteamshop.com>
* @copyright 2011-2016 PresTeamShop
* @license   see file: LICENSE.txt
*}

<script type="text/javascript">
    var pts_static_token = "{$paramsBack.ZPP_STATIC_TOKEN|escape:'htmlall':'UTF-8'}";
    var module_dir = "{$paramsBack.MODULE_DIR|escape:'htmlall':'UTF-8'}";
    var static_token = "{$paramsBack.STATIC_TOKEN|escape:'htmlall':'UTF-8'}";
    var class_name = "App{$paramsBack.MODULE_PREFIX|escape:'htmlall':'UTF-8'}";
    //status codes
    var ERROR_CODE = {$paramsBack.ERROR_CODE|intval};
    var SUCCESS_CODE = {$paramsBack.SUCCESS_CODE|intval};

    var iso_lang_backoffice_shop = '{$paramsBack.iso_lang_backoffice_shop|escape:'htmlall':'UTF-8'}';
    var ADDONS = {$paramsBack.ADDONS|intval};
    var exist_FAQS_json = {$paramsBack.exist_FAQS_json|intval};

    var actions_controller_url = '{$paramsBack.ACTIONS_CONTROLLER_URL|escape:'quotes':'UTF-8'}';
</script>
{foreach from=$paramsBack.JS_FILES item="file"}
    <script type="text/javascript" src="{$file|escape:'htmlall':'UTF-8'}"></script>
{/foreach}
{foreach from=$paramsBack.CSS_FILES item="file"}
    <link type="text/css" rel="stylesheet" href="{$file|escape:'htmlall':'UTF-8'}"/>
{/foreach}
