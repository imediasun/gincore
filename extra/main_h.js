var site_init = function(){
    
    var $html = $('html'),
        $window = $(window),
        win_h = 0,
        win_w = 0,
        win_h_c = 0,
        min_win_w = 970,
        min_win_h = 600,
        $header = $('#header'),
        loader = '<img width="15" height="15" src="'+prefix+'images/loading.gif" alt=" ">',
        hashchange = true,
        angle_step = 10, 
        current_angle = 0, 
        img_l,
        loader_interval_id, 
        canvas_loading,
        loader_ready = false,
        tree_hover_timeout,

    
    init = function(){
/*
        // hints - показывать всплывающие подсказки
        $('.hinthere').mouseenter(function(){
            var hint = $(this).find('.hint');
            hint.stop().fadeIn(200);
        }).mouseleave(function(){
            var hint = $(this).find('.hint');
            hint.stop().fadeOut(200);
        });
*/     
        
//        function reposition_footer() {
////            console.log($html.height()+' - '+$window.height());
//            if($html.height() < $window.height()){
//                $html.css('height','100%');
//                $('.share_horizontal .footer_text').css('min-height','53px');
//                $('#footer').css('position', 'absolute');
//            } else {
//                $html.css('height','auto');
//                $('.share_horizontal .footer_text').css('min-height','0');
//                $('#footer').css('position', 'relative');
//            }
//        }
//        reposition_footer();
//        window.onresize = function (){reposition_footer()};
        
        $.extend($.support, {
            touch: "ontouchend" in document
        });
            
        window.onscroll = function(){
            if(!$.support.touch){
                if($(window).width()>960) return true;
                var topbar = $('#header'),
                    scrollLeft = window.scrollX;
                topbar.css('left', '-' +scrollLeft + 'px');
            }
        };
        
        if($.support.touch){
            $html.addClass('touch_device');
            
            $('.tree_level').click(function(){
                var $this = $(this),
                    children = $this.children();
                $this.siblings('.tree_level').children().hide();
                if(children.is(':hidden')){
                    children.stop().show();
                }else{
                    children.stop().hide();
                }
            })
            
        }else{
            $('.tree_level').mouseenter(function(){
                var $this = $(this),
                    children = $this.children();
                clearTimeout(tree_hover_timeout);
                tree_hover_timeout = setTimeout(function(){
                    children.stop().show();
                }, 50);
            }).mouseleave(function(){
                clearTimeout(tree_hover_timeout);
                var $this = $(this),
                    children = $this.children();
                children.stop().hide();
            });
        }
        
        
        $.browser.chrome = $.browser.webkit && !!window.chrome;
        $.browser.safari = $.browser.webkit && !window.chrome;
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
/*
    // выпадающее окно контактов
        $('#contacts_inner').click(function(){
            $('#contacts_body').stop(true).slideToggle(300);
        });
        
        $('#close_contacts').click(function(){
            $('#contacts_inner').click();
        });
*/      
        $(window).load(function(){
            // init pseudo anchors
            setTimeout(function(){
                $('.anchor').each(function(){
                    var $this = $(this),
                        $anchor = $('<a>').html($this.html());
                    var attributes_length = $this[0].attributes.length;
                    for (var i = 0; i < attributes_length; i++) {
                        var attr_name = $this[0].attributes[i].name,
                            attr_value = $this[0].attributes[i].value;
                        if(attr_name == 'data-url'){
                            attr_name = 'href';
                        }
                        $anchor.attr(attr_name, attr_value);
                    }
                    $anchor.removeClass('anchor');
                    $this.replaceWith($anchor);
                });
            });
            
            show_loading(false);
        });

        if($('#contacts_city_map').length){
            var myOptions = {
              zoom: 15,
              mapTypeControl:false,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map(document.getElementById("contacts_city_map"), myOptions);
            var marker = null;
            var infowindow = null;
            var i = document.createElement('img'); // or new Image()
            i.onload = function() {
                var icon = new google.maps.MarkerImage(i.src);
                $('.tabs>li>a').click(function(e){
                    e.preventDefault();
                    var $this = $(this),
                        data = $this.parent().data('content');

                    var marker_pos = new google.maps.LatLng(data.lat, data.lng);
                    if(marker !== null){
                        marker.setMap(null);
                    }
                    if(infowindow !== null){
                        infowindow.close();
                    }
                    marker = new google.maps.Marker({
                        position: marker_pos,
                        map: map,
                        icon: icon
                    });
                    map.setCenter(marker.getPosition());
                    setTimeout(function(){
                        infowindow = new google.maps.InfoWindow({
                            content: data.content
                        });
                        if(data.content){
                            google.maps.event.addListener(marker, 'click', function () {
                                infowindow.open(map, marker);
                            });
                            infowindow.open(map, marker);
                        }
                    }, 500);

                    $this.parent().siblings('li').removeClass('active');
                    $this.parent().addClass('active');
                });

                var hash = window.location.hash;

                if(hash.indexOf('#tab-') >= 0){
                    $('.tabs>li>a[href='+hash+']').click();
                }else{
                    $('.tabs>li:first>a').click();
                }
            };
            i.src = prefix+'images/flag.png';
        }

    }, // init

// рисуем прелоадер
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
//                show_loading(true);
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
                    }, 20);
                }
                canvas_loading.style.opacity = 1;
                canvas_loading.style.zIndex = 999999;
            }else{
                canvas_loading.style.zIndex = -10;
                canvas_loading.style.opacity = 0;
            }
        }
    };
    
    canvas_loader();
    
    init();
    
    
      
    
    /**
     * Pjax for service_slider
     */
//    var pjax_services_simple = function() {
//    
//        var 
//            $left_menu = $('.service_listblock .pjax'),
//            $slide_menu = $('.services_slider_backstage .pjax'),
//            $footer1 = $('.footer1'),
//            $content_inner = $('#content_inner'),
//    
//            $title = $('.service_page_title', $content_inner),
////            $service_listblock = $('.service_listblock', $content_inner),
//            $service_block = $('.service_block', $content_inner),
//            $side_banners = $('.side_banners', $content_inner),
//            $buy_old_block = $('.buy_old_block', $content_inner),
//            $buy_old_popup = $('.tradein-popup'),
//            $consult_device = $('#consult .consult_device', $content_inner),
//            $article = $('.article_block_inner', $content_inner),
//            $service_consult = $('.service_contacts .consult_inner', $content_inner),
//            $new_content = $('#false_content')
//
//        ;
//
//        $('body').bind('start.pjax',function() {
//            show_loading(true); 
//        });
//        
//        $('body').bind('success.pjax', function(e, html, status, xhr, options) {
//            show_loading(false); 
//            var magic = function(){
//                if($('#consult_btn').length) {
//                    $(_consult);
//                }
//            };
//            
//            var target_class = e.target.attributes.class.value;
//            // hide all
//            $footer1.animate({opacity: 0, avoidTransforms: $.support.touch}, 200);
//            $content_inner.animate({opacity: 0, avoidTransforms: $.support.touch}, function(){
//                $new_content.html(html);
//                
//                var title = $new_content.find('title').text();
//                if (title) document.title = title;
//                
//                $('#bread_crumbs').replaceWith($new_content.find('#bread_crumbs').clone());
//                
//                $footer1.html($('.footer1', $new_content).html());
//                var $horizontal_class = $('.footer1', $new_content).hasClass('share_horizontal');
//                if($horizontal_class && !$footer1.hasClass('share_horizontal'))
//                    $footer1.addClass('share_horizontal');
//                if(!$horizontal_class)
//                    $footer1.removeClass('share_horizontal');
//                
//                $article.html($new_content.find('.article_block_inner').html());
//                if(!$new_content.find('.buy_old_block').hasClass('hidden')) {
//                    $buy_old_block.show();
//                    $buy_old_block.html($('.buy_old_block', $new_content).html());
//                    $buy_old_popup.html($('.tradein-popup', $new_content).html());
//                } else {
//                    $buy_old_block.hide();
//                }
//                
//                $title.html($('.service_page_title', $new_content).html());
////                $service_listblock.html($('.service_listblock', $new_content).html());
//                $service_block.html($('.service_block', $new_content).html());
//                $side_banners.html($('.side_banners', $new_content).html());
//                $consult_device.html($('#consult .consult_device', $new_content).html());
//                $article.html($('.article_block_inner', $new_content).html());
//                
//                $('.service_recall', $service_consult).hide();
//                $('.service_form', $service_consult).show();
//                
//                $new_content.html('');
//                
//                countdown.init();
//                
//                $('.credit_consult input[name="phone"]').mask("380 (n9) 999-99-99");
//
//            });
////            magic();
//            if($article.hasClass('service_desc'))
//                $article.removeClass('service_desc');
//            // show all
//            $content_inner.animate({opacity: 1, avoidTransforms: $.support.touch}, 200);
//            $footer1.animate({opacity: 1, avoidTransforms: $.support.touch}, 200);
//        });
//
//        $.hash = '#!/';
//        $.siteurl = 'http://'+window.location.host+prefix;
//        $.container = '#content_inner';
//        
//        $slide_menu.pjax('#content_inner', {
//            autoSetContent: false
//        });
//
//        $slide_menu.live('click', function(){
//            $slide_menu.parent().removeClass('active');
//            $(this).parent().addClass('active');
//            $left_menu.parent().removeClass('active');
//            $left_menu.parent().find('[href="'+$(this).attr('href')+'"]')
//                    .parent().addClass('active');
//        });
//        
//        $left_menu.pjax('#content_inner', {
//            autoSetContent: false
//        });
//        
//        $left_menu.live('click', function(){
//            $left_menu.parent().removeClass('active');
//            $(this).parent().addClass('active');
//            $slide_menu.parent().removeClass('active');
//            $slide_menu.parent().find('[href="'+$(this).attr('href')+'"]')
//                    .parent().addClass('active');
//        });
//        
//    };
//
//    $(pjax_services_simple);
};

