$( document ).ready(function() {

    $('.add_product_demand').click(function () {
        var product_id = $(this).data('product_id');
        $.ajax({
            url: prefix + 'ajax.php?act=add-product-demand',
            data: 'product_id=' + product_id,
            type: 'POST',
            success: function (msg) {
                if (msg) {
                    if (msg['qty']) {
                        $('#demand-product-' + product_id).html(msg['qty']);
                    }
                }
            }
        });
    });

    $('.daterange').live('focusin', '.daterange', function(e) {
        $(this).dateRangePicker({
            language: 'ru',
            separator: ' '
        });
    });
});
