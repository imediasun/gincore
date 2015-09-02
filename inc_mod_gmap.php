<?php

// google map
global $gmap_markers, $gmap_zoom;
     $input_js['files_gmap']='';
    $input_js['source_gmap']='var gmap_companyname=\''.$settings['site_name']."';\n".
                             (!empty($gmap_markers) ? "var gmap_markers=".json_encode($gmap_markers).";\n" : '').
                             (!empty($gmap_zoom) ? "var gmap_zoom=".$gmap_zoom.";\n" : '').
                             "var gmap_lat=".$mod['lat'].";\n".
                             "var gmap_lng=".$mod['lng'].";\n";

    $input['map_1'] = '<div id="pageMap"></div>';
/*
// yandex map
     $input_js['files_gmap']='<script src="http://api-maps.yandex.ru/2.0-stable/?load=package.standard&lang=ru-RU" type="text/javascript"></script>'.
                        '<script type="text/javascript" src="'.$prefix.'extra/ymap.js"></script>';

    $input_js['source_gmap']='var map_companyname=\''.$settings['site_name']."';\n".
                             "var map_lat=".$mod['lat'].";\n".
                             "var map_lng=".$mod['lng'].";\n";

    $input['map'] = '<!--<h2 class="gallery_title">Карта</h2>-->
                    <div id="pageMap"></div>';
*/
?>