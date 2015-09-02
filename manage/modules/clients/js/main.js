
$(function() {
    $( ".edit_date" ).datepicker();
});

function confirm_parse_comment(id, avail) {

    jQuery.ajax({
        url: prefix+module+'/ajax/?act=confirm_parse_comment',
        type: 'POST',
        data: 'comment_id=' + id + '&avail=' + avail,
        cache: false,
        success: function(msg){
            if ( msg['error'] ) {
                alert(msg['message']);
            } else {
                $('#comment_parse_empty-' + id).html('');
                $('#comment_parse_edit-' + id).html(msg['response']);
                //$(_this).parents('tr').remove();
                //alert(msg['message']);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function group_clients(_this) {

    $(_this).button('loading');

    jQuery.ajax({
        url: prefix+module+'/ajax/?act=group-clients',
        type: 'POST',
        data: $('#group_clients_form').serialize(),
        cache: false,
        success: function(msg){
            alert(msg['message']);
            $(_this).button('reset');
            $(_this).parents('form').find(':text,:hidden').val('');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function refute_parse_comment(id) {

    jQuery.ajax({
        url: prefix+module+'/ajax/?act=refute_parse_comment',
        type: 'POST',
        data: 'comment_id=' + id,
        cache: false,
        success: function(msg){
            if ( msg['error'] ) {
                alert(msg['message']);
            } else {
                $('#comment_parse_remove-' + id).remove();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}