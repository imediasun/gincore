function gmapinitialize() {
    var is_gmap_markers = typeof gmap_markers == 'object';
    var myLatlng = new google.maps.LatLng(gmap_lat, gmap_lng);
    var myOptions = {
      zoom: gmap_zoom ? gmap_zoom : (is_gmap_markers ? 13 : 16),
      center: myLatlng,
      mapTypeControl:false,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    var map = new google.maps.Map(document.getElementById("pageMap"), myOptions);

    var image = prefix+'images/flag.png';
    
    if(is_gmap_markers){
        $.each(gmap_markers, function(k, marker){
            var marker_pos = new google.maps.LatLng(marker.lat, marker.lng);
            var mark = new google.maps.Marker({
                position: marker_pos,
                map: map,
                title:gmap_companyname,
                icon: image
            });
            google.maps.event.addListener(mark, 'click', function() {
                map.setZoom(17);
                map.setCenter(mark.getPosition());
              });
        });
        $(window).resize(function(){
            if(typeof is_contacts != 'undefined' && is_contacts){
                if(window.innerWidth < 768){
                    map.setZoom(5);
                }else{
                    map.setZoom(6);
                }
            }
        }).resize();
    }else{
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            title:gmap_companyname,
            icon: image
        });
    }
    $('.fixed_map').height('50%');
    panOnClick(map, image);

}

function panOnClick(map, image) {
    var map_links = $('.map_link');
    if (map_links.length>0) {
        map_links.click(function() {
            var geoLatlng = new google.maps.LatLng($(this).data('lat'), $(this).data('lng'));
            map.panTo(geoLatlng);
            marker = new google.maps.Marker({
                        map: map,
                        position: geoLatlng,
                        title:$(this).data('obj'),
                        icon: image
                    });
            return false;
        });
        map_links[0].click();
        /* stick map */
        
        var obj = $('.fixed_map');
        var offset = obj.offset();
        var topOffset = offset.top/2;
        var marginTop = obj.css("marginTop");
        
        $('#content').scroll(function() {
            var scrollTop = $('#content').scrollTop();
            if (scrollTop >= topOffset){
                    obj.css({
                            marginTop: '-15%'
                    });
            }
            if (scrollTop < topOffset){
                    obj.css({
                            marginTop: marginTop
                    });
            }
        });
        
    }
}

