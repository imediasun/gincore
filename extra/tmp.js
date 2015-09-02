/*
 * 
 * get JscrollPane from here
 * 
 */


var narodna_optika = function(){
    
    var win_w = 0,
        win_h = 0,
        min_win_w = 1024,
        scroll_top = 0,
        slider_w,
        slider_h,
        slider_set,
        resize_timeout,
        win_focus = true,
        $window = $(window),
        $html = $('html'),
        $body = $('body'),
        $main_menu = $('#main_menu', $body),
        $content = $('#content', $body),
        $slider = $('#slider'),
        $onepage = $('#onepage'),
        $articles = $('#articles'),
        $op_up,
        $op_down,
        $timer,
        $op_element,
        $slider_nav,
        shop_images,
        $scroll_text,
        $op_right,
        $op_left,
        $onepage_inner_wrapper,
        lis,
        timer_pos = 0,
        timer_interval,
        slider_interval,
        slider_interval_time = 5000,
        $slide,
        $slider_nav_li,
        $kids,
        $podrostki,
        $woman,
        first_hide = false,
        scroll_timeout,
        scroll_reinitialize,
        scroll_timeout_cnt = 0,
        is_scroll = false,
        $man,
        $old_people,
        images = [];
    
    var get_set = function(){
        if(win_w > 1730){
            slider_w = 1920;
            slider_h = 607;
            slider_set = 5;
            return true;
        }
        if(win_w > 1500){
            slider_w = 1730;
            slider_h = 547;
            slider_set = 4;
            return true;
        }
        if(win_w > 1380){
            slider_w = 1500;
            slider_h = 474;
            slider_set = 3;
            return true;
        }
        if(win_w > 1200){
            slider_w = 1380;
            slider_h = 436;
            slider_set = 2;
            return true;
        }
        slider_w = 1200;
        slider_h = 379;
        slider_set = 1;
        return true;
    }
    
    
    var scroll_to_page = function(id){
        var top = 0,
            index = $('#'+id).data('index');
        if(id){
            top = index * win_h;
        }
        $body.add($html).stop(true, true).animate({scrollTop: top}, 1000, '', function(){
            $op_up.add($op_down).css('visibility', 'visible');
        });
    }
    
    var init_slider_images = function(){
        get_set();
        $slider.add($slide).css({
            width: win_w,
            height: slider_h
        });
        $slider.find('.slide').each(function(){
            var $this = $(this),
                gallery = $this.attr('id'),
                li = $this.find('li'),
                inner_html = '',
                i = 0;
            li.each(function(){
                $(this).css({
                    left: -(slider_w - win_w) / 2,
                    width: slider_w,
                    height: slider_h,
                    backgroundImage: 'url('+prefix+'images/'+gallery+'/'+slider_set+'.png)',
                    backgroundPosition: '0 -'+(slider_h*i)+'px'
                });
                i++;
            });
        });
    }
    
    var set_scene_pos = function(ohx, img, k){
        var m = ohx/k, iobj;
        img.css({marginLeft: m});
    }
    
    var slider_timer = function(show){
        timer_pos = 0;
        clearInterval(timer_interval);
        if(show){
            $timer.removeClass('timer_pause').css({
                backgroundPosition: '0px 0px'
            });
            timer_interval = setInterval(function(){
                $timer.css({
                    backgroundPosition: '0px -'+(16*timer_pos)+'px'
                });
                timer_pos ++;
            }, slider_interval_time / 17);
        }else{
            $timer.addClass('timer_pause');
        }
    }
    
    var set_slider_auto = function(){
        slider_timer(true);
        slider_interval = setInterval(function(){
            if(win_focus){
                slider_timer(true);
                var next = $('.slide_nav_active').next();
                if(!next.length){
                    next = $slider_nav_li.eq(0);
                }
                next.trigger('click');
            }else{
                slider_timer(false);
            }
        }, slider_interval_time);
    }
    
    var scroll_controller = function(){
        scroll_top = document.body.scrollTop;

        scroll_top = scroll_top ? scroll_top : $html.scrollTop();

        var current_pos = scroll_top / win_h,
            current_index = current_pos >> 0,
            current_perc = current_pos >= 1 ? current_pos - current_index : current_pos,
            next_index = current_index + 1,
            current_el = lis.eq(current_index),
            next_el = lis.eq(next_index),
            pos = {op_left: win_w / 2 + 20, op_right: win_w / 2 + 20},
            op_right = win_w - ((win_w - pos.op_right) * current_perc) * 1.2,
            op_left = win_w - ((win_w - pos.op_right) * current_perc) * 1.2;

        lis.hide();

//                $op_up.add($op_down).removeClass('disabled active').hide();

        if(!current_index && next_el.height() < win_h/2){
            $op_up.addClass('disabled');
            $op_down.addClass('active');
        }else if(!next_el.length){
            $op_down.addClass('disabled');
        }


        var pel = current_el;
        if(next_el.height() > win_h/3){
            pel = next_el;
        }
        $op_up.attr('data-pageel', pel.prev().attr('id'));
        $op_down.attr('data-pageel', next_el.attr('id'));

        if(scroll_top > win_h && !first_hide){
            first_hide = true;
            $slider_nav.find('li').mouseleave();
        }

        $slider_nav.find('li').removeClass('active_page');
        $slider_nav.find('li#page_'+current_el.attr('id')).addClass('active_page');

        current_el.stop(true, true).show().css({height: win_h});

        current_el.find('.op_right').css({
            left: pos.op_right
        });

        current_el.find('.op_left').css({
            right: pos.op_left
        });

        next_el.stop(true, true).show().animate({height: win_h * current_perc}, 150);

        next_el.find('.op_right').stop(true, true).animate({
            left: op_right < pos.op_right ? pos.op_right : op_right
        }, 150);

        next_el.find('.op_left').stop(true, true).animate({
            right: op_left < pos.op_left ? pos.op_left : op_left
        }, 150);

        $scroll_text.jScrollPane({
            mousewheel: false,
            showArrows: true,
            verticalDragMinHeight: 100,
            verticalGutter: 8,
            horizontalGutter: 0
        });
    }
    
    var init = function(){
        
        $('.fancy_content').each(function(){
            var self = $(this);
            self.fancybox({
                overlayColor: '#000',
                onComplete: function(){
                    var id = self.attr('href'),
                        lat = self.data('lat'),
                        lng = self.data('lng');
                    gmapinitialize(lat, lng, document.getElementById(id.replace('#', '')));
                },
                onClosed: function(){
                    $(self.attr('href')).html('');
                }
            });
        });
        
        
        
        // articles
        if($articles.length){
           $.getScript(prefix+'extra/articles.js'); 
           $('#siteinfo').mouseenter(function(){
               $('#siteinfo_hint').stop(true, true).fadeIn(200);
           }).mouseleave(function(){
               $('#siteinfo_hint').stop(true, true).fadeOut(200);
           });
        }// articles
        
        // slider
        if($slider.length){
            
            $timer = $('#timer');
            
            $slide = $('.slide');
            $slider_nav_li = $('#islider_nav > ul > li');
            $kids = $('#kids');
            $podrostki = $('#podrostki');
            $woman = $('#woman');
            $man = $('#man');
            $old_people = $('#old_people');
            images['kids'] = [];
            images['kids']['i0'] = $('.i0', $kids);
            images['kids']['i1'] = $('.i1', $kids);
            images['kids']['i2'] = $('.i2', $kids);
            images['kids']['i3'] = $('.i3', $kids);
            images['kids']['i4'] = $('.i4', $kids);
            images['podrostki'] = [];
            images['podrostki']['i0'] = $('.i0', $podrostki);
            images['podrostki']['i1'] = $('.i1', $podrostki);
            images['podrostki']['i2'] = $('.i2', $podrostki);
            images['podrostki']['i3'] = $('.i3', $podrostki);
            images['woman'] = [];
            images['woman']['i0'] = $('.i0', $woman);
            images['woman']['i1'] = $('.i1', $woman);
            images['woman']['i2'] = $('.i2', $woman);
            images['man'] = [];
            images['man']['i0'] = $('.i0', $man);
            images['man']['i1'] = $('.i1', $man);
            images['man']['i2'] = $('.i2', $man);
            images['man']['i3'] = $('.i3', $man);
            images['old_people'] = [];
            images['old_people']['i0'] = $('.i0', $old_people);
            images['old_people']['i1'] = $('.i1', $old_people);
            images['old_people']['i2'] = $('.i2', $old_people);
            
            $slide.bind('mousemove', function(e){ 
                var ohx = win_w/2 - e.pageX,
                    id = $(this).attr('id');

                if(id == 'kids'){
                    set_scene_pos(ohx, images['kids']['i0'], 65);
                    set_scene_pos(ohx, images['kids']['i1'], 45);
                    set_scene_pos(ohx, images['kids']['i2'], 25);
                    set_scene_pos(ohx, images['kids']['i3'], 25);
                    set_scene_pos(ohx, images['kids']['i4'], 15);
                }
                if(id == 'podrostki'){
                    set_scene_pos(ohx, images['podrostki']['i0'], 85);
                    set_scene_pos(ohx, images['podrostki']['i1'], 45);
                    set_scene_pos(ohx, images['podrostki']['i2'], 15);
                    set_scene_pos(ohx, images['podrostki']['i3'], 25);
                }
                if(id == 'woman'){
                    set_scene_pos(ohx, images['woman']['i0'], 60);
                    set_scene_pos(ohx, images['woman']['i1'], 40);
                    set_scene_pos(ohx, images['woman']['i2'], 20);
                }
                if(id == 'man'){
                    set_scene_pos(ohx, images['man']['i0'], 60);
                    set_scene_pos(ohx, images['man']['i1'], 50);
                    set_scene_pos(ohx, images['man']['i2'], 40);
                    set_scene_pos(ohx, images['man']['i3'], 35);
                }
                if(id == 'old_people'){
                    set_scene_pos(ohx, images['old_people']['i0'], 60);
                    set_scene_pos(ohx, images['old_people']['i1'], 40);
                    set_scene_pos(ohx, images['old_people']['i2'], 30);
                }

            });
            
            $slider.mouseenter(function(){
                clearInterval(slider_interval);
                slider_timer(false);
            }).mouseleave(set_slider_auto);
            
            set_slider_auto();
            
            $slider_nav_li.click(function(){
                var $this = $(this),
                    id = $(this).attr('id').replace('slide_', ''), 
                    next_el = $('#'+id);
                if(!next_el.hasClass('active_slide')){
                    $slider_nav_li.removeClass('slide_nav_active');
                    $this.addClass('slide_nav_active');
                    next_el.css('opacity', 1);
                    $slide.stop(true, true);
                    $('.active_slide').stop(true, true).fadeTo(600, 0, function(){
                        $slide.removeClass('active_slide');
                        next_el.addClass('active_slide');
                    });
                }
            });
        } // slider end
        
        
        // onepage
        if($onepage.length){
            
            $op_up = $('#op_up');
            $op_down = $('#op_down');
            $op_element = $('.op_element', $onepage);
            $scroll_text = $('.scroll_text');
            $op_right = $('.op_right', $onepage);
            $op_left = $('.op_left', $onepage);
            $onepage_inner_wrapper = $('.onepage_inner_wrapper', $onepage);
            lis = $onepage.children('li');
            shop_images = $('.shop_images');
            $slider_nav = $('#slider_nav', $onepage);
            
            $slider_nav.find('li').mouseenter(function(){
                var $this = $(this),
                    hint = $('div.op_hint', $this);
                hint.css({
                    display: 'block',
                    opacity: 0
                });
                hint.stop(true, true).animate({right: 45, opacity: 1}, 300);
            }).mouseleave(function(){
                var $this = $(this),
                    hint = $('div.op_hint', $this);
                hint.stop(true, true).animate({right: 35, opacity: 0}, 300, '', function(){
                    hint.css({
                        display: 'none'
                    });
                });
            });
            
            $('map[name=diagram] area').mouseenter(function(){
                $('.diagrama_hover').fadeOut(200);
                $('#dh_'+$(this).attr('id')).stop(true, true).fadeIn(200);
            }).mouseleave(function(){
                $('.diagrama_hover').fadeOut(200);
            });
            
            $('.submenu a, .op_links a, map[name=diagram] area, .scroll_text a').add($slider_nav.find('a')).click(function(){
                $('.submenu:visible').mouseleave();
                var id = $(this).attr('href').split('#');
                if(window.location.pathname == id[0]){
                    scroll_to_page(id[1]);
                    return false;
                }
            });
            
            
            
            $window.scroll(function(){
                is_scroll = true;
                clearTimeout(scroll_timeout);
                clearInterval(scroll_reinitialize);
                scroll_timeout = setTimeout(function(){
                    is_scroll = false;
                    clearInterval(scroll_reinitialize);
                    scroll_reinitialize = setInterval(function(){
                        if(!is_scroll){
                            if(scroll_timeout_cnt > 5){
                                scroll_timeout_cnt = 0;
                                clearInterval(scroll_reinitialize);
                            }else{
                                scroll_controller();
                                scroll_timeout_cnt ++ ;
                            }
                        }
                    }, 100);
                }, 100);
                
                scroll_controller();
                
            });
            
            
            
//            $content.mousemove(function(e){
//                var up = true;
//                if($op_up.hasClass('disabled')){
//                    up = false;
//                }
//                if((e.pageY - scroll_top) < 150){
//                    if(up){
//                        $op_up.fadeIn(200);
//                    }
//                    $op_up.css({
//                        top: (e.pageY - scroll_top) - 49,
//                        left: e.pageX - 47
//                    });
//                }else if(up){
//                    $op_up.fadeOut(200);
//                }
//
//                var down = true;
//                if($op_down.hasClass('disabled') || $op_down.hasClass('active')){
//                    down = false;
//                }
//                if(win_h-(e.pageY - scroll_top) < 80){
//                    if(down){
//                        $op_down.fadeIn(200);
//                    }
//                    $op_down.css({
//                        top: (e.pageY - scroll_top) - 49,
//                        left: e.pageX - 47
//                    });
//                }else if(down){
//                    $op_down.fadeOut(200);
//                }
//            });
            
//            $('.op_nav').click(function(){
//                var _this = $(this),
//                    to = _this.attr('data-pageel');
//                scroll_to_page(to);
//                _this.fadeOut(200, function(){
//                    $op_up.add($op_down).css('visibility', 'hidden');
//                });
//            });
            
            $content.mouseleave(function(){
                if(!$op_down.hasClass('disabled') && !$op_down.hasClass('active') 
                   && !$op_up.hasClass('disabled') && !$op_up.hasClass('active')){
                        $op_up.add($op_down).fadeOut(200);
                }
            });
            
        } // onepage end
        
        // resize
        $window.resize(function(){
            win_h = $window.height();
            win_w = $window.width();
            
            if(win_w < min_win_w){
                win_w = min_win_w;
            }
                   
//            get_size();
            
//            $html.removeAttr('class').addClass('size'+site_size);
            
            $body.add($main_menu).css({
                width: win_w
            });
            
            if($articles.length){
                $articles.add($content).add($html).add($body).css({
                    width: win_w,
                    height: win_h
                });
            }
            
            if($onepage.length){
                
                shop_images.each(function(){
                    var self = $(this),
                        nav = '#'+self.data('navid'),
                        w = win_w * 0.4;
                    $(nav).html('');
                    self.cycle('destroy').cycle({ 
                        pager:  nav
                    });
                    shop_images.add(shop_images.find('div')).css({width: w, height: w / 1.334});
                });
                
//                $('#u_right').css({
//                    height: win_h - 150
//                });
                
                var i = 0;
                lis.each(function(){
                    $(this).attr('data-index', i).css({
                        width: win_w,
                        zIndex: i+1
                    });
                    i++;
                });

                $onepage_inner_wrapper.css({
                    width: win_w,
                    height: win_h
                });

                $op_element.css({
                    width: win_w * 0.4,
                    height: win_h - 200
                });
                $scroll_text.css({
                    height: win_h - 200 - 60,
                    width: win_w * 0.4
                });

                $op_left.filter(':hidden').css({
                    right: win_w
                });

                $op_right.filter(':hidden').css({
                    left: win_w
                });
                
                $content.css({
                    width: win_w,
                    height: lis.size() * win_h
                });
                
                $body.css({
                    width: $window.width() < min_win_w ? min_win_w : $window.width()
//                    height: win_h
                });
                
                $window.scroll();
            }
            
            if($slider.length){
                $slider.css({
                    marginTop: $main_menu.height()
                });
                init_slider_images();
            }
            
//            clearTimeout(resize_timeout);
//            resize_timeout = setTimeout(function(){
//                
//            }, 100);
            
        }); // resize end
        
//        var menu_timeout;
        
        $('#root_menu li', $main_menu).mouseenter(function(){
            var $this = $(this),
                link = $this.children('a'),
                height = $this.height(),
                pos_y = 96 - height,
                submenu = link.next('div.submenu');
            if(!$this.hasClass('activeMenu')){
                link.stop(true)
                     .css({backgroundPosition: '0px -96px'})
                     .animate({color: '#fff', backgroundPosition: '0px -'+pos_y+'px'}, 250, '', function(){
                     });
                         submenu.stop(true, true).fadeIn(150);
            }else if(submenu.length){
//                menu_timeout = setTimeout(function(){
                    submenu.stop(true, true).fadeIn(150);
//                }, 250);
            }
        }).mouseleave(function(){
//            clearTimeout(menu_timeout);
            var $this = $(this),
                link = $this.children('a'),
                submenu = link.next('div.submenu');
         
            if(!$this.hasClass('activeMenu')){
                var out_menu = function(){
                    link.stop(true).animate({color: '#2E4A9E', backgroundPosition: '0px -96px'}, 250); // 24A057
                }
                if(submenu.length){
                    out_menu();
                    submenu.stop(true, true).fadeOut(300);
                }else{
                    out_menu();
                }
            }else if(submenu.length){
                submenu.stop(true, true).fadeOut(400);
            }
        });
                
        $('.submenu a').mouseenter(function(){
            $(this).stop(true, false).animate({backgroundColor: '#ffffff'}, 300);
        }).mouseleave(function(){
            $(this).stop(true, false).animate({backgroundColor: '#f5f4f4'}, 300);
        });
        
        $('a[href=#]').click(function(){
            return false;
        });
        
        $window.load(function(){
            if($onepage.length){
                if(window.location.hash){
                    setTimeout(function(){
                        is_scroll = true;
                        scroll_to_page(window.location.hash.replace('#', ''));
                    }, 100);
                }
            }
        });

        $window.focus(function(){
            win_focus = true;
        });

        $window.blur(function(){
            win_focus = false;
        });

        $window.resize();
        
        if($.browser.msie && $.browser.version < 9){
            $.reject({ 
                reject: {   
                    all: false,  
                    msie5: true,
                    msie6: true,
                    msie7: true,
                    msie8: true
                },
                browserInfo: {
                    msie: {
                        text: 'Internet Explorer 9',
                        url: 'http:\/\/www.microsoft.com/ru-ru/windows/internet-explorer/onlinekrasota/'
                    },
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
                display: ['msie','firefox','chrome','opera'],
                header: 'Вы знаете, что ваш браузер Internet Explorer очень устарел?',
                paragraph1: 'Ваш браузер устарел и может не корректно отображать наш и другие современные сайты. Список наиболее популярных браузеров находится нниже.', // Paragraph 1
                paragraph2: 'Выберите любую из предложеных иконок и вы попадете на страницу загрузки.<br><br>', // Paragraph 2
                closeLink: '',
                closeMessage: 'Загрузите и установите современный браузер и сново зайдите на сайт.',
                imagePath: prefix+'images/design/'
            });
        }

    }
    
    return init();
    
};

$(document).ready(narodna_optika);