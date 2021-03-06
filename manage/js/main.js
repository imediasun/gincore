var multiselect_options = {
  buttonContainer: '<div class="btn-group multiselect-btn-group" />',
  nonSelectedText: L['choose'],
  enableFiltering: true,
  inheritClass: true,
  includeSelectAllOption: true,
  enableCaseInsensitiveFiltering: true,
  //dropRight: true,
  allSelectedText: L['checkAll'],
  //dataprovider: [{label:1, value: 1}],
  buttonWidth: '150px',
  maxHeight: 200,
  maxWidth: 330/*,
   label: function(element) {
   var str = element.text;
   //str.replace("-", "прошел")
   return $(element).html();
   }*/
};

/*function tags_input() {
 $('.tags-input').each(function() {
 var input = $(this);
 var options = {
 itemValue: 'value',
 itemText: 'text',
 trimValue: true,
 allowDuplicates: false,
 typeahead: {
 source: function(query) {
 return $.post(prefix + "messages.php?act=tags", {table: input.data('table')});
 }
 }
 };
 if (input.data('maxtags')) {
 options.maxTags = input.data('maxtags');
 }

 input.tagsinput(options);

 var values = input.val();
 var texts = input.data('texts');

 if (values && texts) {
 values = values.split(',');
 texts = texts.split(',');
 for (var i = 0; i < values.length; i++) {
 input.tagsinput('add', {value: values[i], text: texts[i]});
 }
 }
 });
 }*/

function vd($some) {
  console.log($some);
}

function remove_alarm(_this, id) {
  $(_this).button('loading').html('');

  $.ajax({
    url: prefix + 'messages.php?act=remove-alarm',
    type: 'POST',
    data: 'id=' + id,
    dataType: 'json',
    success: function (result) {
      if (result) {
        if (result['state'] == true) {
          $(_this).parents('tr').remove();
        }
        if (result['state'] == false && result['msg']) {
          alert(result['msg']);
        }
      }
      $(_this).button('reset');
    }
  });

  return false;
}

function add_alarm(_this) {

  $(_this).button('loading');

  $.ajax({
    url: prefix + 'messages.php?act=add-alarm',
    type: 'POST',
    data: $('form#add-alarm').serialize(),
    dataType: 'json',
    success: function (result) {
      if (result) {
        if (result['state'] == false && result['msg']) {
          alert(result['msg']);
        }
        if (result['state'] == true) {
          click_tab_hash();
          close_alert_box();
        }
      }
      $(_this).button('reset');
      return false;
    }
  });
}

function auto_layout_keyboard(str)
{
  var replace = new Array(
    "й","ц","у","к","е","н","г","ш","щ","з","х","ъ",
    "ф","ы","в","а","п","р","о","л","д","ж","э",
    "я","ч","с","м","и","т","ь","б","ю"
  );
  var search = new Array(
    "q","w","e","r","t","y","u","i","o","p","\\[","\\]",
    "a","s","d","f","g","h","j","k","l",";","'",
    "z","x","c","v","b","n","m",",","\\."
  );

  for (var i = 0; i < replace.length; i++) {
    var reg = new RegExp(replace[i], 'mig');
    str = str.replace(reg, function (a) {
      return a == a.toLowerCase() ? search[i] : search[i].toUpperCase();
    })
  }
  return str
}



