//var transaction_type = 0;
//var order_id = 0;
//var body_id = 0;
//var transaction_extra = '';
//var tips = $(".validateTips");

$(function () {



    $('table.table-reports tbody td:not(:first-child)')
        .live('mouseleave', function () {
            $('table.table-reports td.is_hover').removeClass('is_hover');
        })
        .live('mouseenter', function () {
            $(this).parents('tr').children('td').addClass('is_hover');
            $('table.table-reports tbody td:nth-child(' + (1 + $(this).index()) + ')').addClass('is_hover');
        });

    $('#create-cat-income .object_id').live('click', function (e) {
        e.preventDefault();
        alert_box(this, false, 'create-cat-income');
    });
    $('#create-cat-expense .object_id').live('click', function (e) {
        e.preventDefault();
        alert_box(this, false, 'create-cat-expense');
    });

    $('#show_reports_turnover_profit_button').live('click', function (e) {
        $(this).remove();
        $('.reports_turnover_profit').removeClass('invisible');
    });
    $('#show_reports_turnover_margin_button').live('click', function (e) {
        $(this).remove();
        $('.reports_turnover_margin').removeClass('invisible');
    });

    $(document).on('click', '.add-cashbox-table', function () {
        $(this).tooltip('hide');
    });

    $(document).on('submit', 'form.form_contractor_category', function (e) {
        if ($('#transaction_form').length){
            e.preventDefault();
            var elem = $(this);
            $(this).ajaxSubmit({
                dataType: 'json',
                data: {'ajax': true},
                success: function (responseJSON) {
                    if(responseJSON.hasError){
                        alert(responseJSON.error);
                    } else {
                        var $select = $('#contractor_category-'+responseJSON.transaction_type);
                        $select.append('<option value="'+responseJSON.id+'">'+responseJSON.title+'</option>');
                        $select.multiselect('destroy');
                        $select.multiselect({
                            enableFiltering: true,
                            filterBehavior: 'value'
                        });
                        $select.multiselect('select', [parseInt(responseJSON.id)]);
                        select_contractor_category($select[0], 1);

                        $('.form_contractor_category').closest('.modal-body').find('.bootbox-close-button').trigger('click');
                    }
                }
            })
        }
    });

});

function cashbox_submit() {
    $('#cashbox-form')[0].submit();
};

function toggle_report_cashflow(_this, e, p) {
    e.preventDefault();
    if ($(_this).children().hasClass('icon-chevron-down')) {
        $('.report-cashflow-' + p).hide();
        $(_this).children().removeClass('icon-chevron-down').addClass('icon-chevron-up');
    } else {
        $('.report-cashflow-' + p).toggle();
        $(_this).children().removeClass('icon-chevron-up').addClass('icon-chevron-down');
    }
    //$('.report-cashflow-' + p).show();
    return false;
}

