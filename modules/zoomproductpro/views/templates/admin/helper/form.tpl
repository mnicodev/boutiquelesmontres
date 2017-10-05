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

{assign var='is_depend' value=false}
{if isset($depend)}
    {assign var=is_depend value=$depend}
{/if}
{if $option.type eq $global->type_control->select}
    <div class="col-xs-{if isset($option.tooltip)}10{else}12{/if} col-sm-{if isset($modal) and $modal}9{elseif isset($depend) and $depend}4{else}4{/if} nopadding
         {if isset($modal) and $modal}input-sm{/if} input-group-sm">
        <select autocomplete="off" class="form-control" id="{$option.prefix|escape:'htmlall':'UTF-8'}-{$option.name|escape:'htmlall':'UTF-8'}"
                name="{$option.name|escape:'htmlall':'UTF-8'}{if isset($option.multiple) and $option.multiple}[]{/if}"
                {if isset($option.multiple) and $option.multiple}multiple{/if}>
            {foreach from=$option.data key="key" item="item"}
                {if isset($option.option_value)}
                    {assign var='value' value=$item[$option.option_value]}
                {elseif isset($option.reverse_option) and $option.reverse_option}
                    {assign var='value' value=$item}
                {else}
                    {assign var='value' value=$key}
                {/if}
                {if isset($option.option_text)}
                    {assign var='text' value=$item[$option.option_text]}
                {elseif (isset($option.reverse_option) and $option.reverse_option) or (isset($option.key_as_value) and $option.key_as_value)}
                    {assign var='text' value=$key}
                {else}
                    {assign var='text' value=$item}
                {/if}
                {if isset($option.condition) and is_array($option.condition) and count($option.condition)}
                    {if $option.condition.operator eq 'neq'}
                        {if $option.condition.compare neq $item[$option.condition.value]}
                            {include file='./option.tpl' option=$option value=$value text=$text}
                        {/if}
                    {elseif $option.condition.operator eq 'eq'}
                        {if $option.condition.compare eq $item[$option.condition.value]}
                            {include file='./option.tpl' option=$option value=$value text=$text}
                        {/if}
                    {/if}
                {else}
                    {include file='./option.tpl' option=$option value=$value text=$text}
                {/if}
            {/foreach}
        </select>
    </div>
{elseif $option.type eq 'wysiwyg'}
    {include languages=$paramsBack.LANGUAGES input_name=$option.name file='./wysiwyg_text_lang.tpl' option=$option}
{elseif $option.type eq 'hidden'}
    <input type="hidden" name="{$option.name|escape:'htmlall':'UTF-8'}" id="{$option.prefix|escape:'htmlall':'UTF-8'}-{$option.name|escape:'htmlall':'UTF-8'}"
           {if isset($option.value)}value="{$option.value|escape:'htmlall':'UTF-8'}"{/if}>
{elseif $option.type eq $global->type_control->checkbox}
    <div class="col-xs-3 col-sm-2 nopadding simple-switch col-sm-push-0 pull-right-xs">
        <label class="pull-right-xs switch{if isset($depend) and $depend} switch-green{/if}">
            <input type="{$option.type|escape:'htmlall':'UTF-8'}" class="switch-input" data-switch="{$option.name|escape:'htmlall':'UTF-8'}"
                   name="{$option.name|escape:'htmlall':'UTF-8'}" id="{$option.prefix|escape:'htmlall':'UTF-8'}-{$option.name|escape:'htmlall':'UTF-8'}"
                   {if $option.check_on}checked{/if} autocomplete="off">
            <span class="switch-label" data-on="{if isset($option.label_on)}{$option.label_on|escape:'htmlall':'UTF-8'}{/if}"
                  data-off="{if isset($option.label_off)}{$option.label_off|escape:'htmlall':'UTF-8'}{/if}"></span>
            <span class="switch-handle"></span>
        </label>
    </div>
{elseif $option.type eq $global->type_control->textbox and isset($option.color) and $option.color}
    <div class="col-xs-12 col-sm-{if isset($modal) and $modal}9{else}4{/if} col-md-2 nopadding">
        <div class="input-group color-picker">
            <input autocomplete="off" type="text" class="form-control" name="{$option.name|escape:'htmlall':'UTF-8'}"
                   id="{$option.prefix|escape:'htmlall':'UTF-8'}-{$option.name|escape:'htmlall':'UTF-8'}"
            {if isset($option.placeholder)}{$option.placeholder|escape:'htmlall':'UTF-8'}{/if}
            {if isset($option.value)}value="{$option.value|escape:'htmlall':'UTF-8'}"{/if}>
        <span class="input-group-addon"><i></i></span>
    </div>
