var mce_init = false;
function init_mce() {
    mce_init = true;
    tinyMCE.init({
        mode: "textareas",
        theme: "advanced",
        language: "ru",
        editor_selector: "mcefull",

        plugins: "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
        content_css: prefix + "../extra/mce_main.css",
        file_browser_callback: "tinyBrowser",

        document_base_url: "/manage/",
        relative_urls: false,
        apply_source_formatting: true,
        remove_script_host: true,

        theme_advanced_buttons1: "save,newdocument,|,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,mybutton,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
        theme_advanced_toolbar_location: "bottom",
        theme_advanced_toolbar_align: "left",
        theme_advanced_statusbar_location: "bottom",
        theme_advanced_resizing: true,

        table_styles: L['huge-table'] +'(' + L['width'] + ')=table_break_words',

        extended_valid_elements: "object[width|height|param|embed],param[name|value],embed[src|type|width|height|allowscriptaccess|allowfullscreen],li[class|rel|id],div[class|rel|id|style],nobr", setup: function (ed) {
            ed.addButton('mybutton', {
                title: L['insert-gallery'],
                image: prefix + 'modules/map/img/dnld.png',
                onclick: function () {
                    ed.focus();
                    ed.selection.setContent('{-page_gallery-}');
                }
            });
        }
    });
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function createCookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else var expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}

function tiny_mce() {
    var checkbox = $('.toggle_mce').iphoneStyle({
        checkedLabel: L['on'],
        uncheckedLabel: L['off'],
        onChange: function (checkbox, state) {
            if (state) {
                createCookie('mce_on', 1, 300);
                if (!mce_init) {
                    init_mce();
                } else {
                    tinymce.execCommand('mceToggleEditor', false, 'page_content');
                    tinymce.execCommand('mceToggleEditor', false, 'product_content');
                }
            } else {
                createCookie('mce_on', 0, 300);
                tinymce.execCommand('mceToggleEditor', false, 'page_content');
                tinymce.execCommand('mceToggleEditor', false, 'product_content');
            }
        }
    });

    if (readCookie('mce_on') == 1 || readCookie('mce_on') == null) {
        init_mce();
    }
}

$(document).ready(function () {

    $("#tree").Tree();

    $("#remove-search-info").click(function () {
        window.location = $(this).data('url');
    });

    /*$('.export-supplier-order').click(function () {
        var order_id = $(this).data('id');

        $.ajax({
            url: prefix + module + '/ajax/?act=export-supplier-order',
            dataType: "json",

            data: '&order_id=' + order_id,
            type: 'POST',
            success: function (msg) {
                if (msg['error']) {
                    alert(msg['message']);
                } else {
                    alert('Успешно выгружено');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });

        return false;
    });*/

    /*$('.export_product').click(function () {
        var goods_id = $(this).attr('data');

        $.ajax({
            url: prefix + module + '/ajax/',
            type: 'POST',
            data: 'act=export_product&goods_id=' + goods_id,
            success: function (msg) {
                if (msg['error']) {
                    alert(msg['message']);
                } else {
                    alert(msg['message']);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });
        return false;
    });*/

});

function add_cat(_this, id) {
    $("#market_category").dialog("open");
    $('#market_id').val(id);
    return false;
}

Array.prototype.clean = function(deleteValue) {
    this.sort();

    for (var i = 0; i < this.length; i++) {
        if (this[i] == '' || this[i] == undefined || this[i] == null || this[i] == 0 || (deleteValue && this[i] == deleteValue) || this[i] == this[i - 1]) {
            this.splice(i, 1);
            i--;
        }
    }
    return this;
};

/*
function checkbox_select(el, val, revers, childs) {

    var req = val.split('-');

    if ((el.checked == false && revers) || (el.checked == true && !revers)) {
        if (childs) {
            var req1 = childs.split('-');
            req = req.concat(req1);
        }
        req.push(el.value)
    } else {
        if (childs) {
            var req1 = childs.split('-');
            //req = req.concat(req1);
            for (var i = 0; i < req1.length; i++) {
                req.clean(req1[i]);
            }
        }
        req.clean(el.value);
    }

    if (req.length > 1)
        req.clean('all');

    req.clean();
    var url = req.join('-');

    window.location = prefix + module + '/' + url
}*/

