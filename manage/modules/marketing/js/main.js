var marketing = (function(){
    
    function load_history($btn){
        var site = $btn.data('site'),
            $site_events = $('#site_events_'+site);
        if(!$site_events.hasClass('loaded')){
            $site_events.html('Загрузка данных...');
            $.ajax({
                url: prefix + module + '/ajax/',
                type: 'POST',
                data: 'act=show_events&site=' + site,
                dataType: 'json',
                success: function (data) {
                    if (data.state) {
                        $site_events.html(data.content).addClass('loaded');
                    }else{
                        alert(data.msg);
                    }
                }
            });
        }
    }
    
    function load_links($btn){
        var site = $btn.data('site'),
            $site_events = $('#site_links_'+site);
        if(!$site_events.hasClass('loaded')){
            $site_events.html('Загрузка данных...');
            $.ajax({
                url: prefix + module + '/ajax/',
                type: 'POST',
                data: 'act=show_links&site=' + site,
                dataType: 'json',
                success: function (data) {
                    if (data.state) {
                        $site_events.html(data.content).addClass('loaded');
                    }else{
                        alert(data.msg);
                    }
                }
            });
        }
    }
    
    return {
        init: function(){
            $('#marketing_tabs a').click(function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
            
            if(window.location.hash){
                $(window.location.hash).modal('show');
                load_history($('.load_events').filter('[href="'+window.location.hash+'"]'));
                window.location.hash = '';
            }
            
            $('.load_events').click(function(){
                load_history($(this));
            });
            
            $('.load_links').click(function(){
                load_links($(this));
            });
        }
    };
    
})();
$(function(){
    marketing.init();
});