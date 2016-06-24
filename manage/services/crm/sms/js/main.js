var crm_sms = (function($){
    
    function reset_form(){
        $('#sms_body').val('');
    }
    
    return {
        init: function(){
            $(document).on('click', '#sms_modal_btn', function(){
                reset_form();
                var $this = $(this),
                    phone = $this.data('phone'),
                    object = $this.data('object');
                $('#sms_phone').val(phone);
                $('#sms_object').val(object);
                return false;
            });
            $(document).on('change', '#sms_template_select', function(){
                var body = $(this).find('option:selected').data('body');
                $('#sms_body').val(body);
            });
        },
        send_callback: function(data){
            if(data.state){
                reset_form();
                if(data.msg){
                    alert(data.msg);
                }
            }
        }
    };
    
})(jQuery);
$(function(){
    crm_sms.init();
});
function send_sms_callback(data){
    crm_sms.send_callback(data);
}