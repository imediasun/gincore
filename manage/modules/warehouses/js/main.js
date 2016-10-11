//var array_serial_inputs = {};

$(document).ready(function () {
  $(document).on('click', '.add-cashbox-table', function () {
    $(this).tooltip('hide');
  });
  $('.js-go-to').on('click', function () {
    var id = $(this).attr('data-goto_id');
    if (id) {
      $(id).click();
    }
  });
  $(document).on('click', 'input.colorpicker:not(.colorpicker-element)', function () {
    $(this).colorpicker({
      format: 'hex',
      align: 'left'
    }).colorpicker('show');
  });

  var scanner_moves_timer;

  function scanner_moves_alert_timer() {
    var block = $('#scanner-moves-alert-timer');
    if (block.length > 0) {
      var timer = parseInt(block.html());

      if (timer > 0) {
        block.html(timer - 1);
        clearTimeout(scanner_moves_timer);
        scanner_moves_timer = setTimeout(scanner_moves_alert_timer, 1000);
      } else {
        $('#scanner-moves-alert').removeClass('in').removeClass('alert-success').removeClass('alert-error');
        $('#scanner-moves-alert-body').html('');
        $('#scanner-moves').val('');
        $('#scanner-moves-old').val('');
      }
    }
  }

  $(this).keydown(function (e) {
    if (window.location.hash == '#scanner_moves') {
      //$('#scanner-moves-alert').removeClass('in').removeClass('alert-success').removeClass('alert-error');
      //$('#scanner-moves-alert-body').html('');
      var input = $('#scanner-moves');
      var input_old = $('#scanner-moves-old');

      // scan the same
      if (input.val() && input_old.val() && input.val() == input_old.val()) {
        $('#scanner-moves-alert').removeClass('in').removeClass('alert-success').removeClass('alert-error');
        $('#scanner-moves-alert-body').html('');
        input.val('');
        input_old.val('');
        return;
      }

      if (e.which == 13) {
        input.prop('disabled', true);

        $.ajax({
          url: prefix + module + '/ajax/?act=scanner-moves',
          type: 'POST',
          dataType: "json",
          data: {scanned: [input_old.val(), input.val()]},
          success: function (msg) {
            if (msg) {
              if (msg['state'] == true) {
                $('#scanner-moves-alert').removeClass('alert-error').addClass('alert-success');
                if (msg['value']) {
                  input_old.val(msg['value']);
                }
              } else {
                $('#scanner-moves-alert').removeClass('alert-success').addClass('alert-error');
              }
              if (msg['msg']) {
                $('#scanner-moves-alert-body').html(msg['msg']);
                $('#scanner-moves-alert').addClass('in');
                if ($('#scanner-moves-alert-timer').length > 0) {
                  clearTimeout(scanner_moves_timer);
                  scanner_moves_timer = setTimeout(scanner_moves_alert_timer, 1000);
                }
              }
              if (msg['ok']) {
                input.val('');
                input_old.val('');
                setTimeout(function () {
                  $('#scanner-moves-alert').removeClass('in').removeClass('alert-success').removeClass('alert-error');
                  $('#scanner-moves-alert-body').html('');
                }, 5000);
              }
            }
            input.val('');
            input.prop('disabled', false);
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

        return false;
      }
      input.focus();
    }
  });

});


function multiselect() {
  init_multiselect();
//    $('.multiselect').multiselect({
//        buttonContainer: '<span class="dropdown" />',
//        nonSelectedText: L['choose'],
//        enableFiltering: true,
//        numberDisplayed: 1,
//        maxHeight: 200
//    });
}

function multiselect_goods(tab) {
  $('.multiselect-goods-tab-' + tab).multiselect({
    buttonText: function (options) {
      if (options.length == 0) {
        return L['name'] + ' <b class="caret"></b>';
      } else {
        return options.length + ' selected  <b class="caret"></b>';
      }
    },
    buttonContainer: '<div class="btn-group" style="display: inline-block;">'
  });

  $.ajax({
    url: prefix + module + '/ajax/' + arrequest()[2] + '?act=goods-in-warehouse',
    type: 'POST',
    dataType: "json",
    //data:

    success: function (msg) {
      var data = [];

      if (msg) {
        if (msg['html']) {
          $('.multiselect-goods-tab-' + tab).html(msg['options']);
        }
        if (msg['options']) {
          for (var prop in msg['options']) {
            var object = {};
            object.value = prop;
            object.label = msg['options'][prop];
            data.push(object);
          }
        }
      }
      $('.multiselect-goods-tab-' + tab).multiselect('dataprovider', data);
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

}

function add_goods_to_inv(_this, tab) {
  var selected = [];
  $('.multiselect-goods-tab-' + tab).each(function () {
    selected.push($(this).val());
  });

  $.ajax({
    url: prefix + module + '/ajax/' + arrequest()[2] + '?act=add-goods-to-inv',
    type: 'POST',
    dataType: "json",
    data: 'goods=' + selected,

    success: function (msg) {
      if (msg && msg['state'] && msg['state'] == true) {
        click_tab_hash();
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
}

function consider(_this, i) {
  var $checkboxes = $('.consider_' + i),
    current = $(_this).attr('name'),
    is_check = $(_this).is(':checked'),
    $all = $checkboxes.filter('[name=consider_all]'),
    $store = $checkboxes.filter('[name=consider_store]');
  switch (current) {
    case 'consider_all':
      if (!is_check && $store.is(':checked')) {
        $all.attr('checked', true);
      }
      break;
    case 'consider_store':
      if (is_check) {
        $all.attr('checked', true);
      }
      break;
  }
}

function open_inventory(_this, it) {
  var tab = '#inventories-journal';
  window.location.href = prefix + module + '/create/' + it + tab;
  click_tab_hash(tab)
}

function create_inventories(_this, wh_id) {
  $.ajax({
    url: prefix + module + '/ajax/?act=create-inventory',
    type: 'POST',
    dataType: "json",

    data: '&wh_id=' + $('#create-inventory-wh_id').val(),
    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && msg['message']) {
          alert(msg['message']);
        }
        if (msg['state'] == true && msg['it'] > 0) {
          open_inventory(null, msg['it']);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function scan_serial(_this, id) {
  $(_this).button('loading');

  $.ajax({
    url: prefix + module + '/ajax/' + arrequest()[2] + '?act=scan-serial',
    type: 'POST',
    dataType: "json",

    data: '&serial=' + $('input#scan-serial-' + id).val(),
    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && msg['message']) {
          $('.scan-serial-error').html(msg['message']);
        }
        if (msg['state'] == true/* && msg['item']*/) {
          /*$('#scan-serial-error').html('');
           $('#tbody-inv-scans').prepend(msg['item']);*/
          click_tab_hash();
        }
        /*if (msg['remove'] == true) {
         $('.scan-serial-block').remove();
         }*/
      }

      $('.scan-serial').val('');
      $(_this).button('reset');
      $('.focusin').focus();
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function close_inventory(_this, inv_id) {
  if (confirm(L['close-inventory'] + '?')) {
    $.ajax({
      url: prefix + module + '/ajax/' + /*arrequest()[2] +*/ '?act=close-inventory',
      type: 'POST',
      dataType: "json",

      data: '&inv_id=' + inv_id,
      success: function (msg) {
        if (msg) {
          if (msg['state'] == false && msg['message']) {
            alert(msg['message']);
          }
          if (msg['state'] == true) {
            click_tab_hash();
          }
        }
      },
      error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.responseText);
      }
    });

    return false;
  }
}

function global_print_labels() {
  var items = $("input.check-item:checked").map(function (key, value) {
    return value.value;
  }).get();

  window_open(prefix + 'print.php?act=label&object_id=' + items)
}

function checked_item() {
  var count = $('.check-item:checked').length;

  $('.count-selected-items').html(count);
}

function open_product_inventory(_this, goods_id) {
  if ($(_this).children().hasClass('icon-chevron-down')) {
    window.location.href = $.param.querystring(window.location.href, 'inv_p=' + goods_id);
    /*
     $.ajax({
     url: prefix + module + '/ajax/' + arrequest()[2] + '?act=open-product-inventory',
     type: 'POST',
     dataType: "json",

     data: '&goods_id=' + goods_id,
     success: function(msg) {
     if (msg) {
     if (msg['state'] == false && msg['message']) {
     alert(msg['message']);
     }
     if (msg['state'] == true && msg['out']) {
     $("#product-inventory-" + goods_id).html(msg['out']);
     $("#product-inventory-" + goods_id).slideDown("quick", function () {
     $(_this).children().removeClass('icon-chevron-down').addClass('icon-chevron-up');
     });
     }
     }
     },
     error: function (xhr, ajaxOptions, thrownError) {
     alert(xhr.responseText);
     }
     });

     return false;*/
  } else {
    window.location.href = $.param.querystring(window.location.href, 'inv_p');
    /*$("#product-inventory-" + goods_id).slideUp("quick", function () {
     $(_this).children().removeClass('icon-chevron-up').addClass('icon-chevron-down');
     });*/
  }
}

function show_bind_button(_this) {
  $(_this).parents('tr').first().find('input.bind-button').first().show();
}
function create_warehouse_modal(_this) {
  var buttons = {
    success: {
      label: "Сохранить",
      className: "btn-success",
      callback: function () {
        var title = $('#create-warehouse-modal input[name="title"]').first().val(),
          location = $('#create-warehouse-modal input[name="location[]"]').first().val();
        if (title.length == 0) {
          alert('Заполните название склада');
          return false;
        }
        if (location.length == 0) {
          alert('Введите название локации');
          return false;
        }
        $.ajax({
          url: prefix + 'warehouses?act=create-warehouse',
          dataType: "json",
          type: 'POST',
          data: $('form#create-warehouse-modal').serialize(),
          success: function (data) {
            if (data['state'] == false) {
              alert(data['message']);
            } else {
              window.location.reload();
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

        $(this).button('reset');
      }
    },
    main: {
      label: "Отменить",
      className: "btn-primary",
      callback: function () {
        $(this).button('reset');
      }
    }
  };
  $.ajax({
    url: prefix + 'warehouses/ajax?act=create-warehouse',
    dataType: "json",
    type: 'GET',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          dialog_box(this, data['title'], data['content'], buttons);
        }
        if (data['state'] == false && data['message']) {
          alert(data['message']);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
  return false;
}

function create_purchase_invoice(act) {
  var buttons = {
    confirm: {
      label: L['debit_btn'],
      className: "btn-success js-create-purchase-invoice",
      callback: function () {
        $('#suppliers-order-form').parsley().reset();
        if (false === $('#suppliers-order-form-header').parsley().validate() ||
            false === $('#suppliers-order-form-body').parsley().validate())
        {
          return false;
        }

        $.ajax({
          // url: prefix + 'warehouses?act=create-purchase-invoice'
          url: prefix + 'warehouses?act=create-purchase-invoice-and-posting',
          dataType: "json",
          type: 'POST',
          data: $('form#suppliers-order-form').serialize(),
          success: function (data) {
            if (data['state'] == false) {
              alert(data['message']);
            } else {
              alert_box(this, false, 'form-debit-purchase-invoice', {object_id:data['id']});
              // window.location.reload();
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

        $(this).button('reset');
      }
    },
    cancel: {
      label: L['cancel'],
      className: "btn-primary",
      callback: function () {
        $(this).button('reset');
      }
    }
  };
  $.ajax({
    url: prefix + 'warehouses/ajax?act=create-purchase-invoice-form',
    dataType: "json",
    type: 'GET',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          dialog_box(this, data['title'], data['content'], buttons);
        }
        if (data['state'] == false && data['message']) {
          alert(data['message']);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
  return false;
}
function cancel_purchase_invoice(_this, id) {

}
function edit_purchase_invoice(id) {
  var buttons = {
    success: {
      label: "Сохранить",
      className: "btn-success",
      callback: function () {
        $.ajax({
          url: prefix + 'warehouses?act=edit-purchase-invoice',
          dataType: "json",
          type: 'POST',
          data: $('form#suppliers-order-form').serialize(),
          success: function (data) {
            if (data['state'] == false) {
              alert(data['message']);
            } else {
              window.location.reload();
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

        $(this).button('reset');
      }
    },
    main: {
      label: "Отменить",
      className: "btn-primary",
      callback: function () {
        $(this).button('reset');
      }
    }
  };
  $.ajax({
    url: prefix + 'warehouses/ajax?act=edit-purchase-invoice-form&id=' + id,
    dataType: "json",
    type: 'GET',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          dialog_box(this, data['title'], data['content'], buttons);
        }
        if (data['state'] == false && data['message']) {
          alert(data['message']);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
  return false;
}
function debit_purchase_invoice(_this) {
  $(_this).button('loading');
  $('form#debit-so-form .dso-msg').html('');
  $('form#debit-so-form .html-msg').remove();

  $.ajax({
    url: prefix + 'warehouses/ajax/?act=debit-purchase-invoice',
    type: 'POST',
    dataType: "json",
    data: $('form#debit-so-form').serialize(),

    success: function (msg) {
      if (msg) {
        var reload = false;
        if (msg['state'] == false) {
          if (msg['msg']) {
            alert(msg['msg']);
          }
          if (msg['message']) {
            alert(msg['message']);
          }
        }
        if (msg['result']) {
          $('#debit-so-form-content').html(msg['result']);
          $(_this).hide().siblings("[data-bb-handler='ok']").text('OK');
          reload = true;
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
function start_purchase_invoice(_this) {
  alert_box(_this, false, 'posting-one');
  return false;
}
function purchase_invoice_import_form() {
  var buttons = {
    success: {
      label: "Импортировать и приходовать",
      className: "btn-success",
      callback: function () {
        $.ajax({
          url: prefix + 'warehouses/ajax?act=purchase-invoice-import',
          dataType: "json",
          type: 'POST',
          data: $('form#import_form').serialize(),
          success: function (data) {
            if (data['state'] == false) {
              alert(data['message']);
            } else {
              alert_box(this, false, 'form-debit-purchase-invoice', {object_id:data['id']});
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

        $(this).button('reset');
      }
    },
    main: {
      label: "Отменить",
      className: "btn-primary",
      callback: function () {
        $(this).button('reset');
      }
    }
  };
  $.ajax({
    url: prefix + 'warehouses/ajax?act=purchase-invoice-import-form',
    dataType: "json",
    type: 'GET',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          dialog_box(this, data['title'], data['content'], buttons);
        }
        if (data['state'] == false && data['message']) {
          alert(data['message']);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
  return false;
}