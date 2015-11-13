
function checked_all_writeoff(_this) {
    if ($(_this).prop('checked') == true)
        $('input.writeoff-items:checkbox').prop('checked', true);
    else
        $('input.writeoff-items:checkbox').prop('checked', false);
}

function move_item(_this, rand) {

    $(_this).button('loading');

    var result = {};
    var data = $('#moving-item-form-' + rand).serializeArray();

    $.each(data, function(i, field) {
        result[this.name] = this.value;
    });

    if (result.item_id) {
        ajax_move_item(result, _this)
    } else {
        if ($("input.check-item:checked").length > 0) {
            $("input.check-item:checked").map(function(key, value) {
                result['item_id'] = value.value;
                ajax_move_item(result, _this);
            });
        } else {
            ajax_move_item(result, _this);
        }
    }
}

function ajax_move_item(data, _this) {
    $.ajax({
        url: prefix + module + '/ajax/?act=move-item',
        type: 'POST',
        dataType: "json",

        data: data,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false) {
                    if (msg['message']) {
                        alert(msg['message']);
                    }
                    if (msg['location']) {
                        window.location.href = msg['location'];
                    }
                }
                if (msg['state'] == true || msg['reload'] == true) {
                    click_tab_hash();
                    close_alert_box();
                }
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

function return_item(_this, items) {

    if (confirm('Активировать возврат?')) {
        $(_this).button('loading');

        if (!items) {
            items = $("input.check-item:checked").map(function(key, value) {
                return value.value;
            }).get();
        }

        $.ajax({
            url: prefix + module + '/ajax/?act=return-item',
            type: 'POST',
            dataType: "json",

            data: '&items=' + items,
            success: function(msg) {
                if (msg) {
                    if (msg['state'] == false) {
                        if (msg['message']) {
                            alert(msg['message']);
                        }
                        if (msg['location']) {
                            window.open(msg['location']);
                        }
                    }
                    if (msg['state'] == true) {
                        click_tab_hash();
                    }

                    $(_this).button('reset');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });
    }
}

function sold_item(_this, item) {

    if (confirm('Активировать продажу?')) {
        if (false === $('#sold-item-form').parsley().validate())
        return;
        $(_this).button('loading');

        var data = $('#sold-item-form').serializeArray();

        data[data.length] = {
            name: "items",
            value: item ? item : $("input.check-item:checked").map(function (key, value) {
                return value.value;
            }).get()
        };

        $.ajax({
            url: prefix + module + '/ajax/?act=sold-item',
            type: 'POST',
            dataType: "json",

            data: data,
            success: function(msg) {
                if (msg) {
                    if (msg['state'] == false) {
                        if (msg['message']) {
                            alert(msg['message']);
                        }
                        if (msg['location']) {
                            window.open(msg['location']);
                        }
                    }
                    if (msg['state'] == true) {
                        //$('a.click_tab[href="' + window.location.hash + '"]').click();
                        click_tab_hash();
                    }

                    $(_this).button('reset');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });
    }
}

function write_off_item(_this, items) {
    if (confirm('Активировать списание?')) {
        $(_this).button('loading');

        if (!items) {
            items = $("input.check-item:checked, input.writeoff-items:checkbox:checked").map(function(key, value) {
                return value.value;
            }).get();
        }

        $.ajax({
            url: prefix + module + '/ajax/?act=write-off-item',
            type: 'POST',
            dataType: "json",

            data: '&items=' + items,
            success: function(msg) {
                if (msg) {
                    if (msg['state'] == false) {
                        if (msg['message']) {
                            alert(msg['message']);
                        }
                        if (msg['location']) {
                            window.open(msg['location']);
                        }
                    }
                    if (msg['state'] == true) {
                        //$('a.click_tab[href="' + window.location.hash + '"]').click();
                        click_tab_hash();
                    }

                    $(_this).button('reset');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });
    }
}

function item_move_logistic(_this) {

    var logistic = $(_this).prop('checked') ? 1 : 0;

    $.ajax({
        url: prefix + module + '/ajax/?act=get-options-for-item-move',
        type: 'POST',
        dataType: "json",

        data: '&logistic=' + logistic,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && msg['message']) {
                    alert(msg['message']);
                }
                if (msg['state'] == true && msg['options']) {
                    $('.select-warehouses-item-move').html(msg['options']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

function accept_chain_body(_this, b_id) {

    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/?act=accept-chain-body',
        type: 'POST',
        dataType: "json",

        data: '&b_id=' + b_id,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && msg['message']) {
                    alert(msg['message']);
                }
                if (msg['class']) {
                    $(_this).parents('tr.operation').attr('class', msg['class']);
                }
                if (msg['state'] == true) {
                    //window.location.reload();
                    //$('a.click_tab[href="' + window.location.hash + '"]').click();
                    click_tab_hash();
                }
                $(_this).button('reset');

                if (msg['disabled'] && msg['disabled'] == true) {
                    $(_this).attr('disabled', true);
                }
            } else {
                $(_this).button('reset');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

function issued_chain_body(_this, b_id) {

    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/?act=issued-chain-body',
        type: 'POST',
        dataType: "json",

        data: '&b_id=' + b_id,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && msg['message']) {
                    alert(msg['message']);
                }
                if (msg['class']) {
                    $(_this).parents('tr.operation').attr('class', msg['class']);
                }
                if (msg['state'] == true) {
                    //window.location.reload();
                    //$('a.click_tab[href="' + window.location.hash + '"]').click();
                    click_tab_hash();
                }
                $(_this).button('reset');

                if (msg['disabled'] && msg['disabled'] == true) {
                    $(_this).attr('disabled', true);
                }
            } else {
                $(_this).button('reset');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

function btn_unbind_request_item_serial(_this, item_id) {

    if ($(_this).hasClass('disabled'))
        return false;

    $(_this).addClass('disabled');

    $.ajax({
        url: prefix + module + '/ajax/?act=unbind-request-item-serial',
        type: 'POST',
        dataType: "json",

        data: '&item_id=' + item_id,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && msg['msg']) {
                    alert(msg['msg']);
                }
                if (msg['state'] == true) {
                    $(_this).remove();
                }
            }
            $(_this).removeClass('disabled');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

function btn_unbind_item_serial(_this, rand) {

    if ($(_this).hasClass('disabled'))
        return false;

    $(_this).addClass('disabled');

    $.ajax({
        url: prefix + module + '/ajax/?act=unbind-item-serial',
        type: 'POST',
        dataType: "json",

        data: $('#moving-item-form-' + rand).serialize(),//'&item_id=' + item_id,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && (msg['msg'] || msg['message'])) {
                    alert(msg['msg'] || msg['message']);
                }
                if (msg['state'] == true) {
                    click_tab_hash();
                    close_alert_box();
                }
            }
            $(_this).removeClass('disabled');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });
}

function btn_bind_item_serial(_this, order_product_id, conf) {

    $(_this).button('loading');
    var item_id = $('#bind_item_serial-' + order_product_id + ':visible').val();
    var serial = $('#bind_item_serial_input-' + order_product_id + ':visible').val();

    $.ajax({
        url: prefix + module + '/ajax/?act=bind-item-serial',
        type: 'POST',
        dataType: "json",

        data: '&item_id=' + item_id + '&order_product_id=' + order_product_id + '&serial=' + serial + '&confirm=' + conf,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && msg['message']) {
                    if (msg['confirm']) {
                        if (confirm(msg['message'])) {
                            btn_bind_item_serial(_this, order_product_id, 1);
                        }
                    } else {
                        alert(msg['message']);
                    }
                }
                if (msg['disabled'] && msg['disabled'] == true) {
                    $('#bind_item_serial-' + h_id).attr('disabled', true);
                    $(_this).attr('disabled', true);
                }
                if (msg['class']) {
                    $(_this).parents('tr.operation').attr('class', msg['class']);
                }
                if (msg['item_id']) {
                    $('#bind_item_serial-' + h_id).val(msg['item_id'])
                }
                if (msg['state'] == true) {
                    //window.location.reload();
                    //$('a.click_tab[href="' + window.location.hash + '"]').click();
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

function logistic_order_icon(_this, order_id, icon_type) {
    $.ajax({
        url: prefix + module + '/ajax/?act=order-icon',
        type: 'POST',
        dataType: "json",

        data: 'order_id=' + order_id + '&icon_type=' + icon_type,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == true) {
                    click_tab_hash();
                }
                if (msg['state'] == false && msg['message']) {
                    alert(msg['message']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function logistic_parent_icon(_this, chain_id, icon_type) {
    $.ajax({
        url: prefix + module + '/ajax/?act=chain-icon',
        type: 'POST',
        dataType: "json",

        data: 'parent=' + chain_id + '&icon_type=' + icon_type,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == true) {
                    click_tab_hash();
                }
                if (msg['state'] == false && msg['message']) {
                    alert(msg['message']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function clear_serial(_this, item_id) {
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax?act=clear-serial',
        type: 'POST',
        dataType: "json",

        data: '&item_id=' + item_id,
        success: function(msg) {
            if (msg) {
                if (msg['state'] == false && msg['message']) {
                    alert(msg['message']);
                }
                if (msg['state'] == true && msg['href']) {
                    window.location.href = msg['href'];
                }
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}