$(site_init);



/*  Submenu on hower */
var submenu_init = function(){
    var $list = $('.left_menu').find('.submenu_present');

    $list.live('mouseenter', function(){
            $list.children('.submenu').hide();
            var $this = $(this),
                link = $this.children('a'),
                submenu = $this.children('.submenu');

            if(submenu.length){
                resize_submenu(submenu);
                submenu.stop(true, true).fadeIn(150);
            }
        })
        .live('mouseleave',function(){
            var $this = $(this),
                link = $this.children('a'),
                submenu = $this.children('.submenu');

                if(submenu.length){
                    submenu.stop(true, true).fadeOut(1000);
                }

        });
        
        /*
         * resize submenu to 4 columns
         * @param {type} submenu
         * @returns {undefined}
         * 
         */
        function resize_submenu(submenu) {
            var $wh = $(window).height(),
                $mh = submenu.height();
        
            if ($mh > $wh*0.6 && submenu.length<2) {
//                console.log($wh + ' - ' + $mh);
                
                var max_heigth = $wh*0.6,
                    lis = submenu.find('li'),
                    links = lis.find(':first'),
                    li_count = lis.length;
//                    var a_padding = parseInt(lis.find(':first').css('padding-top'));
                // if no show - can't  count elements
                submenu.show();
                var maxHeight = Math.max.apply(null, links.map(function ()
                    {
                        return $(this).height();
                    }).get());
                submenu.hide();
                // aligh blocks height
                links.each(function(){ 
                    $(this).height(maxHeight);
                });
                    
//                    lis.length; //count elements
                var num_els = Math.ceil((max_heigth / maxHeight));
                var num_cols = Math.ceil(lis.length / num_els);
                if (num_cols > 4)
                    num_cols = 4;
                num_els =  Math.ceil(lis.length / num_cols);

                var add_menu ='',
                    left_margin = submenu.parent().width()+submenu.width();
                // build more div + ul
                for (var i = num_cols; i > 1; i--) {
                    left_margin = submenu.parent().width()+ (i-1)*submenu.width();
                    add_menu = '<div class="submenu submenu_col'+i+'"\
                            style="left:'+left_margin+'px"\
                            >\
                            <ul class="submenu_inner">\
                            </ul></div>';
                    submenu.after(add_menu);
                }
                submenu.parent().find('.submenu').show();
                // move lis
                lis.each(function(index) {
                    if(index>=(num_els)){
//                        console.log(submenu.parent()
//                            .find('.submenu_col'+Math.ceil(index/num_els)+' ul'));
//                        console.log(Math.floor(index/(num_els)+1));
                        $(this).appendTo(
                            submenu.parent()
                            .find('.submenu_col'+Math.floor(index/(num_els)+1)+' ul'));
                    }
                });
                
            }
            
            
            // summenu to window align
            var $mfh = submenu.height();
//            console.log($mh + ' - ' +$mfh);
            var $dtop = $(document).scrollTop();
            var $mtop = submenu.parent().offset().top;
            var $off = $mtop - $dtop; // relative screen
            var $lih = submenu.parent().height();
            
            submenu = submenu.parent().children('.submenu');
                
            if ($off < (0.2*$wh)){
//                console.log('$top < (0.3*$wh');
                submenu.css('top', 0);            
            } else if ($off + $lih > (0.8*$wh)) {
//                console.log('$lih');
                submenu.css('top', $lih - $mfh);
            } else if ( $mfh > 0.9*$wh ) {
//                console.log('$mfh > $wh');
                submenu.css('top', 0.1*$wh - $off);
            } else if ($off + $mfh > $wh) {
//                console.log('mid');
                if(($off+$mfh/2) > $wh || ($off-$mfh/2) < 0)
                    submenu.css('top', 0.1*$wh - $off);
                else
                    submenu.css('top', -$mfh/2);
            } else {
//                console.log($off +' - ' + $mfh +' > ' + $wh );
                submenu.css('top', 0);
            }
                    
            
            
        } 
        // disable click on menu with submenus
        /*
        .find('a').click(function(){
            if($(this).parent().hasClass('submenu_present'))
                return false;
        });
        */
};

$(submenu_init);


/*  Submenu on hower */
var submenu_top_init = function () {
    var $list = $('.top_menu_1').find('li.submenu_present');
    var $cover = $('#page_content_body_cover');
    var $animation = false;
    var $cover_init = false;

    $list.live('mouseenter', function () {
        var $this = $(this),
            link = $this.children('a'),
            submenu = $this.children('.top_menu_2'),
            $menu_delay = 350;

        if (submenu.length) {
            $animation = true;
            if ($cover_init == true) {
                $cover.stop(true, true).delay(500).queue(function () {
                    $(this).show();
                    $(this).dequeue();
                });
            }

            if ($list.children('.top_menu_2').is(':visible')) {
                $menu_delay = 0;
            }
            submenu.stop(true, true).delay($menu_delay).queue(function () {
                $list.children('.top_menu_2')
                     .css('z-index', '-2')
                     .hide();
                $(this).find('.center_block').stop(true, true).fadeIn(400).end()
                       .css('z-index', '-1')
                       .slideDown(3 * $menu_delay, function () {
                        });
                $(this).dequeue();
            });
        }
    }).live('mouseleave', function () {
        var $this = $(this),
            link = $this.children('a'),
            submenu = $this.children('.top_menu_2');
        $animation = false;
        if (submenu.length) {
            $this.find('.center_block').fadeOut(500, function () {
                if ($animation)
                    return false;
                if ($cover_init == true) {
                    $cover.stop(true, true).delay(1500).queue(function () {
                        $(this).hide();
                        $(this).dequeue();
                    });
                }
            });
            submenu.stop(true, true).slideUp(700);
        }
    });
    $('.top_menu_2', $list).live('click', function () {
        $animation = false;
        $(this).stop(true, true).slideUp(500);
        if ($cover_init == true) {
            $cover.stop(true, true).delay(500).queue(function () {
                $(this).hide();
                $(this).dequeue();
            });
        }
    });
    if ($cover_init == true) {
        $cover.live('click', function () {
            $animation = false;
            $('.top_menu_2', $list).stop(true, true).slideUp(500);
            $cover.stop(true, true).delay(500).queue(function () {
                $(this).hide();
                $(this).dequeue();
            });
        });
    }
};
$(submenu_top_init);

