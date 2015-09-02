$(document).ready(function(){

    $('.datepicker').datepicker();
});

$(function(){

    $('.send-mess').popover({
        trigger:'click',
        html:'<a>sss</a>',
        placement:'right',
        toggle:'popover',
        title:'Создать сообщение <i class="icon-remove close-popover" onclick="javascript:$(\'.send-mess\').popover(\'hide\');"></i>',
        content:'' +
            '<p><input class="na-mess span3" /></p>' +
            '<p><textarea class="ta-mess span3" rows="3"></textarea></p>' +
            '<p><input type="button" class="btn" onclick="send_mess()" value="Отправить" /></p>',
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