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

{foreach from=$languages item="language" name="f_languages"}
    <div class="translatable-field lang_{$language.id_lang|intval} {if $language.id_lang ne $paramsBack.DEFAULT_LENGUAGE}hide{/if}">
        <div class="col-sm-10 col-sm-offset-2 col-xs-12 col-xs-offset-0">
{*        <div class="col-sm-5">*}
            <div id="c{$input_name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}">
                <textarea class="rte autoload_rte" cols="30" rows="30" id="{$input_name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}"
                          name="{$input_name|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}">{if isset($option.input_value) and isset($option.input_value[$language.id_lang])}{$option.input_value[$language.id_lang]|escape:'htmlall':'UTF-8':false:true}{/if}</textarea>
            </div>
        </div>
        <div class="translatable-flags wysiwyg-flags col-sm-2 pull-right text-right">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                <i class="fa-pts fa-pts-flag"></i>
                {$language.iso_code|escape:'htmlall':'UTF-8'}
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                {foreach from=$languages item=flag_language}
                    <li>
                        <a class="change-language" for="lang_{$flag_language.id_lang|intval}">
                            {$flag_language.name|escape:'htmlall':'UTF-8'}
                            {if $flag_language.id_lang eq $language.id_lang}
                                <i class="fa-pts fa-pts-flag-checkered nohover"></i>
                            {/if}
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
{/foreach}