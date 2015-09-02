var _consult = function(){
    
    var _this = this,
        /**
         * status:
         *  0 - только открыли
         *  1 - отправили вопрос, ждем админа
         *  2 - чат завершен
         *  3 - админ не в сети, предлагаем отправить в офлайн
         */
        status = 0,
        admin_online = false,
        $consult = $('#consult'),
        $consult_btn = $('#consult_btn'),
        $consult_start_form = $('#consult_start_form'),
        $consult_offline_form = $('#consult_offline_form'),
        $consult_form = $('#consult_form'),
        $consult_phone = $('#consult_phone'),
        $consult_name = $('#consult_name'),
        $consult_start_message = $('#consult_start_message'),
        $consult_messages = $('#consult_messages'),
        $first_message = $('#first_message'),
        $consult_message = $('#consult_message'),
        $consult_send = $('#consult_send'),
        $consult_success = $('.success_message', $consult),
        $consult_recall = $('.consult_recall', $consult),
        $consult_device = $('.consult_device', $consult), 
        cid = '',
        user_time_offset = 0,
        consult_autoupdate_time = 1000,
        admin_online_update_id,
        error_message_timeout_id,
        consult_interval;
    
    
    this.show_consult = function(){
//        console.log('show consult');
        if(timeout_id)
            clearTimeout(timeout_id);
        if($consult.is(':hidden')){
//            console.log('$consult is hidden');
            $consult_btn.hide();
//            $consult.show();

           $consult.css('right', '-300px')
                .css('opacity', '0.25');
            $consult.animate({
           opacity: 1,
           right: '+=300',
           height: 'toggle'
           }, 1000, function() {
           // Animation complete.
           });
        }
    };
    
    this.hide_consult = function(){
        $consult.animate({
                opacity: 0,
                right: '-=300',
                height: 'toggle'
            }, 
            1000, 
            function() {
                $consult_btn.show();
                $consult.hide();
            });
    };
    
    var set_error_message = function(form, message){
        clearTimeout(error_message_timeout_id);
        var error_el = form.find('span.error_message');
        error_el.text(message);
        error_message_timeout_id = setTimeout(function(){
            error_el.text('');
        }, 5000);
    };
    
    var get_timestamp = function(){
        return Math.round(+new Date()/1000);
    };
    
    var get_current_time = function(){
        var date = new Date(),
            minutes = date.getMinutes();
        if (minutes < 10) {
            minutes = "0" + minutes
        }
        return date.getHours()+':'+minutes;
    };
    
    var init = function(){
       
       var date = new Date();
       user_time_offset = date.getTimezoneOffset() * -60; // offset from UTC
       

       // начинаем чат
       $('#start_consult').click(function(){
           clearTimeout(timeout_id);
           var $this = $(this),
               phone = $consult_phone.val(),
               phone = phone.replace(/\D/g,''),
               msg = $consult_device.text();
               data = 'phone='+phone+'&device='+msg;
           $this.attr('disabled', true);
           $.ajax({
                url: prefix+'consult/ajax.php?act=new_consult',
                data: data,
                type: "POST",
                dataType: "json",
                success: function(data){
                    $this.attr('disabled', false);
                    if(data.state){
                        $consult_start_form.hide();
                        $consult_success.text($consult_recall.text());
//                        _this.set_consult_updater();
                    }else{
                        set_error_message($consult_start_form, data.msg);
                    }
                }
            });
       });
        
       // отправлям сообщение в чат
       $consult_send.click(function(){
           var message = $consult_message,
               data = 'cid='+cid+'&message='+message.val()+'&time='+get_timestamp();
           
           if($.trim($consult_message.val())){
               var new_el = $first_message.clone().removeAttr('id').addClass('c_message');
               var author = new_el.children('.consult_message_autor');
               author.children('time').text(get_current_time());
               new_el.children('.consult_message_text').text(message.val());
               new_el.appendTo($consult_messages);
               message.val('');
               $consult_messages.scrollTop(9999999);
               $.ajax({
                    url: prefix+'consult/ajax.php?act=add_message',
                    data: data,
                    type: "POST",
                    dataType: "json",
                    success: function(data){
                        if(!data.state){
                            set_error_message($consult_form, data.msg);
                        }
                    }
                });
           }else{
               set_error_message($consult_form, 'Введите ваше сообщение');
           }
           
       });
       $consult_message.keydown(function(e){
           if(e.keyCode == 13){
               $consult_send.click();
               return false;
           }
       });
       
       $consult_btn.click(_this.show_consult);
       
       $('#close_consult').click(function(){
           //disable csroll

           if(!$consult_success.text())
                $consult_success.text(' ');
           write_cookie('restore_close_consult', true);
           _this.hide_consult();
       });
       
       //оффлайн сообщение с чата
       $('#send_offline_message').click(function(){
           var name = $consult_offline_name,
               email = $consult_offline_email,
               msg = $consult_offline_message;
               data = 'name='+name.val()+'&message='+msg.val()+'&email='+email.val();
           $.ajax({
                url: prefix+'consult/ajax.php?act=offline_message',
                data: data,
                type: "POST",
                dataType: "json",
                success: function(data){
                    if(data.state){
                        name.add(email).add(msg).val('');
                    }
                    set_error_message($consult_offline_form, data.msg);
                }
            });
       });
        
    };
    
    var timeout_id,
    first_show = false;
    /*
    $(window).bind('DOMMouseScroll', function(e){
         if(e.originalEvent.detail > 0 && !first_show && !$consult_success.text()) {
            timeout_id = setTimeout( show_smth , 5000);
         }
     });
     //IE, Opera, Safari
     $(window).bind('mousewheel', function(e){
         if(e.originalEvent.wheelDelta < 0 && !first_show && !$consult_success.text()) {
            timeout_id = setTimeout( show_smth , 5000);
         }
     });
     */
    var timeout_id1 = setTimeout(function(){
        $(window).on('scroll', function(e){
             if(!$('div.submenu').is(":visible") && !first_show 
                     && !$consult_success.text()
                     && !read_cookie('restore_close_consult')
                ) {
                first_show = true;
                timeout_id = setTimeout( show_smth , 5000);
             }
         });
     }, 200);

    function show_smth() {
        if($('div.submenu').is(":visible"))
            return false;
        first_show = true;
        _this.show_consult();
        clearTimeout(timeout_id);
        timeout_id = setTimeout( _this.hide_consult , 15000);
    }
    
    // перехват pjax смены страницы
//    $('.item a', $('.services_slider_backstage, .service_listblock')).live('click', function() { 
//        clear_first_show();
//        $consult_start_form.show();
//        $consult_success.text('');
//        return false;
//    }); 
    
    /*
    $('div.submenu').on('mouseenter', function(){
        if(!$consult_success.text())
                $consult_success.text(' ');
    });
    */
    
    function clear_first_show(){
        $consult.hide();
        $consult_btn.show();
        clearTimeout(timeout_id);
        first_show = false;
    }
    
    $consult_phone.on('focus', function(){
         clearTimeout(timeout_id);
    });
    
    init();

    function write_cookie(cookiename, cookievalue) {
        var today = new Date();
        var expire = new Date();
        expire.setTime(today.getTime() + 3600000*2); // 2 hours
        document.cookie = cookiename+"="+escape(cookievalue)
                         + ";expires="+expire.toGMTString();
    }
    
    function read_cookie(cookiename)
    {
       var index = document.cookie.indexOf(cookiename),
           allcookies = document.cookie,
           cookiearray  = allcookies.split(';');
        if (index==-1)
            return false;
        return cookiearray[index].split('=')[1];
    }
    
};

$(_consult);