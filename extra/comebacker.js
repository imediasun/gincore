var comebacker = function($, prefix){
    
    var $els = {},
        vars = {
            cmb_active: true,
            visible: false
        };
    
    function createCookie(name, value, days) {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            var expires = "; expires=" + date.toGMTString();
        }
        else
            var expires = "";
        document.cookie = name + "=" + value + expires + "; path="+prefix;
    }
    
    function user_make_request(){
        if(!vars.test_mode){
            vars.cmb_active = false;
            $els.document.unbind('mouseleave');
            createCookie('cmb_off', 1, 300);
        }
    }
    
    function hide(){
        vars.visible = false;
        $els.comebacker_alpha.hide();
        $els.comebacker_form.removeClass('comebacker_in');
    }
    
    function show(){
        if(vars.cmb_active){
            user_make_request();
            vars.visible = true;
            $els.comebacker_alpha.show();
            $els.comebacker_form.addClass('comebacker_in');
            var $phone = $els.comebacker_form.find('.input-phone');
            $phone.select();
            resize();
            $.ajax({
                url: prefix+'ajax.php',
                data: 'act=comebacker_show',
                type: 'POST'
            });
            if(typeof(ga) == 'function'){
                ga('send', 'pageview', {page: '/comebacker_show', title: document.title});
            }else if(typeof(_gaq) != 'undefined'){
                _gaq.push(['_trackPageview', '/comebacker_show']);
            }
        }
    }
    
    function resize(){
        if(vars.visible){
            $els.comebacker_form.css({
                marginLeft: - $els.comebacker_form.outerWidth() / 2,
                marginTop: - $els.comebacker_form.outerHeight() / 2
            });
        }
    }
    
    return {
        resize: resize,
        show: show,
        user_make_request: user_make_request,
        init: function(){
            $els.document = $(document);
            $els.window = $(window);
            $els.comebacker_form = $('#comebacker_form');
            $els.comebacker_alpha = $('#comebacker_alpha');
            
            $els.comebacker_form.find('.close').add($els.comebacker_alpha).click(function(){
                hide();
            });
            
            $els.window.resize(resize);
            
            var mouseenter_timeout = 0;
            $els.document.on('mousemove', function(e){
                $els.document.unbind('mousemove');
                clearTimeout(mouseenter_timeout);
                mouseenter_timeout = setTimeout(function(){
                    $els.document.on('mouseleave', function(e){
                        var mousey = e.clientY || e.pageY;
                        if(mousey < 40){
                            show();
                        }
                    });
                }, 50);
            });
        }
    };
    
}(jQuery, prefix);
$(function(){
    comebacker.init();
});