/*  Submenu on hower */
var submenu_floor_init = function(){
    var $list = $('.footer_menu_1').find('li.submenu_present');

    $list.live('mouseenter', function(){
            $list.children('.top_menu_2').hide();
            var $this = $(this),
                link = $this.children('a'),
                submenu = $this.children('.top_menu_2');

            if(submenu.length){
//                resize_submenu(submenu);
                submenu.stop(true, true)
//                        .delay(700)
                        .fadeIn(150);
            }
        })
        .live('mouseleave',function(){
            var $this = $(this),
                link = $this.children('a'),
                submenu = $this.children('.top_menu_2');

                if(submenu.length){
                    submenu.stop(true, true).fadeOut(700);
                }

        });
        
        $('.top_menu_2', $list).live('click', function(){
            $(this).hide();
        })
        .bind('touchend', function(){
            $(this).hide();
        }) 
        ;

};

$(submenu_floor_init);

/*  Submenu on click */
var submenu_init_onclick = function(){
    var $list = $('.left_menu').find('.submenu_present');
        $list.find('a').click(function(){
            var $main = $(this).parent();
            if($main.hasClass('submenu_present')){      
                var $submenu = $main.children('.submenu');
                if ($submenu.is(":visible")) {
                    $submenu.stop(true, true).fadeOut(150);
                } else {
                    $('.submenu').stop(true, true).fadeOut(150);
                    $submenu.stop(true, true).fadeIn(150);
                }
                return false;
            }
        });
        $('html').click(function() {
            $('.submenu').hide();
        });
};

//$(submenu_init_onclick);

/* header gallery */
var header_gallery = function(){
    var $head_pics = $('.head_pics'),
        $images = $head_pics.children();
        
        if($images.length < 1){
            $head_pics.attr('style', 'padding:0;')
                .height(0);
        }
};

$(header_gallery);



/* ajax forms */
var ajax_forms = function(){
    var $form = $('.form form'),
        $submit = $form.find('[type=submit]');
    
   $form.live('submit', function(){
        var $button = $(this).find('[type=submit]');
            $button.fadeOut(200);
            $msg = $(this).find('.message').html('Отправляю ...'),
            $err = $(this).find('.error_message').html('');
            $keystring = $('[name="keystring"]', $(this));
            
//        if($keystring !== 'undefined'){
//            console.log ($.session.get('captcha_keystring'));
//        }
//            return false;
            
            
        $(this).ajaxSubmit({
                success: function(data, statusText, xhr, $form){
                    var $fields = $form.find('.fields, .table_oneclick, .tradein_fields');
                    if(data.state){
                        if(data.msg){
                           $msg.html(data.msg);
                           $fields.hide();
                        }
                    } else {
                        if(data.err){
                            $err.html(data.err);
                            $msg.html('');
                        }
                    }
                    $button.fadeIn();
                }
        });
        return false;
    });
};

$(ajax_forms);

/* article list resizing */
var article_list = function() {
    
    function article_list_resize (){
        var maxHeight = Math.max.apply(null, $(".article_block_inner").map(function ()
        {
            return $(this).height();
        }).get());
        $(".article_block").each(function(){ 
            $(this).height(maxHeight*1.08);
        });
    }

    if ($(".article_block").length > 0) {
        article_list_resize();
        $(window).resize(function(){
            article_list_resize();
        });
    }
};

$(document).ready(function(){
    setTimeout("$(article_list)",100); /* delay for Chrome */
});

/* gallery */
var gallery_funcs = function() {
    if($(".fancy").length){
        $(".fancy").fancybox({
            openEffect	: 'fade',
            closeEffect	: 'fade'
        });
    }
};

$(gallery_funcs);


/* cycle banners */
var banner_cycle = function() {
    var banner = $('.mainpage_banner .slider'),
        mainpage_banner = $('.mainpage_banner')
//        cont_blocks = $('.mainpage_content_blocks')
        ;
    
    function banner_resize (){
        banner
            .cycle({ 
                    fx:    'fade',
                    speed:    800, 
                    timeout:  5000,
                    next: '.slider_next',
                    prev: '.slider_prev',
                    pager: '.slider_nav',
                    pauseOnPagerHover: 1,
                    fit: 1,
//                    width: $('.mainpage_banner').width()
slideResize: 1,
containerResize: 1,
width: '100%',
height: '100%',
before: onBefore
                    });
                    
        mainpage_banner 
            .mouseenter(function(){
                        banner.cycle('pause');
                        $('.slider_prev').fadeIn();
                        $('.slider_next').fadeIn();
                        })            
            .mouseleave(function(){
                        banner.cycle('resume');
                        $('.slider_prev').fadeOut();
                        $('.slider_next').fadeOut();
                        });              
     }

    function onBefore(curr, next, opts, fwd) {
        var Ww = $(window).width(),
            $padding,
            $img_width = 0;
        if(Ww < 1920){
            $('.slider img').each( function() {
                var Cw = $(this).width();
                if(typeof Cw !== 'undefined' && Cw != 0) 
                $img_width = Cw;
            });
            $padding = ($img_width - Ww) / 2 ;
            $('.mainpage_banner .slider').find('img').css('margin-left', '-'+$padding+'px');
        } else {
            $('.mainpage_banner .slider').find('img').css('margin-left', 'auto');
        }
    }
    
    function banner_resize_after_window (){
        /* first stop cycle to start via beginning & have no overshow slides */
        /*
            banner
                .cycle('stop')
                .cycle({ 
                    fit: 1,
                    width: $('.mainpage_banner').width()
                    })
                .cycle('resume');
        */
        $('.slider_nav').html('');
        /* first stop cycle to start via beginning & have no overshow slides */
            banner
                .cycle('stop')
                .cycle({ 
                    speed:    800, 
                    timeout:  5000,
                    next: '.slider_next',
                    prev: '.slider_prev',
                    pager: '.slider_nav',
                    pauseOnPagerHover: 1,
                    fit: 1,
                    slideResize: 1,
                    containerResize: 1,
                    width: '100%',
                    height: '100%',
                    before: onBefore

                    })
                .cycle('resume');
    
    }

    if(banner.length){
        banner_resize();
        var timeout_id;
        $(window).resize(function(){
            clearTimeout(timeout_id);
            timeout_id = setTimeout(banner_resize_after_window, 500);
        });

    }
    
    function mainpage_blocks_resize(){
        var $left = cont_blocks.find('.left'),
            $rightChild = cont_blocks.find('.right').children(':last'),
            cH = cont_blocks.height(),
            $padding = parseInt($rightChild.css('padding-top'));

            if ($left.height() < cH) {
                $left.height(cH);
                $left.children(':last').height(cH - $left.children(':first').height() - (6 * $padding) + 10);
                $rightChild.height(cH - (2.5 * $padding));
            } else {
                $rightChild.height(cH - (2.5 * $padding));
                $left.height(cH);
                $left.children(':last').height(cH - $left.children(':first').height() - (6 * $padding) + 10);
            }
        
    }
    
//    if (cont_blocks.length>0){
//        setTimeout(mainpage_blocks_resize, 500);
//    }
    
//    $('a[href=#video]').live('click', function(){
//        $('div.show_video.sm_content .top').text(L.video1_popup_title);
//        $('.thumb_video').click();
//        return false;
//    });
    $('a[href^=#video-]').live('click', function(){
        var $this = $(this);
        $('div.show_video.sm_content .top').text($this.attr('data-title'));
        show_popup('show_video', '');
        show_video($this.attr('href').replace('#video-', ''));
        return false;
    });
    
    
};

$(document).ready(function(){
    if ($('.mainpage_content_blocks').length>0)
        $(banner_cycle);
});

