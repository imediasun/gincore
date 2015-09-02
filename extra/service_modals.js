$( document ).ready(function() {
   $(document).on('click', '.show_service_modal', function () {
        var $this = $(this),
            target = $this.data('target'),
            $form = $('#'+target);
        $form.show();
        $('#service_modal_alpha').show();
        $form.find('input.service').val($this.siblings('.service_name').text());
        $form.find('.hashed_data').each(function(){
            var $this = $(this);
            $this.replaceWith(Base64.decode($this.data('content')));
        });
        resize_modals();
    });
    
    $(document).on('click', '.close_service_modal', function(){
        var $this = $(this),
            $modal = $this.parents('.service_modal');
        $modal.hide();
        $modal.find('.service_btn_request, .service_form').show();
        $modal.find('.service_btn_request_form, .service_recall').hide();
        $('#service_modal_alpha').hide();
    });
    
    $(document).on('click', '#service_modal_alpha', function(){
        $(this).hide();
        $('.service_modal:visible').hide();
    });
    
    $(document).on('click', '.service_btn_request', function(){
        var $this = $(this);
        $this.hide();
        $this.siblings('.service_btn_request_form').show();
        resize_modals();
    });
    
    $(window).resize(function(){
        resize_modals();
    });
});

function resize_modals(){
    var $modal = $('.service_modal:visible'),
        $modal_body = $modal.find('.modal_body'),
        $modal_btn = $modal.find('.modal_body_btn'),
        $modal_header = $modal.find('.modal_header');

    $modal_body.height('');

    var height = $modal.outerHeight(),
        width = $modal.outerWidth();
    
    $modal.css({
        marginTop: - height / 2,
        marginLeft: - width / 2
    });
    $modal_body.height(height - $modal_header.outerHeight() - $modal_btn.outerHeight());
}

var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(input){var output="";var chr1,chr2,chr3,enc1,enc2,enc3,enc4;var i=0;input=Base64._utf8_encode(input);while(i<input.length){chr1=input.charCodeAt(i++);chr2=input.charCodeAt(i++);chr3=input.charCodeAt(i++);enc1=chr1>>2;enc2=((chr1&3)<<4)|(chr2>>4);enc3=((chr2&15)<<2)|(chr3>>6);enc4=chr3&63;if(isNaN(chr2)){enc3=enc4=64}else if(isNaN(chr3)){enc4=64}output=output+this._keyStr.charAt(enc1)+this._keyStr.charAt(enc2)+this._keyStr.charAt(enc3)+this._keyStr.charAt(enc4)}return output},decode:function(input){var output="";var chr1,chr2,chr3;var enc1,enc2,enc3,enc4;var i=0;input=input.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(i<input.length){enc1=this._keyStr.indexOf(input.charAt(i++));enc2=this._keyStr.indexOf(input.charAt(i++));enc3=this._keyStr.indexOf(input.charAt(i++));enc4=this._keyStr.indexOf(input.charAt(i++));chr1=(enc1<<2)|(enc2>>4);chr2=((enc2&15)<<4)|(enc3>>2);chr3=((enc3&3)<<6)|enc4;output=output+String.fromCharCode(chr1);if(enc3!=64){output=output+String.fromCharCode(chr2)}if(enc4!=64){output=output+String.fromCharCode(chr3)}}output=Base64._utf8_decode(output);return output},_utf8_encode:function(string){string=string.replace(/\r\n/g,"\n");var utftext="";for(var n=0;n<string.length;n++){var c=string.charCodeAt(n);if(c<128){utftext+=String.fromCharCode(c)}else if((c>127)&&(c<2048)){utftext+=String.fromCharCode((c>>6)|192);utftext+=String.fromCharCode((c&63)|128)}else{utftext+=String.fromCharCode((c>>12)|224);utftext+=String.fromCharCode(((c>>6)&63)|128);utftext+=String.fromCharCode((c&63)|128)}}return utftext},_utf8_decode:function(utftext){var string="";var i=0;var c=c1=c2=0;while(i<utftext.length){c=utftext.charCodeAt(i);if(c<128){string+=String.fromCharCode(c);i++}else if((c>191)&&(c<224)){c2=utftext.charCodeAt(i+1);string+=String.fromCharCode(((c&31)<<6)|(c2&63));i+=2}else{c2=utftext.charCodeAt(i+1);c3=utftext.charCodeAt(i+2);string+=String.fromCharCode(((c&15)<<12)|((c2&63)<<6)|(c3&63));i+=3}}return string}};