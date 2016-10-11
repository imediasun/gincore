var avatar_uploader = null;
function create_avatar_uploader(){
    var $fileuploader = $('#fileuploader');
    if($fileuploader.length){
        avatar_uploader = new qq.FileUploader({
            uploadButtonText: L.qq_uploadButtonText,
            dragText : L.qq_dragText,
            cancelButtonText : L.qq_cancelButtonText,
            failUploadText : L.qq_failUploadText,
            element: $fileuploader[0],
            action: prefix + module + '/ajax/',
            multiple: false,
            demoMode: false,
            disableDefaultDropzone: true,
            sizeLimit: 3 * 1024 * 1024,
            allowedExtensions: ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'],
            maxConnections: 1,
            debug: false,
            onSubmit: function(){

            },
            onComplete: function(id, filename, data){
                if(data.success){
                    $('.upload_avatar_btn[data-uid="'+data.uid+'"]').attr('src', data.avatar);
                }
            }
        });
    }
    $('.upload_avatar_btn').click(function(){
        avatar_uploader.setParams({
            act: 'upload_avatar',
            uid: $(this).data('uid')
        });
        $('#upload_avatar').modal('show');
    });
}



function add_user_validation() {
    loginInput = document.querySelector('input[name="login"]');
    passwordInput = document.querySelector('input[name="pass"]');
    emailInput = document.querySelector('input[name="email"]');

    if($.trim(loginInput.value) == '') {
        loginInput.style.background = '#F2DEDE';
    } else {
        loginInput.style.background = 'white';
    }

    if($.trim(emailInput.value) == '') {
        emailInput.style.background = '#F2DEDE';
    } else {
        emailInput.style.background = 'white';
    }

    if($.trim(passwordInput.value) == '') {
        passwordInput.style.background = '#F2DEDE';
    } else {
        passwordInput.style.background = 'white';
    }

    if($.trim(loginInput.value) == '' || $.trim(passwordInput.value) == '' || $.trim(emailInput.value) == '') {
        document.documentElement.scrollTop = 0;
        return false;
    }


}

function add_user_check_existance(field, query) {
    var is_exist;

    $.ajax({
        url: prefix + module + '/ajax/?act=find-user-by-field&field=' + field + '&query=' + query,
        async: false,
        cache: false,
        type: 'GET',
        success: function(msg) {
            if (msg.state == false && msg.message) {
                alert(msg.message);
            }
            if(msg.state == true) {
                if (msg.exists){
                    is_exist = true;
                } else {
                    is_exist = false;
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return is_exist;
}

function click_by_block(_this) {
    var count = 0, i = 0, limit = $('input[name=limit]').val();
    if ($(_this).hasClass('disabled')) {
        return false;
    }

    $('.js-block-by-tariff').each(function () {
        $(this).removeAttr('disabled');
        if ($(this).is(':checked')) {
            count++;
        }
    });

    if(count >= limit) {
        $('.js-block-by-tariff').each(function () {
            if ($(this).is(':checked')) {
                if (i >= limit) {
                    $(this).attr('disabled', 'disabled');
                }
                i++;
            } else {
                $(this).attr('disabled', 'disabled');
            }
        });
    }
    if($(this).is(':checked') && (count > limit)) {
        alert('Вы достигли лимита активных пользователей по текущему тарифу');
        return false;
    }
}

$(function(){
    $('.datepicker').datepicker();

    $('.js-block-by-tariff').on('click', function(){
        click_by_block(this);
    });
    click_by_block($('body').find('.js-block-by-tariff').first());

    $('.send-mess').popover({
        trigger:'click',
        html:'<a>sss</a>',
        placement:'right',
        toggle:'popover',
        title: L['create-message'] + ' <i class="icon-remove close-popover" onclick="javascript:$(\'.send-mess\').popover(\'hide\');"></i>',
        content:'' +
            '<p><input class="na-mess  form-control" /></p>' +
            '<p><textarea class="ta-mess form-control" rows="3"></textarea></p>' +
            '<p><input type="button" class="btn" onclick="send_mess()" value="' + L['send'] +'" /></p>',
    });

    create_avatar_uploader();

    $('.js-change-roles-btn').on('click', function(){
        $('.js-block-by-tariff').removeAttr('disabled');
    });


   $('.js-edit-user').on('click', function(){
       var uid = $(this).attr('data-uid'), _this = this;
       $.ajax({
           url: prefix + module + '/ajax/?act=edit-user&uid=' + uid,
           type: 'GET',
           success: function(msg) {
               if (msg.state == false && msg.message) {
                   alert(msg.message);
               }
               if(msg.state == true && msg.html.length > 0) {

                   buttons =  {
                       success: {
                           label: L.save,
                           className: "btn-success",
                           callback: function() {
                               $.ajax({
                                   url: prefix + module + '/ajax/?act=update-user',
                                   type: 'POST',
                                   data: $('form.edit-user').serialize(),
                                   success: function(msg) {
                                       window.location = prefix + module;
                                       window.location.reload();
                                   },
                                   error: function (xhr, ajaxOptions, thrownError) {
                                       alert(xhr.responseText);
                                   }
                               });
                               $(_this).button('reset');
                           }
                       },
                       main: {
                           label: L.cansel,
                           className: "btn-primary",
                           callback: function() {
                               $(_this).button('reset');
                           }
                       }
                   };
                   dialog_box(_this, msg.title || '', msg.html, buttons);
                   reset_multiselect();
                   create_avatar_uploader();
               }
           },
           error: function (xhr, ajaxOptions, thrownError) {
               alert(xhr.responseText);
           }
       });
       return false;
   });
});


function send_mess() {

        /*jQuery.each($('.send-mess-user:checked'), function(k, v){
            alert( "Key: " + k + ", Value: " + v );
        });*/

    $.ajax({
        url: prefix+'messages.php',
        type: 'POST',
        data: 'act=send_message&' + $('#users-form input.send-mess-user:checkbox').serialize() + '&text=' + $('.ta-mess').val()
            + '&title=' + $('.na-mess').val(),
        success: function(msg) {
            if ( msg['error'] ) {
                alert(msg['message']);
            } else {
                alert(msg['message']);
                $('.send-mess').popover('hide');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
    return false;
}


function per_change( _this, ids, del ) {
    var a = ids.split('-');
    var b = a[1].split(',');
    for(var i = 0; i< b.length; b++) {
        if ( b && $(_this).prop('checked') ) {
            $('#per_id_' + a[0] + '_' + b[i]).prop('checked', true);
        }
    }
    if( !$(_this).prop('checked') )
        $('form.form-horizontal').find('input.del-' + del).prop('checked', false);
}

function delete_user(_this, uid) {
    if(confirm('Вы действительно хотите удалить пользователя?')) {
      $.ajax({
          url: prefix + module + '/ajax/?act=delete_user',
          type: 'POST',
          dataType: "json",
          data: '&uid=' + uid,
          success: function(msg) {
              $(_this).parent().parent('td').parent().hide();
          },
          error: function (xhr, ajaxOptions, thrownError) {
              alert(xhr.responseText);
          }
      });
    }
    return false;
}
function select_divercified_pay(_this, name) {
    $('.js-fixed-pay').prop('disabled', $('.js-devircified-pay:checked').length > 0);
    $('input[name="'+name+'"]').prop('checked', false);
}
