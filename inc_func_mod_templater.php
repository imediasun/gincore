<?php

// генерим шаблоны и скрипты для видео

function templater_no_left_block($width = 80){
    global $input_html, $input_js;
    $margin = (100 - $width) / 2;
    // no left block
    $input_html['news_block']='';
    $input_html['fb_director_block']= '';
    $input_html['video_block']='';
    $input_html['flayers']='';
    if (!isset($input_js['extra'])) $input_js['extra'] = '';
    $input_js['extra'].='
        <script>
        $(document).ready(function(){
            $(".sidebar").hide();
            $(".ajax_content").css({"width":"'.$width.'%", "margin": "0 '.$margin.'%",});
        });
        </script>
        ';
}

?>
