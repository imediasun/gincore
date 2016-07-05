(function($){
    
    function handle_time_zone(){
        var $continents = $('#tz_continents');
        var $zones_options = $('#tz_zones').find('option');
        $continents.on('change', function(){
            var val = $(this).val();
            $zones_options.removeClass('hidden');
            if(val != 'all'){
                $zones_options.filter('[data-continent!="'+val+'"]').addClass('hidden');
                if($zones_options.filter(':selected').hasClass('hidden')){
                    $zones_options.filter('[data-continent="'+val+'"]').eq(0).attr('selected', true);
                }
            }
        }).change();
    }
    
    $(function(){
        handle_time_zone();
    });
    
})(jQuery);