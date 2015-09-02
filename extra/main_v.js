var site_init = function(){
    
    var $html = $('html'),
        $window = $(window),
        $navigation = $('#navigation'),
        $root_menu = $('#root_menu'),
        $active_menu = $('.active_menu'),
        $toolbar = $('#toolbar'),
        $qformcenter = $('.qformcenter'),
        $add_question = $('#add_question'),
        $question = $('#question'),
        
        loader = '<img width="15" height="15" src="'+prefix+'images/loading.gif" alt=" ">',

        t_h = $toolbar.height(),
        change_location = true, // флаг для тулбара в меню(просто перехать - false, переезд со сменой страницы - тру)
        win_h = 0,
        win_w = 0,
        angle_step = 10, 
        current_angle = 0, 
        img_l,
        loader_interval_id, 
        canvas_loading,
        loader_ready = false,
        slider_set,
        min_win_w = 970,
        min_win_h = 600,
    
    init = function(){
        
        $.extend($.support, {
            touch: "ontouchend" in document
        });
        
        if($.support.touch){
            $html.addClass('touch_device');
        }
        
        $.browser.chrome = $.browser.webkit && !!window.chrome;
        $.browser.safari = $.browser.webkit && !window.chrome;

        // кликаем на ссылку в главном меню
        $root_menu.find('a').click(function(){
            move_toolbar($(this), change_location);
            if ($question.hasClass('active_qform'))
                $question.click();
            return false;
        });
        
        // закрыть форму Задать вопрос
        $('#close_question', $add_question).click(function(){
            $question.click();
        });
        
        // открыть форму Задать вопрос
        $question.click(function(){
            var $this = $(this),
                hide_left = -($add_question.outerWidth() - $navigation.width() + 10),
                show_left = $navigation.outerWidth() - 2;
            if(!$this.hasClass('active_qform')){
                $this.addClass('active_qform');
                $('.qform_message_big').hide();
                $add_question.show().css({left: hide_left}).stop(true).animate({left: show_left, avoidTransforms: $.support.touch}, 600);
            }else{
                $this.removeClass('active_qform');
                $add_question.stop(true).animate({left: hide_left, avoidTransforms: $.support.touch}, 600, function(){
                    $add_question.hide();
                });
            }
        });
  
        // в форме Задать вопрос делаем активными поля в зависимости от заполнения
        $('.qform input[type=text], .qform textarea').keyup(function(){
            var $this = $(this),
                 text = $.trim($this.val());
            if(text){
                $this.addClass('not_empty');
                var active = true;
                $this.siblings('input[type=text], textarea').each(function(){
                    var $this = $(this),
                        text = $.trim($this.val());
                    if(!text){
                        active = false;
                    }
                });
            }else{
                active = false;
                $this.removeClass('not_empty');
            }
            if(active){
                $this.siblings(':button').addClass('enabled_qform');
            }else{
                $this.siblings(':button').removeClass('enabled_qform');
            }
        });
  
        // отправляем форму Задать вопрос
        $('.qform :button').click(function(){
            var $this = $(this),
                form = $this.parent(),
                form_message = form.find('.qform_message'),
                form_message_big = form.find('.qform_message_big'),
                type = form.data('type');
            
            if($this.hasClass('enabled_qform')){
                var data = '',
                    input1,
                    input2,
                    input3;
                if(type == 1){
                    input1 = form.find('textarea');
                    input2 = form.find('input[name=email]');
                    data = 'message='+input1.val()+'&email='+input2.val();
                }else if(type == 2){
                    input1 = form.find('input[name=name]');
                    input2 = form.find('input[name=tel]');
                    input3 = form.find('input[name=time]');
                    data = 'name='+input1.val()+'&tel='+input2.val()+'&time='+input3.val();
                }
                form_message.html(loader);
                $.ajax({
                    url: prefix+'ajax.php?act=feedback&type='+type,
                    data: data,
                    type: "POST",
                    dataType: "json",
                    success: function(data){
                        if(data.state){
                            input1.add(input2).add(input3).val('').removeClass('not_empty');
                            $this.removeClass('enabled_qform');
                            if(type == 1){
                                form_message.html(data.msg);
                            }
                            if(type == 2){
                                form_message.html('');
                                form_message_big.show().css({
                                    top: form.height()
                                }).animate({top: 0}, 300);
                            }
                            setTimeout(function(){
                                form_message.html('');
                            }, 5000);
                        }else{
                            form_message.html(data.msg);
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) { 
                        form_message.html('');
                        alert ('Не могу отправить запрос');
                    }
                });
            }
        });
        
        $('.qform_message_big .close_small').click(function(){
            var el = $(this).parents('.qform_message_big');
            el.animate({top: el.parent().height()}, 350, function(){
                el.hide();
            });
        });


        window.onresize = resize;
        window.onload = function(){
            resize();
            show_loading(false);
//            $('#global').animate({opacity: 1}, 500);
        };
        
        resize();

        if($.browser.version < 8){ 
            $.reject({ 
                reject: {   
                    all: false,  
                    msie5: true,
                    msie6: true,
                    msie7: true
                },
                browserInfo: {
                    chrome: {
                        text: 'Chrome',
                        url: 'http:\/\/www.google.com/chrome/'
                    },
                    firefox: {
                        text: 'Firefox', // Text below the icon
                        url: 'http:\/\/www.mozilla.com/firefox/' // URL For icon/text link
                    },
                    opera: {
                        text: 'Opera',
                        url: 'http:\/\/www.opera.com/download/'
                    }
                },
                display: ['chrome','firefox','opera'],
                header: 'Вы знаете, что ваш браузер Internet Explorer очень устарел?',
                paragraph1: 'Ваш браузер устарел и может не корректно отображать наш и другие современные сайты. Список наиболее популярных браузеров находится нниже.', // Paragraph 1
                paragraph2: 'Выберите любую из предложеных иконок и вы попадете на страницу загрузки.<br><br>', // Paragraph 2
                closeLink: '',
                closeMessage: 'Загрузите и установите современный браузер и сново зайдите на сайт.',
                imagePath: prefix+'images/'
            });
        }

    },
    
/*
 *    Перемещение курсора меню
 *        
 */    
    move_toolbar = function(anchor, change_loc){
        var li = anchor.parent('li'),
            h = li.outerHeight(true),
            pos = li.position(),
            css = {top: pos.top + h/2 - t_h/2, opacity: 1, avoidTransforms: true};
        if(change_loc){
            if(anchor.hasClass('active_menu')){
                window.location.href = anchor.attr('href');
            }else{
                $toolbar.animate(css, 350, 'easeOutBack', function(){ 
                    window.location.href = anchor.attr('href');
                });
            }
        }else{
            change_location = true;
            $toolbar.css(css);
        }
    },
    
    
/*
 *  Изменяем размеры страницы при 
 *  изменении размеров окна
 *        
 */    
    resize = function(){
        
        //редирект на моб версию
        if($window[0].screen.width <= 800 && !ipad){
            $('html').html('');
            window.location = prefix+'m/';
            return true;
        }
        
        win_w = window.innerWidth || document.documentElement.clientWidth;
        win_w = win_w >= min_win_w ? win_w : min_win_w;
        win_h = window.innerHeight || document.documentElement.clientHeight;
        win_h = win_h > min_win_h ? win_h : min_win_h;

        $qformcenter.css({
            marginTop: (win_h - $qformcenter.outerHeight()) / 2
        });


        $html.css({
            width: win_w,
            height: win_h
        });
        
        if($active_menu.length){ 
            change_location = false;
            $active_menu.click();
        }else{
            $toolbar.css({opacity: 0});
        }

        var hide_left = -($add_question.outerWidth() - $navigation.width() + 10),
            show_left = $navigation.outerWidth() - 2;
        if($question.hasClass('active_qform')){
            $add_question.css({left: show_left});
        }else{
            $add_question.css({left: hide_left});
        }

    },

/*
 *  Рисуем прелоадер
 *  maybe inside template ?
 *
 */
    draw = function(){
        var canvas = canvas_loading.getContext('2d');
        canvas.clearRect(0, 0, 110, 110);
        canvas.save();
        canvas.translate(55, 55);
        current_angle = current_angle == 360 ? 1 : current_angle + angle_step;
        canvas.rotate( current_angle * Math.PI / 180 );
        canvas.drawImage(img_l, -55, -55);
        canvas.restore();
    },

    canvas_loader = function(){
        if(($.browser.msie && $.browser.version > 8) || !$.browser.msie){
            canvas_loading = document.getElementById('loading');
            img_l = new Image();
            img_l.onload = function(){
                loader_ready = true;
                show_loading(true);
            };
            img_l.src = prefix + 'images/loader.png';
        }
    },

    show_loading = function(show){
        if(loader_ready){
            clearInterval(loader_interval_id);
            if(show){
                if(($.browser.msie && $.browser.version > 8) || !$.browser.msie){
                    loader_interval_id = setInterval(function(){
                        draw();
                    }, 50);
                }
                canvas_loading.style.zIndex = 999999;
            }else{
                canvas_loading.style.zIndex = -1;
            }
        }
    },
    
    
 /* 
  *  Создаём куки
  * 
  */
    createCookie = function(name,value,days) {
	if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
	}else{
            var expires = "";
        } 
	document.cookie = name+"="+value+expires+"; path="+prefix;
    };
    
    // инициализируем лоадер
    canvas_loader();
        
    init();
    
};

$(site_init);