function add_related(_this, product_id) {
    $.ajax({
        url: prefix + module + '/ajax/' + arrequest()[2] + '?act=add-related',
        data: 'product_id=' + product_id,
        type: 'POST',
        success: function (msg) {
            if (msg['error']) {
                alert(msg['message']);
            } else {
                click_tab_hash();
            }
        }
    });
}

function add_similar(_this, product_id) {
    $.ajax({
        url: prefix + module + '/ajax/' + arrequest()[2] + '?act=add-similar',
        data: 'product_id=' + product_id,
        type: 'POST',
        success: function (msg) {
            if (msg['error']) {
                alert(msg['message']);
            } else {
                click_tab_hash();
            }
        }
    });
}

if ($("#file-uploader")[0]) {
    //var pid = $("#cur_product").val();
    var pid = arrequest()[2];
    var uploader = new qq.FileUploader({
        // Pass the HTML element here
        element: document.getElementById('file-uploader'),
        maxConnections: 500,
        allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
        action: prefix + module + '/ajax/',
        params: {
            act: 'upload_picture_for_goods',
            product: pid
        },
        onSubmit: function () {
            uploader.setParams({
                act: 'upload_picture_for_goods',
                watermark: jQuery('#product_watermark').is(':checked') ? true : false,
                oist: jQuery('#one-image-secret_title').is(':checked') ? true : false,
                product: pid
            });
        },
        onComplete: function (id, fileName, responseJSON) {
            if (responseJSON.success == true) {
                //$("#goods_images").html(responseJSON.filename);
                document.getElementById('goods_images').innerHTML += '<p><img class="img-polaroid" width="50px" title="" ' +
                    'src="' + siteprefix + 'shop/images/' + pid + '/' + responseJSON.filename + '" /> ' +
                    '<input class="span4 " placeholder="title" value="" name="images_title[' + responseJSON.img_id + ']" /> ' +
                    '<input class="span2" onkeydown="return isNumberKey(event)" placeholder="' + L['priority'] + '" name="image_prio[' + responseJSON.img_id + ']" value="" />' +
                    ' <input type="checkbox" name="images_del[' + responseJSON.img_id + ']" value="' + responseJSON.filename + '" /> ' + L['delete'] + '</p>';
                //$("#picture").val(fileName).change();
                //$("#current_picture").attr('src', siteprefix+'images/'+$("#sel_gallery").val()+'/'+fileName);
            }
        }
    });
}

var tips = $(".validateTips");


$(function () {

    tiny_mce();

    $("#market_category").dialog({
        autoOpen: false,
        show: {
            effect: "blind",
            duration: 1000
        },
        hide: {
            effect: "explode",
            duration: 1000
        },
        buttons: {
            "Создать": function () {
                var bValid = true;

                bValid = bValid && checkLength(jQuery('#new_market_category'), "названия", 3, 200);

                if (bValid) {

                    $.ajax({
                        url: prefix + module + '/ajax/',
                        data: 'act=new_market_category&name=' + $('#new_market_category').val() + '&market_id=' + $('#market_id').val(),
                        success: function (msg) {
                            if (msg['error']) {
                                alert(msg['message']);
                            } else {
                                $('#market-category-' + $('#market_id').val()).prepend(
                                    $("<option></option>")
                                        .attr("value", msg)
                                        .text($('#new_market_category').val())
                                );
                            }
                        }
                    });
                    $(this).dialog("close");
                }
            },
            'Отменить': function () {
                $(this).dialog("close");
            }
        },
        open: function () {
            $("#market_category").keypress(function (e) {
                if (e.keyCode == $.ui.keyCode.ENTER) {
                    $(this).parent().find("button:eq(0)").trigger("click");
                    return false;
                }
            });
        }
    });

    /*$("#dialog").dialog({
        autoOpen: false,
        show: {
            effect: "blind",
            duration: 1000
        },
        hide: {
            effect: "explode",
            duration: 1000
        },
        buttons: {
            "Создать": function () {
                var bValid = true;
                bValid = bValid && checkLength(jQuery('#new_section_name'), "названия", 3, 200);
                if (bValid) {

                    var name = $('#new_section_name').val();

                    $.ajax({
                        url: prefix + module + '/ajax/',
                        data: 'act=new_sections&id=' + arrequest()[2] + '&name=' + name,
                        success: function (msg) {
                            if (msg['error']) {
                                alert(msg['message']);
                            } else {
                                if (msg['add']) {
                                    click_tab_hash();
                                    //$('.select-section').prepend("<option value='" + msg['add'] + "'>" + name + "</option>");
                                }
                            }
                        }
                    });
                    $(this).dialog("close");
                }
            },
            'Отменить': function () {
                $(this).dialog("close");
            }
        },
        open: function () {
            $("#dialog").keypress(function (e) {
                if (e.keyCode == $.ui.keyCode.ENTER) {
                    $(this).parent().find("button:eq(0)").trigger("click");
                    return false;
                }
            });
        }
    });

    $("#opener").click(function () {
        $("#dialog").dialog("open");
    });*/

    $('#goods_add_size_group').change(function(){
        var $this = $(this),
            $control = $this.closest('.control-group'),
            group_id = $this.val(),
            $form = $this.closest('form'),
            $avail = $form.find('input[name=avail]'),
            $title = $form.find('input[name=title]'),
            $price = $form.find('input[name=price]'),
            $categories = $form.find('select[name="categories[]"]'),
            $article = $form.find('input[name=article]');
        $('option', $categories).each(function(element) {
            $(this).removeAttr('selected').prop('selected', false);
        });
        $('#group_size_select').remove();
        $title.add($article).add($price).val('');
        $avail.attr('checked', false);
        tinyMCE.get('product_content').setContent('');
        if(group_id > 0){
            $.ajax({
                url: prefix + module + '/ajax/',
                type: 'POST',
                data: 'act=goods_add_size_group&group_id='+group_id,
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var product = data.product;
                    if(data.state){
                        $title.val(product.title);
                        $article.val(product.article);
                        $price.val(product.price);
                        $control.after(data.size_select);
                        $.each(product.categories, function(k, v){
                            $categories.multiselect('select', v);
                        });
                        $avail.attr('checked', true);
                        tinyMCE.get('product_content').setContent(product.content);
                    }
                }
            });
        }
    });
});

