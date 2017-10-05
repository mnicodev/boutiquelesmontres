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

$(function() {
    AppZPP.init();
});

var AppZPP = {
    loaded_images: false,
    mobile: false,
    num_images: 1,
    time: null,
    init: function() {
        AppZPP.default_zpp_height = zpp_height;
        AppZPP.default_zpp_width = zpp_width;

        AppZPP.registerEvents();

        if ( $(window).width() <= 500 && $(window).height() <= 400 ) {
            AppZPP.mobile = true;
        }

        /* Remove attr href to thumbnails and add event hover */
        $('#thumbs_list li a').each(function(i, element) {
            if ( AppZPP.mobile ) {
                return false;
            }

            var $element = $(element);
            var url_image = $element.attr('href').replace('-thickbox' + zpp_postfix_name_images, '-large' + zpp_postfix_name_images);

            $element.unbind();
            $element.hover(function() {
                $(zpp_identifier_image).attr('src', url_image);
            }, function() {});

            $element.on('click', function() {
                return false;
            });
        });
        /* Remove attr href to thumbnails and add event hover THEME: PANDA*/
        $('#thumbs_list div.owl-item a img').each(function(i, element) {
            if ( AppZPP.mobile ) {
                return false;
            }

            var $element = $(element);
            var url_image = $element.attr('src').replace('-small' + zpp_postfix_name_images, '-large' + zpp_postfix_name_images);

            $element.unbind();
            $element.hover(function() {
                $(zpp_identifier_image).attr('src', url_image);
            }, function() {});

            $element.on('click', function() {
                return false;
            });
        });

        if ( !AppZPP.mobile ) {
            $(zpp_identifier_image).unbind();
        }

        /* Set cursor zoom to image large product */
        $(zpp_identifier_image)
                .attr(
                    'style',
                    'cursor: url("' + zoomproductpro_img + 'zoom_icon.png"), pointer; cursor : url("' + zoomproductpro_img + 'zoom_icon.cur"), pointer;'
                );

        var window_width = $(window).width();
        var window_height = $(window).height();

        if (window_width <= 360) {
            zpp_width = window_width;
            zpp_height = window_height;
        } else if ( zpp_full_screen ) {
            zpp_width = window_width - 130;
            zpp_height = window_height - 40;
        } else {
            if ( zpp_width > window_width ) {
                zpp_width = window_width - 50;
            }
            if ( zpp_height > window_height ) {
                zpp_height = window_height - 50;
            }
            if ( zpp_width > zpp_height ) {
                zpp_width = zpp_height;
            }
        }

        if (!AppZPP.mobile) {
            $(zpp_identifier_image).bind('click', function(event) {
                if ( !AppZPP.loaded_images ) {
                    if ($('#primary_block span.fancybox_zoomproductpro_loading').length > 0) {
                        return false;
                    }

                    $(zpp_identifier_image).before('<span class="fancybox_zoomproductpro_loading">' + MsgZPP.msg_loading + '</span>');
                    $(url_thumbnails).each(function(i, src) {
                        var $img = $('<img/>')
                                .attr({src: src.replace('-medium' + zpp_postfix_name_images, zpp_watermark ? '-zoomproductpro' : '')})
                                .hide()
                                .appendTo('body')
                                .one('load', function(event) {
                                    if (AppZPP.num_images == url_thumbnails.length ) {
                                        AppZPP.loaded_images = true;
                                        $('.fancybox_zoomproductpro_loading').remove();

                                        AppZPP.showZoomProductsPro();
                                    }
                                    AppZPP.num_images++;
                                });
                    });
                } else {
                    AppZPP.showZoomProductsPro();
                }
            });
        }
    },
    registerEvents: function() {
        if (zpp_enable_360 === 1) {
            $(document).on('click', '#fancybox_zoomproductpro_360', function(e) {
                var $element = $(e.currentTarget);
                var action = $element.attr('data-action');

                if (action === 'enable') {
                    $element.attr('data-action', 'disable');
                    $element.children('img').attr('src', zoomproductpro_img+'cur_disable.png');

                    /*===============================  Se ocultan los elementos del zoomproductspro ===============================*/
                    $('div.smooth_zoom_preloader, #fancybox_zoomproductpro').hide(); /* imagen y contenedor zoomproductspro*/
                    $('.fancybox_zoomproductpro_gallery_nav_control, #fancybox_zoomproductpro_gallery').hide(); /* navegadores (izquierda- derecha) zoomproductspro*/
                    $('a#fancybox_zoomproductpro_reset, a#fancybox_zoomproductpro_zoom_out, a#fancybox_zoomproductpro_zoom_in').hide(); /* botones de zoom */

                    /*===============================  Se muestran los elementos del 360 slider ===============================*/
                    $('a#nav_left_360, a#nav_right_360').removeClass('hidden'); /* navegadores de slider 360 (izquierda - derecha) */
                    $('#slider_360-reel').removeClass('hidden'); /* contenedor slider 360 */

                } else {
                    $element.attr('data-action', 'enable');
                    $element.children('img').attr('src', zoomproductpro_img+'cur_enable.png');

                    /*===============================  Se muestran los elementos del zoomproductspro ===============================*/
                    $('div.smooth_zoom_preloader, #fancybox_zoomproductpro').show(); /* imagen y contenedor zoomproductspro*/
                    $('.fancybox_zoomproductpro_gallery_nav_control, #fancybox_zoomproductpro_gallery').show(); /* navegadores (izquierda- derecha) zoomproductspro*/
                    $('a#fancybox_zoomproductpro_reset, a#fancybox_zoomproductpro_zoom_out, a#fancybox_zoomproductpro_zoom_in').show(); /* botones de zoom */

                    /*===============================  Se ocultan los elementos del 360 slider ===============================*/
                    $('a#nav_left_360, a#nav_right_360').addClass('hidden'); /* navegadores de slider 360 (izquierda - derecha) */
                    $('#slider_360-reel').addClass('hidden'); /* contenedor slider 360 */
                }
            });
        }

        AppZPP.createResize();
    },
    showZoomProductsPro: function() {
        var html = '<img id="fancybox_zoomproductpro" src="'+$(zpp_identifier_image).attr('src').replace('-large' + zpp_postfix_name_images, zpp_watermark ? '-zoomproductpro' : '')+'" />';
        $.fancybox(html, {
            autoSize: true,
            width: zpp_width,
            height: zpp_height,
            closeClick: false,
            autoResize:false,
            scrolling:'no',
            padding:0,
            afterLoad: function(){
                AppZPP.loadZoomProductsPro(html, '.fancybox-wrap', '.fancybox-inner', '0 0');
                //scroll top
                window.scrollTo(0, $('.fancybox-wrap').offset().top);

                if (!zpp_full_screen && $(window).width() > 640) {
                    $('.fancybox-skin, .fancybox-wrap, .fancybox-inner, .fancybox-outer').css({
                        'height': zpp_height,
                        'max-height': zpp_height,
                        'max-width': zpp_width
                    });
                }
            },
            afterShow: function() {
                if (zpp_enable_360 === 1) {
                    AppZPP.loadViews360('.fancybox-inner');
                }
            }
        });
    },
    loadViews360: function(fancybox_content) {
        var data = {
            action: 'verify360ProductImages',
            token: zpp_static_token,
            dataType: 'json',
            id_product: $('#center_column form input[name="id_product"][type="hidden"]').val()
        };
        var _json = {
            data: data,
            beforeSend: function() {
                AppZPP.removeResize();
            },
            success: function (data) {
                if (data.message_code === 0) {
                    var $fancybox_content = $(fancybox_content);
                    $fancybox_content.addClass('pts bootstrap');

                    /*=========================== crear boton con evento para ver 360 ===========================*/
                    var $a_view_360_button = $('<a/>')
                        .attr({
                            id:'fancybox_zoomproductpro_360',
                            'data-action': 'enable'
                        });

                    $('<img>')
                            .attr('src', zoomproductpro_img+'cur_enable.png')
                            .appendTo($a_view_360_button);

                    if ($(window).width() < 498) {
                        $a_view_360_button.css({
                            bottom: 0,
                            top: 'auto',
                            width: '100%'
                        });
                    }

                    $a_view_360_button
                            .css('border-radius', '0')
                            .appendTo($fancybox_content);

                    /*=========================== se crea 360 y se inicializa ===========================*/
                    $('<img>')
                            .addClass('reel')
                            .attr({
                                src: data.dir_images+data.image,
                                id: 'slider_360',
                                'width': '100%',
                                'height': '100%',
                                position: 'absolute'

                            })
                            .css({
                                bottom: 0,
                                left: 0,
                                margin: 'auto',
                                'max-height': '618px',
                                'max-width': '600px',
                                position: 'absolute',
                                right: 0,
                                'text-align': 'center',
                                top: 0
                            })
                            .appendTo($fancybox_content);

                    var $image = $fancybox_content.find('#slider_360');

                    $image.reel({
                        footage:     5,
                        images:      data.dir_images+'##'+extension_images_360,
                        frames: data.frames,
                        frame: 1
                    });


                    /*=============== se anade los estilos al contenedor de las imagenes 360 ===============*/
                    $('div#slider_360-reel').addClass('hidden').css({
                        width: $('.smooth_zoom_preloader').width(),
                        height: $('.smooth_zoom_preloader').height(),
                        margin: '0 auto'
                    });

                    /*=============== se crean iconos de navegacion 360 (left/right) ===============*/
                    var $a_nav_left = $('<a>')
                            .attr('id', 'nav_left_360')
                            .addClass('hidden')
                            .css({
                                left: 0,

                                cursor: 'pointer'
                            })
                            .on('click', function() {
                                $image.trigger('stepLeft');
                            });

                    $('<img/>')
                        .attr('src', zoomproductpro_img+'left_icon.png')
                        .appendTo($a_nav_left);

                    var $a_nav_right = $('<a>')
                            .attr('id', 'nav_right_360')
                            .addClass('hidden')
                            .css({
                                right: 0,
                                cursor: 'pointer'
                            })
                            .on('click', function() {
                                $image.trigger('stepRight');
                            });

                    $('<img/>')
                        .attr('src', zoomproductpro_img+'right_icon.png')
                        .appendTo($a_nav_right);

                    $fancybox_content.append($a_nav_left, $a_nav_right);


                    /* $(document).on('click', 'div.fancybox-overlay a#nav_left_360', function() {
                $('div.fancybox-overlay div#slider_360-reel img#slider_360').trigger('stepLeft');
            });

            $(document).on('click', 'div.fancybox-overlay a#nav_right_360', function() {
                $('div.fancybox-overlay div#slider_360-reel img#slider_360').trigger('stepRight');
            });
                     */
                }
            },
            complete: function() {
                AppZPP.createResize();
            }
        };
        $.makeRequest(_json);
    },
    loadZoomProductsPro: function(html, fancybox_wrap, fancybox_content, initial_position) {
        $(fancybox_wrap).addClass('fancybox-wrap');

        var $fancybox_content = $(fancybox_content);
        $fancybox_content.append(html);

        var smooth_data = {
            width: (zpp_view_gallery ? zpp_width - 10 : zpp_width),
            height: (zpp_view_gallery ? zpp_height - 10 : zpp_height),
            pan_BUTTONS_SHOW: false,
            zoom_BUTTONS_SHOW: false
        };

        if (typeof initial_position !== typeof undefined) {
            smooth_data.initial_POSITION = initial_position;
        }

        $('#fancybox_zoomproductpro').smoothZoom(smooth_data);

        /*Delete borders*/
        $('#fancybox_zoomproductpro ~ div').each(function(i){
            if(i > 0)
                $(this).css({'background-color':'none'}).remove();
            if(i == 4)return false;
        });

        /*Create close button*/
        var close_button = $('<a/>')
                .attr('id', 'fancybox_zoomproductpro_close')
                .on('click', function() {
                    $.fancybox.close();
                })
                .addClass('fancybox_zoomproductpro_button fancybox_zoomproductpro_button_large')
                .html(MsgZPP.msg_close);

        /*Create reset button*/
        var reset_button = $('<a/>')
                .attr('id', 'fancybox_zoomproductpro_reset')
                .on('click', function(){
                    $('#fancybox_zoomproductpro').smoothZoom('Reset');
                })
                .addClass('fancybox_zoomproductpro_button fancybox_zoomproductpro_button_large')
                .html(MsgZPP.msg_reset);

        /*Create zoom buttons*/
        var zoom_out_button = $('<a/>')
                .attr('id', 'fancybox_zoomproductpro_zoom_out')
                .mousedown(function(){
                    $('#fancybox_zoomproductpro').smoothZoom('zoomOut');
                })
                .addClass('fancybox_zoomproductpro_button fancybox_zoomproductpro_button_small')
                .html('&nbsp;');

        var zoom_in_button = $('<a/>')
                .attr('id', 'fancybox_zoomproductpro_zoom_in')
                .mousedown(function(){
                    $('#fancybox_zoomproductpro').smoothZoom('zoomIn');
                })
                .addClass('fancybox_zoomproductpro_button fancybox_zoomproductpro_button_small')
                .html('&nbsp;');

        $fancybox_content.append(close_button, reset_button, zoom_out_button, zoom_in_button);

        /*Create gallery*/
        if(zpp_view_gallery) {
            AppZPP.viewGallery();
        }
    },
    viewGallery: function(params) {
        var param = $.extend({}, {
            fancybox_content: '.fancybox-inner',
            errors: {}
        }, params);

        var id_product_clicked = parseInt($(zpp_identifier_image).attr('id').replace(/bigpic_/,''));
        var $fancybox_content  = $(param.fancybox_content);

        var width  = parseInt($fancybox_content.css('width').replace(/px/, ''));
        var height = parseInt($('#fancybox_zoomproductpro').parent().css('height').replace(/px/, ''));

        $(param.fancybox_content + ', ' + param.fancybox_content + ' div:lt(2)').css('width', (width + 90));
        $('#fancybox_zoomproductpro').parent().css('height', (height + 10));

        /*Get url thumbnails by AJAX*/
        var $content_ul = $('<ul/>');
        if (!isNaN(id_product_clicked)) {
            var _json = {
                data: {
                    action: 'getThumbnailsProduct',
                    id_product: 'id_product_clicked'
                },
                success: function(json) {
                    if (json.message_code === 0) {
                        $(json.data).each(function(i, src){
                            var temp_img = $('<img/>').attr({
                                src: src,
                                id: 'fancybox_zoomproductpro_gallery_item_'+i
                            });

                            $content_ul.append(temp_img);

                            if ($('img[src="'+src.replace('-medium' + zpp_postfix_name_images, '')+'"]').length == 0) {
                                $('<img/>')
                                        .attr({
                                            src: src.replace('-medium' + zpp_postfix_name_images, zpp_watermark ? '-zoomproductpro' : '')
                                        })
                                        .hide()
                                        .appendTo('body');
                            }
                        });
                    }
                },
                error:function(jqXHR, textStatus, errorThrown){
                    alert(textStatus);
                }
            };
            $.makeRequest(_json);

        } else {
            $(url_thumbnails).each(function(i, src){
                var temp_img = $('<img/>')
                    .attr({
                        src: src,
                        id: 'fancybox_zoomproductpro_gallery_item_'+i
                    });

                $content_ul.append(temp_img);
            });
        }

        /* create gallery */
        var gallery = $('<div/>')
                .attr('id', 'fancybox_zoomproductpro_gallery')
                .html($content_ul);

        $fancybox_content.append(gallery);
        var $ul_gallery = $('#fancybox_zoomproductpro_gallery ul');

        /*Nav control*/
        var $nav_control_left = $('<a/>')
                .addClass('fancybox_zoomproductpro_gallery_nav_control')
                .css({
                    left:0,
                    cursor: 'pointer'
                })
                .append(
                    $('<img/>')
                        .attr('src', zoomproductpro_img+'left_icon.png')
                )
                .on('click', function() {
                    var index = parseInt($ul_gallery.find('li img.fancybox_zoomproductpro_gallery_selected')
                                    .attr('id').replace(/fancybox_zoomproductpro_gallery_item_/, ''));

                    var next = 0;
                    if(index <= 0) {
                        next = ($ul_gallery.find('li img').length) - 1;
                    } else {
                        next = index - 1;
                    }

                    $('#fancybox_zoomproductpro_gallery_item_' + next).trigger('click');
                });

        var $nav_control_right = $('<a/>')
                .addClass('fancybox_zoomproductpro_gallery_nav_control right_zoom_control')
                .css({
                    right: 112,
                    cursor: 'pointer'
                })
                .append(
                    $('<img/>')
                        .attr('src', zoomproductpro_img+'right_icon.png')
                )
                .on('click', function() {
                    var index = parseInt($ul_gallery.find('li img.fancybox_zoomproductpro_gallery_selected')
                                    .attr('id').replace(/fancybox_zoomproductpro_gallery_item_/, ''));

                    var next = index + 1;
                    if (next > (($ul_gallery.find('li img').length) - 1)) {
                        next = 0;
                    }

                    $('#fancybox_zoomproductpro_gallery_item_' + next).trigger('click');
                });

            $fancybox_content.append($nav_control_left, $nav_control_right);
            $ul_gallery.find('img').wrap('<li>');

            /*Auto select image*/
            var autoselected_image = false;
            $.each($ul_gallery.find('li img'), function(i, element) {
                var $element = $(element);

                if ($element.attr('src').replace('-medium' + zpp_postfix_name_images, '') == $('#fancybox_zoomproductpro').attr('src').replace(/-large/, '')) {
                    $element
                            .addClass('fancybox_zoomproductpro_gallery_selected');

                    autoselected_image = true;
                    return false;
                }
            });

            if(!autoselected_image) {
                $ul_gallery.find('li img:eq(0)').addClass('fancybox_zoomproductpro_gallery_selected');
            }

            $ul_gallery.find('li img').on('click', function(e) {
                if ($(e.target).hasClass('fancybox_zoomproductpro_gallery_selected'))
                    return false;

                $ul_gallery.find('img').removeClass('fancybox_zoomproductpro_gallery_selected');
                $(e.target).addClass('fancybox_zoomproductpro_gallery_selected');
                $('#fancybox_zoomproductpro')
                        .hide()
                        .attr({
                            src: $(e.target).attr('src').replace('-medium' + zpp_postfix_name_images, zpp_watermark ? '-zoomproductpro' : '')
                        });

                $('#fancybox_zoomproductpro').smoothZoom('ChangeImg',{
                    obj:$('#fancybox_zoomproductpro')
                });
            });
    },
    createResize: function() {
        $(window).on('resize', AppZPP.resize);
    },
    removeResize: function() {
        $(window).off('resize', AppZPP.resize);
    },
    resize: function(e) {
        if (typeof $('.fancybox-wrap')[0] === typeof undefined) {
            return;
        }

        if (AppZPP.time !== null) {
            clearTimeout(AppZPP.time);
            AppZPP.time = null;
        }

        AppZPP.time = setTimeout(function() {
            var rebuild_smooth = false;
            if (zpp_width <= 640) {
                rebuild_smooth = true;
            }
            if (zpp_full_screen || $(window).width() <= 640) {
                zpp_width = $(window).width();
                zpp_height = $(window).height();
            } else {
                zpp_width = AppZPP.default_zpp_width;
                zpp_height = AppZPP.default_zpp_height;
                if ($(window).height() < zpp_height) {
                    zpp_height = $(window).height();
                }
            }
            if (typeof $.fancybox.update !== typeof undefined) {
                $.fancybox.update();
            } else if (typeof $.fancybox.resize !== typeof undefined) {
                $.fancybox.resize();
            }

            if (!zpp_full_screen && $(window).width() > 640) {
                $('.fancybox-skin, .fancybox-wrap, .fancybox-inner, .fancybox-outer').css({
                    'height': zpp_height,
                    'max-height': zpp_height,
                    'max-width': zpp_width
                });
            }

            if ($(window).width() <= 640 || rebuild_smooth) {
                $('#fancybox_zoomproductpro').smoothZoom('destroy');
                var html = '<img id="fancybox_zoomproductpro" src="'+$(zpp_identifier_image).attr('src').replace('-large' + zpp_postfix_name_images, zpp_watermark ? '-zoomproductpro' : '')+'" />';

                AppZPP.loadZoomProductsPro(html, '.fancybox-wrap', '.fancybox-inner');

                AppZPP.showZoomProductsPro();
            } else {
                $('#fancybox_zoomproductpro').smoothZoom('resize', e);
            }

            clearTimeout(AppZPP.time);
            AppZPP.time = null;
        }, 300);
    }
};