var gcw_status_widget = (function($){

    function resize(){
        var $modal = jQuery('#gcw_status_modal .gcw_modal');
        if($modal.is(':visible')){
            var w = $modal.width(),
                h = $modal.height();
            $modal.css({
                marginTop: -h / 2, 
                marginLeft: -w / 2 
            });
        }
    }
    
    function modal_on_close(){
        jQuery('#gcw_form_html').empty().siblings('.gcw_form').show();
    }
    
    var callbacks = {
        status_by_phone: function($form, data){
            $form.hide();
            jQuery('#gcw_form_html').html(data.html);
            resize();
        }
    };
    
    return {
        init: function(){
            
            jQuery(document).on('click', '.gcw_show_modal', function(){
                var $this = jQuery(this),
                    id = $this.data('id');
                jQuery('#'+id).show();
                resize();
            });
            jQuery(window).resize(resize).resize();
            
            var form_msg_timeout;
            jQuery(document).on('submit', '.js-status-form', function(e){
                var $this = jQuery(this),
                    $error_msg = $this.find('.gcw_form_error'),
                    method = $this.find('input[name=action]').val(),
                    action = $this.attr('action');
                var contentType = "application/x-www-form-urlencoded;charset=utf-8";
                if (window.XDomainRequest){
                    contentType = "text/plain";
                }
                $.ajax({
                    url: action,
                    data: $this.serialize(),
                    type: "POST",
                    dataType: "json",
                    contentType: contentType,
                    success: function(data){
                        if(data.state){
                            callbacks[method]($this, data);
                        }else{
                            clearTimeout(form_msg_timeout);
                            jQuery('.gcw_form_error').text(data.msg);
                            form_msg_timeout = setTimeout(function(){
                                $error_msg.empty();
                            }, 7000);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown){
                        console.log(errorThrown);
                    }
                });
                e.preventDefault();
            });
        }
    };
    
})(jQuery);
function close_gcw(_this) {
      jQuery(_this).parents('.gcw_modal_box').hide();
    jQuery('#gcw_form_html').empty().siblings('.gcw_form').show();
}
jQuery(function(){
    gcw_status_widget.init();
});