jQuery(function () {
    var forms_timeout = [];

    $('.data_form input, .data_form textarea').live('keyup', function () {
        var $this = $(this),
            form = $this.parents('.data_form'),
            id = form.data('id');
        createCookie('form_data[' + id + ']', encodeURIComponent(form.serialize()));
    });

    $('.data_form').live('submit', function (e) {
        //e.preventDefault();

        var form = $(this),
            in_modal = form.parents('.modal').length,
            id = form.data('id'),
            message = form.find('.data_form_message'),
            submit = form.find(':submit'),
            btn_text = submit.find('.btn_text'),
            loader = form.find('.order_send_loader');
        submit.attr('disabled', true);
        btn_text.hide();
        loader.show();
        $.ajax({
            url: form.attr('action'),
            data: form.serialize() /*+ '&lang=' + lang*/,
            type: 'POST',
            dataType: 'JSON',
            success: function (data, statusText, xhr, $form) {
                submit.attr('disabled', false);
                btn_text.show();
                loader.hide();
                message.html(data.msg);
                if (message.is(':hidden')) {
                    if (in_modal) {
                        message.stop(true).slideDown(200);
                    } else {
                        message.stop(true).fadeIn(200);
                    }
                }
                clearTimeout(forms_timeout[id]);
                if (data.state) {
                    if (data.html) {
                        $('.ajax_forms_msg .top').html('<div class="sm_close"></div>' + data.name);
                        $('.ajax_forms_msg .bottom').html(data.html);
                        show_popup('ajax_forms_msg', '');
                    }
                    // del user form from cookie
                    eraseCookie('form_data[' + id + ']');
                    // comebacker
                    if(id == 3){
                        $('.comebacker_desc').hide();
                        $('.comebacker_final_msg').show();
                        comebacker.resize();
                        if(typeof(ga) == 'function'){
                            ga('send', 'pageview', {page: '/comebacker_submit', title: document.title});
                        }else if(typeof(_gaq) != 'undefined'){
                            _gaq.push(['_trackPageview', '/comebacker_submit']);
                        }
                    }else{
                        //                        push_to_ga('form_'+id+'_submit');
                        message.removeClass('error');
                        if (!in_modal) {
                            submit.hide();
                        }
                        form.find(':text, textarea').val('');
                        setTimeout(function () {
                            if (in_modal) {
                                message.stop(true).slideUp(200);
                            } else {
                                message.stop(true).fadeOut(200);
                                submit.show();
                            }
                        }, 3000);
                    }
                } else {
                    message.addClass('error');
                    if (typeof forms_timeout[id] == 'undefined') {
                        forms_timeout[id] = [];
                    }
                    forms_timeout[id] = setTimeout(function () {
                        if (in_modal) {
                            message.stop(true).slideUp(200);
                        } else {
                            message.stop(true).fadeOut(200);
                        }
                    }, 3000);
                }
            }
        });
        return false;
    });

    function createCookie(name, value, days) {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            var expires = "; expires=" + date.toGMTString();
        }
        else var expires = "";
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    function eraseCookie(name) {
        createCookie(name, "", -1);
    }

});

$(document).on("focus", ".input-phone", function() {
    $(this).unmask();
    $(this).mask("+380 99 999-99-99");
});

function callback(_this) {
    if (!$(_this).prop('disabled')) {
        $(_this).prop('disabled', true);

        $.ajax({
            url: '',
            data: {phone: $('#callback-phone').val()},
            type: 'POST',
            dataType: 'JSON',
            success: function (data, statusText, xhr, $form) {
                $(_this).prop('disabled', false);
            }
        });
    }
}