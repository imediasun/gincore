function gmapinitialize() {
    var myLatlng = new google.maps.LatLng(gmap_lat, gmap_lng);
    var myOptions = {
      zoom: 13,
      center: myLatlng,
      mapTypeControl:false,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    
    var map = new google.maps.Map(document.getElementById("pageMap"), myOptions);

    var image = prefix+'images/flag.png';

    var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        title:gmap_companyname,
        icon: image

    });

    
  }
$(window).load(function(){
    gmapinitialize();
});
