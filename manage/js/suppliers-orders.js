function edit_so_date_check(_this, event, order_id) {
  $(_this).button('loading');
  event.stopImmediatePropagation();

  $.ajax({
    url: prefix + module + '/ajax/?act=edit-so-date_check',
    dataType: "json",

    data: {order_id: order_id, date_check: $(_this).parent().siblings('input[name="date_check"]').val()},
    type: 'POST',
    success: function (msg) {
      if (msg['state'] == false && msg['msg']) {
        alert(msg['msg']);
      }
      if (msg['state'] == true) {
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

function check_item(_this, item_id) {
  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/?act=check-item',
    dataType: "json",

    data: {item_id: item_id},
    type: 'POST',
    success: function (msg) {
      if (msg['state'] == false && msg['msg']) {
        alert(msg['msg']);
      }
      if (msg['state'] == true) {
        //click_tab_hash();
        $(_this).replaceWith('<span class="icon-ok text-success"></span>');
      }
      $(_this).button('reset');
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function edit_supplier_order(_this) {
  $('#suppliers-order-form-cart').parsley().reset();
  if (false === $('#suppliers-order-form-header').parsley().validate() || false === $('#suppliers-order-form-cart').parsley().validate())
    return;

  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/?act=edit-supplier-order',
    dataType: "json",

    data: $('#suppliers-order-form').serialize(),
    type: 'POST',
    success: function (msg) {
      if (msg['state'] == false && msg['msg']) {
        alert(msg['msg']);
      }
      if (msg['state'] == true) {
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
          if (msg['location']) {
            window.location.href = msg['location'];
          } else {
            click_tab_hash();
          }
        }
        close_alert_box();
      }
      $(_this).button('reset');
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function accept_supplier_order(_this, callback) {
  var order_id = $('input[name="order_id"]').val();
  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/?act=accept-supplier-order',
    type: 'POST',
    dataType: "json",
    data: $('#form-accept-so').serialize(),
    success: function (msg) {
      if ($('#modal-dialog').is(':visible')) {
        var $div = $('<div class="modal-backdrop fade in"></div>');
        $('body').append($div);
        edit_order_dialog_by_order_id(order_id, 'display-order');
        setTimeout(function () {
          $('#modal-dialog').css('overflow', 'auto');
          $('#modal-dialog').css('display', 'block');
        }, 10);
        close_alert_box();
      }
      if (msg && msg['error']) {
        alert(msg['message']);
        $(_this).button('reset');
        return;
      }
      if (msg && msg['new_date']) {
        $('#order_supplier_date_wait').css('display', 'block');
        alert(msg['message']);
        $(_this).button('reset');
        return;
      } else {
        if (typeof callback == 'function') {
          $(_this).button('reset');
          $(_this).removeClass('disabled');
          $(_this).prop('disabled', false);
          close_alert_box();
          callback(_this);
          return;
        }
        close_alert_box();
        click_tab_hash();
        if (msg['infopopover_modal']) {
          show_infopopover_modal(msg['infopopover_modal']);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function create_supplier_order(_this) {

  $('#suppliers-order-form-cart').parsley().reset();
  if (false === $('#suppliers-order-form-header').parsley().validate() || false === $('#suppliers-order-form-cart').parsley().validate())
    return;

  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/' + arrequest()[2] + '?act=create-supplier-order',
    dataType: "json",

    data: $('#suppliers-order-form').serialize(),
    type: 'POST',
    success: function (msg) {
      if (msg['state'] == false && msg['msg']) {
        alert(msg['msg']);
      }
      if (msg['state'] == true) {
        if (msg['hash']) {
          click_tab_hash(msg['hash']);
        } else {
          click_tab_hash();
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

function remove_supplier_order(_this, order_id) {
  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/?act=remove-supplier-order',
    dataType: "json",

    data: '&order_id=' + order_id,
    type: 'POST',
    success: function (msg) {
      if (msg['error']) {
        alert(msg['message']);
      } else {
        //$('#supplier-order_id-' + order_id).remove();
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

function orders_link(_this, form_open_btn) {
  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/?act=orders-link',
    type: 'POST',
    dataType: "json",
    data: $('#form-orders-links').serialize(),
    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && msg['msg']) {
          alert(msg['msg']);
        }
        if (msg['state'] == true) {
          $(form_open_btn).click();
          if (msg['msg']) {
            alert(msg['msg']);
          }
        }
      }
      $(_this).button('reset');
    },
    error: function (xhr, ajaxOptions, thrownError) {
      $(_this).button('reset');
      if (xhr.responseText)
        alert(xhr.responseText);
    }
  });

  return false;
}

function end_supplier_order(_this, order_id) {
  //alert('В работе');
  $(_this).button('loading');

  $.ajax({
    url: prefix + 'messages.php?act=end-supplier-order',
    type: 'POST',
    dataType: "json",
    data: 'order_id=' + order_id,

    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && msg['msg']) {
          alert(msg['msg']);
        }
        if (msg['state'] == true) {
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

function close_supplier_order(_this, order_id) {
  $(_this).button('loading');

  $.ajax({
    url: prefix + 'messages.php?act=close-supplier-order',
    type: 'POST',
    dataType: "json",
    data: 'order_id=' + order_id,

    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && msg['msg']) {
          alert(msg['msg']);
        }
        if (msg['state'] == true) {
          click_tab_hash();
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

function avail_supplier_order(_this, order_id, avail) {
  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/?act=avail-supplier-order',
    type: 'POST',
    dataType: "json",
    data: {order_id: order_id, avail: avail},
    success: function (msg) {
      if (msg['error']) {
        alert(msg['message']);
      } else {
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
      $(_this).button('reset');
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function debit_supplier_order(_this) {
  $(_this).button('loading');
  $('form#debit-so-form .dso-msg').html('');
  $('form#debit-so-form .html-msg').remove();

  $.ajax({
    url: prefix + 'warehouses/ajax/?act=debit-supplier-order',
    type: 'POST',
    dataType: "json",
    data: $('form#debit-so-form').serialize(),

    success: function (msg) {
      if (msg) {
        var reload = false;
        if (msg['state'] == false && msg['msg']) {
          alert(msg['msg']);
        }
        if (msg['result']) {
          $('#debit-so-form-content').html(msg['result']);
          $(_this).hide().siblings("[data-bb-handler='ok']").text('OK');
          reload= true;
        }
        if (msg['html']) {
          $('#debit-so-form-content').append('<div class="html-msg"><div class="alert alert-block">' +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
            msg['html'] + '</div>');
        }
        if (msg['print_link']) {
          window_open(msg['print_link']);
        }
        if (reload == true) {
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
