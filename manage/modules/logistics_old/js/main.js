function create_chain(_this) {

    $(_this).button('loading');
    var data = $(_this).parents('form').serialize();

    $.ajax({
        url: prefix + module + '/ajax/?act=create-chain',
        type: 'POST',
        dataType: "json",
        data: data,
        success: function (msg) {
            if (msg) {
                if (msg['state'] == false && msg['msg']) {
                    alert(msg['msg']);
                }
                if (msg['state'] == true) {
                    click_tab_hash();
                }
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

function remove_chain(_this, chain_id) {
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/?act=remove-chain',
        type: 'POST',
        dataType: "json",
        data: {chain_id: chain_id},
        success: function (msg) {
            if (msg) {
                if (msg['state'] == false && msg['msg']) {
                    alert(msg['msg']);
                }
                if (msg['state'] == true) {
                    click_tab_hash();
                }
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}