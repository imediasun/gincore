<?php
global $mod, $prefix, $db, $arrequest;


include 'inc_func_news_actions.php';

$html = generate_news($mod, 1);

gen_content_array($mod['content']);

$input['content'] = 

        '<h2>'.$mod['name'].'</h2>'.
        $html

;

$input_html['news_block']= '';

?>