/*
function globalGmapInitialize() {
    var myLatlng = new google.maps.LatLng(global_gmap_lat, global_gmap_lng);
    var myOptions = {
      zoom: 16,
      center: myLatlng,
      mapTypeControl:false,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    var global_map = new google.maps.Map(document.getElementById("globalMap"), myOptions);

    var image = prefix+'images/flag.png';

    var global_marker = new google.maps.Marker({
        position: myLatlng,
        map: global_map,
        title: global_gmap_sitename,
        icon: image
    });

        // slide down on click
    var $map_position  = $('.slidedown_map').position().top;
    var $map_height  = $('.slidedown_map').height() * 0.7;
    $('.slidedown_map').css('top', '-999px');
    setTimeout(function(){
        $('.slidedown_map').height(0);
        $('#globalMap').hide();
        $('.slidedown_map').css({top : $map_position+'px',
                                   opacity: 0
                                })
            .animate({ opacity: 1}, 800);
        }, 3000);
    $('.slidedown_map_link').on('click', function(e){
        e.stopPropagation();
        if ($('#globalMap').is(':visible')){
            $('#globalMap').slideUp();
            $('.slidedown_map').height(0);
        }
        else {
            $('.slidedown_map').height($map_height);
            $('#globalMap').slideDown();
            
        }
    });
}

function globalGMI() {

    var getmap = function(){
        var myLatlng = new google.maps.LatLng(global_gmap_lat, global_gmap_lng);
        var myOptions = {
          zoom: 16,
          center: myLatlng,
          mapTypeControl:false,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var global_map = new google.maps.Map(document.getElementById("globalMap"), myOptions);

        var image = prefix+'images/flag.png';

        var global_marker = new google.maps.Marker({
            position: myLatlng,
            map: global_map,
            title: global_gmap_sitename,
            icon: image
        });
    };
    
    // slide down on click
    var $map_position  = $('.slidedown_map').position().top;
    var $map_height  = $('.slidedown_map').height() * 0.7;
    $('#globalMap').hide();
    $('.slidedown_map').height(0);
    $('.slidedown_map_link').css({right: $('#logo').position().left - 10});

    $(window).resize(function(){
        $('.slidedown_map_link').css({right: $('#logo').position().left - 10});
        if ($('#globalMap').is(':hidden') && $('#globalMap div').length > 0)
            $('#globalMap').text(' ');
    });
    
    $('.slidedown_map_link').on('click', function(e){
        e.stopPropagation();
        if ($('#globalMap').is(':visible')){
            $('#globalMap').slideUp();
            $('.slidedown_map').height(0);
        }
        else {
            $('.slidedown_map').height($map_height);
            $('#globalMap').slideDown(800, function(){
                if($('#globalMap div').length < 1)
                    $(getmap);
            });
            
        }
    });
}
*/
function slide_down_map() {
    var mapsld_mouseenter_timeout = 0;

    var getmap = function(){
        $('.header_contacts_gmap_autoinit').each(function(){
            var $this = $(this);
            var myLatlng = new google.maps.LatLng($this.data('lat'), $this.data('lng'));
            var myOptions = {
              zoom: 13,
              center: myLatlng,
              mapTypeControl:false,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            var global_map = new google.maps.Map($this[0], myOptions);

            var image = prefix+'images/flag.png';

            var global_marker = new google.maps.Marker({
                position: myLatlng,
                map: global_map,
                title: global_gmap_sitename,
                icon: image
            });
        });
    };
    
    // slide down on click
    var $map_slider = $('.slidedown_map');
    $map_slider.show();
    var $map_position  = $('.slidedown_map').position().top;
    var $map_height  = $('.slidedown_map').height();
    $map_slider.hide();
    $('.slidedown_map').height(0);
//    $('.slidedown_map_link').css({right: $('#logo').position().left - 10});

    $(window).resize(function(){
//        $('.slidedown_map_link').css({right: $('#logo').position().left - 10});
        if ($map_slider.is(':hidden') && $('#globalMap div').length > 0)
            $('#globalMap', $map_slider).text(' ');
    });
    var $slidedown_map_link = $('.slidedown_map_link');
    $('.slidedown_map_link').on('click', function(e){
        if(window.innerWidth < 768){
            window.location = prefix+url_lang+'restore/contacts';
            return;
        }
        clearTimeout(mapsld_mouseenter_timeout);
        e.stopPropagation();
        if ($map_slider.is(':visible')){
            $map_slider.slideUp();
            $slidedown_map_link.find('i').html('&#x25BC;');
        }
        else {
            $('.slidedown_map').height($map_height);
            $map_slider.slideDown(800, function(){
                if($('#globalMap div').length < 1)
                    $(getmap);
            });
            $slidedown_map_link.find('i').html('&#x25B2;');
        }
        return false;
    });
    
//   close on page click
    $('html').on('click', function(e){
        var is_gmap = $(e.target).parents().hasClass('gm-style');
        if ($map_slider.is(':visible') && !is_gmap){
            $map_slider.slideUp();
            $slidedown_map_link.find('i').html('&#x25BC;');
        }
    });
    
    function show_dropdown_map(e){
        e.stopPropagation();
        $map_slider.stop(true, false);
        $('.slidedown_map').height($map_height).css('overflow','visible');
        $map_slider.slideDown(800, function(){
            if($('#globalMap div').length < 1)
                $(getmap);
            $(this).removeAttr('style').show(); // remove styles for Chrome incompletement
        });
        $slidedown_map_link.find('i').html('&#x25B2;');
    }
    
//   show on mouseenter
    $('a.slidedown_map_link, #contacts_inner').on('mouseenter', function(e){
        clearTimeout(mapsld_mouseenter_timeout);
        mapsld_mouseenter_timeout = setTimeout(function(){
            show_dropdown_map(e);
        }, 500);
    });
    $('.slidedown_map').on('mouseenter', function(e){
        clearTimeout(mapsld_mouseenter_timeout);
        show_dropdown_map(e);
    });
    
    $('a.slidedown_map_link, #contacts_inner, .slidedown_map').on('mouseleave', function(e){
        clearTimeout(mapsld_mouseenter_timeout);
        e.stopPropagation();
        $map_slider.stop(true,true); // complete current animation immidiately
        if ($map_slider.is(':visible')){
            $map_slider.slideUp();
            $slidedown_map_link.find('i').html('&#x25BC;');
        }
    });
    
}

function service_map() {

    var get_service_map = function(){
        var is_gmap_markers = typeof service_gmap_markers == 'object';
        var serviceLatlng = new google.maps.LatLng(service_gmap_lat, service_gmap_lng);
        var serviceOptions = {
          zoom: is_gmap_markers ? 11 : 16,
          center: serviceLatlng,
          mapTypeControl:false,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var service_map = new google.maps.Map(document.getElementById("serviceMap"), serviceOptions);

        var image = prefix+'images/flag.png';
        if(is_gmap_markers){
            $.each(service_gmap_markers, function(k, marker){
                var marker_pos = new google.maps.LatLng(marker.lat, marker.lng);
                new google.maps.Marker({
                    position: marker_pos,
                    map: service_map,
                    title:service_gmap_sitename,
                    icon: image
                });
            });
        }else{
            var service_marker = new google.maps.Marker({
                position: serviceLatlng,
                map: service_map,
                title: service_gmap_sitename,
                icon: image
            });
        }
        
    };
/*
    $(window).resize(function(){
        $('.slidedown_map_link').css({right: $('#logo').position().left - 10});
        if ($map_slider.is(':hidden') && $('#globalMap div').length > 0)
            $map_slider.text(' ');
    });
    */

    $(get_service_map);

}

$(document).ready(function(){
    if (typeof gmap_lat !== 'undefined')
        gmapinitialize();
    if (typeof global_gmap_lat !== 'undefined')
//        globalGmapInitialize();
//        globalGMI();
        slide_down_map();
    if ($('#serviceMap').length > 0)
        service_map();
});
