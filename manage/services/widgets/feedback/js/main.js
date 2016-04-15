var gcw_feedback_widget = (function ($) {

  function resize() {
    var $modal = $('.gcw_modal');
    if ($modal.is(':visible')) {
      var w = $modal.width(),
        h = $modal.height();
      $modal.css({
        marginTop: -h / 2,
        marginLeft: -w / 2
      });
    }
  }

  function modal_on_close() {
    $('#gcw_form_html').empty().siblings('.gcw_form').show();
  }

  var callbacks = {
    add: function ($form, data) {
      $form.hide();
      $('.js-feedback-body').html(data.html);
      resize();
    },
    send_sms: function ($form, data) {
      $form.find('.gcw_buttons').html(data.html);
    }
  };

  function send($this, data, method) {
    var action = $this.attr('data-action'),
      contentType = "application/x-www-form-urlencoded;charset=utf-8";

    if (window.XDomainRequest) {
      contentType = "text/plain";
    }
    $.ajax({
      url: action,
      data: data,
      type: "POST",
      dataType: "json",
      contentType: contentType,
      success: function (data) {
        if (data.state) {
          callbacks[method]($this, data);
        } else {
          alert(data.msg);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(errorThrown);
      }
    });
    return false;
  }

  return {
    init: function () {
      $(document).on('click', '.gcw_show_modal', function () {
        var $this = $(this),
          id = $this.data('id');
        $('#' + id).show();
        resize();
      });
      $(document).on('click', '.gcw_modal_close', function () {
        $(this).parents('.gcw_modal_box').hide();
        modal_on_close();
      });
      $(window).resize(resize).resize();

      $(document).on('submit', '.js-feedback-form', function (e) {
        var $this = $(this),
          method = $this.find('input[name=action]').val(),
          data,
          phone = $('input[name=phone]').val(),
          sms = $('input[name=sms]').val(),
          code = $('input[name=code]').val();

        if ((typeof code != 'undefined' && code.length > 0) || (typeof sms != 'undefined' && sms.length > 0)) {
          send($this, $this.serialize(), method);
        }
        else {
          if (typeof phone != 'undefined' && phone.length > 0) {
            data = {
              phone: phone,
              action: 'send_sms',
              widget: 'feedback'
            };

            send($this, data, 'send_sms');
          } else {
            alert('Заполните поле "Код клиента", "Код из sms" или "Номер телефона"');
          }
        }

        e.preventDefault();
        return false;
      });
    }
  };

})(jQuery);
jQuery(function () {
  gcw_feedback_widget.init();
});
