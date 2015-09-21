function gen_tree() {
    $("#tree").Tree();
}

/*function add_supplier_form(_this) {

    if ( typeof add_supplier_form.counter == 'undefined' ) {
        add_supplier_form.counter = 0;
    }

    $.ajax({
        url: prefix + module + '/ajax/?act=add-supplier-form',
        type: 'POST',
        data: '&counter=' + ++add_supplier_form.counter,
        success: function(msg) {
            if (msg['state'] == false && msg['message']) {
                alert(msg['message'])
            }
            if(msg['state'] == true && msg['html']) {
                $('#for-new-supplier-order').replaceWith(msg['html']);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}*/

function sale_order(_this, item) {
    $(_this).button('loading');
    var data = $(_this).parents('form').serializeArray();
    $.ajax({
        url: prefix + module + '/ajax/?act=sale-order',
        type: 'POST',
        dataType: "json",
        data: data,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false) {
                    if (msg['message']) {
                        alert(msg['message']);
                    }
                }else {
                    if (msg['location']) {
                        window.location = msg['location'];
                    }
                }

                $(_this).button('reset');
            }
        }
    });
}

function display_serial_product_title_and_price(_this, item_id)
{
    $(_this).parent().find('small').html('');
    $.ajax({
        url: prefix + 'messages.php?act=get-product-title-and-price',
        type: 'POST',
        dataType: "json",
        data: '&item_id=' + item_id,
        success: function(msg) {
            if (msg) {
                if (msg['msg']) {
                    $(_this).parent().find('.product-title').html(msg['msg']);
                    $('#sale_poduct_cost').val(msg['price'] ? msg['price'] : '');
                    $(_this).siblings('input[name=items]').val(msg['id']);
                }
            }
        }
    });
    return false;
}