var service_cycle = function() {
    
$('.ajax_content').each(function(){
    var $this = $(this),
        items = $('.content_images a', $this),
        itemsMargin = parseInt(items.css('marginLeft')),
        containerWidth = $('.fotos_container', $this).width(),
        sliderUl = $('.fotos_container', $this).children('.content_images'),
        imgs = items,
        itemsWidth = items.width()+ 2*parseInt(items.css('padding-left'))+1 ,
        imgWidth = 2*itemsMargin + itemsWidth,
        imgsLen = imgs.length,
        totalImgsWidth = imgsLen * imgWidth;
        current = 1,
        imgQuantity = Math.floor(containerWidth/imgWidth),
        itemsMargin = Math.floor((containerWidth/imgQuantity - itemsWidth)/2);
        /*imgs.setAttribute("style","margin: 0 " + itemsMargin + "px");*/
        $('.content_images a', $this).css('margin', "0 " + itemsMargin + "px");
        imgWidth = 2*itemsMargin + itemsWidth;
        $('.container', $this).width(imgWidth * imgQuantity);
        imgsLen -= imgQuantity - 1;
        totalImgsWidth = imgsLen * imgWidth;
        var $stop = false;

    
        // check if we need scroll
        if (imgsLen > 1) {
            var $active_item = $('.content_images', $this).find('.active'),
                current = items.index($active_item)+1;
            if(!current) current = 1;
            if (current > 1) {
                if (current>imgsLen)
                    current = imgsLen;
                var loc = (current-1)*imgWidth;
//                console.log(imgsLen);
                transition(sliderUl, loc, 'next');
             }
             
            $('.fotos_left_arrow, .fotos_right_arrow', $this).on('click', function() {
                // no click until animation ends
                if ($stop) return false;
                $stop = true;
//                console.log(imgsLen + ' - ' + current);
                var direction = $(this).data('nav'),
                loc = imgWidth;

                ( direction === 'next' ) ? ++current : --current;

                if ( current === 0 ) {
                        current = imgsLen;
                        loc = totalImgsWidth - imgWidth;
                        direction = 'next' ;
                } else if ( current > imgsLen) {
//                } else if ( current - 1 === imgsLen) {
                        current = 1;
                        loc = 0;
                }

                transition(sliderUl, loc, direction);
            });
        } else {
            $('.fotos_left_arrow, .fotos_right_arrow', $this).hide();
            $('.fotos_container', $this).width(containerWidth + 10);
            $('.content_images', $this).width('auto');
        }
        
    // show slider after resizing
    $('.content_images .service_container', $this).css('visibility', 'visible');

    function transition ( container, loc, direction ) {
        
        var unit;

        if (direction && loc !== 0) {
                unit = ( direction === 'next' ) ? '-=' : '+=' ;
        }


        container.animate({
                'margin-left': unit ? ( unit + loc) : loc
        }, 500, function(){$stop=false});

    }
    
//    items.live('click', function() {
//        var $this = $(this),
//            $submenu = $('.submenu_inner li', $this),
//            url=$this.attr('href');
//        
//        items.removeClass('active');
//        $this.addClass('active');   
//
//        $submenu.removeClass('active_submenu');
//        $submenu.find('a').filter('[href="'+url+'"]')
//        .parent().addClass('active_submenu');
//
////        return false;
//    });
    
/*    
    items.on('click', function() {
        var $this = $(this),
            $block = $('.article_block_inner'),
            $submenu = $('.submenu_inner li'),
            url=$this.data('rel');
        
        items.removeClass('active');
        $block.removeClass('active');
        $block.filter('[data-rel='+url+']').addClass('active');
        $this.addClass('active');   
        
        url = $this.attr('href');
        
        $submenu.removeClass('active_submenu');
        $submenu.find('a').filter('[href="'+url+'"]')
        .parent().addClass('active_submenu');
        
        $('#consult .consult_device').text($this.find('span:last').text());
        document.title = $this.data('title');
        window.history.pushState({path:url},'',url);
//        return false;
    });
*/

});
};

//$(service_cycle);


var menu_2_cycle = function() {
    
$('.top_menu_2').each(function(){
    // to calculate params we have to display item
    var $this = $(this),
        $hide_object_while_calculate = true;
    
    if ($hide_object_while_calculate) {
        var  $this_top = $this.css('top');
            $this.css({
       //            'display':'none',
                   'top':'-999999px',
                       })
                .show();
    }
    var items = $('.content_images div > a', $this),
        itemsMargin = parseInt(items.css('marginLeft')),
        containerWidth = $('.menu_container', $this).width(),
        sliderUl = $('.menu_container', $this).children('.content_images'),
        imgs = items,
        itemsWidth = items.width()+ 2*parseInt(items.css('padding-left'))+1 ,
        imgWidth = 2*itemsMargin + itemsWidth,
        imgsLen = imgs.length,
        totalImgsWidth = imgsLen * imgWidth;
        current = 1,
        imgQuantity = Math.floor(containerWidth/imgWidth),
        itemsMargin = Math.floor((containerWidth/imgQuantity - itemsWidth)/2);
        /*imgs.setAttribute("style","margin: 0 " + itemsMargin + "px");*/
//        $('.content_images a', $this).css('margin', "0 " + itemsMargin + "px");
        var $padding =  parseInt(items.css('padding-left')) + itemsMargin;
        // only for 2-nd menu
        $('.content_images div > a', $this).css({
            'padding-left': $padding,
            'padding-right': $padding,
        });
        
        imgWidth = 2*itemsMargin + itemsWidth;
        $('.container', $this).width(imgWidth * imgQuantity);
        imgsLen -= imgQuantity - 1;
        totalImgsWidth = imgsLen * imgWidth;
        var $stop = false;
        
        // check if we need scroll
        if (imgsLen > 1) {
            var $active_item = $('.content_images', $this).find('.active'),
                current = items.index($active_item)+1;
            if(!current) current = 1;
            if (current > 1) {
                if (current>imgsLen)
                    current = imgsLen;
                var loc = (current-1)*imgWidth;
//                console.log(imgsLen);
                transition(sliderUl, loc, 'next');
             }
             
            $('.menu_left_arrow, .menu_right_arrow', $this).on('click', function() {
                // no click until animation ends
                if ($stop) return false;
                $stop = true;
                
                var direction = $(this).data('nav'),
                loc = imgWidth;

                ( direction === 'next' ) ? ++current : --current;

                if ( current === 0) {
                        current = imgsLen;
                        loc = totalImgsWidth - imgWidth;
                        direction = 'next' ;
                } else if ( current > imgsLen) {
//                } else if ( current - 1 === imgsLen) {
                        current = 1;
                        loc = 0;
                }

                transition(sliderUl, loc, direction);
            });
        } else {
            $('.menu_left_arrow, .menu_right_arrow', $this).hide();
            $('.menu_container', $this).width(containerWidth + 10);
            $('.content_images', $this).width('auto');
        }
        
    // show slider after resizing
    $('.content_images .service_container', $this).css('visibility', 'visible');

    function transition ( container, loc, direction ) {
        var unit;

        if (direction && loc !== 0) {
                unit = ( direction === 'next' ) ? '-=' : '+=' ;
        }


        container.animate({
                'margin-left': unit ? ( unit + loc) : loc
        }, 500, function(){$stop=false});

    }
    
//    items.live('click', function() {
//        var $this = $(this),
////            $submenu = $('.submenu_inner li', $this),
//            url=$this.attr('href');
//        
//        items.removeClass('active');
//        $this.addClass('active');   
//
////        $submenu.removeClass('active_submenu');
////        $submenu.find('a').filter('[href="'+url+'"]')
////        .parent().addClass('active_submenu');
//
////        return false;
//    });
    
    if ($hide_object_while_calculate) {
        // hide and place to normal place after resizing
        $this.hide()
             .css({
    //            'display':'none',
                'top': $this_top,
                    });
    }
    
}); // each
};

