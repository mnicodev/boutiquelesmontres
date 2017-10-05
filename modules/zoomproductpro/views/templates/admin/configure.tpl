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

<div id="pts_content" class="configure_zoomproductpro pts bootstrap row nopadding">
    <div class="col-xs-12 nopadding">
        <div class="{*col-xs-12*} row">
            {if isset($show_saved_message) and $show_saved_message}
                <div class="clearfix col-xs-12 col-md-6">
                    <div class="alert alert-success">
                        <b>{l s='Configuration was saved successful' mod='zoomproductpro'}</b>
                    </div>
                </div>
            {/if}
            <div class="clear row-fluid clearfix col-xs-12">
                <div class="pts-menu-xs visible-xs visible-sm pts-menu">
                    <span class="belt text-center">
                        <i class="fa-pts fa-pts-align-justify fa-pts-3x nohover"></i>
                    </span>
                    <div class="pts-menu-xs-container hidden"></div>
                </div>
                <div class="hidden-xs hidden-sm col-sm-3 col-lg-2 pts-menu">
                    <ul class="nav">
                        <li class="pts-menu-title hidden-xs hidden-sm">
                            <a>
                                {l s='Menu' mod='zoomproductpro'}
                            </a>
                        </li>
                        {foreach from=$paramsBack.HELPER_FORM.tabs item='tab' name='tabs'}
                            <li class="{if (isset($CURRENT_FORM) && $CURRENT_FORM eq $tab.href) || (not isset($CURRENT_FORM) && $smarty.foreach.tabs.first)}active{/if}">
                                <a href="#tab-{$tab.href|escape:'htmlall':'UTF-8'}" data-toggle="tab" class="{if isset($tab.sub_tab)}has-sub{/if}">
                                    <i class='fa-pts fa-pts-{if isset($tab.icon)}{$tab.icon|escape:'htmlall':'UTF-8'}{else}cogs{/if} fa-pts-1x'></i>&nbsp;{$tab.label|escape:'htmlall':'UTF-8'}
                                </a>
                                {if isset($tab.sub_tab)}
                                    <div class="sub-tabs" data-tab-parent="{$tab.href|escape:'htmlall':'UTF-8'}" style="display: none;overflow: hidden;">
                                        <ul class="nav sub-tab-list">
                                            {foreach from=$tab.sub_tab item='sub_tab'}
                                                <li class="{if (isset($CURRENT_FORM) && $CURRENT_FORM eq $sub_tab.href)}active{/if}">
                                                    <a href="#tab-{$sub_tab.href|escape:'htmlall':'UTF-8'}" data-toggle="tab">
                                                        <i class='fa-pts {if isset($sub_tab.icon)}{$sub_tab.icon|escape:'htmlall':'UTF-8'}{else}{$tab.icon|escape:'htmlall':'UTF-8'}{/if} fa-pts-1x'></i>&nbsp;{$sub_tab.label|escape:'htmlall':'UTF-8'}
                                                    </a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                {/if}
                            </li>
                        {/foreach}
                    </ul>
                </div>
                <div class="col-xs-12 col-md-10 pts-content">
                    <div class="panel pts-panel nopadding">
                        <div class="panel-heading main-head">
                            {*                            <span>{$paramsBack.DISPLAY_NAME}</span>&nbsp;v{$paramsBack.VERSION}*}
                            <span class="pull-right bold">{l s='Version' mod='zoomproductpro'}&nbsp;{$paramsBack.VERSION|escape:'htmlall':'UTF-8'}</span>
                            <span class="pts-content-current-tab">&nbsp;</span>
                        </div>
                        <div class="panel-body">
                            <!-- Tab panes -->
                            <div class="tab-content">
                                {if isset($ANOTHER_MODULES) and file_exists($paramsBack.MODULE_TPL|cat:'views/templates/admin/helper/another_modules.tpl')}
                                    <div class="tab-pane{if (isset($CURRENT_FORM) && $CURRENT_FORM eq 'another_modules')} active{/if}" id="tab-another_modules">
                                        {include file=$paramsBack.MODULE_TPL|cat:'views/templates/admin/helper/another_modules.tpl' modules=$ANOTHER_MODULES}
                                    </div>
                                {/if}
                                {if isset($ADDONS) and file_exists($paramsBack.MODULE_TPL|cat:'views/templates/admin/helper/another_modules.tpl')}
                                    <div class="tab-pane{if (isset($CURRENT_FORM) && $CURRENT_FORM eq 'addons')} active{/if}" id="tab-addons">
                                        {include file=$paramsBack.MODULE_TPL|cat:'views/templates/admin/helper/another_modules.tpl' modules=$ADDONS}
                                    </div>
                                {/if}
                                {if isset($paramsBack.HELPER_FORM)}
                                    {if isset($paramsBack.HELPER_FORM.forms) and is_array($paramsBack.HELPER_FORM.forms) and count($paramsBack.HELPER_FORM.forms)}
                                        {foreach from=$paramsBack.HELPER_FORM.forms key='key' item='form' name='forms'}
                                            {if isset($form.modal) and $form.modal}{assign var='modal' value=1}{else}{assign var='modal' value=0}{/if}
                                            <div class="tab-pane {if (isset($CURRENT_FORM) && $CURRENT_FORM eq $form.tab) || (not isset($CURRENT_FORM) && $smarty.foreach.forms.first)}active{/if}" id="tab-{$form.tab|escape:'htmlall':'UTF-8'}">
                                                <form action="{$paramsBack.ACTION_URL|escape:'htmlall':'UTF-8'}" {if isset($form.method) and $form.method neq 'ajax'}method="{$form.method|escape:'htmlall':'UTF-8'}"{/if}
                                                      class="form form-horizontal clearfix {if isset($form.class)}{$form.class|escape:'htmlall':'UTF-8'}{/if}"
                                                      {if isset($form.id)}id="{$form.id|escape:'htmlall':'UTF-8'}"{/if}
                                                      autocomplete="off">
                                                    <div class="col-xs-12 {if not $modal}col-md-8{/if} content-form pts-content nopadding-xs">
                                                        {foreach from=$form.options item='option'}
                                                            <div class="form-group clearfix clear {if isset($option.hide_on) and $option.hide_on}hidden{/if}"
                                                                 {if isset($option.data_hide)}data-hide="{$option.data_hide|escape:'htmlall':'UTF-8'}"{/if}
                                                                 id="container-{$option.name|escape:'htmlall':'UTF-8'}">
                                                                {if isset($option.label)}
                                                                    <div class="col-xs-{if $modal}3{else}{if $option.type eq $paramsBack.GLOBALS->type_control->checkbox}9 pts-nowrap{else}12{/if} col-sm-6 col-md-5 nopadding-xs{/if}"
                                                                         title="{$option.label|escape:'quotes':'UTF-8'}">
                                                                        <label class="pts-label-tooltip col-xs-12 nopadding control-label">
                                                                            {$option.label|escape:'quotes':'UTF-8'}
                                                                            {if isset($option.tooltip)}
                                                                                {include file='./helper/tooltip.tpl' option=$option}
                                                                            {/if}
                                                                        </label>
                                                                    </div>
                                                                {/if}
                                                                {include file=$paramsBack.MODULE_TPL|cat:'views/templates/admin/helper/form.tpl' option=$option global=$paramsBack.GLOBALS modal=$modal}
                                                            </div>
                                                        {/foreach}
                                                    </div>
                                                    <div class="col-xs-12 nopadding clear clearfix">
                                                        <hr />
                                                        {include file=$paramsBack.MODULE_TPL|cat:'views/templates/admin/helper/action.tpl' form=$form key=$key modal=$modal}
                                                    </div>
                                                </form>
                                                {if isset($form.list) and is_array($form.list) and count($form.list)}
                                                    {if isset($form.list.headers) and is_array($form.list.headers) and count($form.list.headers)}
                                                        {if $form.tab eq 'fields_comments'}
                                                            <div class="clearfix">
                                                                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-3 nopadding-xs">
                                                                    <div class="pull-left col-xs-12 nopadding">
                                                                        <span id="btn-manage_field_options" class="btn btn-default btn-block">
                                                                            <i class="fa-pts fa-pts-list nohover"></i>
                                                                            {l s='Manage field options' mod='zoomproductpro'}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-xs-12 col-sm-6 col-md-5 col-lg-3 nopadding-xs pull-right">
                                                                    <div class="pull-right pull-left-xs col-xs-12 nopadding">
                                                                        <span id="btn-new_register" class="btn btn-success btn-block">
                                                                            <i class="fa-pts fa-pts-edit nohover"></i>
                                                                            {l s='New custom field' mod='zoomproductpro'}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {* Modal options *}
                                                            {*                                                        <div class="modal fade" id="form_manage_field_options">*}
                                                            <form class="form form-horizontal clearfix hidden" id="form_manage_field_options">
                                                                <div class="col-xs-12 nopadding">
                                                                    <div class="row">
                                                                        <div class="col-xs-6">
                                                                            <span>{l s='Object' mod='zoomproductpro'}</span>
                                                                        </div>
                                                                        <div class="col-xs-6">
                                                                            <span>{l s='Field' mod='zoomproductpro'}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-xs-6">
                                                                            <select id="lst-manage-object" class="form-control" autocomplete="false"></select>
                                                                        </div>
                                                                        <div class="col-xs-6">
                                                                            <select id="lst-manage-field" class="form-control" disabled autocomplete="false">
                                                                                <option value="">--</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">&nbsp;</div>
                                                                    <div class="col-xs-12 nopadding">
                                                                        <div class="hidden" id="aux_clone_translatable_input">
                                                                            {include file=$paramsBack.MODULE_TPL|cat:'views/templates/admin/helper/input_text_lang.tpl'
                                                                                    languages=$paramsBack.LANGUAGES input_name='' input_value=''}
                                                                        </div>
                                                                        <div class="clearfix">
                                                                            <span class="btn btn-success pull-right disabled" id="btn-add_field_option">{l s='Add' mod='zoomproductpro'}</span>
                                                                        </div>
                                                                        <table id="table-field-options">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="{*col-xs-5 nopadding*}">{l s='Value' mod='zoomproductpro'}</th>
                                                                                    <th class="">{l s='Description' mod='zoomproductpro'}</th>
                                                                                    <th class="">{l s='Action' mod='zoomproductpro'}</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody></tbody>
                                                                        </table>
                                                                    </div>
                                                                    <div class="row">&nbsp;</div>
                                                                    <div class="row">
                                                                        <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4">
                                                                            <span id="btn-update_field_options" class="btn btn-primary btn-block disabled">
                                                                                <i class="fa-pts fa-pts-save nohover"></i>
                                                                                {l s='Save' mod='zoomproductpro'}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                            {*/Modal options *}
                                                        {/if}
                                                        <div class="row">&nbsp;</div>
                                                        <div class="table-responsive">
                                                            <div class="pts-overlay"></div>
                                                            <table class="table table-bordered" id="{$form.list.table|escape:'htmlall':'UTF-8'}">
                                                                <thead>
                                                                    <tr>
                                                                        {foreach from=$form.list.headers item='header_text' key='header'}
                                                                            <th {if $header eq 'actions'}class="col-sm-2 col-md-1 text_center"{/if}>{$header_text|escape:'htmlall':'UTF-8'}</th>
                                                                            {/foreach}
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    {/if}
                                                {/if}
                                            </div>
                                        {/foreach}
                                        {*tab translate*}
                                        <div id="tab-translate" class="tab-pane">
                                            <div class="row">
                                                <div class="col-md-12 nopadding">
                                                    <div class="form-inline">
                                                        <div class="form-group">
                                                            <span>{l s='Select language' mod='zoomproductpro'}</span>
                                                            <select class="form-control" id="lst-id_lang">
                                                                {foreach $paramsBack.LANGUAGES as $language}
                                                                    <option value="{$language.iso_code|escape:'htmlall':'UTF-8'}" {if $paramsBack.id_lang == $language.id_lang} selected="selected" {/if}>
                                                                        {$language.name|escape:'htmlall':'UTF-8'}
                                                                    </option>
                                                                {/foreach}
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <button type="button" class="btn btn-default" id="btn-save-translation" data-action ="save">
                                                                <i class="fa-pts fa-pts-floppy-o nohover"></i> {l s='Save' mod='zoomproductpro'}
                                                            </button>
                                                        </div>
                                                        <div class="form-group">
                                                            <button type="button" class="btn btn-default" id="btn-save-download-translation" data-action="save_download">
                                                                <i class="fa-pts fa-pts-download nohover"></i> {l s='Save and Download' mod='zoomproductpro'}
                                                            </button>
                                                        </div>
                                                        <div class="form-group">
                                                            <button type="button" class="btn btn-default" id="btn-share-translation">
                                                                <i class="fa-pts fa-pts-share nohover"></i> {l s='Share us your translation' mod='zoomproductpro'}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="clear clearfix">&nbsp;</div>
                                                <div class="col-md-12 nopadding">
                                                    <div class="alert alert-warning">
                                                        {l s='Some expressions use the syntax' mod='zoomproductpro'}: %s. {l s='Not replace, don\'t modified this' mod='zoomproductpro'}.
                                                    </div>
                                                </div>
                                                <div class="col-md-12 overlay-translate hidden text-center">
                                                    <img src="{$paramsBack.MODULE_IMG|escape:'htmlall':'UTF-8'}loader.gif">
                                                </div>
                                                <div class="col-md-12 nopadding">
                                                    <h4 class="title_manage_settings text-primary">
                                                        {l s='Management settings' mod='zoomproductpro'}
                                                    </h4>
                                                </div>
                                                <div class="col-md-12 nopadding" id="content_translations">
                                                    <div class="panel-group">
                                                        {foreach $paramsBack.array_label_translate as $key => $value}
                                                            {if $key !== 'translate_language'}
                                                                <div class="panel content_translations" data-file="{$key|escape:'htmlall':'UTF-8'}">
                                                                    <div class="panel-heading" style="white-space: normal; padding: 0px;">
                                                                        <h4 class="panel-title clearfix" style="text-transform: none; font-weight: bold;">
                                                                            <a class="accordion-toggle collapsed" data-toggle="collapse" href="#collapse_{$key|escape:'htmlall':'UTF-8'}">
                                                                                <span>{l s='File' mod='zoomproductpro'}: {$key|escape:'htmlall':'UTF-8'}</span>
                                                                                <span><i class="indicator pull-right fa-pts {if isset($value.empty_elements)} fa-pts-minus {else} fa-pts-plus {/if}"></i></span>
                                                                            </a>
                                                                        </h4>
                                                                    </div>
                                                                    <div id="collapse_{$key|escape:'htmlall':'UTF-8'}" class="panel-collapse collapse {if isset($value.empty_elements)} in {/if}">
                                                                        <div class="panel-body">
                                                                            <div class="content_text-translation table-responsive">
                                                                                <table class="table">
                                                                                    {foreach $value as $key_label => $label_translate}
                                                                                        {if $key_label !== 'empty_elements'}
                                                                                            <tr>
                                                                                                <td>
                                                                                                    <label for="{$key_label|escape:'htmlall':'UTF-8'}" class="control-label col-sm-12">
                                                                                                        {$label_translate['en']|escape:'htmlall':'UTF-8'}
                                                                                                    </label>
                                                                                                </td>
                                                                                                <td>=</td>
                                                                                                <td class="input_content_translation" width="60%">
                                                                                                    <input type="hidden" value="{$key|escape:'htmlall':'UTF-8'}" name="{$key_label|escape:'htmlall':'UTF-8'}">
                                                                                                    <input type="text" class="form-control {if empty($label_translate['lang_selected'])} input-error-translate {/if}" value="{$label_translate['lang_selected']|escape:'htmlall':'UTF-8'}" name="{$key_label|escape:'htmlall':'UTF-8'}">
                                                                                                </td>
                                                                                            </tr>
                                                                                        {/if}
                                                                                    {/foreach}
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="panel-footer">
                                                                        <button class="btn btn-default pull-right" name="btn-save-translation-{$key|escape:'htmlall':'UTF-8'}" type="button" data-action="save">
                                                                            <i class="process-icon-save"></i> {l s='Save' mod='zoomproductpro'}
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            {/if}
                                                        {/foreach}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {*tab translate*}
                                        <div class="tab-pane" id="tab-code_editors">
                                            <div class="col-md-12">
                                                {foreach $paramsBack.code_editors as $key => $row}
                                                    <div class="col-md-12 nopadding">
                                                        <h4>
                                                            {$key|escape:'htmlall':'UTF-8'}
                                                        </h4>
                                                        <div class="col-md-12">
                                                            {foreach $row as $value}
                                                                <form action="{$paramsBack.ACTION_URL|escape:'htmlall':'UTF-8'}" class="form-horizontal form_code_editors">
                                                                    <h4>{$value.filename|escape:'htmlall':'UTF-8'}.{if $key === 'css'}css{else}js{/if}</h4>
                                                                    <div class="form-group">
                                                                        <textarea name="txt-{$key|escape:'htmlall':'UTF-8'}-{$value.filename|escape:'htmlall':'UTF-8'}" class="linedtextarea form-control" rows="20" cols="60">{$value.content|escape:'htmlall':'UTF-8':false:true}</textarea>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-default pull-right btn-save-code-editors" data-filepath="{$value.filepath|escape:'htmlall':'UTF-8'}" data-type="{$key|escape:'htmlall':'UTF-8'}" data-name="{$value.filename|escape:'htmlall':'UTF-8'}">
                                                                            {l s='Save' mod='zoomproductpro'}
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            {/foreach}
                                                        </div>
                                                    </div>
                                                {/foreach}
                                            </div>
                                        </div>
                                        <div id="tab-faqs" class="tab-pane"></div>
                                        <div id="tab-suggestions" class="tab-pane">
                                            <div class="row">
                                                <div class="alert alert-info center-block clearfix">
                                                    <div class="col-sm-12">
                                                        <div class="col-sm-3 col-md-2">
                                                            <img src="{$paramsBack.MODULE_IMG|escape:'htmlall':'UTF-8'}star.png" class="img-responsive">
                                                        </div>
                                                        <div class="col-sm-9 col-md-10 text-left content-text-suggestions">
                                                            {l s='Share with us your suggestions, functionalities and opinions' mod='zoomproductpro'}
                                                            <a id="suggestions-opinions">{l s='Here' mod='zoomproductpro'}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="alert alert-success center-block clearfix">
                                                    <div class="col-sm-12">
                                                        <div class="col-sm-3 col-md-2">
                                                            <img src="{$paramsBack.MODULE_IMG|escape:'htmlall':'UTF-8'}support.png" class="img-responsive">
                                                        </div>
                                                        <div class="col-sm-9 col-md-10 text-left content-text-suggestions">
                                                            {l s='You have any questions or problems regarding our module' mod='zoomproductpro'}?
                                                            <a id="suggestions-contact">{l s='Contact us' mod='zoomproductpro'}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    {/if}
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file=$paramsBack.MODULE_TPL|cat:'views/templates/admin/helper/credits.tpl'}
</div>
<div class="col-xs-12">&nbsp;</div>