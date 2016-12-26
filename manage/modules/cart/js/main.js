
//lopushansky edit
//отследить событие нажатие корзины
//при нажатии поменять цвет карзины и верхнего поля
//добавить циферку которая будет увеличиваться с каждым добавлением товара этой позиции в корзину

function add_to_cart(id,_this) {

  
//Взять колличество этого товара из корзины по id
var count=_this.find('span').html();
if(count==0){
count=1;
  _this.find('span').html(1);
  _this.find('span').css('display','block')
}
  $.ajax({
    url: prefix + 'cart/ajax?act=add-to-cart&id=' + id,
    dataType: "json",
    data:{id:id},
    type: 'POST',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          $('#cart-quantity').html(data['quantity']);
        }
        if (data['state'] == false && data['message']) {
          alert(data['message']);
        }
      location.reload()
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
  return false;
}



function show_cart() {

  var buttons = {
    sale: {
      label: '<i class="fa fa-plus-circle" aria-hidden="true"></i> ' + (L['sale'] || 'sale'),
      className: "btn-success",
      callback: function () {
        $.ajax({
          url: prefix + 'cart/sale',
          dataType: "json",
          type: 'POST',
          data: $('form#cart-form').serialize(),
          success: function (data) {
            if (data.state && data.redirect) {
              window.location.href = data.redirect;
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

      }
    },
    purchase: {
      label: '<i class="fa fa-plus-circle" aria-hidden="true"></i> ' + (L['purchase'] || 'purchase'),
      className: "btn-success",
      callback: function () {
        $.ajax({
          url: prefix + 'cart/purchase',
          dataType: "json",
          type: 'POST',
          data: $('form#cart-form').serialize(),
          success: function (data) {

            if (data.state && data.redirect) {
              window.location.href = data.redirect;
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

      }
    },
    clear: {
      label: L['clear'],
      className: "btn-default",
      callback: function () {
        $.ajax({
          url: prefix + 'cart/ajax?act=clear-cart',
          dataType: "json",
          type: 'POST',
          data: {},
          success: function (data) {
            if (data.state) {
              $('#cart-quantity').html(data['quantity']);
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

      }
    },
  };
  $.ajax({
    url: prefix + 'cart/ajax?act=show-cart',
    dataType: "json",
    type: 'GET',
    success: function (data) {

      if (data) {
        if (data['state'] == true) {
          dialog_box(this, data['title'], data['content'], buttons, null, 'ml-dialog');
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
function delete_from_cart(_this, id) {
  $.ajax({
    url: prefix + 'cart/ajax?act=remove-from-cart&id=' + id,
    dataType: "json",
    type: 'GET',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          $('#cart-quantity').html(data['quantity']);
          $(_this).parents('tr').hide();
        }
        if (data['state'] == false && data['message']) {
          alert(data['message']);
        }
        location.reload();
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
  return false;
}
function cart_select_price_type(_this) {
  var type = $(_this).attr('data-price_type');
  $('input[name="price_type"]').val(type);
  $('.btn-title-price_type').html($(_this).attr('data-title'));
  $('.js-price').hide();
  if (type == 1) {
    $('.js-price-sale').show();
  }
  if (type == 2) {
    $('.js-price-wholesale').show();
  }
  if (type == 3) {
    $('.js-price-purchase').show();
  }
  recalculate_cart_sum();
}
function recalculate_cart_sum() {

  var $body = $('.cart-items > tbody');
  $body.children('tr').each(function () {
    var $row = $(this),
      quantity = parseInt($row.find('.quantity').first().val()),
      price = parseFloat($row.find('.js-price-sale').first().html()).toFixed(2),
      type = $('input[name="price_type"]').val();
    if (type == 2) {
      price = parseFloat($row.find('.js-price-wholesale').first().html()).toFixed(2);
    }
    if (type == 3) {
      price = parseFloat($row.find('.js-price-purchase').first().html()).toFixed(2);
    }

    $row.find('.js-sum').first().html(Math.round(quantity * price));
  });
}
