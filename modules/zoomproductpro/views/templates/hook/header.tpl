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
    var zpp_static_token = '{$ZPP_STATIC_TOKEN|escape:'htmlall':'UTF-8'}';
    var zoomproductpro_img = '{$zoomproductpro_img|escape:'htmlall':'UTF-8'}';
    var zoomproductpro_dir = '{$zoomproductpro_dir|escape:'htmlall':'UTF-8'}';
    var zpp_identifier_image = '{$CONFIGS.ZPP_IDENTIFIER_IMAGE|escape:'htmlall':'UTF-8'}';
    var zpp_width = parseInt({$CONFIGS.ZPP_WIDTH|intval});
    var zpp_height = parseInt({$CONFIGS.ZPP_HEIGHT|intval});
    var zpp_view_gallery = Boolean({$CONFIGS.ZPP_VIEW_GALLERY|intval});
    var zpp_full_screen = Boolean({$CONFIGS.ZPP_FULL_SCREEN|intval});
    var zpp_watermark = Boolean({$CONFIGS.ZPP_WATERMARK|intval});
    var zpp_postfix_name_images = '{$CONFIGS.ZPP_POSTFIX_NAME_IMAGES|escape:'htmlall':'UTF-8'}';
    var zpp_zoom_assets_dir = '{$zoomproductpro_zoom_assets|escape:'htmlall':'UTF-8'}';
    var url_thumbnails = new Array();

    var zpp_enable_360 = parseInt({$CONFIGS.ZPP_ENABLE_360|intval});
{*    var zoomproductpro_360 = '{$zoomproductpro_360|escape:'htmlall':'UTF-8'}';*}
    var extension_images_360 =  '{$CONFIGS.ZPP_IMAGES_EXTENSION|escape:'htmlall':'UTF-8'}';
    var actions_controller_url = '{$ACTIONS_CONTROLLER_URL|escape:'htmlall':'UTF-8'}';

    {assign var='name_image' value='medium'|cat:$CONFIGS.ZPP_POSTFIX_NAME_IMAGES}
    {foreach from=$ids_thumbnails item='item' name='f_ids_thumbnails'}
        url_thumbnails.push("{$link->getImageLink($link_rewrite_product, $item, $name_image)|escape:'htmlall':'UTF-8'}");
    {/foreach}

    var MsgZPP = {ldelim}
        msg_close: "{l s='Close' mod='zoomproductpro' js=1}",
        msg_reset: "{l s='Reset' mod='zoomproductpro' js=1}",
        msg_loading : "{l s='Loading images zoom' mod='zoomproductpro' js=1}...",
        enable_360 : "{l s='Active 360' mod='zoomproductpro' js=1}",
        disable_360 : "{l s='Desactive 360' mod='zoomproductpro' js=1}"
    {rdelim};

</script>

{if $CONFIGS.ZPP_FULL_SCREEN}
<style>
    {literal}
    #fancybox-wrap.fancybox-wrap_zoomproductpro{ left: 0!important; top: 0!important; padding: 20px!important;}
    {/literal}
</style>
{/if}