/*  Submenu on hower */
/*  Submenu on hower */
var submenu_top_2_init = function(){

$('.top_menu_2').each(function(){
    var $this_menu = $(this);
    var $list = $this_menu.find('.submenu_present');
    var $window_multiplier = 0.6;
    if($list.length<1) return false;
    
    $list.live('click', function(){
//            don't click if menu behind arrows
        if($(this).offset().left < $('.center_block', $this_menu).offset().left) {
           return false;
        }
    });
            
    $list.live('mouseenter', function(){
            $list.children('.submenu').hide();
            var $this = $(this),
                link = $this.children('a'),
                submenu = $this.children('.submenu'),
                $main_menu_left = $('.center_block', $this_menu).offset().left;
                
//            don't show submenu if menu behind arrows
            if($this.offset().left < $main_menu_left) {
               return false;
            }
                
            if(submenu.length){
                resize_submenu(submenu);
                submenu.stop(true, true).fadeIn(150);
            }
        })
        .live('mouseleave',function(){
            var $this = $(this),
                link = $this.children('a'),
                submenu = $this.children('.submenu');

                if(submenu.length){
                    submenu.stop(true, true).fadeOut(1000);
                }

        });
        
        /*
         * resize submenu to 4 columns
         * @param {type} submenu
         * @returns {undefined}
         * 
         */
        function resize_submenu(submenu) {
            submenu.show();
            var $wh = $(window).height(),
                $mh = submenu.height(),
                $ww = $(window).width(),
                num_cols = submenu.length;
            var $max_cols = 5;
            var max_heigth = $wh*$window_multiplier;
            
            // высота меню больше высоты окна и одна клонка
            if ($mh > max_heigth && submenu.length<2) {
//                console.log($wh + ' - ' + $mh);
                 
               var  lis = submenu.find('li'),
                    links = lis.find(':first'),
                    li_count = lis.length;
//                    var a_padding = parseInt(lis.find(':first').css('padding-top'));
                // if no show - can't  count elements
                
                var maxHeight = Math.max.apply(null, links.map(function ()
                    {
//                        console.log($(this).height());
                        return $(this).height();
                    }).get());
                submenu.hide();
                // aligh blocks height
                links.each(function(){ 
                    $(this).height(maxHeight);
                });
                
                maxHeight = maxHeight + 2*parseInt(links.css('margin-top')) + 2*parseInt(links.css('padding-top'));
//                    +parseInt($(this).css('margin-top')) + parseInt($(this).css('padding-top')
//                    lis.length; //count elements
                var num_els = Math.floor((max_heigth / maxHeight));
                    num_cols = Math.ceil(lis.length / num_els);
                   
//console.log(lis.length + ' - ' + num_els);

                if($ww < 1056 /*1400*/) $max_cols = 4;
                if (num_cols > $max_cols)
                    num_cols = $max_cols;
                num_els =  Math.ceil(lis.length / num_cols);
                
                var add_menu ='',
                    left_margin = submenu.width() + submenu.parent().position().left;
//                    left_margin = submenu.parent().width()+submenu.width();
//console.log(submenu.parent().position().left);
//console.log(submenu.parent().offset().left);

                // build more div + ul
                for (var i = num_cols; i > 1; i--) {
                    
                    left_margin = (i-1)*submenu.width() + submenu.parent().position().left;
//                    left_margin = submenu.parent().width()+ (i-1)*submenu.width();
                    add_menu = '<div class="submenu submenu_col'+i+'"\
                            style="left:'+left_margin+'px; top:100%"\
                            >\
                            <ul class="submenu_inner">\
                            </ul></div>';
                    submenu.after(add_menu);
                }
                submenu.parent().find('.submenu').show();
                // move lis
                lis.each(function(index) {
                    if(index>=(num_els)){
//                        console.log(submenu.parent()
//                            .find('.submenu_col'+Math.ceil(index/num_els)+' ul'));
//                        console.log(Math.floor(index/(num_els)+1));
                        $(this).appendTo(
                            submenu.parent()
                            .find('.submenu_col'+Math.floor(index/(num_els)+1)+' ul'));
                    }
                });
                
                /*  Submenu scroller  */
                
                var submenu1 = submenu.parent().find('.submenu');
                var $sw1 = submenu.width();
                var submenu_maxHeight = Math.max.apply(null, submenu1.map(function ()
                    {
        //                        console.log($(this).height());
                        return $(this).height();
                    }).get());
                    // max current submenu height
                if(submenu_maxHeight> max_heigth && num_cols == $max_cols){
                    submenu1.each(function(){
                       $(this).css({
                           'overflow':'auto',
    //                       multiply * 0.05
                           'max-height': (max_heigth)+'px',
                           'width' : ($sw1+20)+'px',
                                });
                    });
                }
                
            }
            
            // summenu to window align horizontal & scroll (slide_pane)
            
            var $mfw = submenu.width() * num_cols;
            var $mleft = submenu.parent().offset().left;
            // find shift of menu_2 to page;
            var $parent_scroll = parseInt(submenu.parent().parent().css('margin-left'));
            var $off = $mleft 
//                    + $parent_scroll
            ; // relative screen !!!
            var $liw = submenu.parent().width();
            var $sw = submenu.width();
            var $shift = false; // for shift
//            console.log($mfw);

            submenu = submenu.parent().children('.submenu');
//            console.log('left = '+$mleft+ ' off= '+ $off  );
            if ($mleft < (0.2*$ww)){
//                console.log('$top < 0.2*$ww '+$mleft );
                $shift = $off;     
            } else if ($off + $liw > (0.7*$ww) && ($off + $liw - $mfw ) > 0 ) {
//                console.log('$lih = '+ $liw );
                $shift = $off + $liw - $mfw;
            } else if ($off + $mfw > $ww) {
//                console.log('mid ' + $off+ ' '+$liw/2+ ' '+$mfw/2 + ' = ' + $ww);
                if(($off+$liw/2+$mfw/2) > $ww || ($off+$liw/2-$mfw/2) < 0)
                    $shift = 0.1*$ww;
                else
                    $shift = $off+$liw/2-$mfw/2;
            } else if ($off + $mfw < $ww) {
                $shift = $mleft; 
            }else {
//                console.log($off +' - ' + $mfw +' > ' + $ww );
                $shift = false;
            }
            
            // do shift
            if($shift!==false) {
                var $i=0;
                submenu.each(function(){
                    $(this).css('left', $shift + ($i*$sw) +'px');
                    $i++;
                });
            }
            
            
            

            
            /*
            // summenu to window align vertical
            var $mfh = submenu.height();
//            console.log($mh + ' - ' +$mfh);
            var $dtop = $(document).scrollTop();
            var $mtop = submenu.parent().offset().top;
            var $off = $mtop - $dtop; // relative screen
            var $lih = submenu.parent().height();
            
            submenu = submenu.parent().children('.submenu');
                
            if ($off < (0.2*$wh)){
//                console.log('$top < (0.3*$wh');
                submenu.css('top', 0);            
            } else if ($off + $lih > (0.8*$wh)) {
//                console.log('$lih');
                submenu.css('top', $lih - $mfh);
            } else if ( $mfh > 0.9*$wh ) {
//                console.log('$mfh > $wh');
                submenu.css('top', 0.1*$wh - $off);
            } else if ($off + $mfh > $wh) {
//                console.log('mid');
                if(($off+$mfh/2) > $wh || ($off-$mfh/2) < 0)
                    submenu.css('top', 0.1*$wh - $off);
                else
                    submenu.css('top', -$mfh/2);
            } else {
//                console.log($off +' - ' + $mfh +' > ' + $wh );
                submenu.css('top', 0);
            }
            
            */
            submenu.css('top', '100%');

            
            
        } 
        // disable click on menu with submenus
        /*
        .find('a').click(function(){
            if($(this).parent().hasClass('submenu_present'))
                return false;
        });
        */
});
};

//$(submenu_top_2_init);

//$(menu_2_cycle);

/* News block */
var news_block = function(){
    var slider = false,
        news_block = $('.one_news_block'),
        news_title = $('.news_title', news_block),
        news_content = $('.news_content:visible', news_block);

    news_title.mouseenter(function(){
        var $this = $(this);
        if(slider || $this.next().is(':visible'))
            return false;
        slider=true;
        news_content.slideUp(400);
        news_content = $this.next();
        news_content.slideDown(400,function(){slider=false;});
    });
    
    // go to page
    news_block.click(function(){
        var $url = $(this).data('link');
        window.location = $url;
    });
    
    $('.news_block_title').live('click', function(){
        window.location = $(this).data('link');
    });
};

$(news_block);


