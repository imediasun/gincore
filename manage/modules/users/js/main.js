var avatar_uploader = null;
function create_avatar_uploader(){
    var $fileuploader = $('#fileuploader');
    if($fileuploader.length){
        avatar_uploader = new qq.FileUploader({
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
}


function add_user_validation() {
    loginInput = document.querySelector('input[name="login"]');
    passwordInput = document.querySelector('input[name="pass"]');

    if(loginInput.value == '') {
        loginInput.style.background = '#F2DEDE';
    } else {
        loginInput.style.background = 'white';
    }

    if(passwordInput.value == '') {
        passwordInput.style.background = '#F2DEDE';
    } else {
        passwordInput.style.background = 'white';
    }

    if(loginInput.value == '' || passwordInput.value == '') {
        document.documentElement.scrollTop = 0;
        return false;
    }
}

$(function(){
    $('.datepicker').datepicker();

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
    
    $('.upload_avatar_btn').click(function(){
        avatar_uploader.setParams({
            act: 'upload_avatar',
            uid: $(this).data('uid')
        });
        $('#upload_avatar').modal('show');
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