function add_to_cart(id) {
  $.ajax({
    url: prefix + 'cart/ajax?act=add-to-cart&id=' + id,
    dataType: "json",
    type: 'POST',
    success: function (data) {
      if (data) {
        if (data['state'] == true) {
          $('#cart-quantity').html(data['quantity']);
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
function show_cart() {
  var buttons = {
    sale: {
      label: L['sale'],
      className: "btn-success",
      callback: function () {
        $.ajax({
          url: prefix + 'cart/sale',
          dataType: "json",
          type: 'POST',
          data: $('form#cart-form').serialize(),
          success: function (data) {
            if(data.state && data.redirect) {
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
      label: L['purchase'],
      className: "btn-success",
      callback: function () {
        $.ajax({
          url: prefix + 'cart/purchase',
          dataType: "json",
          type: 'POST',
          data: $('form#cart-form').serialize(),
          success: function (data) {
            if(data.state && data.redirect) {
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
      className: "btn-success",
      callback: function () {
        $.ajax({
          url: prefix + 'cart/ajax?act=clear-cart',
          dataType: "json",
          type: 'POST',
          data: {},
          success: function (data) {
            if(data.state) {
              $('#cart-quantity').html(data['quantity']);
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
          }
        });

      }
    },
    main: {
      label: L['cancel'],
      className: "btn-primary",
      callback: function () {
      }
    }
  };
  $.ajax({
    url: prefix + 'cart/ajax?act=show-cart',
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
function delete_from_cart(_this, id) {
  $.ajax({
    url: prefix + 'cart/ajax?act=remove-from-cart&id='+id,
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
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      alert(xhr.responseText);
    }
  });
  return false;
}