var news_cycle = function() {
    var $this = $('.slider_container');
    if($this.length<1)
        return false;
    
//    var timeout_id;
//    $(window).resize(function(){
//        clearTimeout(timeout_id);
//        timeout_id = setTimeout(gen_cycle, 500);
//    });
    
    gen_cycle();
    
    function gen_cycle() {
        // hide slider before resizing
        
        $('.slider_items .service_container', $this).css('visibility', 'hidden');

        
        var $this = $('.slider_container');
        $('.items_container', $this).width($this.width() * 0.9);
        var default_margin = 5,
            items = $('.slider_items .item', $this);
            items.css('margin', '0 '+default_margin+'px');
        var itemsMargin = default_margin,
    //        itemsMargin = parseInt(items.css('marginLeft')),
            containerWidth = $('.items_container', $this).width(),
            sliderUl = $('.items_container', $this).children('.slider_items'),
            imgs = items,
            itemsWidth = items.width()+ 2*parseInt(items.css('padding-left')) ,
            imgWidth = 2*itemsMargin + itemsWidth,
            imgsLen = imgs.length,
            totalImgsWidth = imgsLen * imgWidth,
            current = 1,
            imgQuantity = Math.floor(containerWidth/imgWidth),
            itemsMargin = Math.floor((containerWidth/imgQuantity - itemsWidth)/2);
            /*imgs.setAttribute("style","margin: 0 " + itemsMargin + "px");*/
            $('.slider_items .item', $this).css('margin', "0 " + itemsMargin + "px");
            imgWidth = 2*itemsMargin + itemsWidth;
    //        $('.container', $this).width(imgWidth * imgQuantity);
            imgsLen -= imgQuantity - 1;
            totalImgsWidth = imgsLen * imgWidth;
            var $stop = false;

            // check if we need scroll
            if (imgsLen > 1) {
//                var $active_item = $('.slider_items', $this).find('.active'),
//                    current = items.index($active_item)+1;
                if(!current) current = 1;
                if (current > 1) {
                    if (current>imgsLen)
                        current = imgsLen;
                    var loc = (current-1)*imgWidth;
    //                console.log(imgsLen);
                    transition(sliderUl, loc, 'next');
                 }
                 
                $('.slider_left_arrow, .slider_right_arrow', $this).on('click', function() {
                    // no click until animation ends
                    if ($stop) return false;
                    $stop = true;
    //                console.log(imgsLen + ' - ' + current);
                    var direction = $(this).data('nav'),
                    loc = imgWidth;

                    ( direction === 'next' ) ? ++current : --current;

                    if ( current === 0 ) {
                            current = imgsLen;
                            loc = totalImgsWidth - imgWidth;
                            direction = 'next' ;
                    } else if ( current > imgsLen) {
    //                } else if ( current - 1 === imgsLen) {
                            current = 1;
                            loc = 0;
                    }
                    transition(sliderUl, loc, direction);
                });
            } else {
                $('.slider_left_arrow, .slider_right_arrow', $this).hide();
//                $('.items_container', $this).width(containerWidth + 10);
                $('.items_container', $this).width($this.width()*1.1);
                $('.slider_items', $this).width('auto');
            }
        // show slider after resizing
        $('.slider_items .service_container', $this).css('visibility', 'visible');

        function transition ( container, loc, direction ) {
            var unit;
            if (direction && loc !== 0) {
                    unit = ( direction === 'next' ) ? '-=' : '+=' ;
            }
            container.animate({
                    'margin-left': unit ? ( unit + loc) : loc
            }, 500, function(){$stop=false});
        }
        
    };

};

//$(news_cycle);
// U have 2 set item width

var news_cycle1 = function() {
    
    var _this = this,
        $page_gallery = $('.slider_container');
    if($page_gallery.length<1)
        return false;
    $page_gallery.css('visibility', 'visible');

    var $fotos = $('.slider_items',$page_gallery),
        qty_in_fotos = 0,
        $page_gallery_preview = $('.item',$page_gallery),
        $fotos_left = $('.slider_left_arrow',$page_gallery),
        $fotos_right = $('.slider_right_arrow',$page_gallery),
        $fotos_inner = $('.items_container',$page_gallery),
    
    init = function(){
        
        $.extend($.support, {
            touch: "ontouchend" in document
//            touch: true
        });
        
        window.onresize = resize;
        
        window.onload = function(){
            resize();
        };
        
        resize();
        
        setTimeout(function(){
            resize();
        }, 500);
        
//        console.log($page_gallery.length);
        if($page_gallery.length){
                $fotos_left.click(function(){
                    move_fotos(-1);
                });

                $fotos_right.click(function(){
                    move_fotos(1);
                });
            
            set_fotos_nav_buttons();
            /*
            $fotos_inner.mousewheel(function(e, delta){
                delta > 0 ? move_fotos(-1) : move_fotos(1);
                return false;
            });
            */
            if($.support.touch){
                
                var moved = false,
                    moved_distance = 0,
                    tprev = 0;
                
                $fotos_inner.bind('touchmove', function(e){
                    e.preventDefault();
                    if(moved_distance++ > 2){
                        moved = true;
                    }
                });
                
                $fotos_inner.bind('touchstart', function(e){
                    var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                    tprev = touch.pageX;
                });
                
                $fotos_inner.bind('touchend', function(e){
                    if(moved){
                        var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                        tprev < touch.pageX ? move_fotos(-1) : move_fotos(1) 
                    }
                    moved_distance = 0;
                    moved = false;
                });
                
            }
        }
    },
    resize = function(){
        _this.win_h = $(window).height();
        
        if($page_gallery.length){
            var p_width = $page_gallery.parents('.inner_fullwidth').width() - 1,
                inner_width = p_width - $fotos_left.width()*2,
                pr_width = $page_gallery.find('.item').width(),
                default_margin = 10,
                qty = $page_gallery_preview.length;
            qty_in_fotos = parseInt(parseInt(inner_width) / parseInt(pr_width+2*default_margin));
//            qty_in_fotos = qty_in_fotos > qty ? qty : qty_in_fotos;
            var margin = (inner_width - qty_in_fotos*pr_width) / qty_in_fotos / 2;
            if (margin < default_margin) margin = default_margin;
            $fotos_inner.css({
                width: inner_width
            });
            
            $page_gallery_preview.css({
                margin: '0 '+margin+'px' 
            });
            
            $fotos.css({
                marginLeft: -$page_gallery.data('left') * (pr_width + margin*2)
            });
            
            set_fotos_nav_buttons();
            
            // disable arrows & set to center
            if (qty*(pr_width + 2*margin) < p_width) {
                $page_gallery_preview.css({
                    margin: '0 '+margin+'px' 
                });
                $fotos_left.hide();
                $fotos_right.hide();
                $fotos_inner.width('100%');
                $fotos.width('auto').css('margin-left','auto');
            } else {
                $fotos_left.show();
                $fotos_right.show();
                $fotos.width('20000px');
                /* resize if out of space */
                if($fotos_inner.width()+2*$fotos_left.width() > $page_gallery.parents('.inner_fullwidth').width()-1)
                    resize();
                    
            }
        }
        
    },
    
    set_fotos_nav_buttons = function(){
        // for no limits
        return false;
        var now_left = $page_gallery.data('left');
        if(!now_left){
            $fotos_left.addClass('disabled');
        }else{
            $fotos_left.removeClass('disabled');
        }
        if(!($page_gallery_preview.length - qty_in_fotos - now_left) || qty_in_fotos > $page_gallery_preview.length){
            $fotos_right.addClass('disabled');
        }else{
            $fotos_right.removeClass('disabled');
        }
    },
    
    move_fotos = function(qty){
        /*
         // begin to end limits
        if(qty < 0 && $fotos_left.hasClass('disabled')){
            return false;
        }
        
        if(qty > 0 && $fotos_right.hasClass('disabled')){
            return false;
        }
        
        var now_left = $page_gallery.data('left'),
            new_left = now_left + qty;
            
        if(new_left < 0){
            new_left = 0;
        }
        
        if(new_left >= $page_gallery_preview.length-qty_in_fotos){
            new_left = $page_gallery_preview.length-qty_in_fotos;
        }
        */
       
        // NO begin to end limits
        var now_left = $page_gallery.data('left'),
            new_left = now_left + qty;
    
        if(new_left < 0){
            new_left = $page_gallery_preview.length-qty_in_fotos;
        }
        
        if(new_left > $page_gallery_preview.length-qty_in_fotos){
            new_left = 0;
        }
        
        
        $page_gallery.data('left', new_left);
        
        set_fotos_nav_buttons();
        
        if($('[dir=rtl]').length>0){
            $fotos.stop(true, false).animate({
                    marginRight: $page_gallery_preview.outerWidth(true) * -new_left
            }, 300);
        }else {
            $fotos.stop(true, false).animate({
                    marginLeft: $page_gallery_preview.outerWidth(true) * -new_left
            }, 300);
        }
    };
    
    init();
    
};
$(news_cycle1);


