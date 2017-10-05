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
 * @version   37
 */

$(function () {
    $('#pts_register_product')
        .on('click', '.register-button', function() {
            $('#pts_register_product .form-register').removeClass('hidden').slideDown();
            $('#pts_register_product .form-validate').slideUp();
        })
        .on('click', '.validate-button', function() {
            $('#pts_register_product .form-register').slideUp();
            $('#pts_register_product .form-validate').removeClass('hidden').slideDown();
        })
        .on('click', '#btn_send_register', function() {
            $('#frm_register_product').submit();
        })
        .on('click', '#btn_validate_license', function() {
            $('#frm_register_product').submit();
        });

    $(document).on('input change', '.tooltip-title-value', function(event) {
        $(event.currentTarget).attr('title', $(event.currentTarget).val());
    });
    $('.tooltip-title-value').trigger('change');

    //remove focus for elements
    $('.pts a, .pts .btn, .pts input:checkbox').click(function (e) {
        $(e.currentTarget).blur();
    });

    //change language of helper languages templates
    $('.pts').on('click', '.change-language', $.changeLanguage);
});

jQuery.extend(
        jQuery.expr[ ":" ],
        {reallyvisible: function (a) {
                return !(jQuery(a).css('display') == 'none');
            }}
);

jQuery.extend({
    isEmpty: function () {
        var count = 0;
        $.each(arguments, function (i, data) {
            if (typeof data !== typeof undefined && data !== null && data !== '' && parseInt(data) !== 0) {
                count++;
            }
            else
                return false
        });
        return (arguments).length == count ? false : true;
    },
    isEmail: function (val) {
        var regExp = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i;
        return regExp.exec(val);
    },
    isJson: function (str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    },
    htmlEncode: function (value) {
        return $('<div/>').text(value).html();
    },
    htmlDecode: function (value) {
        return $('<div/>').html(value).text();
    },
    tinyMCEInit: function (element) {
        $().ready(function () {
            $(element).tinymce({
                // General options
                theme: "advanced",
                plugins: "safari,pagebreak,style,layer,table,advimage,advlink,inlinepopups,media,searchreplace,contextmenu,paste,directionality,fullscreen",
                // Theme options
                theme_advanced_buttons1: "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
                theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
                theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
                theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,pagebreak",
                theme_advanced_toolbar_location: "top",
                theme_advanced_toolbar_align: "left",
                theme_advanced_statusbar_location: "bottom",
                theme_advanced_resizing: true,
                content_css: tiny_content_css,
                document_base_url: tiny_doc_base_url,
                template_external_list_url: "lists/template_list.js",
                external_link_list_url: "lists/link_list.js",
                external_image_list_url: "lists/image_list.js",
                media_external_list_url: "lists/media_list.js",
                elements: "nourlconvert",
                convert_urls: false,
                language: tiny_lang,
                width: "600"
            });
        });
    },
    getList: function (table, action, parameters, callback) {
        var $table = table;
        if (typeof table === 'string') {
            $table = $('#'+table);
        } else {
            table = $table.attr('id');
        }

        var div_loading = '#' + table + ' tbody';
        var data = {
            action: action,
            page: 1
        };
        $.extend(data, parameters);
        var _json = {
            data: data,
            beforeSend: function (request) {
                $('#' + table).addClass('table-loading');
            },
            success: function (json) {
                $('#' + table + ' thead').empty();
                $('#' + table + ' tbody').empty();
                $('#' + table).removeClass('table-loading');

                var $tr_head = $('<tr/>');
                if (typeof json.masive !== typeof undefined && typeof json.masive.actions !== typeof undefined && Object.keys(json.masive.actions).length > 0) {
                    var $chk_masive = $('<input/>').attr({type: 'checkbox'}).addClass('masive-check-all');
                    $chk_masive.on('change', function(e) {
                        $('#' + table + ' tbody').find('.td_masive input').prop('checked', $(e.currentTarget).prop('checked'));
                    });
                    var $th_masive = $('<th>').addClass('text-center').appendTo($tr_head);
                    $chk_masive.appendTo($th_masive);
                }
                $.each(json.headers, function (field, name_field) {
                    var $th_head = $('<th/>');
                    if (field === 'actions' || (typeof json.status !== typeof undefined && json.status instanceof Array
                            && (json.status.indexOf(field) !== -1) || field in json.status)) {
                        $th_head.addClass('text-center');
                    }
                    $th_head.html(name_field).appendTo($tr_head);
                });
                $tr_head.appendTo($('#' + table + ' thead'));

                $.each(json.content, function (i, data) {
                    var $tr = $('<tr/>');
                    if (typeof json.prefix_row !== typeof undefined && !$.isEmpty(json.prefix_row) &&
                            typeof data.id !== typeof undefined && !$.isEmpty(data.id)) {
                        $tr.attr('id', json.prefix_row + '_' + data.id);
                    }

                    if (typeof json.color !== typeof undefined && typeof data[json.color.by] !== typeof undefined) {
                        $tr.addClass(json.color.colors[data[json.color.by]]);
                    }

                    //masive actions
                    if (typeof json.masive !== typeof undefined && typeof json.masive.actions !== typeof undefined && Object.keys(json.masive.actions).length > 0) {
                        var $td_masive = $('<td/>').addClass('td_masive text-center');
                        $('<input/>').attr({type: 'checkbox'}).addClass('masive-check').appendTo($td_masive)
                                .data({tr: $tr, data: data});
                        $td_masive.appendTo($tr);
                    }

                    $.each(json.headers, function (field, name_field) {
                        var $td = $('<td/>');
                        if (field == 'actions') {
                            $td.addClass('actions text-center');
                            $.each(json.actions, function (action, attributes) {
                                if (typeof attributes.condition !== typeof undefined) {
                                    if (data[attributes.condition.field] != attributes.condition.comparator) {
                                        return true;
                                    }
                                }

                                var action_class = class_name;
                                if (typeof attributes.action_class !== typeof undefined) {
                                    action_class = attributes.action_class;
                                }
                                if (typeof attributes['class'] !== typeof undefined) {
                                    var $span = $('<span/>').addClass(attributes['class']);
                                    $span.html('&nbsp;' + attributes.title);
                                    if (typeof attributes.icon !== typeof undefined) {
                                        var $icon = $('<i/>').addClass(attributes.icon);
                                        $icon.prependTo($span);
                                    }
                                    if (typeof attributes.tooltip !== typeof undefined) {
                                        $span.tooltip({title: attributes.tooltip});
                                    }
                                    $span.click(function (event) {
                                        var objects = action_class.split(".");
                                        var object_class = null;

                                        for (var i = 0, len = objects.length; i < len; i++) {
                                            if (object_class === null) {
                                                object_class = window[objects[i]];
                                            } else {
                                                object_class = object_class[objects[i]];
                                            }
                                        }

                                        $(event.currentTarget).data('content', data);
                                        event.data = data;
                                        object_class[action](event, data);
                                    });
                                    $span.appendTo($td);
                                } else if (typeof attributes.img !== typeof undefined) {
                                    var _img = $('<img/>').attr({
                                        src: module_img + 'icon/' + attributes.img,
                                        title: attributes.title,
                                        alt: attributes.title
                                    });
                                    _img.click(function (event) {
                                        window[action_class][action](event, data);
                                    });
                                    _img.appendTo($td);
                                } else {
                                    $td.html(data[field]);
                                }
                            });
                        } else if (typeof json.status !== typeof undefined && json.status instanceof Array
                                && (json.status.indexOf(field) !== -1) || field in json.status) {
                            var $span_status = $('<span/>');
                            var $icon_status = $('<i/>');
                            var label_class;
                            var icon_class;
                            var status = parseInt(data[field]);
                            if (status) {
                                label_class = 'success';
                                icon_class = 'check';
                            } else {
                                label_class = 'danger';
                                icon_class = 'times';
                            }
                            $icon_status.addClass('nohover fa-pts fa-pts-' + icon_class);
                            $span_status.addClass('label-status label label-' + label_class);

                            $icon_status.appendTo($span_status);
                            $span_status.appendTo($td);
                            if (field in json.status && json.status[field] instanceof Object
                                    && typeof json.status[field].action !== typeof undefined) {
                                $span_status.addClass('cursor-pointer');
                                if (typeof json.status[field]['class'] !== typeof undefined)
                                    $span_status.addClass(json.status[field]['class']);

                                $span_status.click(function (event) {
                                    var action_class = class_name;
                                    if (typeof json.status[field].action_class !== typeof undefined) {
                                        action_class = json.status[field].action_class;
                                    }
                                    window[action_class][json.status[field].action](event, data);
                                });
                            }

                            $td.addClass('text-center');
                        } else {
                            var text = data[field];

                            if (text instanceof Object && typeof text[id_language_default] !== typeof undefined) {
                                text = text[id_language_default];
                            }

                            if (typeof json.truncate !== typeof undefined) {
                                if (typeof json.truncate[field] !== typeof undefined) {
                                    if (!$.isEmpty(text) && text.length > json.truncate[field]) {
                                        var $_span = $('<span/>');
                                        var _text_truncate = text.substring(0, json.truncate[field]) + '...';
                                        $_span.html(_text_truncate);
                                        //tooltip
                                        $_span.attr({
                                            'data-toggle': 'tooltip',
                                            'data-placement': 'top',
                                            'data-original-title': text
                                        });
                                        $_span.tooltip();
                                        $_span.appendTo($td);
                                    } else {
                                        $td.html(text);
                                    }
                                } else {
                                    $td.html(text);
                                }
                            } else {
                                $td.html(text);
                            }

                            if (typeof json.link !== typeof undefined) {
                                if ($.inArray(field, json.link.fields) !== -1) {

                                    var url = json.link.url;

                                    if (typeof json.link.params !== typeof undefined) {
                                        var _params = new Array();
                                        $.each(json.link.params, function (p, param) {
                                            if (p === 'token') {
                                                var _param_token = p + '=' + param;
                                                _params.push(_param_token);
                                            } else {
                                                var _param = p + '=' + data[param];
                                                _params.push(_param);
                                            }
                                        });
                                        url += '?' + _params.join('&');
                                    }

                                    var $link = $('<a/>');
                                    $link.attr({
                                        href: url,
                                        target: '_blank'
                                    });

                                    if (typeof json.link.icon !== typeof undefined) {
                                        var $icon_link = $('<i/>');
                                        $icon_link.addClass(json.link.icon);
                                        $icon_link.appendTo($link);
                                    }

                                    $link.appendTo($td);
                                }
                            }
                        }
                        $td.appendTo($tr);
                    });
                    $tr.appendTo($('#' + table + ' tbody'));
                });
                if (typeof json.masive !== typeof undefined && typeof json.masive.actions !== typeof undefined && Object.keys(json.masive.actions).length > 0) {
                    if (typeof $('#' + table + ' tfoot')[0] === typeof undefined)
                        $table.append($('<tfoot/>'));

                    $('#' + table + ' tfoot').empty();
                    var $tr_foot = $('<tr/>');
                    var $td_foot = $('<td/>').addClass('text-right').appendTo($tr_foot).attr('colspan', Object.keys(json.headers).length + 1);
                    $tr_foot.appendTo($('#' + table + ' tfoot'));

                    var $btn_group_container = $('<div/>').addClass('input-group-btn');
                    var $btn_masive_actions = $('<button/>').addClass('btn btn-default dropdown-toggle').attr('data-toggle', 'dropdown');
                    var $icon_masive_actions = $('<i/>').addClass('fa-pts fa-pts-caret-down nohover');
                    $btn_masive_actions.text(json.masive.label+'\xA0');
                    $icon_masive_actions.appendTo($btn_masive_actions);

                    var $list_actions = $('<ul/>').addClass('dropdown-menu pull-right');

                    $.each(json.masive.actions, function(masive_action, params) {

                        var $li_masive_action = $('<li/>');
                        var $span_masive_action = $('<a/>').text(params.title).attr('href', '#');

                        if (typeof params.icon !== typeof undefined) {
                            $('<i>')
                                    .addClass('fa-pts '+params.icon)
                                    .css('margin-right', '5px')
                                    .prependTo($span_masive_action);
                        }

                        $span_masive_action.appendTo($li_masive_action);
                        $li_masive_action.appendTo($list_actions);

                        $span_masive_action.on('click', function(masive_event) {
                            var masive_data = [];
                            $table.find('tbody tr td.td_masive input.masive-check:checked').each(function(i, checkbox_masive) {
                                masive_data.push({
                                    index: i,
                                    tr: $(checkbox_masive).data('tr'),
                                    data: $(checkbox_masive).data('data')
                                });
                            });

                            var action_class = class_name;
                            if (typeof params.action_class !== typeof undefined)
                                action_class = params.action_class;

                            if (masive_data.length > 0)
                                window[action_class][masive_action](masive_event, masive_data);
                        });
                    });

                    $list_actions.appendTo($btn_group_container);
                    $btn_masive_actions.appendTo($btn_group_container);
                    $btn_group_container.appendTo($td_foot);

                }

                //pagination
                if (typeof json.pagination !== typeof undefined) {
                    $table.makePagination(json.pagination, action, parameters, callback);
                }

                //callback
                if(typeof callback === 'function'){
                    callback(json);
                }
            },
            div_loading: div_loading
        };

        $.makeRequest(_json);
    },
    radioHandler: function () {
        $('div.radio-group button').click(function (e) {
            var $parent = $(e.target).parent();
            $parent.find('button').removeClass('active blue');
            $(e.target).addClass('active blue');
            var _name = $parent.attr('data-toggle-name');
            var _val = $(e.target).val();
            $('input[name=' + _name + ']').val(_val);
        });
    },
    showMessage: function (message_code, message) {
        if (typeof $.growl !== 'undefined') {
            var data = {
                title: "",
                message: message,
                close: '&times;',
                duration: 10000
            };
            if (message_code === SUCCESS_CODE) {
                data.icon = 'fa-pts fa-pts-check fa-pts-2x pull-left';
                $.growl.notice(data);
            } else {
                data.icon = 'fa-pts fa-pts-times fa-pts-2x pull-left';
                $.growl.error(data);
            }
        }
    },
    makeRequest: function (params) {
        if (typeof params.data.dataType === typeof undefined)
            params.data.dataType = 'json';

        if (typeof params.data.async === typeof undefined)
            params.data.async = true;

        if (typeof params.data.token === typeof undefined)
            params.data.token = pts_static_token;

        if (typeof params.data.url_call === typeof undefined)
            params.data.url_call = actions_controller_url;

        $.each(params.data, function (i, d) {
            if (typeof d === 'boolean') {
                params.data[i] = d ? 1 : 0;
            }
        });

		params.data.navigator = navigator.userAgent;

        $.ajax({
            type: 'POST',
            url: params.data.url_call,
            async: params.data.async,
            cache: false,
            dataType: params.data.dataType,
            data: params.data,
            beforeSend: function (request) {
                $('.has-action').addClass('disabled');

                if (typeof params.beforeSend === 'function')
                    params.beforeSend();

                if (typeof params.e !== typeof undefined && typeof params.e.target !== typeof undefined) {
                    if ($(params.e.target).hasClass('spinnable')) {
                        var $span = $('<span/>');
                        $span.addClass('spinner');
                        var $i = $('<i/>');
                        $i.addClass('icon-spin icon-refresh');
                        $i.appendTo($span);
                        $span.appendTo($(params.e.target));
                    }

                    $(params.e.target).blur();
                }
            },
            success: function (data) {
                //write error log
                if (params.data.dataType == 'json' && typeof data != 'object') {
                    $.extend(true, params.data, {
                        dataType: 'html',
                        async: true,
                        action: 'writeLog',
                        error: data,
                        data_sent: JSON.stringify(params.data)
                    });
                    params.beforeSend = null;
                    params.complete = null;
                    params.success = function (data) {
                        if (typeof params.error === 'function')
                            params.error(data);
                        else {
                            alert(data);
                        }
                    };
                    $.makeRequest(params);

                    return;
                }

                if (typeof params.success === 'function')
                    params.success(data);

                if (typeof data !== typeof undefined)
                    if (typeof data.message !== typeof undefined)
                        $.showMessage(data.message_code, data.message);
            },
            complete: function (jqXHR, textStatus) {
                $('.has-action').removeClass('disabled');
                if (typeof params.complete === 'function')
                    params.complete(jqXHR, textStatus);

                //remove spinner
                if (typeof params.e !== 'undefined' && typeof params.e.target !== 'undefined') {
                    if ($(params.e.target).hasClass('spinnable'))
                        $(params.e.target).find('.spinner').remove();
                }

				if (typeof callbackExtraFunctions == 'function'){
                    callbackExtraFunctions(params.data.action);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                if (XMLHttpRequest.readyState === 4 && XMLHttpRequest.status === 403 && XMLHttpRequest.statusText === 'Forbidden') {
                    location.reload();
                    return false;
                }
				if (XMLHttpRequest.status != 0){
					//write error log
					$.extend(true, params.data, {
						dataType: 'html',
						async: true,
						action: 'writeLog',
						code_error: XMLHttpRequest.status,
						name_error: XMLHttpRequest.statusText,
						error: XMLHttpRequest.responseText,
						data_sent: JSON.stringify(params.data)
					});
					params.beforeSend = null;
					params.complete = null;
					params.success = function (data) {
						if (typeof params.error === 'function')
							params.error(data);
						else
							alert(data);
					};
					$.makeRequest(params);
				}
            }
        });
    },
    utf8_decode: function (str_data) {
        var tmp_arr = [],
                i = 0,
                ac = 0,
                c1 = 0,
                c2 = 0,
                c3 = 0,
                c4 = 0;

        str_data += '';

        while (i < str_data.length) {
            c1 = str_data.charCodeAt(i);
            if (c1 <= 191) {
                tmp_arr[ac++] = String.fromCharCode(c1);
                i++;
            } else if (c1 <= 223) {
                c2 = str_data.charCodeAt(i + 1);
                tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
                i += 2;
            } else if (c1 <= 239) {
                // http://en.wikipedia.org/wiki/UTF-8#Codepage_layout
                c2 = str_data.charCodeAt(i + 1);
                c3 = str_data.charCodeAt(i + 2);
                tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            } else {
                c2 = str_data.charCodeAt(i + 1);
                c3 = str_data.charCodeAt(i + 2);
                c4 = str_data.charCodeAt(i + 3);
                c1 = ((c1 & 7) << 18) | ((c2 & 63) << 12) | ((c3 & 63) << 6) | (c4 & 63);
                c1 -= 0x10000;
                tmp_arr[ac++] = String.fromCharCode(0xD800 | ((c1 >> 10) & 0x3FF));
                tmp_arr[ac++] = String.fromCharCode(0xDC00 | (c1 & 0x3FF));
                i += 4;
            }
        }

        return tmp_arr.join('');
    },
    utf8_encode: function (argString) {
        if (argString === null || typeof argString === 'undefined') {
            return '';
        }

        var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
        var utftext = '',
                start, end, stringl = 0;

        start = end = 0;
        stringl = string.length;
        for (var n = 0; n < stringl; n++) {
            var c1 = string.charCodeAt(n);
            var enc = null;

            if (c1 < 128) {
                end++;
            } else if (c1 > 127 && c1 < 2048) {
                enc = String.fromCharCode(
                        (c1 >> 6) | 192, (c1 & 63) | 128
                        );
            } else if ((c1 & 0xF800) != 0xD800) {
                enc = String.fromCharCode(
                        (c1 >> 12) | 224, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
                        );
            } else { // surrogate pairs
                if ((c1 & 0xFC00) != 0xD800) {
                    throw new RangeError('Unmatched trail surrogate at ' + n);
                }
                var c2 = string.charCodeAt(++n);
                if ((c2 & 0xFC00) != 0xDC00) {
                    throw new RangeError('Unmatched lead surrogate at ' + (n - 1));
                }
                c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
                enc = String.fromCharCode(
                        (c1 >> 18) | 240, ((c1 >> 12) & 63) | 128, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
                        );
            }
            if (enc !== null) {
                if (end > start) {
                    utftext += string.slice(start, end);
                }
                utftext += enc;
                start = end = n + 1;
            }
        }

        if (end > start) {
            utftext += string.slice(start, stringl);
        }

        return utftext;
    },
    isUrl: function (val) {
        var regExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
        return regExp.exec(val);
    },
    strpos: function (haystack, needle, offset) {
        // Finds position of first occurrence of a string within another
        //
        // version: 1109.2015
        // discuss at: http://phpjs.org/functions/strpos    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   improved by: Onno Marsman
        // +   bugfixed by: Daniel Esteban
        // +   improved by: Brett Zamir (http://brett-zamir.me)
        // *     example 1: strpos('Kevin van Zonneveld', 'e', 5);    // *     returns 1: 14

        var i = (haystack + '').indexOf(needle, (offset || 0));
        return i === -1 ? false : i;
    },
    changeLanguage: function (e) {
        var for_element = $(e.target).attr('for');
        $('.pts .translatable-field').addClass('hide');
        $('.pts .translatable-field.' + for_element).removeClass('hide');
    },
    ptsChangeTab: function(event) {
        if ($(event.currentTarget).hasClass('has-sub')) {
            if (!$(event.currentTarget).parent().find('div.sub-tabs').is(':visible'))
                $(event.currentTarget).parent().find('div.sub-tabs').slideDown('fast');
            else
                $(event.currentTarget).parent().find('div.sub-tabs').slideUp('fast');
        } else {
            $('.pts-menu ul li').removeClass('active');
            var $parent = $(event.currentTarget).parents('.sub-tabs');
            if (typeof $parent[0] !== typeof undefined)
                $parent.parent().addClass('active');

            var text = $(event.currentTarget).text();
            $('.pts-content-current-tab').text(text);
            $('.pts-content-current-tab i').remove();
            $(event.currentTarget).find('i').clone().prependTo($('.pts-content-current-tab'));

            $('html, body').animate({scrollTop: $('body').offset().top + 'px'}, 'fast');

            var tab = $(event.currentTarget).attr('href').split('-').pop();

            var location = window.location.href + '';
            location = location.split('#').shift();
            window.location.href = location + '#' + tab;
        }
    },
    ptsToggleMenuSmall: function(event) {
        var visible = !$('.pts-menu-xs .pts-menu-xs-container').hasClass('hidden');

        if (!visible) {
            $('.pts-menu-xs .pts-menu-xs-container').hide(function() {
                $('.pts-menu-xs .pts-menu-xs-container').removeClass('hidden').show('600');
                $('.pts-content').css({opacity: 0.4});
                $('.pts-menu-xs').css({height: '100%'});
                $('.pts-menu-xs-container').height('auto');

                if ($('.pts-menu-xs-container').outerHeight() > $(window).outerHeight()) {
                    var dif = $(window).outerHeight() - $('.pts-menu-xs').position().top - 100;
                    $('.pts-menu-xs-container').css({overflow: 'scroll'});
                    $('.pts-menu-xs-container').height(dif);
                }
            });
        } else {
            $('.pts-menu-xs .pts-menu-xs-container').show('600', function() {
                $('.pts-menu-xs .pts-menu-xs-container').addClass('hidden');
                $('.pts-content').css({opacity: 1});
                $('.pts-menu-xs').css({height: 'auto'});
                $('.pts-menu-xs-container').css({overflow: 'auto'});
            });
        }
    },
    ptsGoToMenuSmall: function(event) {
        if ($(event.currentTarget).hasClass('has-sub')) {
//            $.ptsToggleMenuSmall(event); //Comentamos esto pero se mantiene por si ocurre algun error.
        } else {
            $.ptsToggleMenuSmall(event);
        }
    },
    ptsInitTabDrop: function() {
        $('.pts-menu').on('click', 'ul li a', $.ptsChangeTab);

        var location = window.location.href + '';
        var tab = location.split('#').pop();

        if (typeof $('.pts-menu ul li a[href="#tab-'+tab+'"]')[0] === typeof undefined)
            $('.pts-menu ul li.active a').trigger('click');
        else {
            $('.pts-menu ul li.active').removeClass('active');
            $('.pts-menu ul li a[href="#tab-'+tab+'"]').trigger('click');
            $('.pts-menu ul li a[href="#tab-'+tab+'"]').parents('.sub-tabs').parent().addClass('active');
            $('.pts-menu ul li a[href="#tab-'+tab+'"]').parents('.sub-tabs').slideDown('fast');
        }

        $('.pts-menu-xs').on('click', '.belt', $.ptsToggleMenuSmall);

        //fill responsive menu
        var $menu_xs = $('.pts-menu ul').clone();
        $menu_xs.on('click', 'li a', $.ptsGoToMenuSmall);

        $('.pts-menu-xs .pts-menu-xs-container').append($menu_xs);
    },
    ptsEventToggle: function() {
        $('.pts *[data-auto-toggle]').each(function(i, element) {
            if ($(element).is(':checkbox')) {
                $(element).on('switchChange', $.ptsAutoToggle);
            }
        });
    },
    ptsAutoToggle: function(event) {
        var data_hide = $(event.currentTarget).attr('name');
        $('.pts *[data-hide="' + data_hide + '"]').toggleClass('hidden');
    },
    ptsToggleSwitchDepend: function(event) {
        var param = $.extend({}, {
            checked: true
        }, event.data);

        var name = $(event.currentTarget).attr('name');
        var checked = $(event.currentTarget).is(':checked');

        if (checked === param.checked) {
            $('.depend-' + name).removeClass('hidden');
        } else {
            $('.depend-' + name).addClass('hidden');
        }
    },
    ptsInitColorPicker: function() {
        $('.color-picker').colorpicker();
    },
    ptsInitPopOver: function() {
        $('.btn-popover').each(function(i, element) {
            var id = $(element).attr('id');
            var $content = $('#' + id + '-content');
            if (typeof $content[0] !== typeof undefined) {
                $(element).popover({
                    html: $content.hasClass('popover-html'),
                    content: $content.html(),
                    placement: function(pop,ele) {
                        if ($(window).outerWidth() < 769) {
                            return 'bottom';
                        } else {
                            if ($(element).parent().hasClass('text-left'))
                                return 'top';
                            else
                                return 'left';
                        }
                    }
                });
            }
        });

        $('.pts-label-tooltip').on('click', $.ptsToggleTooltip);
        $('.pts-label-tooltip').parent().on('mousemove', function(e) {
            window.temp_x = e.pageX + 20;
            window.temp_y = e.pageY + 10;
        });
    },
    ptsToggleTooltip: function(e) {
        // Hover over code
        if (!$(e.target).hasClass('pts-label-tooltip'))
            return;

        var title = $(e.currentTarget).text();
        $(e.currentTarget).data('tipText', title).removeAttr('title');
        var $tooltip = $('<p/>').addClass('pts-tooltip-container').text(title).appendTo('body').fadeIn('slow');
        $tooltip.css({
            position:'absolute',
            border:'1px solid #333',
            'background-color':'#161616',
            'border-radius':'5px',
            padding:'10px',
            color:'#fff',
            'font-size':'12px Arial'
        });

        var remove_function = function() {
            $(e.currentTarget).off('click');
            $(e.currentTarget).off('mousemove');
            $tooltip.remove();
            $('.pts-tooltip-container').remove();
            $(e.currentTarget).on('click', $.ptsToggleTooltip);
        };
        var move_function = function(e) {
            var mousex = e.pageX + 20; //Get X coordinates
            var mousey = e.pageY + 10; //Get Y coordinates

            if (typeof e.pageY === typeof undefined && typeof window.temp_y !== typeof undefined)
                mousey = window.temp_y;
            if (typeof e.pageX === typeof undefined && typeof window.temp_x !== typeof undefined)
                mousex = window.temp_x;

            $tooltip.css({ top: mousey, left: mousex });
        };

        $(e.currentTarget).on('click mouseout', remove_function);
        $(e.currentTarget).on('mousemove', move_function);
        $(e.currentTarget).trigger('mousemove');
    },
    getFAQs: function(){
        $.get(module_dir + 'docs/FAQs.json', function( data ) {
            if (Object.keys(data).length > 0) {
                var i = 0;

                var $div_panel_group = $('<div>').addClass('panel-group').attr('id', 'content_faqs');
                $.each(data, function(key, value) {

                    var question = value['question_'+iso_lang_backoffice_shop];
                    var answer = value['answer_'+iso_lang_backoffice_shop];

                    if (typeof question === 'undefined') {
                        question = value.question_en;
                    }
                    if (typeof answer === 'undefined') {
                        answer = value.answer_en;
                    }

                    var $div_panel = $('<div>').addClass('panel').appendTo($div_panel_group);
                    var $div_panel_heading = $('<div>').addClass('panel-heading').css({
                        'white-space': 'normal',
                        padding: '0px'
                    }).appendTo($div_panel);

                    var $h = $('<h5>').addClass('panel-title clearfix').css({'text-transform': 'none', 'font-weight': 'bold'}).appendTo($div_panel_heading);
                    var $a = $('<a>').addClass('accordion-toggle').attr('data-toggle', 'collapse').attr('data-parent', '#content_faqs').attr('href', '#collapse'+i).appendTo($h);
                    var $i = $('<i>').addClass('indicator pull-right fa-pts fa-pts-plus');

                    var $span_content_i = $('<span>').addClass('col-sm-1 pull-right').appendTo($a).append($i);
                    var $span = $('<span>').addClass('col-sm-11 pull-left').html(question).appendTo($a);

                    var $div_collapse = $('<div>').attr('id', 'collapse'+i).addClass('panel-collapse collapse').appendTo($div_panel);
                    var $div_panel_body = $('<div>').addClass('panel-body').css('padding', '8px 0px').appendTo($div_collapse).html(answer);

                    i++;
                });
                $('div.tab-content div#tab-faqs').append($div_panel_group);
            }
        });
    }
});

jQuery.fn.extend({
    truncate: function (options) {
        var defaults = {
            more: '...'
        };
        var options = $.extend(defaults, options);
        return this.each(function (num) {
            var height = parseInt($(this).css("height"));
            var width = parseInt($(this).css("width"));
            var content = $(this).html();
            while (this.scrollHeight > height) {
                content = content.replace(/\s+\S*$/, "");
                $(this).html(content + " " + options.more);
            }
        });
    },
    displayErrors: function (errors) {
        if (!$.isEmpty(errors)) {
            var html = '';

            errors = jQuery.parseJSON(errors);

            html = '<ol>';
            $.each(errors, function (i, message) {
                html += '<li>' + message + '</li>';
            });
            html += '</ol>';

            jQuery(this).append('<br/><br/>' + html);
        }
    },
    onlyCharacter: function () {
        jQuery(this).keypress(function (e) {
            var key = (document.all) ? e.keyCode : e.which;
            if (key == 8 || key == 0)
                return true;
            var regExp = /[A-Za-z\s]/;
            return regExp.test(String.fromCharCode(key));
        });

        return jQuery(this);
    },
    onlyNumber: function () {
        jQuery(this).keypress(function (e) {
            var key = (document.all) ? e.keyCode : e.which;
            if (key == 8 || key == 0)
                return true;
            var regExp = /^[0-9.]+$/;
            return regExp.test(String.fromCharCode(key));
        });

        return jQuery(this);
    },
    validName: function () {
        jQuery(this).keypress(function (e) {
            var key = (document.all) ? e.keyCode : e.which;
            if (key == 8 || key == 0)
                return true;

            var character = String.fromCharCode(key).toString();
            var regExp = /^[a-zA-Zá-úÁ-ÚÄ-Üà-ù.'\s]*$/;

            return regExp.test(character);
        });

        return jQuery(this);
    },
    validAddress: function () {
        jQuery(this).keypress(function (e) {
            var key = (document.all) ? e.keyCode : e.which;
            if (key == 8 || key == 0)
                return true;

            var character = String.fromCharCode(key).toString();
            var regExp = /^[a-zA-Zá-úÁ-ÚÄ-Üà-ù0-9#/.ºª\-\s,]*$/;

            return regExp.test(character);
        });

        return jQuery(this);
    },
    addOverlay: function () {
        return jQuery(this).addClass('overlay').fadeTo(0, .4);
    },
    delOverlay: function () {
        return jQuery(this).fadeTo(100, 1).removeClass('overlay');
    },
    //Deshabilitar boton con la opcion de enviar un texto para setearlo, dado el caso que dentro del arreglo de {Msg}
    //Existe la llave como ID, se toma la propiedad {off} y se omite el texto enviado por parametro
    disableButton: function (val) {
        if (Msg[jQuery(this).attr('id')]) {
            return jQuery(this).attr('disabled', true).find('span.ui-button-text').html(Msg[jQuery(this).attr('id')].off);
        }
        else
            return jQuery(this).attr('disabled', true).find('span.ui-button-text').html(val);
    },
    //Habilitar boton con la opcion de enviar un texto para setearlo, dado el caso que dentro del arreglo de {Msg}
    //Existe la llave como ID, se toma la propiedad {on} y se omite el texto enviado por parametro
    enableButton: function (val) {
        if (Msg[jQuery(this).attr('id')]) {
            return jQuery(this).attr('disabled', false).find('span.ui-button-text').html(Msg[jQuery(this).attr('id')].on).parent();
        }
        else
            return jQuery(this).attr('disabled', false).find('span.ui-button-text').html(val).parent();
    },
    //change label status (colors and icons)
    toggleLabelStatus: function () {
        if ($(this).hasClass('label-danger')) {
            $(this).removeClass('label-danger').addClass('label-success');
            $(this).children('i').removeClass('fa-pts-times').addClass('fa-pts-check');
        } else {
            $(this).addClass('label-danger').removeClass('label-success');
            $(this).children('i').removeClass('fa-pts-check').addClass('fa-pts-times');
        }
    },
    ptsToggleDepend: function () {
        var me = this;
        var callback_checkbox = function (event) {
            var checked = $(event.currentTarget).is(':checked');
            var data_hide = $(event.currentTarget).data('switch');
            var $element = $('.pts .pts-content [data-depend="' + data_hide + '"]');//.toggleClass('hidden');
            if (checked) // && !$element.is(':visible')) {
                $element.slideDown();
            else if (!checked) // && $element.is(':visible')) {
                $element.slideUp();
        };

        var callback_select = function (event) {
            var value = $(event.currentTarget).val();
            var name = $(event.currentTarget).attr('name');

            $('.pts .pts-content [data-depend="' + name + '"][data-depend-on]').each(function(i, item) {
                var data_depend = $(item).data('depend-on');
                if (typeof data_depend === "string") {
                    if (data_depend === value)
                        $(item).show();
                    else
                        $(item).hide();
                } else if (typeof data_depend === "object") {
                    var shown = false;
                    $.each(data_depend, function(i_d, depend) {
                        if (depend === value) {
                            shown = true;
                        }

                        if (i_d === (data_depend.length - 1)) {
                            if (shown)
                                $(item).show();
                            else
                                $(item).hide();
                        }
                    });
                }
            });
        };

        if ($(me).hasClass('switch')) {
            $(me).on('change', '.switch-input', callback_checkbox);
            $(me).find('.switch-input').trigger('change');
        } else {
            if ($(me).is('select')) {
                if (!$(me).hasClass('depend-available')) {
                    $(me).addClass('depend-available');
                    $(me).off('change', callback_select).on('change', callback_select);
                    $(me).trigger('change');
                }
            }
        }
    },
    clearTextLimit: function () {
        $(this).off('keyup');
    },
    textLimit: function (limit, callback) {
        var me = this;
        $(me).on('keyup', function(event) {
            var text= $(me).val();
            if(text.length > limit) {
                $(me).val(text.substring(0,limit));
            }
            if (typeof callback === "function")
                callback(me, event);
        });
    },
    makePagination: function(pagination, action, parameters, callback) {
        Pagination.init($(this), pagination, action, parameters, callback);
    }
});


var Pagination = {
    table: null,
    params: null,
    container: null,
    context: [],
    clone: function(obj) {
        if (null === obj || "object" !== typeof obj)
            return obj;

        var copy = obj.constructor();
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr))
                copy[attr] = obj[attr];
        }
        return copy;
    },
    init: function($table, pagination, action, parameters, callback) {
        var index_context = Pagination.context.length;
        var instance = Pagination.clone(Pagination);
        Pagination.context[index_context] = instance;

        //init
        Pagination.context[index_context].total = pagination.total;
        Pagination.context[index_context].total_pages = pagination.total_pages;
        Pagination.context[index_context].current_page = pagination.current_page;
        Pagination.context[index_context].items_per_page = pagination.items_per_page;
        Pagination.context[index_context].pages_to_show = 4;
        Pagination.context[index_context].table = $table;
        Pagination.context[index_context].params = {
            action: action,
            parameters: parameters,
            callback: callback
        };
        //badge
        var $badge = Pagination.context[index_context].table.parents('.table-container-list').find('#'+pagination.badge);
        if (pagination.hasOwnProperty('badge') && typeof $badge !== typeof undefined && typeof $badge[0] !== typeof undefined) {
            $badge.text(Pagination.context[index_context].total);
        }
        //remove previos pagination instance
        Pagination.context[index_context].table.parent().find('.paginator').remove();
        Pagination.context[index_context].container = $('<div/>').addClass('paginator col-md-12 text-center');
        //create container
        Pagination.context[index_context].table.after(Pagination.context[index_context].container);
        //create all controls
        Pagination.context[index_context].createPaginator(index_context);
    },
    registerEvents: function(index_context) {
        Pagination.context[index_context].container.find('a.end_or_next_page, a.first_or_before_page').on('click', function(e) {
            var $element = $(e.currentTarget);
            var page = $element.attr('data-page');

            if ($element.parent('li').is('.disabled')) {
                return;
            }

            if ($element.hasClass('first_or_before_page')) {
                Pagination.context[index_context].viewFirstOrBeforePage(index_context, {page: page});
            } else {
                Pagination.context[index_context].viewNextOrEndPage(index_context, {page: page});
            }
        });
    },
    getPagination: function(index_context) {
        if (Pagination.context[index_context].total_pages > 0 ) {
            var from_page = (Pagination.current_page < Pagination.context[index_context].pages_to_show) ? 1 : Pagination.context[index_context].current_page;
            var to_page = (from_page + Pagination.context[index_context].pages_to_show) - 1;
            if (parseInt(to_page) >= Pagination.context[index_context].total_pages) {
                to_page = Pagination.context[index_context].total_pages;

                var diff = (parseInt(to_page) - parseInt(from_page));
                if (diff < Pagination.context[index_context].pages_to_show) {
                    from_page = (to_page - Pagination.context[index_context].pages_to_show) + 1;
                    from_page = (from_page < 1) ? 1 : from_page;
                }
            }

            Pagination.context[index_context].createItemsPagination(index_context, {from_page: from_page, to_page: to_page, page: Pagination.context[index_context].current_page});
        }
    },
    createPaginator: function(index_context) {
        //pagination
        var $ul =  $('<ul>')
            .addClass('pagination')
            .appendTo(Pagination.context[index_context].container);

        $('<a/>')
            .append($('<li/>').addClass('fa-pts fa-pts-step-backward nohover'))
            .appendTo($('<li>').appendTo($ul))
            .addClass('first_or_before_page first_page')
            .attr('data-page', 1);

        $('<a/>')
            .append($('<li>').addClass('fa-pts fa-pts-angle-double-left nohover'))
            .appendTo($('<li>').appendTo($ul))
            .addClass('first_or_before_page before_page');

        $('<a/>')
            .addClass('end_or_next_page next_page')
            .append($('<li/>').addClass('fa-pts fa-pts-angle-double-right nohover'))
            .appendTo($('<li>').appendTo($ul));

        $('<a/>')
            .append($('<li/>').addClass('fa-pts fa-pts-step-forward nohover'))
            .appendTo($('<li>').appendTo($ul))
            .addClass('end_or_next_page end_page')
            .attr('data-page', Pagination.context[index_context].total_pages);

        if (parseInt(Pagination.context[index_context].total_pages) === 0) {
            Pagination.context[index_context].container.hide();
        } else {
            Pagination.context[index_context].container.show();
        }

        //items per page
        var $div_items_per_page = $('<div/>').addClass('pull-right pagination-pages').appendTo(Pagination.context[index_context].container);
        var show_lang = (typeof Msg !== typeof undefined && typeof Msg.show !== typeof undefined) ? Msg.show : 'Show';
        $('<span/>').text(show_lang+':').appendTo($('<div/>').addClass('pull-left').appendTo($div_items_per_page));
        var $select = $('<select/>').addClass('form-control').appendTo($('<div/>').addClass('pull-right').appendTo($div_items_per_page));

        $.each([20, 50, 100, 300, 1000], function(i, num) {
            $('<option/>').val(num).text(num).appendTo($select).prop('selected', (parseInt(Pagination.context[index_context].items_per_page) === parseInt(num) ? true : false));
        });

        $select.on('change', function(e) {
            Pagination.context[index_context].current_page = 1;
            Pagination.context[index_context].items_per_page = $(e.currentTarget).val();
            Pagination.context[index_context].getList(index_context);
            $(e.currentTarget).blur();
        });

        Pagination.context[index_context].total_pages = Math.ceil(parseInt(Pagination.context[index_context].total) / Pagination.context[index_context].items_per_page);

        //create paginator
        Pagination.context[index_context].getPagination(index_context);

        //register events
        Pagination.context[index_context].registerEvents(index_context);
    },
    viewFirstOrBeforePage: function(index_context, params) {
        var param = $.extend({}, {
            page: 1
        }, params);

        Pagination.context[index_context].current_page = param.page;
        Pagination.context[index_context].getList(index_context);
    },
    viewNextOrEndPage: function(index_context, params) {
        var param = $.extend({}, {
            page: Pagination.context[index_context].total_pages,
            get_list: true
        }, params);

        Pagination.context[index_context].current_page = param.page;

        param.page = parseInt(param.page);
        var from_page = param.page;
        var to_page = (param.page + Pagination.context[index_context].items_per_page) - 1;

        if (param.page === Pagination.context[index_context].total_pages || parseInt(to_page) >= Pagination.context[index_context].total_pages) {
            from_page = (param.page - Pagination.context[index_context].items_per_page) + 1;
            from_page = (from_page < 1) ? 1 : from_page;

            if (parseInt(to_page) >= Pagination.context[index_context].total_pages) {
                to_page = param.page ;
            }
        }

        if (param.get_list) {
            Pagination.context[index_context].getList(index_context);
        }
    },
    createItemsPagination: function(index_context, params) {
        var param = $.extend({}, {
            from_page: 1,
            to_page: 1,
            page: 1
        }, params);

        var $element_append = Pagination.context[index_context].container.find('ul.pagination');
        var $elem_content_page  = $element_append.find('li.item_pagination a[data-page="'+param.page+'"]');

        if ($elem_content_page.length > 0 ) {
            $element_append.children('li.item_pagination.active').removeClass('active');
            $elem_content_page.parent('li').addClass('active');
        } else {
            $element_append.parent().find('li.item_pagination').remove();
            var $before = Pagination.context[index_context].container.find('ul.pagination li a.before_page').parent('li');

            if (param.to_page > Pagination.context[index_context].total_pages) {
                param.to_page = Pagination.context[index_context].total_pages;
            }

            for (var i= param.from_page; i<= param.to_page; i++) {
                var $li =  $('<li>').appendTo($element_append).insertAfter($before).addClass('item_pagination');

                if (i === parseInt(param.page)) {
                    $li.addClass('active');
                }

                $('<a/>').html(i).appendTo($li).data('page', i).on('click', function(e) {
                    Pagination.context[index_context].current_page = $(e.currentTarget).data('page');
                    Pagination.context[index_context].getList(index_context);
                });
                $before = $li;
            }
        }

        var $before_page = $element_append.find('a.before_page, a.first_page').parent();
        var $next_page = $element_append.find('a.next_page, a.end_page').parent();

        var before_page = param.page - 1;
        var next_page = parseInt(param.page) + 1;

        $element_append.find('a.before_page').attr('data-page', before_page);
        $element_append.find('a.next_page').attr('data-page', next_page);

        if (next_page > Pagination.context[index_context].total_pages) {
            $next_page.addClass('disabled');
        } else {
            $next_page.removeClass('disabled');
        }

        if (before_page < 1) {
            $before_page.addClass('disabled');
        } else {
            $before_page.removeClass('disabled');
        }
    },
    getList: function(index_context) {
        Pagination.context[index_context].params.parameters.page = Pagination.context[index_context].current_page;
        Pagination.context[index_context].params.parameters.items_per_page = Pagination.context[index_context].items_per_page;
        $.getList(Pagination.context[index_context].table, Pagination.context[index_context].params.action, Pagination.context[index_context].params.parameters, Pagination.context[index_context].params.callback);
    }
};