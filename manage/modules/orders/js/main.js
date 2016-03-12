function gen_tree() {
    $("[id='tree']").Tree();
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

function orders_quick_search(_this, param){
    var query = $.trim($('#orders_quick_search_query').val());
    if(query){
        window.location = prefix+'orders?'+param+'='+encodeURI(query)+'&qsq='+encodeURI(query)+'#show_orders-orders'
    }
}

function create_transaction(_this, conf) {
    $(_this).button('loading');
    $.ajax({
        url: prefix+'accountings/ajax/?act=create-transaction',
        dataType: "json",
        data: $('#transaction_form').serialize() + (conf == 1 ? '&confirm=1' : ''),
        type: 'POST',
        success: function (data) {
            if (data) {
                if (data['state'] == true) {
                    location.reload();
                } else {
                    if (data['msg']) {
                        if (data['confirm']) {
                            if (confirm(data['msg'])) {
                                create_transaction(_this, 1);
                            }
                        } else {
                            alert(data['msg']);
                        }
                    }
                }
            }
            $(_this).button('reset');
        }
    });

    return false;
}

function pay_client_order(_this, tt, order_id, b_id, extra) {
    var data = {client_order_id: order_id, b_id: b_id, transaction_extra: extra};
    alert_box(_this, false, 'begin-transaction-' + tt + '-co', data, null, 'accountings/ajax/');
    return false;
}

function sale_order(_this, item) {
    if (false === $('#sale-form').parsley().validate())
    return;

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
                }
                if (msg['location']) {
                    window.location = msg['location'];
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
        alert(L['do-not-forget-to-pick-up-a-client-replacement-fund']);
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


function location_menu(_this, e) {
    e.preventDefault();
    var hash = window.location.hash;
    var url = $(_this).attr('href');
    window.location.href = url+hash;
    //window.location.reload();
}

$(function() {

    $(document).on('click', '.accept-manager', function(){
        $(this).siblings('[name=accept-manager]').val(1);
    });
    
    $(document).on('click', '.drop-quick-orders-serach', function(){
        var $this = $(this),
            href = $this.attr('href');
        if(href.indexOf('#') === -1){
            $this.attr('href', href + window.location.hash);
        }
    });

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
            type: 'POST',
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
    
    $(document).on('click', '.specify_order_id', function(){
        var $this = $(this),
            $input = $this.siblings('.order_id_input');
        $input.stop(true).slideToggle(200, function(){
            if(!$input.is(':visible')){
                $input.find('input').val('');
            }
        });
    });
    
    $(document).on('submit', '#order-form', function(e){
        $(this).find('#update-order').click();
        e.preventDefault();
    });
    
    $(document).on('click', 'input[name=add_private_comment],input[name=add_public_comment]', function(e){
        update_order(this);
        e.preventDefault();
    });

    $('input.visible-price')
    // event handler
      .keyup(resizeInput)
      // resize on page load
      .each(resizeInput);
    //$('div.floating-width').css('width', )
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

function add_new_order(_this, next, from) {
    $(_this).button('loading');

    var data = $(_this).parents('form').serialize();
    if(next){
        data += '&next='+next;
    }
    if(from){
        data += '&from='+from;
    }
    $.ajax({
        url: prefix + module + '/ajax/?act=add-order',
        type: 'POST',
        data: data,
        success: function(msg) {
            if (msg) {
                if(msg.new_client_id){
                    $('.typeahead-double-value[name=client_id]').val(msg.new_client_id);
                }
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
                if(msg['open_window']) {
                    window_open(msg['open_window']);
                }
                if(msg['location']) {
                    var cur_loc = window.location.pathname+window.location.search+window.location.hash;
                    console.log(cur_loc, msg['location']);
                    if(msg['location'] == cur_loc){
                        window.location.reload(true);
                    }else{
                        window.location.href = msg['location'];
                    }
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

function order_products(_this, product_id, order_product_id, cfm, remove, show_confirm_for_remove) {

    //var count = $('#product_count-' + order_product_id).length ? $('#product_count-' + order_product_id).val() : 1;

    var close_supplier_order = '';
    if(remove && show_confirm_for_remove){
        if(confirm( L['cancel-order-this-spare-part-supplier'] +'?')){
            close_supplier_order = '&close_supplier_order=1';
        }
    }

    $.ajax({
        url: prefix + module + '/ajax/' + arrequest()[2] + '?act=add_product',
        type: 'POST',
        data: 'order_product_id=' + order_product_id +
            '&product_id=' + product_id +
            //'&count=' + count +
            (typeof cfm === 'undefined' ? '' : '&confirm=' + cfm) +
            (remove == 1 ? '&remove=' + 1 : '') + close_supplier_order,
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
                if(msg.reload){
                    window.location.reload(true);
                }
                if($('#goods-table').find('tr').length){
                    $('#goods-table').closest('table').removeClass('hidden');
                }else{
                    $('#goods-table').closest('table').addClass('hidden');
                }
                if($('#service-table').find('tr').length){
                    $('#service-table').closest('table').removeClass('hidden');
                }else{
                    $('#service-table').closest('table').addClass('hidden');
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
    if(new_fio = prompt(L['specify-the-name-of-the-client'] + ":")){
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
//    if(!$this.attr('data-client_fio')){
//       var new_fio = show_fio_prompt(false, true);
//       if(new_fio){
//           $('input[name=crm_request][data-client_id='+client_id+']').attr('data-client_fio', new_fio);
//       }
//    }else{
//       $('#client_fio_hidden').val($this.attr('data-client_fio'));
//    }
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
   
    $('.dropdown-menu.keep-open').on("click", function(e){
        e.stopPropagation();
    });
    
    $('#print_now').click(function(){
        var $checks = $(this).closest('ul').find(':checked');
        $checks.each(function(){
            window_open($(this).val());
        });
    });
});

// редактируем заказ поставщику
function show_suppliers_order(_this, id){
    $.ajax({
        url: prefix + module + '/ajax/?act=supplier-order-form',
        type: 'POST',
        data: 'id='+id,
        success: function(msg) {
            if (msg['state'] == false && msg['message']) {
                alert(msg['message'])
            }
            if(msg['state'] == true && msg['html']) {
                alert_box(_this, msg.html);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

// привязать запчасть к заказу
function bind_product(_this, product_id){
    $.ajax({
        url: prefix + module + '/ajax/?act=bind-product-to-order',
        type: 'POST',
        data: 'product_id='+product_id,
        success: function(msg) {
            if (msg['state'] == false && msg['message']) {
                alert(msg['message'])
            }
            if(msg['state'] == true && msg['html']) {
                alert_box(_this, msg.html);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

//изменение видимой цены продукта или услуги
function change_visible_prices(_this, id) {
    price = $(_this).parent().parent().find('input.visible-price').first().val();
    $.ajax({
        url: prefix + module + '/ajax/?act=change-visible-prices',
        type: 'POST',
        data: 'id='+id+'&'+
              'price='+price,
        success: function(msg) {
            $(_this).parent().hide();
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
    return false;
}

var max_width = 0;
function change_input_width(_this, length, not_show_button) {
    var $parent = $(_this).parent();

    var width  = Math.min((length + 2) * 8 + 25, 100);
    if (typeof(not_show_button)==='undefined') {
        width += 40;
    }
    max_width = Math.max(width, max_width);
    $parent.css('width', max_width + 'px');
    if (typeof(not_show_button)==='undefined') {
        $parent.children('.input-group-btn').show();
    }
}

function resizeInput() {
    var length = $(this).val().length;
    $(this).attr('size', length);
    change_input_width(this, length, true);
}

function manager_setup(_this){
    $.ajax({
        url: prefix + module + '/ajax/?act=manager-setup',
        type: 'GET',
        success: function(msg) {
            if (msg.state == false && msg.message) {
                alert(msg.message);
            }
            if(msg.state == true && msg.html.length > 0) {

                buttons =  {
                    success: {
                        label: "Применить",
                        className: "btn-success",
                        callback: function() {
                            $.ajax({
                                url: prefix + module + '/ajax/?act=manager-setup',
                                type: 'POST',
                                data: $('form#manager-setup').serialize(),
                                success: function(msg) {
                                    location.reload();
                                },
                                error: function (xhr, ajaxOptions, thrownError) {
                                    alert(xhr.responseText);
                                }
                            });
                            $(_this).button('reset');
                        }
                    },
                    main: {
                        label: "Отменить",
                        className: "btn-primary",
                        callback: function() {
                            $(_this).button('reset');
                        }
                    }
                };
                dialog_box(_this, msg.title || '', msg.html, buttons);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
    return false;
}
