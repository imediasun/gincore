function gen_tree() {
  $("[id='tree']").Tree();
}

function orders_quick_search(_this, param) {
  var hash = '', query = $.trim($('#orders_quick_search_query').val()), active = $(_this).parents('ul').first().find('li.active >a').first();

  if (active) {
    hash += active.data('open_tab');
  } else {
    hash += 'show_orders-orders';
  }
  if (query) {
    window.location = prefix + 'orders?' + param + '=' + encodeURI(query) + '&qsq=' + encodeURI(query) + '&hash=' + encodeURI(hash);
  }
}

function pay_client_order(_this, tt, order_id, b_id, extra, issued) {
  var data = {client_order_id: order_id, b_id: b_id, transaction_extra: extra, issued: issued};
  alert_box(_this, false, 'begin-transaction-' + tt + '-co', data, null, 'accountings/ajax/');
  return false;
}

function quick_sale(_this, next, from) {
  if (false === $('#quick-sale-form').parsley().validate())
    return;

  $(_this).button('loading');
  var data = $(_this).parents('form').serializeArray();
  if (next) {
    data.push({name: 'next', value: next});
  }
  if (from) {
    data.push({name: 'from', value: from});
  }
  $.ajax({
    url: prefix + module + '/ajax/?act=quick-sale-order',
    type: 'POST',
    dataType: "json",
    data: data,
    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && (msg['msg'] || msg['message'])) {
          if (msg['prompt']) {
            alert_box(undefined, (msg['msg'] || msg['message']));
            $('.bootbox-alert .modal-footer').prepend(msg['btn']);
          } else {
            alert((msg['msg'] || msg['message']));
          }
        }
        if (msg['open_window']) {
          window_open(msg['open_window']);
        }
        if (msg['location']) {
          var cur_loc = window.location.pathname + window.location.search + window.location.hash;
          if (msg['location'] == cur_loc) {
            window.location.reload(true);
          } else {
            window.location.href = msg['location'];
          }
        }
      }
      $(_this).button('reset');
    }
  });
}
function eshop_sale(_this, next, from) {
  $(_this).button('loading');
  var data = $(_this).parents('form').serializeArray();
  if (next) {
    data.push({name: 'next', value: next});
  }
  if (from) {
    data.push({name: 'from', value: from});
  }
  $.ajax({
    url: prefix + module + '/ajax/?act=eshop-sale-order',
    type: 'POST',
    dataType: "json",
    data: data,
    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && (msg['msg'] || msg['message'])) {
          if (msg['prompt']) {
            alert_box(undefined, (msg['msg'] || msg['message']));
            $('.bootbox-alert .modal-footer').prepend(msg['btn']);
          } else {
            alert((msg['msg'] || msg['message']));
          }
        }
        if (msg['open_window']) {
          window_open(msg['open_window']);
        }
        if (msg['location']) {
          var cur_loc = window.location.pathname + window.location.search + window.location.hash;
          if (msg['location'] == cur_loc) {
            window.location.reload(true);
          } else {
            window.location.href = msg['location'];
          }
        }
      }
      $(_this).button('reset');
    }
  });
}

