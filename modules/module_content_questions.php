<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $url_all_levelsm, $arrequest, $db;

$sql = $db->query('SELECT fullname, uxt, content FROM {map} WHERE parent = ?i AND state = 1 ORDER BY id DESC', array($mod['id']), 'assoc');

$input['title'] = $mod['name'];

$input['content'] = '';

foreach($sql as $row){

    $input['content'] .= '
        <div class="question-answer">
            <div class="question">
                <div class="pqm qplus"><div></div></div>
                <div class="qtitle">'.$row['fullname'].'</div>
            </div>
            <div class="answer">
                '.$row['content'].'
            </div>
        </div>
    ';

}

$input['image'] = '<img src="'.$prefix.'images/'.$mod['gallery'].'/'.str_replace('_m.', '.', $mod['picture']).'" alt=" ">';

?>