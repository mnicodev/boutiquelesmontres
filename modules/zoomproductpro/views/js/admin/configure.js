/**
 * We offer the best and most useful modules PrestaShop and modifications for your online
 store.
 *
 * We are experts and professionals in PrestaShop
 *
 * @category  PrestaShop
 * @category  Module
 * @author    PresTeamShop.com <support@presteamshop.com>
 * @copyright 2011-2016 PresTeamShop
 * @license   see file: LICENSE.txt
 */

var AppZPP = {
    init: function () {
        $.ptsInitPopOver();
        $.ptsInitTabDrop();
    }
};

var AppZPPGlobalTabs = {
    init: function() {
        /* tab translate */
        AppZPPGlobalTabs.div_tab_translations = $('div.tab-content div#tab-translate');
        AppZPPGlobalTabs.div_tab_translations.find('button[name*="btn-save-translation-"]').on('click', AppZPPGlobalTabs.saveTranslations);
        AppZPPGlobalTabs.div_tab_translations.on('hidden.bs.collapse', 'div.content_translations', AppZPPGlobalTabs.toggleIconCollapse);
        AppZPPGlobalTabs.div_tab_translations.on('shown.bs.collapse', 'div.content_translations', AppZPPGlobalTabs.toggleIconCollapse);

        var $div_translations_inline = AppZPPGlobalTabs.div_tab_translations.find('div.form-inline div.form-group');
        $div_translations_inline.children('button#btn-save-translation').on('click', AppZPPGlobalTabs.saveTranslations);
        $div_translations_inline.children('button#btn-save-download-translation').on('click', AppZPPGlobalTabs.saveTranslations);
        $div_translations_inline.children('button#btn-share-translation').on('click', AppZPPGlobalTabs.shareTranslation);
        $div_translations_inline.children('select#lst-id_lang').on('change', AppZPPGlobalTabs.getTranslationsByLang);

        /* tab FAQS */
        if (exist_FAQS_json === 1) {
            $.getFAQs();
        }

        var $content_faqs = $('div.tab-content div#tab-faqs div#content_faqs');
        $content_faqs.on('hidden.bs.collapse', AppZPPGlobalTabs.toggleIconCollapse);
        $content_faqs.on('shown.bs.collapse', AppZPPGlobalTabs.toggleIconCollapse);

        /* tab code editors */
        $('div.tab-content div#tab-code_editors form .btn-save-code-editors').on('click', AppZPPGlobalTabs.saveContentCodeEditors);

        /* tab suggestions  */
        var $div_suggestions = $('div.tab-content div#tab-suggestions div.content-text-suggestions');
        var $a_suggestions_contact = $div_suggestions.find('a#suggestions-contact');
        var $a_suggestions_opinions = $div_suggestions.find('a#suggestions-opinions');

        var href_contact = 'http://www.presteamshop.com/en/contact-us';
        var href_opinions = 'http://www.presteamshop.com/en/modules-prestashop/product-extra-tabs.html?ifb=1';

        if (ADDONS === 1) {
            href_contact = 'https://addons.prestashop.com/en/write-to-developper?id_product=3149';
            href_opinions = 'http://addons.prestashop.com/ratings.php';

            $('div.pts-menu > ul.nav > li > a[href="#tab-another_modules"]')
                    .attr('href', 'http://addons.prestashop.com/en/2_community?contributor=57585')
                    .removeAttr('data-toggle')
                    .attr('target', '_blank');
        }
        $a_suggestions_contact
                .attr('href', href_contact)
                .attr('target', '_blank');

        $a_suggestions_opinions
                .attr('href', href_opinions)
                .attr('target', '_blank');

        $('.switch').ptsToggleDepend();
        $('div[data-depend]').each(function (i, element) {
            var depend_parent = $(element).attr('data-depend');
            $('select[id*="-' + depend_parent + '"]').ptsToggleDepend();
        });
    },
    toggleIconCollapse: function(e) {
        $(e.target)
            .prev('.panel-heading')
            .find("i.indicator")
            .toggleClass('fa-pts-minus fa-pts-plus');
    },
    saveTranslations: function(e){
        var action = $(e.currentTarget).attr('data-action');
        var array_data = {};

        var $elements_key_translations = AppZPPGlobalTabs.div_tab_translations.find('div#content_translations div.content_translations');
        $.each($elements_key_translations, function(i, element){
            var file_translation = $(element).attr('data-file');
            array_data[file_translation] = [];
        });

        var $data_elements = $elements_key_translations.find('div.content_text-translation table tr');
        $.each($data_elements, function(i, element){
            var file_translation = $(element).find('input[type="hidden"]').val();
            var key_translation = $(element).find('input[type!="hidden"]').attr('name');
            var value_translation = $(element).find('input[type!="hidden"]').val();

            var object = {key_translation: key_translation, value_translation: value_translation};
            array_data[file_translation].push(object);

        });

        var lang = AppZPPGlobalTabs.div_tab_translations.find('select#lst-id_lang').val();
        var data = {
            action: 'saveTranslations',
            array_translation: array_data,
            lang: lang,
            dataType: 'json'
        };

        var _json = {
            data: data,
            success: function(json) {
                if (json.message_code === 0) {
                    if (action === 'save_download') {
                        var url = actions_controller_url + '&action=downloadFileTranslation&iso_code='+lang+'&token='+pts_static_token;
                        window.open(url, '_blank');
                    }
                }
            }
        };
        $.makeRequest(_json);
    },
    shareTranslation: function(){
        var data = {
            action: 'shareTranslation',
            iso_code: AppZPPGlobalTabs.div_tab_translations.find('select#lst-id_lang').val()
        };

        var _json = {
            data: data
        };
        $.makeRequest(_json);
    },
    getTranslationsByLang: function(e) {
        var iso_code = $(e.currentTarget).val();
        var $div_content_translations = AppZPPGlobalTabs.div_tab_translations.find('div#content_translations');

        var data = {
            action: 'getTranslations',
            iso_code: iso_code
        };

        var _json = {
            data: data,
            beforeSend: function(){
                $div_content_translations.addOverlay();
                AppZPPGlobalTabs.div_tab_translations
                       .find('.overlay-translate')
                       .removeClass('hidden');
            },
            success: function(data) {
                if (data.message_code === 0) {
                    if (Object.keys(data.data).length > 0) {
                        $.each(data.data, function(i, data_file) {
                            var $content_translation = $div_content_translations.find('div.content_translations[data-file="'+i+'"]');

                            if (data_file.hasOwnProperty('empty_elements')) {
                                $content_translation.find('.panel-heading .panel-title a span i.indicator')
                                        .removeClass('fa-pts-plus')
                                        .addClass('fa-pts-minus');
                                $content_translation.find('div#collapse_'+i)
                                        .addClass('in')
                                        .css('height', 'auto');
                            } else {
                                $content_translation.find('.panel-heading .panel-title a span i.indicator')
                                        .removeClass('fa-pts-minus')
                                        .addClass('fa-pts-plus');
                                $content_translation.find('div#collapse_'+i)
                                        .removeClass('in')
                                        .css('height', '0px');
                            }

                            $.each(data_file, function(key, value){
                                if (key !== 'empty_elements') {
                                    var $content_inputs = $content_translation.find('table tr td.input_content_translation');
                                    var $input = $content_inputs.find('input[name="'+key+'"][type="text"]').attr('value', value);

                                    if ($.isEmpty(value)) {
                                        $input.addClass('input-error-translate');
                                    } else {
                                        if ($input.hasClass('input-error-translate')) {
                                           $input.removeClass('input-error-translate');
                                        }
                                    }
                                }
                            });
                        });
                    } else {
                        var $content_translation = $div_content_translations.find('div.content_translations');
                        $content_translation.find('table tr td.input_content_translation input[type="text"]')
                                .attr('value', '')
                                .addClass('input-error-translate');
                        $content_translation.find('div.panel-collapse')
                                .addClass('in')
                                .css('height', 'auto');
                        $content_translation.find('.panel-heading .panel-title a span i.indicator')
                                .removeClass('fa-pts-plus')
                                .addClass('fa-pts-minus');
                    }
                }
            },
            complete: function() {
                $div_content_translations.delOverlay();
                AppZPPGlobalTabs.div_tab_translations
                        .find('.overlay-translate')
                        .addClass('hidden');
            }
        };
        $.makeRequest(_json);
    },
    saveContentCodeEditors: function(e) {
        var name = $(e.currentTarget).data('name');
        var type = $(e.currentTarget).data('type');
        var filepath = $(e.currentTarget).data('filepath');

        var content = $('div.tab-content div#tab-code_editors form textarea[name="txt-'+type+'-'+name+'"]').val();

        var data = {
            action: 'saveContentCodeEditors',
            content: encodeURIComponent(content),
            dataType: 'json',
            filepath: encodeURIComponent(filepath)
        };

        var _json = {
            data: data
        };
        $.makeRequest(_json);
    }
};

$(function () {
    AppZPP.init();
    AppZPPGlobalTabs.init();
});