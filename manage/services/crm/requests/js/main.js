function new_call_add_request_callback(data, $form){
    if(data.product_site_url){
        $form.find('.request_product').html(data.product_site_url);
    }
    if(data.create_order_btn){
        $form.find('.row-fluid').append(data.create_order_btn);
    }
}

$(function(){
    $('.add_order_to_request_btn').click(function(){
        var request_id = $(this).data('id');
        $('#order_to_request_id').val(request_id);
    });
});