/**
 * We offer the best and most useful modules PrestaShop and modifications for your online store.
 *
 * We are experts and professionals in PrestaShop
 *
 * @category  PrestaShop
 * @category  Module
 * @author    PresTeamShop.com <support@presteamshop.com>
 * @copyright 2011-2015 PresTeamShop
 * @license   see file: LICENSE.txt
 */

$(function () {
    AppDOC.init();
});

var AppDOC = {
    init: function () {
        AppDOC.initAffix();
        AppDOC.initTree();
    },
    initAffix: function() {
//        $('#mainAffix').affix({
//            offset: {
//                top: 100,
//                bottom: function () {
//                    return (this.bottom = $('.bs-footer').outerHeight(true));
//                }
//            }
//        });
    },
    initTree: function() {
        $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Contraer');
        $('.tree li.parent_li > span').on('click', function (e) {
            var children = $(this).parent('li.parent_li').find(' > ul > li');
            if (children.is(":visible")) {
                children.hide('fast');
                $(this).attr('title', 'Expandir').find(' > i').addClass('fa-plus-square').removeClass('fa-minus-square');
            } else {
                children.show('fast');
                $(this).attr('title', 'Contraer').find(' > i').addClass('fa-minus-square').removeClass('fa-plus-square');
            }
            e.stopPropagation();
        });
    }
};