</div>
{elseif $option.type eq $global->type_control->textbox}
    <div class="col-xs-{if isset($option.tooltip) and not $is_depend}10{else}12{/if} col-sm-{if isset($modal) and $modal}9{else}4{/if} nopadding input-group-md">
        {if isset($option.multilang) and $option.multilang}
            {assign var='input_value' value=[]}
            {if isset($option.input_value)}
                {assign var='input_value' value=$option.input_value}
            {/if}
            {include languages=$paramsBack.LANGUAGES input_name={$option.name}
                file='./input_text_lang.tpl' modal=$modal input_value=$input_value}
        {else}
            <input autocomplete="off" type="text" class="form-control"
                   name="{$option.name|escape:'htmlall':'UTF-8'}" id="{$option.prefix|escape:'htmlall':'UTF-8'}-{$option.name|escape:'htmlall':'UTF-8'}"
            {if isset($option.placeholder)}{$option.placeholder|escape:'htmlall':'UTF-8'}{/if}
            {if isset($option.value)}value="{$option.value|escape:'htmlall':'UTF-8'}"{/if}>
    {/if}
</div>
{elseif $option.type eq $global->type_control->textarea}
    <div class="col-xs-{if isset($option.tooltip) and not $is_depend}10{else}12{/if} col-sm-{if isset($modal) and $modal}9{else}4{/if} nopadding input-group-md">
        {if isset($option.multilang) and $option.multilang}
            {assign var='input_value' value=[]}
            {if isset($option.input_value)}
                {assign var='input_value' value=$option.input_value}
            {/if}
            {include languages=$paramsBack.LANGUAGES input_name={$option.name}
                file='./textarea_lang.tpl' modal=$modal input_value=$input_value}
        {else}
            <textarea autocomplete="off" class="form-control" id="{$option.prefix|escape:'htmlall':'UTF-8'}-{$option.name|escape:'htmlall':'UTF-8'}"
            {if isset($option.placeholder)}{$option.placeholder|escape:'htmlall':'UTF-8'}{/if}
            name="{$option.name|escape:'htmlall':'UTF-8'}">{if isset($option.value)}{$option.value|escape:'htmlall':'UTF-8'}{/if}</textarea>
    {/if}
</div>
{/if}
{*{if isset($option.tooltip)}
<div class="col-xs-2 col-md-1">
{include file='./tooltip.tpl' option=$option center=true}
<div class="visible-xs col-xs-12">
{include file='./tooltip.tpl' option=$option}
</div>
</div>
{/if}*}
{if isset($option.depends) and is_array($option.depends) and count($option.depends)}
   <div class="col-xs-12 nopadding clear clearfix">
        {foreach from=$option.depends item='depend'}
{*            <div class="col-xs-12 nopadding-xs depend-{$option.name|escape:'htmlall':'UTF-8'} required_field {if isset($depend.class)}{$depend.class|escape:'htmlall':'UTF-8'}{/if} nopadding-xs"*}
            <div class="clearfix depend-{$option.name|escape:'htmlall':'UTF-8'} required_field {if isset($depend.class)}{$depend.class|escape:'htmlall':'UTF-8'}{/if}"
                 id="container-{$depend.name|escape:'htmlall':'UTF-8'}" data-depend="{$option.name|escape:'htmlall':'UTF-8'}"
                 data-depend-on="{if is_array($depend.hidden_on)}{$depend.hidden_on|json_encode|escape:'htmlall':'UTF-8'}{else}{$depend.hidden_on|escape:'htmlall':'UTF-8'}{/if}">
{*                <div class=">*}
{*                <div class="row form-group clearfix clear" style="margin-top: 5px;">*}
                    {if isset($depend.label) or isset($depend.tooltip)}
                        {if not isset($depend.label)}
                            {include file='./form.tpl' option=$depend depend=true}
                        {/if}
                        <div class="nopadding-xs
                             {if not isset($depend.label) and isset($depend.tooltip)}col-xs-2{else}
                                 col-xs-{if $depend.type eq $global->type_control->checkbox}9 pts-nowrap{else}12{/if} col-sm-6 col-md-5
                             {/if}
                             {if isset($depend.label)}container-depends{/if}">
                            <label class="col-xs-12 nopadding control-label{if not isset($depend.label)} text-left{/if}" title="{if isset($depend.label)}{$depend.label|escape:'htmlall':'UTF-8'}{/if}">
                            {if isset($depend.label)}{$depend.label|escape:'htmlall':'UTF-8'}{else}&nbsp;{/if}
                            {if isset($depend.tooltip)}
                                {include file='./tooltip.tpl' option=$depend}
                            {/if}
                        </label>
                    </div>
                    {if isset($depend.label)}
                        {include file='./form.tpl' option=$depend depend=true}
                    {/if}
                    {else}
                        {include file='./form.tpl' option=$depend depend=true}
                    {/if}
{*                </div>*}
            </div>
        {/foreach}
    </div>
{/if}