function remove_currency(_this) {
    var currency_id = $(_this).data('currency_id');

    $.ajax({
        url: prefix + module + '/ajax/?act=remove-currency',
        dataType: "json",
        data: 'currency_id=' + currency_id,
        type: 'POST',
        success: function (data) {
            if (data['state'] == true) {
                $(_this).parents('tr').remove();
                $('#add_new_course').html(data['add']);
            } else {
                alert(data['msg']);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function add_currency(_this) {
    var val = parseInt(_this.value);

    if (val > 0) {
        $.ajax({
            url: prefix + module + '/ajax/?act=add-currency',
            dataType: "json",
            data: 'currency_id=' + val,
            type: 'POST',
            success: function (data) {
                if (data['state'] == true) {
                    $('#add_new_course').html(data['add']);
                    $('#edit-courses-from').html(data['show']);
                } else {
                    alert(data['msg']);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });
    }

    return false;
}

function create_transaction(_this, conf, event) {
    event.stopPropagation();
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/?act=create-transaction',
        dataType: "json",
        data: $('#transaction_form').serialize() + (conf == 1 ? '&confirm=1' : ''),
        type: 'POST',
        success: function (data) {
            if (data) {
                if (data['state'] == true) {
                    location.reload();
                } else {
                    if (data['msg']) {
                        if (data['confirm']) {
                            if (confirm(data['msg'])) {
                                create_transaction(_this, 1);
                            }
                        } else {
                            alert(data['msg']);
                        }
                    }
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

function accounting_credit_approved(_this) {
    var order_id = $(_this).data('id');

    if (confirm(L['Documents ready'] + "?")) {
        $.ajax({
            url: prefix + module + '/ajax/?act=accounting-credit-approved',
            type: 'POST',
            dataType: "json",

            data: '&order_id=' + order_id,
            success: function (msg) {
                if (msg['error']) {
                    alert(msg['message']);
                } else {
                    $(_this).parent().parent().find('input').attr('disabled', true);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });

    } else {
        $(_this).prop('checked', false);
    }
    return false;
}

function accounting_credit_denied(_this) {
    var order_id = $(_this).data('id');

    if (confirm(L['denied-credit'] + "?")) {
        $.ajax({
            url: prefix + module + '/ajax/?act=accounting-credit-denied',
            type: 'POST',
            dataType: "json",

            data: '&order_id=' + order_id,
            success: function (msg) {
                if (msg['error']) {
                    alert(msg['message']);
                } else {
                    $(_this).parent().parent().find('input').attr('disabled', true);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });

    } else {
        $(_this).prop('checked', false);
    }
    return false;
}

function contractor_remove(_this, contractor_id) {
    if (confirm(L['are-you-sure-you-want-to-delete-this-listing'] + "?")) {
        //var contractor_id = $(_this).data('contractor_id');

        $.ajax({
            url: prefix + module + '/ajax/?act=remove-contractor',
            dataType: "json",
            data: 'contractor_id=' + contractor_id,
            type: 'POST',
            success: function (data) {
                if (data['state'] == true) {
                    //$('.select_contractors').html(data['contractors']);
                    $(_this).closest('.panel').remove();
                } else {
                    alert(data['msg']);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });

        return false;
    }
}

function contractor_category_remove(_this, category_id) {
    $(_this).button('loading');

    if (confirm(L['are_you-sure-you-want-to-delete-this-category'] + "?")) {
        //var category_id = $(_this).data('category_id');

        $.ajax({
            url: prefix + module + '/ajax/?act=remove-category',
            dataType: "json",
            data: 'category_id=' + category_id,
            type: 'POST',
            success: function (data) {
                if (data['state'] == true) {
                    //$('.select_contractors').html(data['contractors']);
                    $(_this).parents('div.accordion-group').remove();
                    window.location.reload();//click_tab_hash();
                } else {
                    alert(data['msg']);
                }
                $(_this).button('reset');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.responseText);
            }
        });

        return false;
    }
}

function select_contractor_category(_this, type) {

    var contractor_category_id = _this.value;
    $('.select_contractors').html('');

    $.ajax({
        url: prefix + module + '/ajax/?act=get-contractors-by-category_id',
        dataType: "json",
        data: 'contractor_category_id=' + contractor_category_id,
        type: 'POST',
        success: function (data) {
            if (data['state'] == true && data['contractors']) {
                $('.select_contractors').html(data['contractors']);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function select_cashbox(_this, type) {
    var cashbox_id = _this.value;
    $('.cashbox_currencies-' + type).html('');

    $.ajax({
        url: prefix + module + '/ajax/?act=get-cashbox-currencies',
        dataType: "json",
        data: 'cashbox_id=' + cashbox_id,
        type: 'POST',
        success: function (data) {
            if (data['state'] == true && data['currencies']) {
                $('.cashbox_currencies-' + type).html(data['currencies']);
                get_course(0);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function get_course(course_from_post) {

    $.ajax({
        url: prefix + module + '/ajax/?act=get-course&course-from-post=' + course_from_post,
        dataType: "json",
        data: $('#transaction_form').serialize(),
        type: 'POST',
        success: function (data) {
            if (data && data['state'] == true) {
                if (data['noconversion'] && data['noconversion'] == true)
                    $('#transaction_form_body.transaction_type-3').removeClass('hide-conversion-3');
                else
                    $('#transaction_form_body.transaction_type-3').addClass('hide-conversion-3');

                if (data['course-db-1']) {
                    $('#conversion-course-db-1').html(data['course-db-1']);
                }
                if (data['course-db-2']) {
                    $('#conversion-course-db-2').html(data['course-db-2']);
                }

                if (data['course-1'])
                    $('#conversion-course-1').val(data['course-1']);
                if (data['course-2'])
                    $('#conversion-course-2').val(data['course-2']);

                if (data['amount-2'])
                    $('#amount-2').val(data['amount-2']);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}

function pay_supplier_order(_this, tt, order_id) {
    var data = {supplier_order_id: order_id};
    alert_box(_this, false, 'begin-transaction-' + tt + '-so', data);
    return false;
}

function pay_client_order(_this, tt, order_id, b_id, extra) {
    var data = {client_order_id: order_id, b_id: b_id, transaction_extra: extra};
    alert_box(_this, false, 'begin-transaction-' + tt + '-co', data);
    return false;
}

function contractor_edit(_this, id) {
    var form_data = $(_this).parents('form');
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/' + id + '?act=contractor-edit',
        dataType: "json",
        data: form_data.serialize(),
        type: 'POST',
        success: function (data) {
            if (data) {
                if (data['state'] == true)
                    click_tab_hash();
                if (data['state'] == false && data['message'])
                    alert(data['message'])
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}


function check_contractor_amount(_this, contractor_id) {
    $(_this).button('loading');

    $.ajax({
        url: prefix + module + '/ajax/?act=contractor-amount',
        dataType: "json",
        data: {contractor_id: contractor_id},
        type: 'POST',
        success: function (data) {
            if (data) {
                if (data['message'])
                    alert(data['message'])
            }
            $(_this).button('reset');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        }
    });

    return false;
}