var services_cycle1 = function() {
    
    var _this = this,
        $page_gallery = $('.service_container');
    if($page_gallery.length<1)
        return false;
    $page_gallery.css('visibility', 'visible');
    
    var $fotos = $('.content_images',$page_gallery),
        qty_in_fotos = 0,
        $page_gallery_preview = $('.item',$page_gallery),
        $fotos_left = $('.fotos_left_arrow',$page_gallery),
        $fotos_right = $('.fotos_right_arrow',$page_gallery),
        $fotos_inner = $('.fotos_container',$page_gallery),
    
    init = function(){

        $.extend($.support, {
            touch: "ontouchend" in document
//            touch: true
        });
        
        window.onresize = resize;
        
        window.onload = function(){
            resize();
        };
        
        resize();
        
        setTimeout(function(){
            resize();
        }, 500);
        
//        console.log($page_gallery.length);
        if($page_gallery.length){
                $fotos_left.click(function(){
                    move_fotos(-1);
                });

                $fotos_right.click(function(){
                    move_fotos(1);
                });
            
            set_fotos_nav_buttons();
            /*
            $fotos_inner.mousewheel(function(e, delta){
                delta > 0 ? move_fotos(-1) : move_fotos(1);
                return false;
            });
            */
            if($.support.touch){
                
                var moved = false,
                    moved_distance = 0,
                    tprev = 0;
                
                $fotos_inner.bind('touchmove', function(e){
                    e.preventDefault();
                    if(moved_distance++ > 2){
                        moved = true;
                    }
                });
                
                $fotos_inner.bind('touchstart', function(e){
                    var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                    tprev = touch.pageX;
                });
                
                $fotos_inner.bind('touchend', function(e){
                    if(moved){
                        var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                        tprev < touch.pageX ? move_fotos(-1) : move_fotos(1) 
                    }
                    moved_distance = 0;
                    moved = false;
                });
                
            }
        }
        
    },
            
    resize = function(){
        
        _this.win_h = $(window).height();
        
        if($page_gallery.length){
            /*
            // if item width is not constant get max width;
            var pr_padding = parseInt($page_gallery_preview.css('padding-left'));
            var pr_width  = Math.max.apply(null, $page_gallery_preview.map(function ()
                    {
                        return ($(this).width()+2*pr_padding) ;
                    }).get());
            */
            var p_width = $page_gallery.parents('.inner_fullwidth').width() - 1,
                inner_width = p_width - $fotos_left.width()*2,
                pr_width = 195,
                default_margin = 5,
                qty = $page_gallery_preview.length,
                $ci_width = '90%';
            qty_in_fotos = parseInt(parseInt(inner_width) / parseInt(pr_width+2*default_margin));
//            qty_in_fotos = qty_in_fotos > qty ? qty : qty_in_fotos;
            var margin = (inner_width - qty_in_fotos*pr_width) / qty_in_fotos / 2;
            if (margin < default_margin) margin = default_margin;
            $fotos_inner.css({
                width: inner_width
            });
            
            $page_gallery_preview.css({
                margin: '0 '+margin+'px' 
            });
            
            $fotos.css({
                marginLeft: -$page_gallery.data('left') * (pr_width + margin*2)
            });
            
            set_fotos_nav_buttons();
            
            // disable arrows & set to center
            if (qty*(pr_width + 2*margin) < p_width) {
                $page_gallery_preview.css({
                    margin: '0 '+margin+'px' 
                });
                $fotos_left.hide();
                $fotos_right.hide();
                $fotos_inner.width('100%');
                $fotos.width('auto').css('margin-left','auto');
            } else {
                $fotos_left.show();
                $fotos_right.show();
                $fotos.width('20000px');
                /* resize if out of space */
                if($fotos_inner.width()+2*$fotos_left.width() > $page_gallery.parents('.inner_fullwidth').width()-1) {
                    resize();
                    return false;
                }
                // move to photo on first load
                $page_gallery_preview.each(function( index, value ) {
                    if($(this).hasClass('active')) {
//                        console.log(index+ ' ' + qty + ' '+ qty_in_fotos);
                        if (index > qty - qty_in_fotos) index = qty - qty_in_fotos;
                        $page_gallery.data('left', index);
//                        console.log(index);
                    }
                });
                
                $ci_width = $fotos_inner.width() - 2*margin - 25;// popravka (item-remont)/2
                $ci_width += 'px';
            }
            // set #content_inner width =  fotos_container - 2*item.margin
//            $('#content_inner').css('width', $ci_width);
        }
        
    },
    
    set_fotos_nav_buttons = function(){
        // for no limits
        return false;
        var now_left = $page_gallery.data('left');
        if(!now_left){
            $fotos_left.addClass('disabled');
        }else{
            $fotos_left.removeClass('disabled');
        }
        if(!($page_gallery_preview.length - qty_in_fotos - now_left) || qty_in_fotos > $page_gallery_preview.length){
            $fotos_right.addClass('disabled');
        }else{
            $fotos_right.removeClass('disabled');
        }
    },
    
    move_fotos = function(qty){
        /*
         // begin to end limits
        if(qty < 0 && $fotos_left.hasClass('disabled')){
            return false;
        }
        
        if(qty > 0 && $fotos_right.hasClass('disabled')){
            return false;
        }
        
        var now_left = $page_gallery.data('left'),
            new_left = now_left + qty;
            
        if(new_left < 0){
            new_left = 0;
        }
        
        if(new_left >= $page_gallery_preview.length-qty_in_fotos){
            new_left = $page_gallery_preview.length-qty_in_fotos;
        }
        */
       
        // NO begin to end limits
        var now_left = $page_gallery.data('left'),
            new_left = now_left + qty;
    
        if(new_left < 0){
            new_left = $page_gallery_preview.length-qty_in_fotos;
        }
        
        if(new_left > $page_gallery_preview.length-qty_in_fotos){
            new_left = 0;
        }
        
        
        $page_gallery.data('left', new_left);
        
        set_fotos_nav_buttons();
        
        if($('[dir=rtl]').length>0){
            $fotos.stop(true, false).animate({
                    marginRight: $page_gallery_preview.outerWidth(true) * -new_left
            }, 300);
        }else {
            $fotos.stop(true, false).animate({
                    marginLeft: $page_gallery_preview.outerWidth(true) * -new_left
            }, 300);
        }
    };
    
    init();
    
};
$(services_cycle1);


var service_form = function() {
    $('.service_send_btn').live('click', function(){
        var $this = $(this),
            $service_form = $this.parent('.service_form'),
            phone = $('input[type="text"]',$service_form).val(),
            phone = phone.replace(/\D/g,''),
//            msg = document.title,
            msg = $('#consult .consult_device').text(),
//            msg = $('.service_page_title h1').text(),
            service_recall = $('.service_recall', $service_form.parent()),
            service = $('.service', $service_form).val(),
            is_credit_form = $service_form.hasClass('credit_consult'),
            data = 'phone='+phone+'&device='+msg+(is_credit_form ? '&credit=1&service='+encodeURIComponent(service) : ''),
            error_message = $('.error_message', $service_form);
        $this.attr('disabled', true);
        $.ajax({
             url: prefix+'consult/ajax.php?act=new_consult',
             data: data,
             type: "POST",
             dataType: "json",
             success: function(data){
                 $this.attr('disabled', false);
                 if(data.state){
                     $service_form.hide();
                     service_recall.show();
//                     $service_form.text(service_recall.text());
//                        _this.set_consult_updater();
                 }else{
                     error_message.text(data.msg);
                 }
             }
         });
    });
};


function countdown($, window){
    
    var self = this,
        settings = {};
    
    function add_zero(num){
        if(num < 10){
            return '0'+num;
        }else{
            return num;
        }
    }
    
    function recount_timer(){
        settings.seconds_left -= 1;
        if(settings.seconds_left >= 0){
            var s = settings.seconds_left;
            var hours = Math.floor(s / 3600);
            s %= 3600;
            var minutes = Math.floor(s / 60);
            s %= 60;
            var seconds = s;
            self.$timer.text(add_zero(hours)+':'+add_zero(minutes)+':'+add_zero(seconds));
        }else{
            stop_timer();
            remove_timer_block();
        }
    }
    
    function start_timer(){
        stop_timer();
        settings.update_interval_id = setInterval(recount_timer, 1000);
    }
    
    function stop_timer(){
        if(settings.update_interval_id){
            clearInterval(settings.update_interval_id);
        }
    }
    
    function remove_timer_block(){
        self.$timer.parent().slideUp(500);
    }
    
    self.init = function(){
        self.$timer = $('#prices_counter');
        if(self.$timer.length){
            settings.seconds_left = +self.$timer.data('seconds');
            start_timer();
        }else{
            stop_timer();
        }
    };
}

