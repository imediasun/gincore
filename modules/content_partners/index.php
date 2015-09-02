<?php
/**
 * сотрудничество
 * 
 */

global $mod, $prefix, $url_lang, $url_all_levelsm, $arrequest, $db, $path, $lang, $def_lang;

$tabs = $db->query("SELECT id FROM {map} WHERE parent = ?i", array($mod['id']), 'assoc:id');

$content = ''; 
if($tabs){
    $translates = get_few_translates(
        'map', 
        'map_id', 
        $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($tabs))))
    );
    foreach($tabs as $tab){
        $tab = translates_for_page($lang, $def_lang, $translates[$tab['id']], $tab, true);
        $content .= '
            <div class="question">
              <div class="title">
                  <i class="fa fa-minus"></i><i class="fa fa-plus"></i> 
                  '.$tab['name'].'
              </div>
              <div class="answer">
                  <div class="answer_text">
                      '.$tab['content'].'
                  </div>
              </div>
            </div>


        ';
    }
}
$input['partners_link_hidden'] = 'display:none;';
$input['title'] = $mod['name'];
$input['tabs'] = $content;
$input['content'] = $mod['content'];