function goods_section(_this, del) {
    $(_this).button('loading');

    jQuery.ajax({
        url: prefix + module + '/ajax/' + arrequest()[2] + '?act=goods-section',
        type: 'POST',
        data: 'name=' + $('#goods_section_name').val() + '&del=' + del,
        cache: false,
        success: function (msg) {
            if (msg['error']) {
                alert(msg['message']);
            } else {
                //alert(msg['message']);
                $('.bootbox .modal-footer .btn.btn-primary').click();
                click_tab_hash();
            }

            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function checkLength( o, n, min, max ) {
    if ( o.val().length > max || o.val().length < min ) {
        o.addClass( "ui-state-error" );
        updateTips( L['length'] +  " \"" + n + "\"  " + L['should-be-at-least'] +
            min + L["and-no-more-than"] + max + " " + L['characters'] + "." );
        return false;
    } else {
        return true;
    }
}

function updateTips( t ) {
    tips
        .text( t )
        .addClass( "ui-state-highlight" );
    setTimeout(function() {
        tips.removeClass( "ui-state-highlight", 1500 );
    }, 500 );
}

function hotline_check_price(_this) {
    $(_this).button('loading');

    jQuery.ajax({
        url: prefix + module + '/ajax/',
        type: 'POST',
        data: 'act=hotline&hotline_url=' + $('#hotline_url').val() + '&goods_id=' + arrequest()[2],
        cache: false,
        success: function (msg) {
            if (msg['error']) {
                alert(msg['message']);
            } else {
                alert(msg['message']);
                //$('#hotline_table').html(msg['table']);
                click_tab_hash();
            }

            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
    return false;
}

function show_length() {
    $('input.show-length, textarea.show-length').maxlength({
        alwaysShow: true,
        threshold: 0,
        warningClass: "label label-success",
        limitReachedClass: "label label-important",
        placement: 'top-right',
        //showEvent: 'ready'
    });
}

function update_context(_this, provider) {
    $(_this).button('loading');

    jQuery.ajax({
        url: prefix + module + '/ajax/?act=context',
        type: 'POST',
        data: 'provider=' + provider + '&goods_id=' + arrequest()[2],
        cache: false,
        success: function (msg) {
            alert(msg['message']);
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}
function start_import_goods(_this) {
    var form_data = new FormData($('#import_form')[0]);
    $(_this).button('loading');
    $('#upload_messages').empty();
    $.ajax({
        url: prefix + 'import/ajax/?act=import',
        dataType: "json",
        data: form_data,
        type: 'POST',
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
            if(data.state){

            }
            if(data.message){
                $('#upload_messages').html(data.message);
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
    return false;
}
