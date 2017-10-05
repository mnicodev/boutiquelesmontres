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

    {foreach from=$option.tooltip item='tooltip' key='type'}
        <span id="tooltip-{$type|escape:'htmlall':'UTF-8'}-{$option.name|escape:'htmlall':'UTF-8'}" type="button" 
                class="btn-popover pts-tooltip" 
                data-container="#container-{$option.name|escape:'htmlall':'UTF-8'}"
                data-toggle="button popover" {*title="{$tooltip.title|escape:'htmlall':'UTF-8'}"*}>
            {if $type eq 'information'}
                <i class='fa-pts fa-pts-question-circle nohover'></i>
            {else if $type eq 'warning'}
                <i class='fa-pts fa-pts-info-circle nohover'></i>
            {/if}
        </span>
        <div id="tooltip-{$type|escape:'htmlall':'UTF-8'}-{$option.name|escape:'htmlall':'UTF-8'}-content" 
             class="tooltip-content {if isset($option.html) and $option.html}popover-html{/if}">{$tooltip.content|escape:'htmlall':'UTF-8'}</div>
    {/foreach}