function display_serial_product_title_and_price(_this, item_id) {
  $(_this).parent().find('small').html('');
  $.ajax({
    url: prefix + 'messages.php?act=get-product-title-and-price',
    type: 'POST',
    dataType: "json",
    data: '&item_id=' + item_id,
    success: function (msg) {
      if (msg) {
        if (msg['msg']) {
          $(_this).parent().find('.product-title').html(msg['msg']);
          $(_this).siblings('input[name=items]').val(msg['id']);
          if (msg['price']) {
            $('input[name="prices"]').attr('data-price', msg['price']);
          } else {
            $('input[name="prices"]').attr('data-price', 0);
          }
          if (msg['price_wholesale']) {
            $('input[name="prices"]').attr('data-price_wholesale', msg['price_wholesale']);
          } else {
            $('input[name="prices"]').attr('data-price_wholesale', 0);
          }
          set_price();
        }
      }
    }
  });
  return false;
}
function display_goods_information(_this) {
  $.ajax({
    url: prefix + module + '/ajax/?act=service-information',
    type: 'POST',
    data: '&goods_id=' + $('.typeahead-value-new-goods3').val(),
    success: function (msg) {
      if (msg['state'] == true) {
        $(_this).attr('data-placement', 'right');
        $(_this).attr('data-trigger', 'focus');
        if (msg['title']) {
          $(_this).attr('data-title', msg['title']);
        }
        if (msg['price']) {
          $('input[name="prices"]').attr('data-price', msg['price']);
        } else {
          $('input[name="prices"]').attr('data-price', 0);
        }
        if (msg['price_wholesale']) {
          $('input[name="prices"]').attr('data-price_wholesale', msg['price_wholesale']);
        } else {
          $('input[name="prices"]').attr('data-price_wholesale', 0);
        }
        if (msg['content']) {
          $(_this).attr('data-content', msg['content']);
        }
        set_price();
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
}

function display_service_information(_this) {
  $.ajax({
    url: prefix + module + '/ajax/?act=service-information',
    type: 'POST',
    data: '&category_id=' + $('.typeahead-value-categories-last3').val(),
    success: function (msg) {
      if (msg['state'] == true) {
        $(_this).attr('data-placement', 'right');
        $(_this).attr('data-trigger', 'focus');
        if (msg['title']) {
          $(_this).attr('data-original-title', msg['title']);
        }
        if (msg['price']) {
          $(_this).attr('data-original-price', msg['price']);
        }
        if (msg['price_wholesale']) {
          $(_this).attr('data-original-price_wholesale', msg['price_wholesale']);
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

function issue_order(_this, type, order_id) {
  if (parseFloat($(_this).data('debt')) > 0) {
    pay_client_order(_this, type, order_id, null, null, true);
  } else {
    give_without_pay(type, _this, order_id);
  }
}

function table_sorter() {
  $('#tablesorter').tablesorter({sortList: [[8, 1]]});
}

$(function () {
  var datepicker = $('.datepicker');
  if (datepicker.length) {
    datepicker.datepicker({yearRange: '1900:'});
  }

  setInterval(function () {
    if (window.location.hash == '#orders_manager') {
      click_tab_hash();
    }
  }, 1000 * 60 * 5);
});


function location_menu(_this, e) {
  e.preventDefault();
  var hash = window.location.hash;
  var url = $(_this).attr('href');
  window.location.href = url + hash;
  //window.location.reload();
}

$(function () {
  $(".test-toggle").bootstrapSwitch();
  $(document).on('click', '.accept-manager', function () {
    $(this).siblings('[name=accept-manager]').val(1);
  });

  $(document).on('click', '.drop-quick-orders-serach', function () {
    var $this = $(this),
      href = $this.attr('href');
    if (href.indexOf('#') === -1) {
      $this.attr('href', href + window.location.hash);
    }
  });

  $('.export_order').click(function () {
    var order_id = $(this).attr('data');

    $.ajax({
      url: prefix + module + '/ajax/?act=export_order',
      type: 'POST',
      data: '&order_id=' + order_id,
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
  });

  $('.client-bind').click(function () {
    var user_id = $(this).attr('data1');
    var order_id = $(this).attr('data2');
    var _this = this;

    $.ajax({
      url: prefix + module + '/ajax/?act=client-bind',
      dataType: "json",

      data: '&user_id=' + user_id + '&order_id=' + order_id,
      type: 'POST',
      success: function (msg) {
        if (msg['error']) {
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

  $(document).on('click', '.specify_order_id', function () {
    var $input = $('.order_id_input').first();
    $('.js-dummy_specify_order_id').toggle();
    $input.stop(true).slideToggle(200, function () {
      if (!$input.is(':visible')) {
        $input.find('input').val('');
      }
    });
  });
  $(document).on('click', '.hide_order_fields', function () {
    var $form = $('#hide-orders-fields-form');
    $form.stop(true).slideToggle(200, function () {
      $('.js-hide-fields').toggle();
      if ($form.is(':visible')) {
        $('.js-fields').removeClass('col-sm-6').addClass('col-sm-5');
        $('.js-requests').removeClass('col-sm-6').addClass('col-sm-5');
        $('.hide-field').show();

      } else {
        $('.js-fields').removeClass('col-sm-5').addClass('col-sm-6');
        $('.hide-field').hide();
        $('.js-requests').removeClass('col-sm-5').addClass('col-sm-6');
      }
    });
  });

  $(document).on('submit', '#order-form', function (e) {
    $(this).find('#update-order').click();
    e.preventDefault();
  });

  $(document).on('click', 'input[name=add_private_comment],input[name=add_public_comment]', function (e) {
    update_order(this);
    e.preventDefault();
  });

  $('input.visible-price')
  // event handler
    .keyup(resizeInput)
    // resize on page load
    .each(resizeInput);
});

function create_new_users_fields(_this) {
  var name = $('input[name="users_field_name"]').val();
  if (name) {
    $.ajax({
      url: prefix + module + '/ajax/?act=add-users-field',
      dataType: "json",
      data: {name: name},
      type: 'POST',
      success: function (msg) {
        if (msg['state']) {
          var $div = $('.js-new_field').clone(),
            $toggle = $('#toggle-for-new-field').clone();

          $div.removeClass('js-new_field');
          $div.children('label').html(msg['title']);
          $div.children('textarea').attr('name', 'users_fields[' + msg.name + ']');
          $('div.new_fields').append($div);
          $div.show();
          $('input[name="users_field_name"]').val('')
          $toggle.attr('id', '');
          $toggle.find('input').attr('name', 'config[' + msg.name + ']').addClass('test-toggle');
          $toggle.show();
          $toggle.insertBefore('#toggle-for-new-field');
          $(".test-toggle").bootstrapSwitch();
        } else {
          alert(msg['msg']);
        }
      },
      error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.responseText);
      }
    });
  } else {
    $('input[name="users_filed_name"]').css('border-color', 'red');
  }
  return false;
}

function create_client(_this) {
  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/?act=add_user',
    dataType: "json",

    data: $('#form-create-client').serialize(),
    type: 'POST',
    success: function (msg) {
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
    success: function (msg) {
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
  var order_id = arrequest()[2] || $('#update-order').data('o_id') || $('input[name="order_id"]').val();
  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/' + order_id + '?act=order-item',
    type: 'POST',
    data: 'order_product_id=' + $(_this).data('order_product_id'),
    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && msg['msg']) {
          alert(msg['msg']);
        }
        if (msg['state'] == true || msg['reload'] == true) {
          if ($('#modal-dialog').is(':visible')) {
            var $div = $('<div class="modal-backdrop fade in"></div>');
            var order_id = $('input[name="order_id"]').val();
            $('body').append($div);
            edit_order_dialog_by_order_id(order_id, 'display-order');
            setTimeout(function () {
              $('#modal-dialog').css('overflow', 'auto');
              $('#modal-dialog').css('display', 'block');
            }, 10);
          } else {
          click_tab_hash();
          }
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
  var order_id = arrequest()[2] || $('#update-order').data('o_id') || $('input[name="order_id"]').val();
  var modal = $(_this).parents('.modal-dialog').length > 0 ? 'modal' : '';

  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/' + order_id + '?act=update-order&show=' + modal,
    type: 'POST',
    data: $('#order-form').serialize(),
    success: function (msg) {
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
        if (msg['paid']) {
          $('.js-pay-button').click();
          $(_this).button('reset');
          return;
        }
        if (msg['location']) {
          if (modal) {
            edit_order_dialog_by_order_id(order_id, 'display-order');
            var $div = $('<div class="modal-backdrop fade in"></div>');
            $('body').append($div);
            setTimeout(function () {
              $('#modal-dialog').css('overflow', 'auto');
              $('#modal-dialog').css('display', 'block');
            }, 10);
          } else {
            window.location.href = msg['location'];
          }
        }
        if (msg['state'] == true || msg['reload'] == true) {
          close_alert_box();
          if (msg['modal']) {
            alert_box(_this, null, 'display-order', null, null, '/orders/ajax/' + order_id + '?act=display-order&show=modal');
          } else {
            click_tab_hash();
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
function apply_hide(_this) {
  $('#hide-orders-fields-form').submit();
}

function add_new_order(_this, next, from) {
  $(_this).button('loading');

  var data = $(_this).parents('form').serialize();
  if (next) {
    data += '&next=' + next;
  }
  if (from) {
    data += '&from=' + from;
  }
  $.ajax({
    url: prefix + module + '/ajax/?act=add-order',
    type: 'POST',
    data: data,
    success: function (msg) {
      if (msg) {
        if (msg.new_client_id) {
          $('.typeahead-double-value[name=client_id]').val(msg.new_client_id);
        }
        if (msg['state'] == false && (msg['msg'] || msg['message'])) {
          if (msg['prompt']) {
            alert_box(undefined, (msg['msg'] || msg['message']));
            $('.bootbox-alert .modal-footer').prepend(msg['btn']);
          } else {
            alert((msg['msg'] || msg['message']));
          }
        }
        if (msg['open_window']) {
          window_open(msg['open_window']);
        }
        if (msg['location']) {
          var cur_loc = window.location.pathname + window.location.search + window.location.hash;
          if (msg['location'] == cur_loc) {
            window.location.reload(true);
          } else {
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

function order_products(_this, product_id, order_product_id, cfm, remove, show_confirm_for_remove) {
  var close_supplier_order = '', url;
  var order_id = $('#update-order').data('order_id') || $('#order-form').find('input[name="order_id"]').first().val() || arrequest()[2] ;
  var is_modal = $('input[name="is_modal"]').val();

  if (remove && show_confirm_for_remove) {
    if (confirm(L['cancel-order-this-spare-part-supplier'] + '?')) {
      close_supplier_order = '&close_supplier_order=1';
    }
  }

  if (remove == 1) {
    url = prefix + module + '/ajax/' + order_id + '?act=remove_product';
  } else {
    url = prefix + module + '/ajax/' + order_id + '?act=add_product';
  }
  $.ajax({
    url: url,
    type: 'POST',
    data: 'order_product_id=' + order_product_id +
    '&product_id=' + product_id + (typeof cfm === 'undefined' ? '' : '&confirm=' + cfm) + close_supplier_order,
    success: function (msg) {
      if (msg) {
        if (msg['confirm']) {
          alert_box('undefined', msg['confirm']);
        }
        if (msg['goods']) {
          $('#goods-table').append(msg['goods']);
        }
        if (msg['service']) {
          $('#service-table').append(msg['service']);
        }
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
        if (msg.reload) {
          if (is_modal) {
            edit_order_dialog_by_order_id(order_id, 'display-order');
            var $div = $('<div class="modal-backdrop fade in"></div>');
            $('body').append($div);
            setTimeout(function () {
              $('#modal-dialog').css('overflow', 'auto');
              $('#modal-dialog').css('display', 'block');
            }, 10);
          } else {
            window.location.reload(true);
          }
        }
        if ($('#goods-table').find('tr').length) {
          $('#goods-table').closest('table').removeClass('hidden');
        } else {
          $('#goods-table').closest('table').addClass('hidden');
        }
        if ($('#service-table').find('tr').length) {
          $('#service-table').closest('table').removeClass('hidden');
        } else {
          $('#service-table').closest('table').addClass('hidden');
        }
      }
      if ($(_this).hasClass('global-typeahead')) {
        $(_this).val('');
      }
      recalculate_total_sum();
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
}

// проверяем если нема у клиентоса фио, предлагаем ввести
function check_fio(_this, item_id, response) {
  if (!response.fio) {
    setTimeout(function () {
      show_fio_prompt(true, false);
    }, 80);
  }
}
function show_fio_prompt(add_to_input, check_input) {
  var new_fio;
  var $client_input = $('#client_fio_hidden').siblings('[name=clients-value]');
  if (check_input && $client_input.val() && $('#client_fio_hidden').val()) {
    return false;
  }
  if (new_fio = prompt(L['specify-the-name-of-the-client'] + ":")) {
    $('#client_fio_hidden').val(new_fio);
    if (add_to_input) {
      $client_input.val($client_input.val() + ' ' + new_fio);
    }
    return new_fio;
  }
}

// достаем заявки по клиенту и устройству при создании заказа если есть
var get_requests_client_id = null;
var get_requests_product_id = null;
function get_requests(_this, item_id, response) {
  return base_get_requests(_this, item_id, response, '#client_requests');
}
function get_requests_from_eshop(_this, item_id, response) {
  return base_get_requests(_this, item_id, response, '#eshop_client_requests');
}
function base_get_requests(_this, item_id, response, id) {
  if ($(_this).data('table') == 'clients') {
    get_requests_client_id = item_id;
  }
  if ($(_this).data('table') == 'categories-last') {
    get_requests_product_id = item_id;
  }
  if (get_requests_client_id || get_requests_product_id) {
    // достаем
    $.ajax({
      url: prefix + 'services/ajax.php',
      type: 'POST',
      data: 'service=crm/requests&' +
      'action=get_request_fro_order&' +
      'client_id=' + get_requests_client_id + '&' +
      'product_id=' + get_requests_product_id,
      dataType: 'json',
      success: function (data) {
        if (data.state) {
          $(id).html(data.content);
        }
      }
    });
  }
}


function change_crm_request($this) {
  var product_id = $this.data('product_id'),
    product_name = $this.data('product_name'),
    client_id = $this.data('client_id'),
    referer_id = $this.data('referer_id'),
    code = $this.data('code');
  if (product_id && client_id) {
    $('input[name="clients"]').val(client_id);
    $('input[name="categories-last"]').val(product_id);
    $('input[name="categories-last-value"]').val(product_name);
    $('#crm_order_code').attr('disabled', true).val(code);
    $('#crm_order_referer').find('select').attr('disabled', true).val(referer_id);
  } else {
    $('input[name="categories-last-value"],input[name="categories-last"]').val('');
    $('input[name="clients-value"],input[name="clients"]').val('');
    $('#crm_order_code').attr('disabled', false).val('');
    $('#crm_order_referer').find('select').attr('disabled', false).val(0);
  }
}
function check_active_request() {
  var $checked_r = $('input[name=crm_request][value!=0]:checked');
  if ($checked_r.length) {
    change_crm_request($checked_r);
  }
}

$(function () {
  $('input[name=crm_request]').live('change', function () {
    change_crm_request($(this));
  });

  $('.dropdown-menu.keep-open').on("click", function (e) {
    e.stopPropagation();
  });

  $('.tooltips').tooltip();
});

// редактируем заказ поставщику
function show_suppliers_order(_this, id) {
  var is_modal = $('#order-form input[name="is_modal"]').val();
  var $div = $('<div class="modal-backdrop fade in"></div>');
  $.ajax({
    url: prefix + module + '/ajax/?act=supplier-order-form',
    type: 'POST',
    data: 'id=' + id,
    success: function (msg) {
      if (msg['state'] == false && msg['message']) {
        alert(msg['message'])
      }
      if (msg['state'] == true && msg['html']) {
        alert_box(_this, msg.html);
        $('.modal-dialog').css('width', '1000px');
      }
      if (is_modal) {
        $('body').append($div);
        setTimeout(function () {
          $('#modal-dialog').css('overflow', 'auto');
          $('#modal-dialog').css('display', 'block');
        }, 10);
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
}

// привязать группу запчасть к заказу
function bind_group_product(_this, product_id, order_id) {
  $.ajax({
    url: prefix + module + '/ajax/?act=bind-group-product-to-order',
    type: 'POST',
    data: 'product_id=' + product_id + (order_id ? '&order_id=' + order_id : ''),
    success: function (msg) {
      var buttons = {};
      if (msg['state'] == false && msg['message']) {
        alert(msg['message'])
      }
      if (msg['state'] == true && msg['html']) {
        buttons = {
          ok: {
            label: "Продолжить",
            className: "btn-primary",
            callback: function () {
              window.location.reload();
              $(_this).button('reset');
            }
          },
          main: {
            label: "Закрыть",
            className: "btn-primary",
            callback: function () {
              window.location.reload();
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
}

// привязать запчасть к заказу
function bind_product(_this, product_id) {
  $.ajax({
    url: prefix + module + '/ajax/?act=bind-product-to-order',
    type: 'POST',
    data: 'product_id=' + product_id,
    success: function (msg) {
      if (msg['state'] == false && msg['message']) {
        alert(msg['message'])
      }
      if (msg['state'] == true && msg['html']) {
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
  var price = $(_this).parent().parent().find('input.visible-price').first().val();
  $.ajax({
    url: prefix + module + '/ajax/?act=change-visible-prices',
    type: 'POST',
    data: 'id=' + id + '&' +
    'price=' + price,
    success: function (msg) {
      $(_this).parent().hide();
      recalculate_total_sum();
    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.log(xhr.responseText);
    }
  });
  return false;
}

function recalculate_total_sum() {
  var total = parseFloat(0);
  $('input.visible-price').each(function () {
    n = parseFloat($(this).val());
    total = n + total;
  });
  total = total.toFixed(2);
  $('span.total-sum').html(total);
  $('input.total-sum').val(total);
  if ($('input#total-sum-checkbox').prop("checked")) {
    $('#order-total').val(total);
  }
}


var max_width = 0;
function change_input_width(_this, length, not_show_button) {
  var $parent = $(_this).parent();

  var width = Math.min((length + 1) * 8 + 25, 100);
  if (typeof(not_show_button) === 'undefined') {
    width += 40;
  }
  max_width = Math.max(width, max_width);
  $parent.css('width', max_width + 'px');
  if (typeof(not_show_button) === 'undefined') {
    $parent.children('.input-group-btn').show();
  }
}

function resizeInput() {
  var length = $(this).val().length;
  $(this).attr('size', length);
  change_input_width(this, length, true);
}

function add_quick_item_to_table() {
  var $row = $('tr.js-quick-row-cloning'),
    cost = $('#sale_poduct_cost').val(),
    title = $('.product-title').html(),
    id = $('input[name="items"]').val(),
    serial = $('input[name="serials"]').val(),
    rnd = parseInt(Math.random() * 1000);

  if (cost == 0) {
    $('#sale_poduct_cost').addClass('parsley-error');
    $('#sale_product_cost_error').show();
  } else {
    $('#sale_poduct_cost').removeClass('parsley-error');
    $('#sale_product_cost_error').hide();
  }
  if (cost > 0 && title.length > 0 && id.length > 0) {
    $clone = $row.clone().removeClass('js-quick-row-cloning');
    $clone.addClass('row-item');
    $clone.find('.js-quick-item-name').first().val(title + '(' + serial + ')').attr('title', title + '(' + serial + ')');
    $clone.find('input.js-quick-item-id').first().val(id).attr('name', 'item_ids[' + rnd + ']');
    $clone.find('select.js-quick-warranty').first().val(id).attr('name', 'warranty[' + rnd + ']');
    $clone.find('.js-quick-price').first().val(cost).attr('name', 'amount[' + rnd + ']');
    $clone.find('.js-quick-discount').first().val(0).attr('name', 'discount[' + rnd + ']');
    $clone.find('.js-quick-discount_type').first().val(1).attr('name', 'discount_type[' + rnd + ']');
    $('#sale_poduct_cost').val('');
    $('input[name="serials-value"]').val('').attr('data-required', 'false');
    $('.product-title').html('');
    $('#item_id').val('');
    $clone.show();
    $row.parent().append($clone);
    $('table.quick-table-items').show();
    recalculate_amount_quick();
  }
  return false;
}


function recalculate_amount_quick() {
  var total = 0,
    $body = $('.quick-table-items > tbody');

  $body.children('tr.row-item').each(function () {
    var $row = $(this),
      discount = parseInt($row.find('.js-quick-discount').first().val()) || 0,
      amount = 0,
      price = parseFloat($row.find('.js-quick-price').first().val()).toFixed(2);

    if (parseInt($row.find('.js-quick-discount_type').first().val()) == 1) {
      amount = price * (1 - discount / 100);
    } else {
      amount = price - discount;
    }
    $row.find('.js-quick-sum').first().val(amount);
    total += amount;
  });
  if (total == 0) {
    if ($body.find('tr').length <= 1) {
      $body.parent().hide();
    }
    $('input[name="serials-value"]').attr('data-required', 'true');
  }
  $('.js-quick-total').val(total);
  if (total > 0) {
    $('.js-quick-pay-button').removeClass('disabled');
  } else {
    $('.js-quick-pay-button').addClass('disabled');
  }
}

function remove_row_quick(_this) {
  $(_this).parent().parent().remove();
  recalculate_amount_quick();
  return false;
}

function add_eshop_item_to_table() {
  var $row = $('tr.js-eshop-row-cloning'),
    cost = $('#eshop_sale_poduct_sum').val(),
    title = $('input[name="new-goods-value"]').attr('data-title'),
    id = $('input[name="new-goods"]').val(),
    price = $('#eshop_sale_poduct_cost').val(),
    discount = $('#eshop_sale_poduct_discount').val(),
    discount_type = $('#eshop_sale_poduct_discount_type').val(),
    quantity = $('#eshop_sale_poduct_quantity').val(),
    rnd = parseInt(Math.random() * 1000);

  if (typeof(title) == 'undefined' || title.length == 0) {
    $('input[name="new-goods-value"]').addClass('parsley-error');
    $('#eshop_sale_product_title_error').show();
    return false;
  } else {
    $('input[name="new-goods-value"]').removeClass('parsley-error');
    $('#eshop_sale_product_title_error').hide();
  }
  if (cost == 0) {
    $('#eshop_sale_poduct_cost').addClass('parsley-error');
    $('#eshop_sale_product_cost_error').show();
  } else {
    $('#eshop_sale_poduct_cost').removeClass('parsley-error');
    $('#eshop_sale_product_cost_error').hide();
  }
  if (cost > 0 && title.length > 0 && id.length > 0) {
    $clone = $row.clone().removeClass('js-eshop-row-cloning');
    $clone.addClass('row-item');
    $clone.find('.js-eshop-item-name').first().val(title).attr('title', title);
    $clone.find('input.js-eshop-item-id').first().val(id).attr('name', 'item_ids[' + rnd + ']');
    $clone.find('select.js-eshop-warranty').first().attr('name', 'warranty[' + rnd + ']');
    $clone.find('.js-eshop-sum').first().val(cost).attr('name', 'sum[' + rnd + ']');
    $clone.find('.js-eshop-price').first().val(price).attr('name', 'amount[' + rnd + ']');
    $clone.find('.js-eshop-quantity').first().val(quantity).attr('name', 'quantity[' + rnd + ']');
    $clone.find('.js-eshop-discount').first().val(discount).attr('name', 'discount[' + rnd + ']');
    $clone.find('.js-eshop-discount_type').first().val(discount_type).attr('name', 'discount_type[' + rnd + ']');
    if (discount_type == 1) {
      $clone.find('.percent').show();
      $clone.find('.currency').hide();
    } else {
      $clone.find('.percent').hide();
      $clone.find('.currency').show();
    }
    $('#eshop_sale_poduct_cost').val('');

    $('#categories-selected > ul.dropdown-menu > li.active > a').html('');
    // $('.typeahead').typeahead('val', '');
    $('#eshop_sale_poduct_sum').val('');
    $('#eshop_sale_poduct_discount').val('');
    $('#eshop_sale_poduct_quantity').val('');
    $('input[name="new-goods-value"]').val('');
    $('input[name="new-goods"]').val('');
    $clone.show();
    $row.parent().append($clone);
    recalculate_amount_eshop();
    $('table.eshop-table-items').show();
  }
  return false;
}

function recalculate_amount_eshop() {
  var total = 0,
    $body = $('.eshop-table-items > tbody');

  $body.children('tr.row-item').each(function () {
    var $row = $(this),
      discount = parseInt($row.find('.js-eshop-discount').first().val()) || 0,
      amount = 0,
      price = parseFloat($row.find('.js-eshop-price').first().val()).toFixed(2),
      count = parseInt($row.find('.js-eshop-quantity').first().val());

    if (parseInt($row.find('.js-eshop-discount_type').first().val()) == 1) {
      amount = price * (1 - discount / 100);
    } else {
      amount = price - discount;
    }
    $row.find('.js-eshop-sum').first().val(amount * count);
    total += (amount * count);
  });
  if (total == 0) {
    if ($body.find('tr').length <= 1) {
      $body.parent().hide();
    }
    $('input[name="serials-value"]').attr('data-required', 'true');
  }
  $('.js-eshop-total').val(total);
  if (total > 0) {
    $('.js-eshop-pay-button').removeClass('disabled');
  } else {
    $('.js-eshop-pay-button').addClass('disabled');
  }
}
function remove_row_eshop(_this) {
  $(_this).parent().parent().remove();
  recalculate_amount_eshop();
  return false;
}

function manager_setup(_this) {
  $.ajax({
    url: prefix + module + '/ajax/?act=manager-setup',
    type: 'GET',
    success: function (msg) {
      if (msg.state == false && msg.message) {
        alert(msg.message);
      }
      if (msg.state == true && msg.html.length > 0) {

        buttons = {
          success: {
            label: "Применить",
            className: "btn-success",
            callback: function () {
              $.ajax({
                url: prefix + module + '/ajax/?act=manager-setup',
                type: 'POST',
                data: $('form#manager-setup').serialize(),
                success: function (msg) {
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
            callback: function () {
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
function set_total_as_sum(_this, orderId, total) {
  if (confirm('Вы уверены?')) {
    $.ajax({
      url: prefix + module + '/ajax/?act=set-total-as-sum',
      data: {id: orderId, total_set: _this.checked},
      type: 'POST',
      success: function (msg) {
        var sum;
        if (msg.state == false && msg.message) {
          alert(msg.message);
        }
        if (msg.state == true) {
          sum = parseFloat($('input.total-sum').val()).toFixed(2);
          if (msg.set == true) {
            $('#order-total').attr('readonly', 'readonly').val(sum);
          } else {
            $('#order-total').removeAttr('readonly');
          }
          $('.js-pay-button').removeClass('disabled');
          if (sum == 0) {
            $('.js-pay-button').addClass('disabled');
          }
        }
      },
      error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.responseText);
      }
    });
  }
}

function select_cashbox(_this) {
  var cashbox = parseInt($(_this).attr('data-cashbox'));
  $('input[name="cashbox"]').val(cashbox);
  $('.btn-title').html($(_this).html());
  return false;
}
function select_price_type(_this) {
  var type = parseInt($(_this).attr('data-price_type'));
  $('input[name="price_type"]').val(type);
  $('.btn-title-price_type').html($(_this).html());
  set_price();
  return false;
}
function set_price(name) {
  var $source = $('input[name="prices"]');
  if ($('input[name="price_type"]').val() == 1) {
    $('input[name="price"]').val(parseFloat($source.attr('data-price')).toFixed(2));
  } else {
    $('input[name="price"]').val(parseFloat($source.attr('data-price_wholesale')).toFixed(2));
  }
  sum_calculate();
}
function toggle_delivery_to(state) {
  if (state == 1) {
    $('input[name="delivery_to"]').show();
  } else {
    $('input[name="delivery_to"]').hide();
  }
}
function change_discount_type(_this) {
  change_discount_type_show(_this);
  $("#update-order").click();
}
function change_discount_type_show(_this) {
  var $this = $(_this),
    $input = $this.find('.js-product-discount-type').first();
  if ($input.val() == 1) {
    $input.val(2);
    $this.find('.currency').show();
    $this.find('.percent').hide();
  } else {
    $input.val(1);
    $this.find('.percent').show();
    $this.find('.currency').hide();
  }
}
function change_status(_this) {
  var $this = $(_this),
    $parent = $this.parents('.dropdown').first().find('.as_button').first();

  $.ajax({
    url: prefix + module + '/ajax/?act=change-status',
    type: 'POST',
    data: '&order_id=' + $this.attr('data-order_id') + '&status=' + $this.attr('data-status_id'),
    success: function (msg) {
      if (msg.state) {
        $parent.css('background-color', $this.css('color'));
        $parent.find('.btn-title').first().html($this.html());
      } else {
        alert(msg.msg);
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
}
function toggle_items(hash) {
  var $group = $('.' + hash + '_group');
  $group.find('.items-show').toggle();
  $group.find('.items-hide').toggle();
  $('.' + hash + '_item').toggle();
}

function edit_order_dialog(_this, tab) {
  var id = $(_this).data('o_id');
  if (id) {
    edit_order_dialog_by_order_id(id, tab);
  }
}

