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

$(document).ready(function () {
    // hide #back-top first
    $("#back-top").hide();

    // fade in #back-top
    $(function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('#back-top').fadeIn();
            } else {
                $('#back-top').fadeOut();
            }
        });

        // scroll body to 0px on click
        $('#back-top a').click(function () {
            $('body,html').animate({
                scrollTop: 0
            }, 400);
            return false;
        });
    });

    // slide-down-box-------------------------------
    var idArray = [],
            click_scroll = false;
    $("#nav a").each(function (index) {
        var attr_a = $(this).attr("href");
        idArray[index] = $(this).attr("href");
    });
    $('ul.list li a').prepend('<i class="fa fa-angle-right"></i>');
    $('li:first-child', "#nav").addClass('first');
    $('li:last-child', "#nav").addClass('last');
    $("#affect_all").toggle(
            function () {
                $("#nav>li").not(".act_item").addClass("open_item").find("dd").stop(true).slideDown(200);
                $(this).find('.expand').hide();
                $(this).find('.close').show();
            },
            function () {
                $("#nav>li").not(".act_item").removeClass("open_item").find("dd").stop(true).slideUp(200);
                $(this).find('.expand').show();
                $(this).find('.close').hide();
            }
    );
    $(".slide-down dt i").click(function () {
        $(this).parents("li").toggleClass("open_item").find('dd').slideToggle(300);
        return false;
    });
    $(".slide-down a").click(function () {
        click_scroll = true;
        $(window).scrollTo($(this).attr("href"), 800, function () {
            click_scroll = false;
            change_menu_item();
        });
        return false;
    });
    $(window).scroll(change_menu_item).trigger("scroll");
    function change_menu_item() {
        if (!click_scroll) {
            $(".current").removeClass("current");
            $(".act_item").removeClass("act_item");
            $(".open_item").removeClass("open_item");
            for (var i = 0, lenghtArray = idArray.length; i < lenghtArray; i++) {
                if ($(idArray[i]).offset().top - $(window).scrollTop() <= (($(window).height() / 2) - 100) && $(idArray[i]).offset().top - $(window).scrollTop() >= 0 || $(window).scrollTop() + (($(window).height() / 2) - 100) > $(idArray[i]).offset().top && $(idArray[i]).offset().top + $(idArray[i]).height() > $(window).scrollTop() + (($(window).height() / 2))) {
                    $("a[href=" + idArray[i] + "]").parent("li").addClass("current");
                    $("a[href=" + idArray[i] + "]").parents("li").addClass("act_item open_item");
                }
            }
            $(".act_item").find("dd").slideDown(200);
            $("li", '#nav').not(".act_item").find("dd").stop().slideUp(200);
        }
    }
});

!function ($) {
    $(function () {
        var $window = $(window)
        // side bar
        $('#nav_container').affix({
            offset: {
                top: 40
            }
        });
    });
}(window.jQuery);