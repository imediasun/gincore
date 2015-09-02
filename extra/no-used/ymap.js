ymaps.ready(initMap);
var myMap;

function initMap(map_el){
    map_el = map_el || 'pageMap';
    myMap = new ymaps.Map (map_el, {
        center: [map_lat, map_lng],
        zoom: 16
    });

    myMap.setType('yandex#publicMap');

    myMap.controls.add('zoomControl').add('mapTools').add('typeSelector');

    myPlacemark = new ymaps.Placemark([map_lat, map_lng], {
        content: map_companyname,
        balloonContent: map_companyname
    });

    myMap.geoObjects.add(myPlacemark);
}