$(function(){
    window.countdown = new countdown(jQuery, window);
    countdown.init();
    
    if($('.service_form').length)
        $(service_form);
            $.mask.definitions['n']='[1-9]';
    $('.service_form input[name="phone"], #consult input[name="phone"]').each(function(){
        $(this).mask("380 (n9) 999-99-99");
    });
});

$(document).ready(function () {
    if ($('[data-datetimeshow]').length > 0) {
        var now = new Date();
        $('[data-datetimeshow]').each(function(i, el) {
            var datetimes = $(el).data('datetimeshow').split(';');
            var hide = true;
            for(var j in datetimes) {
                var datetime = datetimes[j].split('*');
                if (datetime[1]) {
                    var day = datetime[1].split('-');
                    if (day[0] < now.getDay() && day[1] > now.getDay()) {
                    } else {
                        continue;
                    }
                }
                var time = datetime[0].split('-');

                if (time[0] < now.getHours() && time[1] > now.getHours()) {
                    hide = false;
                }
            }
            if (hide == true) {
                $(el).hide();
            }
        });
    }
});



//from index.php
function show_video(video){
        var video_height = Math.round(jQuery(window).height() - 160); //390
        var video_width = Math.round(video_height*720/480); //Math.round(jQuery(document).width() / 2); //480

        //$('#show_video').html('<iframe width=\"'+video_width+'\" height=\"'+video_height+'\" src=\"//www.youtube.com/v/'+video+'&autoplay=1\" frameborder=\"0\" allowfullscreen></iframe>');
        $('#show_video').html('<div class="responsive-video"><object width=\"'+video_width+'\" height=\"'+video_height+'\" data=\"//www.youtube.com/v/'+video+'&autoplay=1\" frameborder=\"0\" type=\"application/x-shockwave-flash\"><param name=\"src\" value=\"//www.youtube.com/v/'+video+'\" /><param value=\"true\" name=\"allowFullScreen\" /></object></div>');
    };
    function show_popup(rel, data){
        var el = jQuery('.' + rel),
            doc_h = jQuery(window).height();
        jQuery('.error-popup').html('');
        jQuery('.sm_content').hide();

        if(!rel) rel = data;

        el.show();
        el.children('.bottom').css({
            maxHeight: doc_h - el.children('.top').outerHeight() - 100
        });
        el.center();

        jQuery('#blackout').show();
    };
        jQuery(document).ready(function($) {
            
            /* video popup */
            jQuery.fn.center = function () {
                var _this = jQuery(this);
                this.css({
                    marginTop: '',
                    marginLeft: ''
                });
                this.css({
                    marginTop: _this.outerHeight() / -2,
                    marginLeft: _this.outerWidth() / -2
                });
                return this;
            };
            jQuery('#blackout').live('click',function(e) {
                jQuery(this).hide();
                jQuery('.sm_content').hide();

                var content = jQuery('div.wishlist_no_auth');

                if ( content.length > 0 ) {
                    content.removeClass('wishlist_no_auth');
                    jQuery('input.wishlist').attr('checked', false);
                }

                if (jQuery('#show_video').length > 0)
                    jQuery('#show_video').html('');

            });
            jQuery('.thumb_video').click(function () {
                var video = jQuery(this).data('rel');
      show_video(video);
    });

    jQuery('.sm_close, #show_video').live('click', function (e) {
      var content = jQuery(this).parents('.sm_content');
      jQuery('.sm_content').hide();
      if (content.hasClass('wishlist_no_auth')) {
        content.removeClass('wishlist_no_auth');
        jQuery('#auth_pass_field, #submit_auth').show();
        jQuery('#save_auth').hide();
        jQuery('input.wishlist').attr('checked', false);
      }
      jQuery('#blackout').hide();

      if (jQuery('#show_video').length > 0)
        jQuery('#show_video').html('');

    });
    jQuery('.on_load_popup').live('click', function (e) {
      var rel = jQuery(this).attr('data-content'),
              data = jQuery(this).attr('data');

      show_popup(rel, data);
      return false;
    });

    /* trade in popup */
    jQuery('.input-radio-state').prop('checked', false);
    jQuery('.input-radio-moisture').prop('checked', false);
    jQuery('.input-radio-state').live('change', function () {
      jQuery('.tradein-pay').html('');
      jQuery('.moisture-block').show();
      jQuery('#tradein-result').html();
      jQuery('#tradein-for-noauthorized').hide();
      jQuery('#sell-goods').hide();
      jQuery('#sell-goods-all').hide();
      jQuery('.input-radio-moisture').prop('checked', false);
      var $el = jQuery('div.tradein-popup');
      $el.children('.bottom').css({
        maxHeight: jQuery(window).height() - $el.children('.top').outerHeight() - 100
      });
      $el.center();
    });
    jQuery('.input-radio-moisture').live('change', function () {
//                jQuery('#tradein-result').html('');
//                jQuery('.tradein-pay').html('');
//                jQuery('#tradein-for-noauthorized').hide();

      if (jQuery(this).attr('data-rel') == 1) {
        var form = jQuery('#tradein-form-all').serialize();
        var sell = '#sell-goods-all';
        var inputs = '#tradein-for-noauthorized-all';
      } else {
        var form = jQuery('#tradein-form').serialize();
        var sell = '#sell-goods';
        var inputs = '#tradein-for-noauthorized';
      }
      jQuery.ajax({
        type: 'POST',
        url: prefix + 'ajax.php?act=tradein',
        data: form,
        cache: false,
        success: function (msg) {
          if (msg['error']) {
            show_alert(msg['message'], 1);
          } else {
            if (msg['price']) {
              jQuery('.tradein-pay').show().html('<h3>' + msg['price'] + '</h3><p>' + msg['message'] + '</p>');
              jQuery(inputs).add(sell).show();
            } else {
              jQuery('.tradein-pay').show().html('<p>' + msg['message'] + '</p>');
              jQuery(inputs).hide();
            }
          }
          var $el = jQuery('div.tradein-popup');
          $el.children('.bottom').css({
            maxHeight: jQuery(window).height() - $el.children('.top').outerHeight() - 100
          });
          $el.center();
        },
        error: function (xhr, ajaxOptions, thrownError) {
          jQuery('#tradein-result').html(xhr.responseText);
          jQuery(sell).css('display', 'none');
        }
      });
      return false;
    });

    jQuery('.sell-goods').live('click', function () {
      if (jQuery('#tradein-form').parsley('isValid') == true) {
        server_request();

        jQuery.ajax({
          type: 'POST',
          url: prefix + 'ajax.php',
          data: 'sell-tradein=1&' + jQuery('#tradein-form').serialize(),
          cache: false,
          success: function (msg) {
            if (msg['error']) {
              show_alert(msg['message'], 1);
            } else {
              show_alert(msg['message']);
              jQuery('#tradein-for-noauthorized').css('display', 'none');
              jQuery('.tradein-pay').css('display', 'none');
              jQuery('#sell-goods').css('display', 'none');
              jQuery('.moisture-block').css('display', 'none');
              jQuery('#tradein-result').removeClass('message_error');
              jQuery('.input-radio-state').prop('checked', false);
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            show_alert(xhr.responseText, 1);
          }
        });
        return false;
      }
    });

    jQuery('#tradein-goods').change(function () {
      jQuery('.tradein-pay').html('');
      jQuery('#sell-goods-all').css('display', 'none');
      jQuery('#sell-goods').css('display', 'none');
      jQuery('.input-radio-moisture').prop('checked', false);
      if (jQuery(this).val() > 0) {
        jQuery('.state-block').css('display', 'block');
        jQuery('.moisture-block').css('display', 'block');

      } else {
        jQuery('.state-block').css('display', 'none');
        jQuery('.moisture-block').css('display', 'none');
      }
    });

  });
