
// create new task
function task_create(_this) {
//    var form_data = $('#new_task_form').serialize();
	// todo - front-and check form data
    var form_data = new FormData($('#new_task_form')[0]);
    $(_this).button('loading');
    $.ajax({
        url: prefix + module + '/ajax/?act=task-create',
        dataType: "json",
        data: form_data,
        type: 'POST',
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data) {
                if (data['state'] == true)
                    click_tab_hash('#alltasks'); // return to all tasks
                if (data['state'] == false && data['message'])
                    alert(data['message']) // bark error massage
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

// save task changes
function task_save(_this) {
//    var form_data = $('#edit_task_form').serialize();
	// todo - front-and check form data
    var form_data = new FormData($('#edit_task_form')[0]);
    $(_this).button('loading');
    $.ajax({
        url: prefix + module + '/ajax/?act=task-save',
        dataType: "json",
        data: form_data,
        type: 'POST',
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data) {
                if (data['state'] == true)
					close_alert_box(); // close box
                    click_tab_hash('#alltasks'); // return to all tasks
                if (data['state'] == false && data['message'])
                    alert(data['message']) // bark error massage
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

$(function(){
    $(document).on('click', '.file_link', function(e){
        e.stopPropagation();
    });
    $(document).on('click', '.task_row', function(e){
        alert_box(this, false, 'task-edit', undefined, undefined, '');
    });
});