function display_service_information(_this) {

    $.ajax({
        url: prefix + module + '/ajax/?act=service-information',
        type: 'POST',
        data: '&category_id=' + $('.typeahead-value-categories-last3').val(),
        success: function(msg) {
            if (msg['state'] == true) {
                $(_this).attr('data-placement', 'right');
                $(_this).attr('data-trigger', 'focus');
                if (msg['title']) {
                    $(_this).attr('data-original-title', msg['title']);
                }
                if (msg['content']) {
                    $(_this).attr('data-content', msg['content']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

function issue_order(_this) {
    if ($('[name="is_replacement_fund"]').prop('checked')) {
        alert('Не забудьте забрать у клиента подменный фонд');
    }
    //$(_this).button('loading');
    var status = $(_this).data('status');
    $('form#order-form select.order-status').val(status).prop('selected',true);
    $('#update-order').click();
}

function table_sorter() {
    $('#tablesorter').tablesorter({sortList: [[8,1]]});
}

$(function() {
    var datepicker = $( '.datepicker' );
    if(datepicker.length){
        datepicker.datepicker({yearRange: '1900:'});
    }

    setInterval(function() {
        if (window.location.hash == '#orders_manager') {
            click_tab_hash();
        }
    }, 1000*60*5);
});

/*function change_city( city_id ) {
    checkout_change();
}*/

/*function checkout_change(change) {

    var person      =  $('#legal-person').attr('checked') ? false : true;
    //var warr_cost   =  (goods_id && goods_id > 0) ? '&warr_cost=' + goods_id : '';
    var shipping    =  $('.checkout-shipping:checked').val();

    if (change == 1 || change == 2) {
        if (change == 1) {
            if (person == false) {
                $('#course').attr('value', 'grn-noncash').find('option[value="grn-cash"]').attr('disabled', true);
            } else {
                $('#course > option[value="grn-cash"]').attr('disabled', false).attr('selected', true);
            }
        }
        $('#course_value').val($('#course option:selected').text());
    }

    var $post       =  $('#order-form').serialize();

    $.ajax({

        url: prefix+module+'/ajax/?act=checkout-change',
        type: 'POST',
        data: $post + '&person=' + person,
        cache: false,
        success: function(msg){
            if ( msg['error'] ) {
                alert(msg['message']);
            } else {
                for (var k in msg) {
                    if (k != 'payment' && k != 'shipping') {
                        $('#' + k).html(msg[k]);
                    }
                }
                if (person == false) {
                    $('#show-corp-rate').show();
                    $('.checkout-corporation-info').find('input').val('');
                    $('.checkout-corporation-info').show();
                } else {
                    $('#show-corp-rate').hide();
                    $('.checkout-corporation-info').find('input').val('');
                    $('.checkout-corporation-info').hide();
                }
                if ( msg['shipping'] != 'courier_today' && msg['shipping'] != 'courier' && msg['shipping'] != 'express' )
                    $('#address_block').hide();
                else
                    $('#address_block').show();

                if ( msg['shipping'] != 'novaposhta_cash' && msg['shipping'] != 'novaposhta' )
                    $('#np_address_block').hide();
                else
                    $('#np_address_block').show();

                if ( $('.checkout-payment:checked').val() == 'installment' )
                    $('#installment-table').css('display', 'table');
                else
                    $('#installment-table').css('display', 'none');

                if (  msg['shipping'] != 'pickup' )
                    $('#office_block').hide();
                else
                    $('#office_block').show();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
    return false;
}*/
/*function change_region(value) {

    $.ajax({
        url: prefix+module+'/ajax/?act=change-region',
        type: 'POST',
        data: '&region='+value,
        cache: false,
        success: function(msg) {
            if ( msg['error'] ) {
                alert(msg['message']);
            } else {
                checkout_change();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            show_alert(xhr.responseText, 1);
        }
    });
    return false;
}*/

function location_menu(_this, e) {
    e.preventDefault();
    var hash = window.location.hash;
    var url = $(_this).attr('href');
    window.location.href = url+hash;
    //window.location.reload();
}

$(function() {

    $('.export_order').click(function() {
        var order_id = $(this).attr('data');

        $.ajax({
            url: prefix+module+'/ajax/?act=export_order',
            type: 'POST',
            data: '&order_id=' + order_id,
            success: function(msg) {
                if ( msg['error'] ) {
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
    });

    $('.client-bind').click(function() {
        var user_id = $(this).attr('data1');
        var order_id = $(this).attr('data2');
        var _this = this;

        $.ajax({
            url: prefix+module+'/ajax/?act=client-bind',
            dataType: "json",

            data: '&user_id='+user_id+'&order_id='+order_id,
            type:'POST',
            success: function(msg) {
                if ( msg['error'] ) {
                    alert(msg['message']);
                } else {
                    $(_this).remove();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });

        return false;
    });
});

function create_client(_this) {
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/?act=add_user',
        dataType: "json",

        data: $('#form-create-client').serialize(),
        type:'POST',
        success: function(msg) {
            if (msg) {
                if (msg['msg']) {
                    alert(msg['msg']);
                }
                if (msg['id'] && msg['name']) {
                    $('.typeahead-value-clients2').val(msg['id']);
                    $('.global-typeahead[data-input="clients2"]').val(msg['name']);
                    close_alert_box();
                }
                /*if (msg['new']) {
                    click_tab_hash();
                }*/
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function send_sms(_this) {
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/' + arrequest()[2] + '?act=send-sms',
        type: 'POST',
        data: $('#sms-form').serialize(),
        success: function(msg) {
            if (msg) {
                if (msg['msg']) {
                    alert(msg['msg']);
                }
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function order_item(_this) {
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/' + arrequest()[2] + '?act=order-item',
        type: 'POST',
        data: 'order_product_id=' + $(_this).data('order_product_id'),
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && msg['msg']) {
                    alert(msg['msg']);
                }
                 /*if (msg['close']) {
                    $('#close-order').show();
                }
                if (msg['sms']) {
                    $('#send-sms').click();
                    $(_this).button('reset');
                    return;
                }
                if (msg['location']) {
                    //window.location.href = msg['location'];
                }*/
                if (msg['state'] == true || msg['reload'] == true) {
                    click_tab_hash();
                    close_alert_box();
                }
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function update_order(_this) {
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/' + arrequest()[2] + '?act=update-order',
        type: 'POST',
        data: $('#order-form').serialize(),
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && msg['msg']) {
                    alert(msg['msg']);
                }
                if (msg['close']) {
                    $('#close-order').data('status', msg['close']);
                    $('#close-order').show();
                }
                if (msg['sms']) {
                    $('#send-sms').click();
                    $(_this).button('reset');
                    return;
                }
                if (msg['location']) {
                    //window.location.href = msg['location'];
                }
                if (msg['state'] == true || msg['reload'] == true) {
                    click_tab_hash();
                    close_alert_box();
                }
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function add_new_order(_this) {
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/?act=add-order',
        type: 'POST',
        data: $(_this).parents('form').serialize(),
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && (msg['msg'] || msg['message'])) {
                    if (msg['prompt']) {
                        alert_box(undefined, (msg['msg'] || msg['message']));
                        $('.bootbox-alert .modal-footer').prepend(msg['btn']);
                        /*var order_id = prompt(msg['msg']);
                        if (order_id != '' && order_id != null) {
                            $('input#serial-id').val(order_id);
                            $(_this).click();
                        }*/
                    } else {
                        alert((msg['msg'] || msg['message']));
                    }
                }
                if (msg['location']) {
                    window.location.href = msg['location'];
                }
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

/*function remove_comment(_this, comment_id) {
    $.ajax({
        url: prefix + module + '/ajax/?act=remove-comment',
        type: 'POST',
        data: 'comment_id=' + comment_id,
        success: function(msg) {
            if (msg) {
                if (msg['state']) {

                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}*/

function order_products(_this, product_id, order_product_id, cfm, remove) {

    //var count = $('#product_count-' + order_product_id).length ? $('#product_count-' + order_product_id).val() : 1;

    $.ajax({
        url: prefix + module + '/ajax/' + arrequest()[2] + '?act=add_product',
        type: 'POST',
        data: 'order_product_id=' + order_product_id +
            '&product_id=' + product_id +
            //'&count=' + count +
            (typeof cfm === 'undefined' ? '' : '&confirm=' + cfm) +
            (remove == 1 ? '&remove=' + 1 : ''),
        success: function(msg) {
            if (msg) {
                if (msg['confirm'] /*&& confirm(msg['confirm'])*/) {
                    alert_box('undefined', msg['confirm']);
                    //order_products(_this, product_id, order_product_id, 1);
                }
                if (msg['goods']) {
                    $('#goods-table').append(msg['goods']);
                }
                if (msg['service']) {
                    $('#service-table').append(msg['service']);
                }
                /*if (msg['psum']) {
                    $('#product_sum-' + order_product_id).html(msg['psum']);
                }*/
                if (msg['msg']) {
                    alert(msg['msg']);
                }
                if (msg['product-total']) {
                    $('#product-total').html(msg['product-total']);
                }
                if (msg['order-total']) {
                    $('#order-total').val(msg['order-total']);
                }
                if (remove == 1 && msg['state'] == true) {
                    $(_this).parents('tr').remove();
                }
            }
            if ($(_this).hasClass('global-typeahead')) {
                $(_this).val('');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

// проверяем если нема у клиентоса фио, предлагаем ввести
function check_fio(_this, item_id, response){
    if(!response.fio){
        setTimeout(function(){
            show_fio_prompt(true, false);
        }, 80);
    }
}
function show_fio_prompt(add_to_input, check_input){
    var new_fio;
    var $client_input = $('#client_fio_hidden').siblings('[name=clients-value]');
    if(check_input && $client_input.val() && $('#client_fio_hidden').val()){
        return false;
    }
    if(new_fio = prompt("Укажите ФИО клиента:")){
        $('#client_fio_hidden').val(new_fio);
        if(add_to_input){
            $client_input.val($client_input.val()+' '+new_fio);
        }
        return new_fio;
    }
}

// достаем заявки по клиенту и устройству при создании заказа если есть
var get_requests_client_id = null;
var get_requests_product_id = null;
function get_requests(_this, item_id, response){
    if($(_this).data('table') == 'clients'){
        get_requests_client_id = item_id;
    }
    if($(_this).data('table') == 'categories-last'){
        get_requests_product_id = item_id;
    }
    if(get_requests_client_id || get_requests_product_id){
        // достаем
        $.ajax({
            url: prefix+'services/ajax.php',
            type: 'POST',
            data: 'service=crm/requests&'+
                  'action=get_request_fro_order&'+
                  'client_id='+get_requests_client_id+'&'+
                  'product_id='+get_requests_product_id,
            dataType: 'json',
            success: function (data) {
                if(data.state){
                    $('#client_requests').html(data.content);
                }
            }
        });
    }
}
function change_crm_request($this){
    var product_id = $this.data('product_id'),
        client_id = $this.data('client_id'),
        referer_id = $this.data('referer_id'),
        code = $this.data('code');
    if(!$this.attr('data-client_fio')){
       var new_fio = show_fio_prompt(false, true);
       if(new_fio){
           $('input[name=crm_request][data-client_id='+client_id+']').attr('data-client_fio', new_fio);
       }
    }else{
       $('#client_fio_hidden').val($this.attr('data-client_fio'));
    }
    if(product_id && client_id){
       $('input[name="clients"]').val(client_id);
       $('input[name="categories-last"]').val(product_id);
       $('#crm_order_code').attr('disabled', true).val(code);
       $('#crm_order_referer').find('select').attr('disabled', true).val(referer_id);
    }else{
       $('input[name="categories-last-value"],input[name="categories-last"]').val('');
       $('input[name="clients-value"],input[name="clients"]').val('');
       $('#crm_order_code').attr('disabled', false).val('');
       $('#crm_order_referer').find('select').attr('disabled', false).val(0);
    }
}
function check_active_request(){
    var $checked_r = $('input[name=crm_request][value!=0]:checked');
   if($checked_r.length){ 
       change_crm_request($checked_r);
   }
}

$(function(){
   $('input[name=crm_request]').live('change', function(){
       change_crm_request($(this));
   }); 
});