var rightSidebar = {
    currentType: '',
    currentId: '',

  init: function () {
    var _this = this;
    $('#right-sidebar .js_close_sidebar').live('click', function () {
      _this.clean_html();
      _this.hide();
    });

    $('[data-action="sidebar_product"]').live('click',function (e) {
        e.preventDefault();
        var elem = $(this);
        _this.load_product(elem.data('id_product'));
    });

    $('[data-action="sidebar_item"]').live('click',function (e) {
        e.preventDefault();
        var elem = $(this);
        _this.load_item(elem.data('id_item'));
    });

    $('#sidebar-product-form-submit').live('click', function (e) {
      e.preventDefault();
      $('#sidebar-product-form').trigger('submit');
    });

    $('#sidebar-moving-form-submit').live('click', function (e) {
      e.preventDefault();
      var rand = $('#moving-item-form-rand-value').val();
      move_order(this, rand, true);
    });


    // Инициализация вызова перемещения
    $('#move-order').live('click', function (e) {
      e.preventDefault();
      _this.load_stock_move();
    });

    _this.image_deleting_init();
    _this.stock_move_init();
  },

  form_init_product: function () {
    var _this = this;

    $('#sidebar-product-form').on('submit', function (e) {
      e.preventDefault();

      var id_product = $('#sidebar-id-product')[0].value;
      var form_data = $(this).serialize();
      $.ajax({
        url: prefix + 'products/ajax/' + id_product + '?act=sidebar-product-update',
        type: 'POST',
        data: form_data,
        dataType: 'json',
        success: function (result) {
          if (result.hasError) {
            result.errors.forEach(function (error, index) {
              _this.noty(error, 'warning');
            })
          } else {
            _this.hide();
            $('#sidebar-product-form-submit ').addClass('hidden');
            _this.noty(result.msg, 'success');
          }
        }
      });
    })
  },

  image_deleting_init: function () {
    $('#right-sidebar .img_delete').live("click", function (e) {
      var id_product = $(this).data('id_product');
      var id_image = $(this).data('id_image');
      var elem = this;
      $.ajax({
        url: prefix + 'products/ajax/' + id_product + '?act=product-image-delete',
        type: 'POST',
        data: {id_product: id_product, images_del: [id_image]},
        dataType: 'json',
        success: function (result) {
          if (result.hasError) {
          } else {
            $(elem).parent().remove();
          }
        }
      });
    })
  },

  load_product: function (id_product) {
    var _this = this;
    _this.currentType = 'load_product';
    _this.currentId = id_product;

    $.ajax({
      url: prefix + '/products/ajax/'+id_product+'?act=sidebar-load',
      type: 'GET',
      dataType: 'json',
      success: function (result) {
        if (result.hasError) {
          _this.noty('Что-то пошло не так.');
        } else {
          _this.html(result.html);
          _this.show();
          _this.form_init_product();
        }
      },
      complete: function () {
        setTimeout(function () {
          $('#sidebar-product-form-submit ').removeClass('hidden');
        }, 500)
      }
    });
    return true;
  },

  load_item: function (id_item) {
    var _this = this;
    _this.currentType = 'load_item';
    _this.currentId = id_item;

    $.ajax({
      url: prefix + '/warehouses/ajax/?act=sidebar-load-item',
      type: 'POST',
      data: {serial: id_item},
      dataType: 'json',
      success: function (result) {
        if (result.hasError) {
          _this.noty('Что-то пошло не так.');
        } else {
          _this.html(result.html);
          _this.show();
        }
      }
    });
    return true;
  },

  load_stock_move: function () {
    var _this = this;

    $.ajax({
      url: prefix + '/messages.php?act=stock_move-order_sidebar',
      type: 'POST',
      data: {},
      dataType: 'json',
      success: function (result) {
        if (result.hasError) {
          _this.noty('Что-то пошло не так.');
        } else {
          _this.html(result.html);
          _this.show(true);
          $('#scanner-moves-sidebar').focus();
          $("#moving-item-sidebar [data-toggle='tooltip']").uitooltip({
            placement: 'left'
          });
        }
      },
      complete: function () {
        setTimeout(function () {
          $('#sidebar-moving-form-submit ').removeClass('hidden');
        }, 500)
      }
    });
    return true;
  },

  stock_move_init: function () {
    $('#scanner-moves-sidebar').live('keydown', function (e) {
      var input = $('#scanner-moves-sidebar');
      var input_old = $('#scanner-moves-old-sidebar');

      // scan the same
      if (input.val() && input_old.val() && input.val() == input_old.val()) {
        $('#scanner-moves-alert-sidebar').removeClass('in').removeClass('alert-success').removeClass('alert-error');
        $('#scanner-moves-alert-body-sidebar').html('');
        input.val('');
        input_old.val('');
        return;
      }

      if (e.which == 13) {
        input.prop('disabled', true);
        var val = auto_layout_keyboard(input.val());
        var val_old = auto_layout_keyboard(input_old.val());
        input.val(val);
        input_old.val(val_old);

        $.ajax({
          url: prefix + 'warehouses/ajax/?act=scanner-moves&from_sidebar=1',
          type: 'POST',
          dataType: "json",
          data: {scanned: [val_old, val]},
          success: function (msg) {
            if (msg) {
              $('#scanner-moves-alert-sidebar').show();
              if (msg['state'] == true) {
                $('#scanner-moves-alert-sidebar').removeClass('text-error').addClass('text-success');
                if (msg['value']) {
                  input_old.val(msg['value']);
                }
              } else {
                $('#scanner-moves-alert-sidebar').removeClass('text-success').addClass('text-error');
              }
              if (msg['msg']) {
                $('#scanner-moves-alert-body-sidebar').html(msg['msg']);
                $('#scanner-moves-aler-sidebart').addClass('in');
                if ($('#scanner-moves-alert-timer-sidebar').length > 0) {
                  clearTimeout(scanner_moves_timer_sidebar);
                  scanner_moves_timer_sidebar = setTimeout(scanner_moves_alert_timer_sidebar, 1000);
                }
              }
              if (msg['ok']) {
                input.val('');
                input_old.val('');
                setTimeout(function () {
                  $('#scanner-moves-alert-sidebar').removeClass('in').removeClass('text-success').removeClass('text-error').hide();
                  $('#scanner-moves-alert-body-sidebar').html('');
                }, 5000);
              }
            }
            input.val('');
            input.prop('disabled', false);
            input.focus();
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

        return false;
      }
    });
  },

  show: function (is_narrow) {
    $('#right-sidebar').addClass('sidebar-open');

    if (is_narrow) {
      $('#right-sidebar').addClass('narrow');
    }
  },

  hide: function () {
    $('#right-sidebar').removeClass('sidebar-open');
    $('#right-sidebar').removeClass('narrow');
    $('#sidebar-product-form-submit ').addClass('hidden');
    $('#sidebar-moving-form-submit ').addClass('hidden');
    this.clean_html();
  },

  html: function (content) {
    $('#right-sidebar-content').html(content);
  },

  clean_html: function (content) {
    $('#right-sidebar-content').html('');
  },

  reload: function () {
      if (this.currentType == 'load_product') {
          this.load_product(this.currentId);
      } else if (this.currentType == 'load_item') {
          this.load_item(this.currentId);
      }
  },

  noty : function (text, type) {
      if (typeof type === 'undefined') {
          type = 'default';
      }
      if($.noty){
          noty({
              text: text,
              timeout: 3000,
              type: type,
              layout: 'topRight'
          });
      } else {
          alert(text);
      }
  }
};

var scanner_moves_timer_sidebar;
function scanner_moves_alert_timer_sidebar() {
  var block = $('#scanner-moves-alert-timer-sidebar');
  if (block.length > 0) {
    var timer = parseInt(block.html());

    if (timer > 0) {
      block.html(timer - 1);
      clearTimeout(scanner_moves_timer_sidebar);
      scanner_moves_timer_sidebar = setTimeout(scanner_moves_alert_timer_sidebar, 1000);
    } else {
      $('#scanner-moves-alert-sidebar').hide();
      $('#scanner-moves-alert-sidebar').removeClass('in').removeClass('text-success').removeClass('text-error');
      $('#scanner-moves-alert-body-sidebar').html('');
      $('#scanner-moves-sidebar').val('');
      $('#scanner-moves-old-sidebar').val('');
    }
  }
}




function change_margin_type(_this, selector) {
  if ($('input[name="' + selector + '_type"]').val() == 1) {
    $('input[name="' + selector + '_type"]').val(0)
  } else {
    $('input[name="' + selector + '_type"]').val(1)
  }
  $('.js-' + selector + '-type').toggle();
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
  if (price == 0) {
    $('#eshop_sale_poduct_cost').addClass('parsley-error');
    $('#eshop_sale_product_cost_error').show();
  } else {
    $('#eshop_sale_poduct_cost').removeClass('parsley-error');
    $('#eshop_sale_product_cost_error').hide();
  }
  if (quantity == 0) {
    $('#eshop_sale_poduct_quantity').addClass('parsley-error');
    $('#eshop_sale_product_quantity_error').show();
  } else {
    $('#eshop_sale_poduct_quantity').removeClass('parsley-error');
    $('#eshop_sale_product_quantity_error').hide();
  }
  vd('dssd');
  if (cost > 0 && title.length > 0 && id.length > 0) {
    vd('sdsd');
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
    $row.find('.js-eshop-sum').first().val(Math.round(amount * count * 100) / 100);
    total += (amount * count);
  });
  if (total == 0) {
    if ($body.find('tr').length <= 1) {
      $body.parent().hide();
    }
    $('input[name="serials-value"]').attr('data-required', 'true');
  }
  $('.js-eshop-total').val(Math.round(total * 100) / 100);
  if (total > 0) {
    $('.js-eshop-pay-button').removeClass('disabled');
  } else {
    $('.js-eshop-pay-button').addClass('disabled');
  }
}

function display_goods_information(_this) {
  $.ajax({
    url: prefix + 'orders/ajax/?act=service-information',
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
  $(_this).removeAttr('data-content');
  $.ajax({
    url: prefix + 'orders/ajax/?act=service-information',
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
          setTimeout(function () {
            alert_box(this, '<h3>' + msg['title'] + '</h3>' + msg['content']);
          }, 0);
          $(_this).attr('data-content', msg['content']);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
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

function remove_row_eshop(_this) {
  $(_this).parent().parent().remove();
  recalculate_amount_eshop();
  return false;
}

function eshop_sale(_this, next, from, from_call) {
  $(_this).button('loading');
  if (from_call) {
    var data = $('#eshop-sale-form').serializeArray();
  } else {
    var data = $(_this).parents('form').serializeArray();
  }
  if (next) {
    data.push({name: 'next', value: next});
  }
  if (from) {
    data.push({name: 'from', value: from});
  }
  
  if (from_call) {
    data.push({name: 'client_fio', value: $('input[name=fio]').val()});
    data.push({name: 'client_phone', value: $('#add-phone-field').val()});
  }
  
  $.ajax({
    url: prefix + 'orders/ajax/?act=eshop-sale-order',
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

        if (from_call) {
          if (msg['location']) {
            $('#eshop-sale-form')[0].reset();
            alert((msg['msg'] || msg['message']));
            window.open(msg['location']);
          } else {
            alert((msg['msg'] || msg['message']));
          }


        } else {
          if (msg['location']) {
            var cur_loc = window.location.pathname + window.location.search + window.location.hash;
            if (msg['location'] == cur_loc) {
              window.location.reload(true);
            } else {
              window.location.href = msg['location'];
            }
          }
        }
        

      }
      $(_this).button('reset');
    }
  });
}

$(document).ready(function () {

  rightSidebar.init();



  $(document).on('click', '.fullscreen', function () {
    $('.close-fullscreen-container').remove();
    var el = $(this).data('el') || 'body';
    $(el).fullscreen({toggleClass: 'fullscreen-container'});
    var btn = $('<div />')
      .addClass('close-fullscreen-container')
      .html('<i class="fa fa-close"></i>')
      .click(function () {
        $.fullscreen.exit();
        $('.close-fullscreen-container').remove();
      });
    $('.fullscreen-container').prepend(btn);
  });


  $('.cloneAndClear').live('click', function () {
    var $this = $(this), input, $el;
    if ($this.data('addon')) {
      $el = $this.parent().prev();
    } else if ($this.data('clone_siblings')) {
      $el = $this.siblings($this.data('clone_siblings')).last();
    } else {
      $el = $this.prev();
    }
    var clone = $el.clone();
    if (clone.hasClass('clone_clear_val')) {
      input = clone;
    } else {
      input = clone.find('.clone_clear_val').last();
    }
    input.val('');
    if (input.hasClass('global-typeahead')) {
      var num = 1 * input.attr('data-select');
      var next = 1 + num;
      clone.find('input[name="serials[]"]').attr('class', '').addClass('typeahead-value-serials' + next);
      input.attr('data-select', next);
      input.attr('data-input', 'serials' + next);
    }
    clone.find('.clone_clear_html').html('');
    $this.before(clone);
    init_input_masks();
  });


  $('.datetimepicker').live('click', function () {
    var format = $(this).data('format') || 'DD.MM.YYYY';
    var date_format = format.replace('dd', 'DD').replace('yyyy', 'YYYY').replace('hh', 'HH');
    $(this).datetimepicker({
      locale: 'ru',
      defaultDate: $(this).val(),
      format: date_format
    });
    $(this).data('DateTimePicker').show();
  });

  $('.editable-click').live('click', function (event) {
    event.preventDefault();
    $(this).editable({
      success: function (response, newValue) {
        if (response && response.msg && response.status == 'error') {
          alert(response.msg);
        } else {
          $(this).val(newValue);
          if ($(this).hasClass('editable-html')) {
            $(this).html(newValue);
          }
          if (response && response.element_id && response.element_value) {
            $('#' + response.element_id).html(response.element_value);
          }
        }
      }
    });
    //$(this).click();
  });

  var daterangepicker_locale = {
    ru: {
      daysOfWeek: [
        "Вс",
        "Пн",
        "Вт",
        "Ср",
        "Чт",
        "Пт",
        "Сб"
      ],
      monthNames: [
        "Январь",
        "Февраль",
        "Март",
        "Апрель",
        "Май",
        "Июнь",
        "Июль",
        "Август",
        "Сентябрь",
        "Октябрь",
        "Ноябрь",
        "Декабрь"
      ],
      firstDay: 1
    },
    en: {
      daysOfWeek: [
        "Su",
        "Mo",
        "Tu",
        "We",
        "Th",
        "Fr",
        "Sa"
      ],
      monthNames: [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
      ]
    }
  };

  $('input.daterangepicker').live('focusin', '.daterangepicker', function (e) {
    $(this).daterangepicker({
      locale: $.extend(daterangepicker_locale[manage_lang], {
        format: 'DD.MM.YYYY'
      }),
      showDropdowns: true,
      autoApply: true
    });
  });
  $('input.daterangepicker_single').live('focusin', '.daterangepicker_single', function (e) {
    var $this = $(this),
      format = $this.data('format') ? $this.data('format') : 'DD.MM.YYYY';
    $(this).daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      locale: $.extend(daterangepicker_locale[manage_lang], {
        format: format
      })
    });
  });

  $('input.show-length, textarea.show-length').live('focusin', '.show-length', function (e) {

    $(this).maxlength({
      alwaysShow: true,
      threshold: 0,
      warningClass: "label label-success",
      limitReachedClass: "label label-important",
      placement: 'top-right'
      //showEvent: 'ready'
    });
  });

  init_multiselect();

  $('.global-typeahead').live('focusin', '.global-typeahead', function (e) {

    //var auto_typeahead = false;
    var input_selector = $(this).data('input');
    var call_function = $(this).data('function');

    $('.global-typeahead').typeahead({
      items: 50,
      minLength: 1,
      source: function (query, process) {

        input_selector = $(this.$element).data('input');
        call_function = $(this.$element).data('function');
        var table = $(this.$element).data('table');
        var fix = $('.select-typeahead-' + $(this.$element).data('select')).val();
        if ($(this.$element).attr('data-phone_mask')) {
          query = query.replace(/\D/g, '');
        }
        return $.ajax({
          url: prefix + 'messages.php',
          type: 'POST',
          data: {
            act: 'global-typeahead', query: query, table: table, fix: fix,
            limit: this.options.items, object: arrequest()[2]
          },
          dataType: 'json',
          success: function (result) {

            //auto_typeahead = false;

            if (result) {
              var resultList = result.map(function (item) {
                var aItem = {
                  original: item, id: item.id, name: item.title.replace(/\u00A0/g, " ").replace(/[\r\n]+/g, "\n")
                };

                return JSON.stringify(aItem);
              });

              return process(resultList);
            }

            return false;
          }
        });
      },

      matcher: function (obj) {
        var item = JSON.parse(obj);
        this.query = $.trim(this.query);
        return item.name.toLowerCase();
        //return ~item.name.toLowerCase().indexOf(this.query.toLowerCase())
      },

      /*sorter: function (items) {
       var beginswith = [], caseSensitive = [], caseInsensitive = [], item;
       while (aItem = items.shift()) {
       var item = JSON.parse(aItem);
       if (!item.name.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(JSON.stringify(item));
       else if (~item.name.indexOf(this.query)) caseSensitive.push(JSON.stringify(item));
       else caseInsensitive.push(JSON.stringify(item));
       }

       return beginswith.concat(caseSensitive, caseInsensitive)
       },*/

      highlighter: function (obj) {
        var item = JSON.parse(obj);
        var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
        // console.log(item);
        if (item.original.class) {
          return '<span class="' + item.original.class + '">' + item.name.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
              return '<strong>' + match + '</strong>'
            }) + '</span>';
        }
        return item.name.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
          return '<strong>' + match + '</strong>'
        })
      },

      updater: function (obj) {
        var item = JSON.parse(obj),
          _this = this;
        $('.typeahead-value-' + input_selector).attr('value', item.id);
        //auto_typeahead = true;
        if (typeof call_function != 'undefined' && call_function.indexOf(',') > 0) {
          var functions = call_function.split(',');
          $.each(functions, function (k, v) {
            if (typeof window[v] == 'function') {
              window[v](_this.$element, item.id, item.original);
            }
          });
        } else {
          if (typeof window[call_function] == 'function') {
            window[call_function](_this.$element, item.id, item.original);
          }
        }
        if ($(this.$element).attr('data-phone_mask')) {
          return item.original.phone;
        } else {
          return item.name;
        }
      }
    }).on('focusout', this, function (e) {
      if (!$(this).data('no_clear_if_null')) {
        input_selector = $(this).data('input');
        if ($(this).val() == '') {
          $('.typeahead-value-' + input_selector).val('');
        }
        if (/*auto_typeahead == false && */$('.typeahead-value-' + input_selector).val() == 0 && typeof $(this).data('anyway') === 'undefined') {
          $(this).val('');
          //$('.typeahead-value-' + input_selector).val('');
        } else if (typeof $(this).data('anyway') !== 'undefined') {
          $('.typeahead-value-' + input_selector).val($(this).val());
        }
      }
    });
    $(document).on('mousedown', 'ul.typeahead', function (e) {
      e.preventDefault();
    });
  });

  $(document).on('click', '.typeahead_add_form', function () {
    var $this = $(this),
      $form = $('#' + $this.data('form_id')),
      id = (new Date()).getTime();
    $this.attr('data-id', 'source-' + id);
    $form.attr('data-id', 'form-' + id);
    if (!$form.hasClass('loaded')) {
      $this.button('loading');
      $.ajax({
        url: prefix + $this.data('action'),
        dataType: "json",
        data: '&name=' + $this.closest('.form-group').find('input.form-control').val(),
        type: 'POST',
        success: function (msg) {
          $form.addClass('loaded').append('<form>' + msg.html + '</form>');
          if ($this.data('form_id') == 'new_device_form') {
            $form.show();
            if ($this.parents(".modal-dialog").length > 0) {
              $form.css('max-width', '1000px').css('position', 'fixed').css('margin-left', 'auto').css('margin-right', 'auto').css('top', '60px');
            }
            $this.button('reset');
          } else {
            reset_multiselect($form, function () {
              $form.show();
              if ($this.parents(".modal-dialog").length > 0) {
                $form.css('max-width', '1000px').css('position', 'fixed').css('margin-left', 'auto').css('margin-right', 'auto').css('top', '60px');
              }
              $this.button('reset');
            });
          }
        }
      });
      $this.parents('form').find('.submit-from-btn').mousedown(function () {
        $form.removeClass('loaded').empty().hide();
      });
    } else {
      $form.toggle();
    }
  });
  $(document).on('click', '.hide_typeahead_add_form', function () {
    $(this).closest('.typeahead_add_form_box').hide();
  });

  $('.js-show-ratings').on('click', function () {
    $.ajax({
      url: prefix + 'users/ajax/?act=ratings',
      dataType: "json",
      type: 'GET',
      success: function (data) {
        if (data) {
          if (data['state'] == true) {
            alert_box(this, data['content']);
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
  });
  $('.js-show-tariff').on('click', function () {
    var buttons = {
      success: {
        label: L.change,
        className: "btn-success",
        callback: function () {
          window.open($('#tariffs-url').val(), '_blank');
          $(this).button('reset');
        }
      },
      main: {
        label: L.cancel,
        className: "btn-primary",
        callback: function () {
          $(this).button('reset');
        }
      }
    };
    $.ajax({
      url: prefix + 'settings/ajax?act=show-tariff',
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
  });
  $(document).on('focusin', '.typeahead-double', function (e) {
    var $this = $(this),
      id = $this.data('id'),
      this_field = $this.data('field'),
      call_function = $this.data('function');
    $(this).typeahead({
      items: 50,
      minLength: 1,
      source: function (query, process) {
        id = $(this.$element).data('id');
        call_function = $(this.$element).data('function');
        var table = $(this.$element).data('table');
        var fix = $('.select-typeahead-' + $(this.$element).data('select')).val();
        var fields = [];
        $('#' + id).val('');
        $('.typeahead-double[data-id="' + id + '"]').each(function () {
          fields.push($(this).data('field'));
        });
        if ($(this.$element).attr('data-phone_mask')) {
          query = query.replace(/\D/g, '');
        }
        return $.ajax({
          url: prefix + 'messages.php',
          type: 'POST',
          data: {
            act: 'global-typeahead', fields: fields.join(','),
            double: true, query: query, table: table, fix: fix,
            limit: this.options.items, object: arrequest()[2]
          },
          dataType: 'json',
          success: function (result) {
            if (result) {
              var resultList = result.map(function (item) {
                var aItem = {
                  original: item, id: item.id, name: item.title.replace(/\u00A0/g, " ").replace(/[\r\n]+/g, "\n")
                };
                return JSON.stringify(aItem);
              });
              return process(resultList);
            }
            return false;
          }
        });
      },
      matcher: function (obj) {
        var item = JSON.parse(obj);
        this.query = $.trim(this.query);
        return item.name.toLowerCase();
      },
      highlighter: function (obj) {
        var item = JSON.parse(obj);
        var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
        return item.name.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
          return '<strong>' + match + '</strong>'
        })
      },
      updater: function (obj) {
        var item = JSON.parse(obj),
          _this = this;
        $('#' + id).attr('value', item.id);
        if (call_function.indexOf(',') > 0) {
          var functions = call_function.split(',');
          $.each(functions, function (k, v) {
            if (typeof window[v] == 'function') {
              window[v](_this.$element, item.id, item.original);
            }
          });
        } else {
          if (typeof window[call_function] == 'function') {
            window[call_function](_this.$element, item.id, item.original);
          }
        }
        if (item.original['tag_id']) {
          $('span.tag').html(item.original['t_title']).css('background-color', item.original['t_color']);
        }
        var return_value = '';
        $('.typeahead-double[data-id="' + id + '"]').each(function () {
          var field = $(this).data('field');
          if (field === this_field) {
            return_value = item.original[field];
          }
          $(this).val(item.original[field]);
        });
        return return_value;
      }
    }).on('focusout', this, function (e) {
      id = $(this).data('id');
      var $clear_els = $('#' + id);
      var is_clear = true;
      $('.typeahead-double[data-id="' + id + '"]').each(function () {
        var $this = $(this);
        if ($.trim($this.val())) {
          is_clear = false;
        } else {
          $clear_els.add($this);
        }
      });
      if (is_clear) {
        $clear_els.val('');
      }
    });
  });

  $('span#ga').parent().click(function () {
    var b = $('body');
    b.append('<div class="loading-wrapper"></div>');
    var dl = $('<div class="loading-wrapper-text">Загружается Google Analytics<br />Пожалуйста, подождите...</div>');
    b.append(dl);
    var left = (b.width() - dl.width()) / 2;
    var top = 300;
    dl.css({
      'left': left + 'px',
      'top': top + 'px'
    });
  });

  function init_hash() {
    if (window.location.hash) {
      var hashs = window.location.hash;
    } else {
      var hashs = $('a.click_tab.default').attr('href');
    }

    if (hashs) {
      var hash = hashs.split('-');
      $('ul.nav.nav-tabs > li > a[href="' + hash[0] + '"]').click();
    }
  }

  init_hash();

  /*$('ul.nav.nav-pills > li > a.unhash').live('click', function (event) {
   event.preventDefault();
   $(this).tab('show');
   });*/


  $('#infoblock>span').click(function () {
    $('#infoblock').toggleClass('activate');
  });

  function arrequest_for_editable() {
    var url = window.location.toString();
    var arr = url.split(prefix.toString())[1];

    arr = arr.split('#')[0];
    var url_get = arr.split('?')[1];
    arr = arr.split('?')[0];
    var arrequest = arr.split('/');
    arrequest['get'] = url_get;

    return arrequest;
  }

  $(".infoblock").editable({
    type: 'textarea',
    url: prefix + 'messages.php',
    title: 'Обновите информацию',
    pk: {act: "infobox", do: "set", hash: module + (arrequest_for_editable()[1] || '')}, //
    emptytext: 'Нет данных, нажмите чтобы добавить.'
  });

  $('.header-link.hide-menu').click(function () {
    $.cookie('hide_menu', $('body').hasClass('hide-sidebar') ? 1 : 0);
  });

  $(document).on('change', '#contractor_type_select', function () {
    var $this = $(this).find(':selected');
    $('.multiselect[data-type="categories_1"]').multiselect('deselectAll', false)
      .multiselect('select', $this.data('categories_1'));
    $('.multiselect[data-type="categories_2"]').multiselect('deselectAll', false)
      .multiselect('select', $this.data('categories_2'));
  });
});

function category_input_fill(data, _this, o_id) {
  var input_group = $('#' + o_id).closest('.input-group');
  input_group.find('input[name=categories-last]').val(data.id);
  input_group.find('input[name=categories-last-value]').val(data.name).trigger('blur');
}

function category_create_form_submit(_this, callback, o_id) {
  var form_data = $('#category-create-form');
  var hide_modal = true;
  var data = form_data.serialize();

  $(_this).button('loading');

  $.ajax({
    url: prefix + 'categories/ajax/?act=create_new',
    dataType: "json",
    data: data,
    type: 'POST',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          if (hide_modal) {
            $(_this).closest('.modal').modal('hide');
          }
          if (typeof callback == 'function') {
            callback(data, _this, o_id);
          }

          // if (typeof callback == 'string') {
          //   window[callback](data, _this);
          // }
        }
        if (data['state'] == false && data['msg'])
          alert(data['msg'])
      }
      $(_this).button('reset');
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function contractor_create(_this, callback) {
  var form_data = $('.bootbox form.form_contractor');
  var hide_modal = true;
  if (form_data.length) {
    var data = form_data.serialize();
  } else {
    hide_modal = false;
    var form = $('.new_supplier_form.loaded');
    var data = form.find('input[name],select[name],textarea[name]').serialize();
  }
  $(_this).button('loading');

  $.ajax({
    url: prefix + 'accountings/ajax/?act=contractor-create',
    dataType: "json",
    data: data,
    type: 'POST',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          if (hide_modal) {
            $(_this).closest('.modal').modal('hide');
          }
          if (typeof callback == 'function') {
            callback(data, _this);
          } else {
            click_tab_hash();
          }
        }
        if (data['state'] == false && data['message'])
          alert(data['message'])
      }
      $(_this).button('reset');
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function contractor_add_callback(data) {
  if (data.state) {
    var $select = $('#contractor-select');


    $select.append('<option value="' + data.id + '">' + data.name + '</option>');
    $select.multiselect('destroy');
    $select.multiselect({
      enableFiltering: false,
    });
    $select.multiselect('select', [parseInt(data.id)]);

    $('.form_contractor ').closest('.modal-body').find('.bootbox-close-button').trigger('click');
  }
}

function quick_create_supplier_callback(data) {
  $('select[name="warehouse-supplier"]').append('<option selected value="' + data.id + '">' + data.name + '</option>');
}

function new_quick_create_supplier_callback(data, _this) {
  $('select[name="warehouse-supplier"]').append('<option selected value="' + data.id + '">' + data.name + '</option>');
  $(_this).closest('.new_supplier_form.loaded').hide().removeClass('loaded').html('');
}

// call as callback in categories/index.php:gencreate and products/index.php:create_product_form
function select_typeahead_device(data, $form) {
  if (data.state && data.id) {
    var id = $form.closest('.new_device_form').attr('data-id'),
      $f = $('[data-id="source-' + id.replace('form-', '') + '"]').closest('.input-group');
    $f.find(':hidden').val(data.id);
    var $input = $f.find('input.form-control');
    $input.val(data.name);
    $form.closest('.typeahead_add_form_box').hide().empty().removeClass('loaded');
    if ($input.data('function')) {
      var call_function = $input.data('function');
      if (call_function.indexOf(',') > 0) {
        var functions = call_function.split(',');
        $.each(functions, function (k, v) {
          if (typeof window[v] == 'function') {
            window[v]($input, data.id);
          }
        });
      } else {
        if (typeof window[call_function] == 'function') {
          window[call_function]($input, data.id);
        }
      }
    }
  }
}

function load_infoblock(hash) {
  hash = hash.replace('#', '');
  $('#infoblock').data("hash", hash);

  //alert($('#infoblock').data("hash"));

  $.ajax({
    type: "POST",
    url: prefix + "messages.php",
    data: {act: "infobox", do: "get", hash: hash}
  }).done(function (json) {
    $('div.infoblock').html(json['text']);
    $('div#infoblock .title').html(json['title']);
    $(".infoblock").editable('setValue', json['text']);
    $(".infoblock").editable('option', 'pk', {act: "infobox", do: "set", hash: $('#infoblock').data("hash")});
  });
}

var popover_target;
var popover_timer;

$(document).on('mouseleave', '.popover-info', function () {
  var _this = this;

  function popover_hide() {

    clearTimeout(popover_timer);

    popover_timer = setTimeout(function () {
      if (!$(".popover:hover").length) {
        $(_this).popover("hide");
        popover_target = null;
        clearTimeout(popover_timer);
      } else {
        popover_hide();
      }
    }, 200);
  };

  popover_hide();
});

$(document).on('mouseenter', '.popover-info', function () {
  clearTimeout(popover_timer);
  var _this = this;
  var placement = $(this).attr('data-placement') ? $(this).attr('data-placement') : 'auto left';
  var trigger = $(this).attr('data-trigger') ? $(this).attr('data-trigger') : 'mouseenter';

  if (popover_target !== _this) {
    $(popover_target).popover("hide");
    popover_target = _this;

    $(_this).popover({
      html: true,
      container: 'body',
      trigger: trigger,
      placement: placement,
      boxLRMargin: -10
    }).popover('show');
  }
});

function popover_with_footer(footer) {
  $(document).on('mouseenter', '.popover-info-with-footer', function () {
    clearTimeout(popover_timer);
    var _this = this;
    var placement = $(this).attr('data-placement') ? $(this).attr('data-placement') : 'left';
    var trigger = $(this).attr('data-trigger') ? $(this).attr('data-trigger') : 'mouseenter';

    if (popover_target !== _this) {
      $(popover_target).popover("hide");
      popover_target = _this;

      $(_this).popover({
        html: true,
        container: 'body',
        trigger: trigger,
        placement: placement,
        boxLRMargin: -10,
        template: '<div class="popover"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div><div class="popover-footer">' + footer + '</div></div>'
      }).popover('show');
    }
  });
}

/**
 * add symbol counter before DOM object,
 * counts symbols in object & output result
 *
 * @param DOM-object
 *
 */
/*
 function add_symbol_counter(obj){
 var objName = obj.attr('name');
 obj.keyup( function (){
 $('.count_signs_'+objName).text('Символов: '+this.value.length);
 });

 if($('.count_signs_'+objName).length <= 0){
 obj.prepend('<div class="count_signs_' + objName + '"></div><br>');
 $('.count_signs_' + objName).insertBefore(obj);
 obj.keyup();
 }
 }

 $(document).ready(function(){
 $('.add_symbol_counter').each(function(){
 add_symbol_counter($(this));
 });

 });

 */

/**
 * mimo84-bootstrap-maxlength plugin usage
 * http://mimo84.github.io/bootstrap-maxlength/
 *
 * adds counter to fields
 *
 * to display counter set data-symbol_counter
 * attribute
 *
 * (uses size="" or maxlength="" attributes)
 */
var set_symbol_counters = function () {

  var max = '';
  var counter_data = 'symbol_counter';
  // for each counted fiels set size attr
  $('[data-' + counter_data + ']').each(function () {
    max = $(this).data(counter_data);
    if (max < 1) max = 10000;
    $(this).attr('size', max);
  });

  $('[data-' + counter_data + ']').maxlength({
    alwaysShow: true,
    placement: 'top',
    preText: 'Символов '
  });
};

function endcountdown() {
  var countdown = $(this);
  var order_id = countdown.data('o_id');
  var alarm_id = countdown.data('alarm_id');
  var text = countdown.data('text');

  $('#btn-timer-' + order_id + '.text-info').removeClass('text-info');
  countdown.countdown('destroy');

  if (order_id == 0) {
    sound('alarm.mp3', 2);
    $("html, body").animate({scrollTop: 0}, "quick");
    var $alerts = $('#wrapper>.content').find('.alerts');
    if ($alerts.length == 0) {
      $('#wrapper>.content').prepend('<div class="alerts col-sm-12"></div>');
    }
    $('#wrapper>.content >.alerts').prepend('<div class="alert alert-danger alert-clock">' +
      '<button type="button" class="close close_alarm" data-alarm_id="' + alarm_id + '" data-dismiss="alert">×</button>' + text + '</div>');
  }
}

function closecountdown() {
  $('.alarm-timer.is-countdown').countdown('destroy');
  $('.btn-timer.text-info').removeClass('text-info');
}

function startcountdown(msg) {
  var countdowns = $('.alarm-timer');

  if (countdowns.length > 0 && typeof msg == 'object') {
    $.each(msg, function (index, value) {

      var countdown = $('#alarm-timer-' + index);
      var date = value.date;

      if (date > 0 && countdown.length > 0) {

        countdown.attr('data-text', value.text);
        countdown.attr('data-alarm_id', value.id);
        $('#btn-timer-' + index + ':not(.text-info)').addClass('text-info');

        countdown.countdown({
          compact: true,
          onExpiry: endcountdown,
          until: new Date(date * 1000)
        });
      }
    });
  }
}

var global_check_mess;
function check_mess(last_time_query) {

  $(document).ready(function () {

    $.ajax({
      url: prefix + 'messages.php?last_seconds=' + last_time_query + location.search.replace('?', '&'),
      type: 'POST',
      data: 'act=global-ajax',
      success: function (msg) {
        var item;

        if (msg) {

          if (typeof msg['alarms'] != 'undefined') {
            startcountdown(msg['alarms'] ? msg['alarms'] : null);
          }

          if (typeof msg['counts'] != 'undefined') {
            $('.tab_count').addClass('hide');
            for (var key in msg['counts']) {
              item = $('span.' + key);
              if (item) {
                item.removeClass('hide').html(msg['counts'][key]);
              }
            }
          }


          if (typeof msg['error'] != 'undefined') {
            alert(msg['message']);
            if (typeof msg['reload'] != 'undefined') {
              location.reload();
            }
          }

          //if (msg['messages'] && msg['messages']['new_count'] > 0) {
          if (msg['new_comments'] && msg['new_comments'] > 0) {
            var n = noty({
              text: 'Новое сообщение',
              timeout: 3000,
              type: 'alert',
              dismissQueue: true,
              layout: 'topRight',
              theme: 'defaultTheme'
            });

            /*$('.messages-block').prepend(msg['messages']['html']);
             $('#new-count-mess').html(msg['messages']['i_count']);
             $('#count-mess').html(msg['messages']['count']);*/

            sound('mess.mp3');
          }
          if (msg['flash']) {
            $('.flash-messages').remove();
            $('body').append(msg['flash']);
          }
          //if (module == 'orders' && msg['new_orders'] > 0) click_tab_hash();
        }

        clearTimeout(global_check_mess);
        global_check_mess = setTimeout(check_mess, 15000, Math.round(new Date().getTime() / 1000));
      }
    });
  });
}

$(document).ready(function () {

  $(document).on('click', '.close_alarm', function () {
    $.ajax({
      url: prefix + 'messages.php',
      type: 'POST',
      data: 'act=close-alarm&id=' + $(this).data('alarm_id')
    });
  });

  $('.btn-timer').live('mouseover', function () {
    var order_id = $(this).data('o_id');
    $(this).attr('title', $('#alarm-timer-' + order_id + ' span').html());
  });

  //reset_tagsinput();
  $(set_symbol_counters);

  clearTimeout(global_check_mess);
  global_check_mess = setTimeout(check_mess, 100, Math.round(new Date().getTime() / 1000));

  $('#show-more-massages').click(function () {
    $.ajax({
      url: prefix + 'messages.php',
      type: 'POST',
      data: 'act=get-messages&skip=' + $('.messages-block > div').length,
      success: function (msg) {
        if (msg) {
          if (msg['html'])
            $('.messages-block').append(msg['html']);
          if (msg['count'] == 0)
            $('#show-more-massages').remove();
        }
      }
    });
  });

//    introJs().start();

});

function parseDate(value) {
  // parse date 24.12.2009
  var tmp = value.split(".");
  return {day: tmp[0], month: tmp[1], year: tmp[2]};
}

function read_mess(_this, mess_id) {
  $.ajax({
    url: prefix + 'messages.php?act=read-message',
    type: 'POST',
    data: 'mess_id=' + mess_id,
    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && msg['msg']) {
          alert(msg['msg']);
        }
        if (msg['state'] == true) {
          $(_this).addClass('muted');
          $(_this).attr('onClick', '');
          $(_this).parent().addClass('panel-white');
        }
        $('.new-count-mess').html(msg['qty']);
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function remove_message(_this, mess_id, type) {
  //var all = mess_id == 'all' ? true : false;

  $.ajax({
    url: prefix + 'messages.php?act=remove-message',
    type: 'POST',
    data: 'mess_id=' + mess_id + '&type=' + type,
    success: function (msg) {
      if (msg) {
        if (msg['state'] == false && msg['msg']) {
          alert(msg['msg']);
        }
        if (msg['state'] == true) {
          if (mess_id == 'all') {
            $('#accordion-messages').html('Сообщений нет');
          } else {
            $(_this).parents('.panel').remove();
          }
        }
        $('.new-count-mess').html(msg['qty']);
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function getURLParameter(name) {
  return decodeURI(
    (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search) || [, null])[1]
  );
}

function isNumberKey(e, _this) {
  //alert(e.keyCode)
  // Allow: backspace, delete, tab, escape, enter and
  if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
    // Allow: Ctrl+A
    (e.keyCode == 65 && e.ctrlKey === true) ||
    // Allow: Ctrl+C
    (e.keyCode == 67 && e.ctrlKey === true) ||
    // Allow: Ctrl+X
    (e.keyCode == 88 && e.ctrlKey === true) ||
    // Allow: Ctrl+V
    (e.keyCode == 86 && e.ctrlKey === true) ||
    // Allow: home, end, left, right
    (e.keyCode >= 35 && e.keyCode <= 39)
  ) {
    // let it happen, don't do anything
    return;
  }

  if (e.keyCode == 190 && _this) {
    return;
  }

  // Ensure that it is a number and stop the keypress
  if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
    e.preventDefault();
  }
}

/*function isNumberKey(e, _this)
 {
 var charCode = (evt.which) ? evt.which : evt.keyCode

 if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46 && charCode != 44
 && charCode != 37 && charCode != 38 && charCode != 39 && charCode != 40 && charCode != 8)
 return false;

 if (charCode == 44) {
 if (_this) {
 var str = $(_this).val();
 //evt.preventDefault();

 //if (theEvent.preventDefault) theEvent.preventDefault();
 if (theEvent.preventDefault) theEvent.preventDefault();

 if ( str.indexOf(".") === -1) {
 $(_this).val(str + '.');
 }
 }

 return false;
 }

 if (charCode == 46) {
 if (_this) {
 var str = $(_this).val();
 if ( str.indexOf(".") === -1) {
 return true;
 }
 }

 return false;
 }

 return true;
 }*/

function arrequest() {
  var url = window.location.toString();
  var arr = url.split(prefix.toString())[1];

  arr = arr.split('#')[0];
  var url_get = arr.split('?')[1];
  arr = arr.split('?')[0];
  var arrequest = arr.split('/');
  arrequest['get'] = url_get;

  return arrequest;
}

function hash_link() {

  $('.hash_link').click(function () {
    window.location.href = $(this).attr('href');
    window.location.reload();
  });

  window.scrollTo(0, 0);
}


function click_tab(_this, e, hashs) {

  //$.fn.ElementNavigate(_this);

  closecountdown();

  if ($(_this).parent().hasClass('disabled') || formdata_original == false) {
    if ($(_this).parent().hasClass('disabled')) {
      e.stopImmediatePropagation();
      //e.preventDefault(e);
    }
    return;
  }
  $('a.click_tab').parent('li:not(.disabled)').addClass('disabled');

  var html = _this.innerHTML;
  $($(_this).attr('href')).html('');
  $(_this).button('loading').html(html);

  var hash = $(_this).attr('href');
  var all_hash = window.location.hash;

  //if (!$(_this).hasClass('unhash'))
  window.location.hash = hash;

  $.ajax({
    url: prefix + module + '/ajax/' + arrequest()[2] + '?act=tab-load&' + arrequest()['get'],
    type: 'POST',
    data: '&tab=' + $(_this).data('open_tab') + '&hashs=' + all_hash,
    beforeSend: function () {
      if ($("#loadingbar").length === 0) {
        $("body").append("<div id='loadingbar'></div>")
        $("#loadingbar").addClass("waiting").append($("<dt/><dd/>"));
        $("#loadingbar").width((50 + Math.random() * 30) + "%");
      }
    }
  }).done(function (msg) {
    if (msg) {
      // if (typeof(msg) == 'string') {
      //   window.location.hash = '';
      //   window.location.reload();
      //   return;
      // }
      if (msg['message']) {
        alert(msg['message']);
      }
      if (msg['state'] == true && msg['html']) {
        $($(_this).attr('href')).html(msg['html']);

        $(_this).closest('ul').find('li.active').removeClass('active').children('.active').removeClass('active');
        $(_this).parent('li').addClass('active');
        $(_this).addClass('active');
        $('div.pill-content > div.active').removeClass('active');
        $('div.pill-content > div' + $(_this).attr('href')).addClass('active');
      }
      if (msg['state'] == true && msg['reload']) {
        console.log(msg);
        window.location = msg['reload'];
      }
    }
    $(_this).button('reset');
    $('a.click_tab').parent('li.disabled').removeClass('disabled');

    if (msg) {
      if (msg['menu']) {
        var str = window.location.hash.split('-');
//                console.log(str[0] + '-menu');
        $(str[0] + '-menu').html(msg['menu']);
      }
      if (msg['functions'] && msg['functions'].length > 0) {
        for (i in msg['functions']) {
          eval(msg['functions'][i]);
        }
      }
    }
    $("#loadingbar").width("101%").delay(200).fadeOut(400, function () {
      $(this).remove();
    });

    hash_link();

    load_infoblock(hash);
    clearTimeout(global_check_mess);
    global_check_mess = setTimeout(check_mess, 100, Math.round(new Date().getTime() / 1000));

    $('.focusin').focus();
    $('[data-toggle="tooltip"]').tooltip();
    infopopovers();
    hide_flashmessages();
    init_input_masks();
  });
}

function click_tab_hash(hash) {
  var href = hash ? hash : window.location.hash;

  if (href) {
    $('a.click_tab[href="' + href + '"]').click();
  } else {
    window.location.reload()
  }
}

function is_enter(_this, e, id, func) {
  if (e.keyCode == 13) {
    window[func](_this, id);
  }
}

function init_multiselect($form, onInitCallback, onChangeCallback) {
  var $multiselect;
  if (typeof $form === 'undefined') {
    $multiselect = $('.multiselect');
  } else {
    $multiselect = $form.find('.multiselect');
  }
  setTimeout(function () {
    $multiselect.each(function () {
      var $this = $(this),
        opts = multiselect_options;
      if (typeof $this.attr('data-numberDisplayed') !== 'undefined') {
        opts.numberDisplayed = $this.attr('data-numberDisplayed');
      }
      if (typeof onChangeCallback !== 'undefined') {
        opts.onChange = onChangeCallback;
      }
      if (typeof onInitCallback !== 'undefined') {
        opts.onInitialized = onInitCallback;
      }

      if (typeof $this.attr('data-nonSelectedText') !== 'undefined') {
        opts.nonSelectedText = $this.attr('data-nonSelectedText');
      }

      if (typeof $this.attr('data-buttonWidth') !== 'undefined') {
        opts.buttonWidth = $this.attr('data-buttonWidth');
      }

      if (typeof $this.attr('data-buttonClass') !== 'undefined') {
        opts.buttonClass = $this.attr('data-buttonClass');
      }

      $this.multiselect(opts);
    });
  }, 0);
}

function reset_multiselect($form, onInitCallback, onChangeCallback) {
  var $multiselect;
  if (typeof $form === 'undefined') {
    $multiselect = $('.multiselect');
  } else {
    $multiselect = $form.find('.multiselect');
  }
  $multiselect.multiselect('destroy');

  init_multiselect($form, onInitCallback, onChangeCallback);
}

function close_alert_box() {
  $('.bootbox .modal-footer').find('button[data-bb-handler="ok"]').click();
}

function alert_box(_this, content, ajax_act, data, callback, url, e, in_place) {
  if (typeof in_place == 'underfined') {
    in_place = false;
  }

  if (e) {
    e.stopPropagation();
  }

  if (($(_this).hasClass('disabled') || $(_this).prop('disabled'))) {
    if (!$(_this).data('alert_box_not_disabled')) {
      return false;
    }
  }

  if ($(_this).is('input') || $(_this).is('button'))
    $(_this).button('loading');
  else
    $(_this).addClass('disabled');

  bootbox.addLocale('ru', {
    OK: L['cansel'],
    CANCEL: L['cansel'],
    CONFIRM: L['confirm']
  });
  bootbox.setDefaults({
    /**
     * @optional String
     * @default: en
     * which locale settings to use to translate the three
     * standard button labels: OK, CONFIRM, CANCEL
     */
    locale: "ru",

    /**
     * @optional Boolean
     * @default: true
     * whether the dialog should be shown immediately
     */
    show: true,

    /**
     * @optional Boolean
     * @default: true
     * whether the dialog should be have a backdrop or not
     */
    backdrop: true,

    /**
     * @optional Boolean
     * @default: true
     * show a close button
     */
    closeButton: true,

    /**
     * @optional Boolean
     * @default: true
     * animate the dialog in and out (not supported in < IE 10)
     */
    animate: true,

    /**
     * @optional String
     * @default: null
     * an additional class to apply to the dialog wrapper
     */
    className: "my-modal"
  });

  if (content) {
    close_alert_box();
    $('.bootbox.bootbox-alert').remove();
    $('.modal-backdrop').remove();

    var btns;
    if (typeof content !== 'string') {
      btns = content['btns'];
      content = content['content'];
    }
    bootbox.alert(content);
    if (btns) {
      $('.bootbox-alert .modal-footer').prepend(btns);
    }
  } else if (ajax_act) {
    if (typeof data !== 'object')
      data = {};

    if (typeof $(_this).data('o_id') !== 'undefined')
      data['object_id'] = $(_this).data('o_id');

    $.ajax({
      url: prefix + (url ? url : module + '/ajax/' + arrequest()[2]) + '?act=' + ajax_act + '&show=modal',
      type: 'POST',
      data: data,
      success: function (msg) {
        if (!in_place) {
          $('.bootbox.bootbox-alert').remove();
          $('.modal-backdrop').remove();
        }


        if (msg) {
          if (msg['state'] == true && msg['content']) {
            var dialog = bootbox.alert(msg['content'], function () {
              if ($('#modal-dialog').is(':visible')) {
                var $div = $('<div class="modal-backdrop fade in"></div>');
                $('body').append($div);
                setTimeout(function () {
                  $('#modal-dialog').css('overflow', 'auto');
                  $('#modal-dialog').css('display', 'block');
                }, 10);
              }
            });

            if (msg['no-cancel-button']) {
              $(dialog).find('.modal-footer').html('');
            }
            if (msg['btns']) {
              $(dialog).find('.modal-footer').prepend(msg['btns']);
            }
          } else {
            if (msg['message']) {
              alert(msg['message']);
            }
          }
          if (msg['functions'] && msg['functions'].length > 0) {
            for (i in msg['functions']) {
              eval(msg['functions'][i]);
            }
          }
        }
        if (msg['width']) {
          $('.bootbox.modal').addClass('bootbox-big');
        }
        $(_this).button('reset');
        return false;
      },
      error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.responseText);
        $(_this).button('reset');
      }
    });

    return false;
  }
}
function dialog_box(_this, title, content, buttons, e, wrapper_class) {
  if (e) {
    e.stopPropagation();
  }

  if (($(_this).hasClass('disabled') || $(_this).prop('disabled')) && !$(_this).data('alert_box_not_disabled'))
    return false;

  if ($(_this).is('input') || $(_this).is('button'))
    $(_this).button('loading');
  else
    $(_this).addClass('disabled');

  bootbox.addLocale('ru', {
    OK: L['cansel'],
    CANCEL: L['cansel'],
    CONFIRM: L['confirm']
  });
  bootbox.setDefaults({
    size: 'large',
    /**
     * @optional String
     * @default: en
     * which locale settings to use to translate the three
     * standard button labels: OK, CONFIRM, CANCEL
     */
    locale: "ru",

    /**
     * @optional Boolean
     * @default: true
     * whether the dialog should be shown immediately
     */
    show: true,

    /**
     * @optional Boolean
     * @default: true
     * whether the dialog should be have a backdrop or not
     */
    backdrop: true,

    /**
     * @optional Boolean
     * @default: true
     * show a close button
     */
    closeButton: false,

    /**
     * @optional Boolean
     * @default: true
     * animate the dialog in and out (not supported in < IE 10)
     */
    animate: true,

    /**
     * @optional String
     * @default: null
     * an additional class to apply to the dialog wrapper
     */
    className: "my-modal"
  });

  if (content) {
    close_alert_box();

    if (!buttons) {
      buttons = {
        success: {
          label: "Success!",
          className: "btn-success",
          callback: function () {
            alert("great success");
          }
        },
        danger: {
          label: "Danger!",
          className: "btn-danger",
          callback: function () {
            alert("uh oh, look out!");
          }
        },
        main: {
          label: "Click ME!",
          className: "btn-primary",
          callback: function () {
            alert("Primary button");
          }
        }
      };
    }
    bootbox.dialog({
      message: content,
      title: title,
      buttons: buttons,
      closeButton: true,
      className: wrapper_class || null
    });
  }
}

function remove_by_id(_this, element_id) {
  $('#' + element_id).remove();
}

function sound(track, time_sec) {
  var audioElement = document.createElement('audio');
  audioElement.setAttribute('src', prefix + track);
  audioElement.setAttribute('autoplay', 'autoplay');
  jQuery.get();
  audioElement.addEventListener("load", function () {
    audioElement.play();
  }, true);
  if (time_sec) {
    setTimeout(function () {
      audioElement.pause();
    }, time_sec * 1000);
  }
}

function display_serial_product(_this, item_id) {
  $(_this).parent().find('small').html('');

  $.ajax({
    url: prefix + 'messages.php?act=get-product-title',
    type: 'POST',
    dataType: "json",

    data: '&item_id=' + item_id,
    success: function (msg) {
      if (msg) {
        /*if (msg['state'] == false) {
         if (msg['message']) {
         alert(msg['message']);
         }
         }*/
        if (msg['msg']) {
          $(_this).parent().find('.product-title').html(msg['msg']);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function move_order(_this, rand, from_sidebar) {
  $(_this).button('loading');

  $.ajax({
    url: prefix + 'messages.php?act=move-order&from_sidebar=true',
    type: 'POST',
    dataType: "json",

    data: $('#moving-item-form-' + rand).serialize(),
    success: function (msg) {
      if (msg) {
        if (msg['state'] == false) {
          if (msg['message']) {
            alert(msg['message']);
          }
          if (msg['location']) {
            window.location.href = msg['location'];
          }
        }

        if (msg['state'] == true || msg['reload'] == true) {
          if (from_sidebar) {
            rightSidebar.noty(msg['message'], 'success');
            rightSidebar.hide();
          } else {
            click_tab_hash();
            close_alert_box();
          }
        }

      }
      $(_this).button('reset');
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
}

function send_get_form(_this) {
  $(_this).button('loading');
  window.location.search = $(_this).parents('form').find(':input[value!=""]').serialize();
  $(_this).button('reset');
}

function change_personal_to(to) {
  $('.js-personal').toggle();
  $('input[name="person"]').val(to);
  return false;
}

function change_warehouse(_this) {
  var suffix = typeof $(_this).data('multi') !== 'undefined' ? $(_this).data('multi') : '';
  var select = $(_this).parents('form').find('select.select-location' + suffix);
  $(select).html('');

  $.ajax({
    url: prefix + 'messages.php?act=get_locations',
    type: 'POST',
    dataType: "json",
    data: 'wh_id=' + $(_this).val(),
    success: function (msg) {
      if (msg) {
        if (msg['html']) {
          $(select).html(msg['html']);
          reset_multiselect();
        }
        if (msg['msg']) {
          alert(msg['msg']);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function icons_marked(_this, object_id, type) {

  if (type == 'oi' || type == 'woi') {
    var icon_active = 'fa fa-bookmark';
    var icon = 'fa fa-bookmark-o';
  } else {
    var icon_active = 'star-marked-active';
    var icon = 'star-marked-unactive';
  }

  var icons_class = icon;
  var icons_class_active = icon_active;

  if ($(_this).hasClass(icon_active) == true) {
    icons_class = icon_active;
    icons_class_active = icon;
  }

  if (object_id && type) {
    $.ajax({
      url: prefix + 'messages.php',
      type: 'POST',
      dataType: "json",

      data: '&object_id=' + object_id + '&type=' + type + '&act=marked-object',
      success: function (msg) {
        if (msg['error']) {
          alert(msg['message']);
        } else {
          if ($(_this).hasClass('star-remove-icons') == true && $(_this).hasClass(icon_active))
            $(_this).parents('.remove-marked-object').remove();

          $(_this).removeClass(icons_class);
          $(_this).addClass(icons_class_active);
          $('#count-marked-' + type).html(msg['count-marked']);
        }
      },
      error: function (xhr, ajaxOptions, thrownError) {
        alert(xhr.responseText);
      }
    });

    return false;
  }
}

function set_cookie(_this, key, value, reload) {
  $.removeCookie(key, {path: prefix});
  $.cookie(key, value, {
    expires: 30, //expires in 10 days
    path: prefix
  });
  if (reload == 1) {
    window.location.href = $.param.querystring(window.location.href, 'p=1');
    window.location.reload();
  }
}
/*
 function reset_tagsinput() {
 $('input.input-tags').tagsinput({
 itemValue: 'id',
 itemText: 'title',
 typeahead: {
 onTagExists: function(item, $tag) {
 $tag.hide.fadeIn();
 },
 //remove : function () {},
 source: function (query, process, element) {
 var table = $(element).data('table');

 return $.ajax({
 url: prefix + 'messages.php',
 type: 'POST',
 data: {act: 'global-tagsinput', query: query, table: table, object: arrequest()[2]},
 dataType: 'json',
 success: function (result) {
 return JSON.stringify(result);
 }
 });
 }
 }
 });

 $('input.input-tags').each(function() {
 var _this = this;
 var value = $(_this).val();
 var arrayValue = value.split('&');

 for (var i = 0; i < arrayValue.length; i++) {
 if (arrayValue[i].length > 0) {
 var key_value = arrayValue[i].split('=');

 if (key_value[0] && key_value[0] > 0 && key_value[1]) {
 $('input.input-tags').tagsinput('add', {id: key_value[0] , title: decodeURIComponent(key_value[1])});
 }
 }
 }
 });
 }*/


$(function () {
  $('#navigation .btn.btn-default').on('click', function () {
    window.location = $(this).attr('data-href');
  });
  function set_events() {
    $('.ajax_form').each(function () {
      var $this = $(this),
        submit_on_blur = $this.data('submit_on_blur');
      if (submit_on_blur) {
        var inputs = submit_on_blur.split(',');
        $.each(inputs, function (k, v) {
          $this.find('[name="' + v + '"]').blur(function (e) {
            var has_values = false;
            $.each(inputs, function (k, v) {
              var $val_el = $this.find('[name="' + v + '"]');
              if ($val_el.hasClass('global-typeahead')) {
                $val_el = $this.find('.typeahead-value-' + $val_el.attr('data-input'));
              }
              var val = $.trim($val_el.val());
              if (val != '' && val != 0) {
                has_values = true;
              }
            });
            if (has_values) {
              $this.submit();
            }
          });
        });
      }
    });
  }

  function ajax_form_event(_this, e) {
    e.preventDefault();
    var $this = $(_this),
      $form = $this.hasClass('ajax_form') ? $this : $this.closest('.ajax_form');
    $form.find(':submit').attr('disabled', true);
    if ($form.hasClass('emulate_form')) {
      var data = $form.closest('form').serialize();
    } else {
      var data = $form.serialize();
    }
    $.ajax({
      url: $form.attr('action') || $form.attr('data-action'),
      type: $form.attr('method') || $form.attr('data-method'),
      data: data,
      dataType: 'json',
      success: function (data) {
        $form.find(':submit').attr('disabled', false);
        if (!data.state) {
          alert(data.msg);
        } else {
          if (data.redirect) {
            window.location = data.redirect;
          }
          var update_val = $form.data('on_success_set_value_for');
          if (update_val) {
            var $input = $form.find('[name="' + update_val + '"]');
            if (!$input.val()) {
              $input.val(data[update_val]);
            }
          }
          if (data.after) {
            $form.after(data.after);
            set_events();
          }
          if ($form.data('callback')) {
            window[$form.data('callback')](data, $form);
          }
          if (data.msg) {
            alert(data.msg);
          }
        }
      }
    });
    return false;
  }

  set_events();
  $('.ajax_form').live('submit', function (e) {
    return ajax_form_event(this, e);
  });
  $('.ajax_form.emulate_form :submit').live('click', function (e) {
    return ajax_form_event(this, e);
  });

  $(document).on('click', '.toggle-hidden', function () {
    var $this = $(this),
      $context_pane = $this.closest('.tab-pane'),
      $context = $context_pane.length ? $context_pane : $this.closest('.toggle-hidden-box'),
      $toggle,
      id = $this.attr('data-toggle')
      ;

    if (id.indexOf('.') == -1 && id.indexOf('#') == -1) {
      id = '#' + id;
    }
    $toggle = $(id, $context);
    if (!$toggle.length) {
      $toggle = $(id, $context.find('.pill-pane.active'));
    }

    if ($toggle.hasClass('hidden')) {
      $toggle.removeClass('hidden');
      $this.addClass('active');
    } else {
      $this.removeClass('active');
      $toggle.addClass('hidden');
    }
  });

  $('.module_submenu_click_tab_event').click(function (e) {
    var url = $(this).attr('data-url');

    if (typeof url != 'undefined' && url.length > 0) {
      window.location = url;
      window.location.reload();
      return;
    }
    var $menu = $('a[href="' + $(this).data('href') + '"]');

    if ($menu.length) {
      e.preventDefault();
      $menu.click();
    }
  });

  var $glossary = $('#glossary');
  var $glossary_alpha = $('#glossary_alpha');
  var $glossary_content = $('#glossary_content');
  $('#show_glossary').click(function () {
    if (!$glossary.hasClass('loaded')) {
      $glossary_content.html('<iframe src="' + $glossary.data('url') + '"></iframe>');
      $glossary.addClass('loaded');
    }
    $glossary_alpha.toggle();
    $glossary.toggle();
    if ($glossary.is(':hidden') && $.cookie('show_intro')) {
      $.removeCookie('show_intro', {path: prefix});
      $('#show_glossary').tooltip({
        placement: 'left',
        trigger: 'click'
      }).tooltip('show');
      $(window).scroll(function () {
        $('#show_glossary').tooltip('destroy');
      });
    }
  }).mouseenter(function () {
    $('#show_glossary').tooltip('destroy');
  }).mousedown(function () {
    $('#show_glossary').tooltip('destroy');
  });
  $('#glossary_close').click(function () {
    $('#show_glossary').click();
  });
  if ($.cookie('show_intro')) {
    $('#show_glossary').click();
  }


  var zadarma_button_call_consultant = $('#zadarma_button_call_consultant');
  zadarma_button_call_consultant.mouseenter(function () {
    $(this).css('width', '194px');
  });
  zadarma_button_call_consultant.mouseout(function () {
    $(this).css('width', '57px');

  });


  $(document).on('click', function (e) {
    if (!$glossary.is(':visible')) return;
    var $this = $(e.target);
    if (!$this.closest('#glossary').length
      && $this.attr('id') != 'glossary'
      && $this.attr('id') != 'show_glossary'
      && !$this.closest('#show_glossary').length) {
      $('#show_glossary').click();
    }
  });

  $('.set_manage_lang').click(function () {
    $.cookie('manage_lang', $(this).data('lang'), {expires: 365});
    window.location.reload(true);
  });

  $(document).on('click', '.toggle_btn', function () {
    var id = $(this).data('id'),
      $id = $('#' + id);
    $('.js-dummy_user_more_data').toggle();
    $id.stop(true).slideToggle(200, function () {
      if ($id.is(':visible')) {
        $.cookie(id, 1, {expires: 365, path: prefix});
      } else {
        $.removeCookie(id, {path: prefix});
      }
    });
  });

  $(window).load(hide_flashmessages);

  init_input_masks();
  $('#print_now').on('click', function () {
    return print_now(this);
  });
});

function print_now(_this) {
  var $checks = $(_this).closest('ul').find(':checked');
  $checks.each(function () {
    window_open($(this).val());
  });
  return false;
}
function print_label(_this) {
  var $checks = $(_this).closest('ul').find(':checked'), ids = [];

  $.each($('input.js-selected-item:checkbox:checked'), function (index, value) {
    ids.push($(value).data('id'));
  });
  if (ids.length > 0) {
    $checks.each(function () {
      window_open($(this).val() + ids.join(','));
    });
  } else {
    alert('Выберите номенклатуру')
  }

  return false;
}
function print_now_from_orders(_this) {
  var $checks = $('ul.print_menu').find(':checked');
  $checks.each(function () {
    window_open($(this).val());
  });
  return false;
}

function init_input_masks() {
  var $els = $('[data-phone_mask]');
  $els.each(function () {
    var $this = $(this),
      mask = $this.data('phone_mask');
    $.mask.definitions['z'] = "[1-9]";
    $.mask.definitions['9'] = "";
    $.mask.definitions['d'] = "[0-9]";
    $this.mask(mask, {});
  });
}

var flashmessages_hide_timeout = 0;
function hide_flashmessages() {
  clearTimeout(flashmessages_hide_timeout);
  flashmessages_hide_timeout = setTimeout(function () {
    $('.flashmessage-alert').alert('close');
  }, 3000);
}

function toogle_siblings(_this, btn_children, multiselect_capability) {
  if (typeof multiselect_capability == 'undefined') {
    multiselect_capability = false;
  }

  if (multiselect_capability) {
    var hiddens = $(_this).siblings('input[type="text"]:hidden, select:hidden + .btn-group:hidden, textarea:hidden');
    var shows = $(_this).siblings('input[type="text"]:visible, select:hidden + .btn-group:visible, textarea:visible');
  } else {
    var hiddens = $(_this).siblings('input[type="text"]:hidden, select:hidden, textarea:hidden');
    var shows = $(_this).siblings('input[type="text"]:visible, select:visible, textarea:visible');
  }

  hiddens.show();
  shows.hide();
  if (btn_children) {
    var children = $(_this).children().children();
  } else {
    var children = $(_this).children();
  }
  if (children) {
    if (children.attr('class') == 'fa fa-keyboard-o') {
      children.attr('class', 'fa fa-caret-square-o-down');
    } else {
      children.attr('class', 'fa fa-keyboard-o');
    }
  }
}

var window_open_msgs_timeout;
var window_open_msg_lock = false;
function window_open(url) {
  if (!window.open(url) && typeof L.window_open_error_msg != 'undefined') {
    if (!window_open_msg_lock) {
      alert(L.window_open_error_msg);
      window_open_msg_lock = true;
      clearTimeout(window_open_msgs_timeout);
      window_open_msgs_timeout = setTimeout(function () {
        window_open_msg_lock = false;
      }, 100);
    }
  }
}


function infopopovers() {
  init_popover($('.infopopover_onload'), true);
  init_popover($('[data-infopopoveronhover]'), false, 'hover');
  $('.infopopover_onload').each(function () {
    $(this).popover('show');
  });
}
function init_popover($els, add_close_btn, trigger_event) {
  var add_close = add_close_btn || false;
  var trigger = trigger_event || 'manual';
  $els.popover({
    placement: 'auto right',
    trigger: trigger,
    html: true,
    template: '<div class="popover infopopover' + (add_close ? ' has_close_btn' : '') + '" role="tooltip">' +
    '<div class="arrow"></div><div class="popover-body clearfix"><h3 class="popover-title"></h3>' +
    '<div class="popover-content"></div>' +
    (add_close ? '<i class="infopopover-close fa fa-times"></i>' : '') +
    '</div></div>'
  });
}
function show_infopopover_modal(modal_html) {
  if (modal_html) {
    bootbox.addLocale('ru', {
      OK: 'Ok',
      CANCEL: 'Ok',
      CONFIRM: L['confirm']
    });
    bootbox.alert(modal_html);

  }
}
(function ($, document) {

  $(function () {

    $(document).on('click', '.infopopover_onclick', function (e) {
//      console.log('test');
      e.stopPropagation();
      var $this = $(this);
      if (!$this.hasClass('hasPopover')) {
        init_popover($this);
        $this.addClass('hasPopover');
      }
      $this.popover('toggle');
    });

    $(document).on('click', '.infopopover-close', function (e) {
      e.stopPropagation();
      var $this = $(this).parents('.popover'),
        $origin = $this.siblings('.infopopover_onetime'),
        info_var = $origin.attr('data-id');
      $this.popover('hide');
      $.ajax({
        url: prefix + 'messages.php?act=hide-infopopover',
        type: 'POST',
        data: 'id=' + info_var,
        dataType: 'json',
        success: function (result) {
        }
      });
    });

    $(document).on('change', ':checkbox[name="infopopover_modal_confirm"]', function (e) {
      e.stopPropagation();
      var $this = $(this),
        state = $this.is(":checked"),
        info_var = $this.attr('data-id');
      $this.popover('hide');
      $this.attr('disabled', true);
      $.ajax({
        url: prefix + 'messages.php?act=hide-toggle-infopopover&state=' + (state ? 1 : 0),
        type: 'POST',
        data: 'id=' + info_var,
        dataType: 'json',
        success: function (result) {
          $this.attr('disabled', false)
        }
      });
    });

    $('html').on('click', function (e) {
      if (!$(e.target).closest('.infopopover').length && !$(e.target).hasClass('infopopover_onclick')) {
        $('.infopopover:not(.has_close_btn)').popover('hide');
      }
    });

    infopopovers();
  });

})(jQuery, document);

function create_transaction_for(type, _this, conf) {
  var is_modal = $('#order-form input[name="is_modal"]').val();
  var order_id = $('#order-form input[name="order_id"]').val();

  $(_this).button('loading');

  $.ajax({
    url: prefix + '/accountings/ajax/?act=create-transaction-' + type,
    dataType: "json",
    data: $('#transaction_form').serialize() + (conf.issued ? '&issued=1' : ''),
    type: 'POST',
    success: function (data) {
      open_print_forms();
      if (data) {
        if (data['state'] == true) {
          if (is_modal) {
            edit_order_dialog_by_order_id(order_id, 'display-order');
            var $div = $('<div class="modal-backdrop fade in"></div>');
            $('body').append($div);
            setTimeout(function () {
              $('#modal-dialog').css('overflow', 'auto');
              $('#modal-dialog').css('display', 'block');
            }, 10);
          } else {
            location.reload();
          }
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
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

  return false;
}

function open_print_forms() {
  var $selectPrintForm = $('.select-print-form').first(),
    order_id = $('#order_id').val(),
    url;

  $.each($selectPrintForm.find('ul.print_menu input[name="print[]"]'), function (ind, value) {
    if ($(value).is(':checked')) {
      url = prefix + 'print.php?act=' + $(value).val() + '&object_id=' + order_id;
      var w = window.open(url, '_blank');
      if (!w && typeof L.window_open_error_msg != 'undefined') {
        alert(L.window_open_error_msg);
      }
    }
  });
}

function create_transaction(_this, conf) {
  var is_modal = $('#order-form input[name="is_modal"]').val();
  var order_id = $('#order-form input[name="order_id"]').val();

  $(_this).button('loading');
  $.ajax({
    url: prefix + 'accountings/ajax/?act=create-transaction',
    dataType: "json",
    data: $('#transaction_form').serialize() + (conf == 1 ? '&confirm=1' : ''),
    type: 'POST',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          if (is_modal) {
            edit_order_dialog_by_order_id(order_id, 'display-order');
            var $div = $('<div class="modal-backdrop fade in"></div>');
            $('body').append($div);
            setTimeout(function () {
              $('#modal-dialog').css('overflow', 'auto');
              $('#modal-dialog').css('display', 'block');
            }, 10);
          } else {
            location.reload();
          }
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
function recalculate_amount_pay(_this) {
  var $parent = $('#transaction_form').first(),
    discount = $parent.find('input[name="discount"]').first().val() || 0,
    discount_type = $('#pay_for_repair_discount_type').val(),
    amount = $parent.find('input#amount_without_discount').first().val(),
    result;

  if (discount_type == 1) {
    result = amount * (1 - discount / 100);
  } else {
    result = amount - discount;
  }
  $parent.find('#amount-with-discount').first().val(Math.round(result * 100) / 100);
}


function give_without_pay(type, _this, order_id) {
  var is_modal = $('#order-form input[name="is_modal"]').val();
  var $div = $('<div class="modal-backdrop fade in"></div>');
  $(_this).button('loading');

  $.ajax({
    url: prefix + '/orders/ajax/?act=issued-order',
    dataType: "json",
    data: {order_id: (order_id ? order_id : $('#order_id').val())},
    type: 'POST',
    success: function (data) {
      open_print_forms();
      if (data) {
        if (data['state'] == true) {
          if (is_modal) {
            edit_order_dialog_by_order_id(order_id, 'display-order');
            $('body').append($div);
            setTimeout(function () {
              $('#modal-dialog').css('overflow', 'auto');
              $('#modal-dialog').css('display', 'block');
            }, 10);
          } else {
            location.reload();
          }
        } else {
          alert(data['msg']);
          if (is_modal) {
            $('body').append($div);
            setTimeout(function () {
              $('#modal-dialog').css('overflow', 'auto');
              $('#modal-dialog').css('display', 'block');
            }, 10);
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

function edit_order_dialog_by_order_id(order_id, tab) {
  $.ajax({
    url: prefix + module + '/ajax?act=' + tab + '&show=modal',
    type: 'POST',
    data: {object_id: order_id},
    success: function (msg) {
      if (msg.content) {
        $('#modal-dialog').html(msg.content).modal('show');
        $('#modal-dialog').on('hidden.bs.modal', function (e) {
          location.href = prefix + module + '#orders_manager';
          location.reload();
        });
      } else {
        alert(msg.msg);
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });

}
function select_discount_type(_this) {
  var cashbox = parseInt($(_this).attr('data-discount_type'));
  $('input[name="discount_type"]').val(cashbox);
  $('.btn-title-discount_type').html($(_this).html());
  sum_calculate();
  return false;
}

function sum_calculate() {
  var
    price = parseFloat($('#eshop_sale_poduct_cost').val()).toFixed(2) || 0,
    discount = parseInt($('#eshop_sale_poduct_discount').val()) || 0,
    quantity = parseInt($('#eshop_sale_poduct_quantity').val()) || 0,
    sum = 0;

  if ($('input[name="discount_type"]').val() == 1) {
    sum = price * (1 - discount / 100) * quantity;
  } else {
    sum = (price - discount) * quantity;
  }
  $('#eshop_sale_poduct_sum').val(Math.round(sum * 100) / 100);
}
function add_supplier_item_to_table() {
  var $row = $('tr.js-supplier-row-cloning'),
    cost = $('#supplier_product_cost').val() || 0,
    quantity = $('#supplier_product_quantity').val() || 0,
    title = $('#suppliers-order-form input[name="goods-goods-value"]').val(),
    id = $('#suppliers-order-form input[name="goods-goods"]').val(),
    rnd = parseInt(Math.random() * 1000),
    so_co = $('input[name="new_so_co"]').val() || '';

  if (cost <= 0) {
    $('#supplier_product_cost').val('');
  }
  if (quantity <= 0) {
    $('#supplier_product_quantity').val('');
  }
  if (false === $('#js-add-product-form').parsley().validate()) {
    return;
  }

  if (cost > 0 && title.length > 0 && id.length > 0 && quantity > 0) {
    $clone = $row.clone().removeClass('js-supplier-row-cloning');
    $clone.addClass('row-item');
    $clone.find('.js-supplier-item-name').first().val(title).attr('title', title).attr('data-required', true);
    $clone.find('input.js-supplier-item-id').first().val(id).attr('name', 'item_ids[' + rnd + ']');
    $clone.find('.js-supplier-price').first().val(cost).attr('name', 'amount[' + rnd + ']').attr('data-required', true);
    $clone.find('.js-supplier-quantity').first().val(quantity).attr('name', 'quantity[' + rnd + ']').attr('data-required', true);
    $clone.find('.js-supplier-order_numbers').first().val(so_co).attr('name', 'so_co[' + rnd + ']');

    $('#supplier_product_cost').val('');
    $('#supplier_product_quantity').val('');
    $('input[name="goods-goods-value"]').val('');
    $('input[name="goods-goods"]').val('');
    $('input[name="new_so_co"]').val('');
    $('input.js-sum').val('');
    $clone.show();
    $row.parent().append($clone);
    $('table.supplier-table-items').show();
    recalculate_amount_supplier();
    $('#js-create-button').show();
  }
  return false;
}

function recalculate_amount_supplier() {
  var total = parseFloat(0),
    $body = $('.supplier-table-items > tbody'),
    count = 0;

  $body.children('tr.row-item').each(function () {
    var $row = $(this),
      quantity = parseInt($row.find('.js-supplier-quantity').first().val()) || 0,
      price = parseFloat($row.find('.js-supplier-price').first().val()).toFixed(2) || 0;

    $row.find('.js-supplier-sum').first().val(Math.round(price * quantity * 100) / 100);
    n = parseFloat($row.find('.js-supplier-sum').first().val());
    total = total + n;
    count++;
  });
  total = total.toFixed(2);
  if (total == 0) {
    if ($body.find('tr').length <= 1) {
      $body.parent().hide();
    }
    $('input[name="serials-value"]').attr('data-required', 'true');
  }
  $('.js-supplier-total').val(Math.round(total * 100) / 100);
  if (count) {
    $('.js-create-purchase-invoice').show();
  } else {
    $('.js-create-purchase-invoice').hide();
  }
  if (count < 2) {
    $('.js-form').removeClass('js-scroll-form');
  } else {
    $('.js-form').addClass('js-scroll-form');
  }
}


function remove_supplier_row(_this) {
  $(_this).parent().parent().remove();
  recalculate_amount_supplier();
  return false;
}

function recalculate_product_sum(_this) {
  var price = parseFloat($('input[name="warehouse-order-price"]').val()).toFixed(2) || 0,
    quantity = parseInt($('input[name="warehouse-order-count"]').val()) || 0;
  $('input.js-sum').val(Math.round(price * quantity * 100) / 100);
}

function add_comma(_this) {
  var $field = $(_this).parents('td').find('.js-so_co').first(),
    val = $field.val();
  if (val.length > 0 && val.slice(-1) != ',') {
    $field.val(val + ',');
  }
  return false;
}
function show_category_addition_info(_this) {
  var id = $('input[name="categories-goods"]').val(),
    buttons = {
      success: {
        label: "Сохранить",
        className: "btn-success",
        callback: function () {
          $.ajax({
            url: prefix + 'categories/ajax?act=change-info',
            dataType: "json",
            type: 'POST',
            data: $('form#category-addition-info').serialize(),
            success: function (data) {
              alert(data['message']);
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
    url: prefix + 'categories/ajax?act=change-info-form&category_id=' + id,
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
function toggle_on_click(_this, event) {
  $('.print_menu').toggle();
  $(_this).dropdown('toggle');
  event.stopPropagation();
}
function goto_delete_all(_this, confirm_msg) {
  if (confirm(confirm_msg)) {
    var parts = location.href.split('?');
    // _url = location.href;
    // _url += (_url.split('?')[1] ? '&':'?') + 'delete-all=';
    location.href = parts[0] + '?delete-all=' + (parts[1] ? '&' + parts[1] : '');
  }
  return false;
}