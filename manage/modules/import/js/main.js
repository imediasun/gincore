function upload_file(_this) {
    var form_data = new FormData($('#import_upload')[0]);
    $(_this).button('loading');
    $.ajax({
        url: prefix + module + '/ajax/?act=upload',
        dataType: "json",
        data: form_data,
        type: 'POST',
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
            if(data.state){
                if(data.location){
                    window.location = data.location;
                }
            }else{
                alert(data.message);
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
    return false;
}
function start_import(_this) {
    var form_data = new FormData($('#import_form')[0]);
    $(_this).button('loading');
    $('#upload_messages').empty();
    $.ajax({
        url: prefix + module + '/ajax/?act=import',
        dataType: "json",
        data: form_data,
        type: 'POST',
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
            if(data.state){
                
            }
            if(data.message){
                $('#upload_messages').html(data.message);
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
    return false;
}