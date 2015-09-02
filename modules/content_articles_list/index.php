<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $url_lang, $arrequest, $db;

//$input['page_tree'] = gen_page_tree();
$articles = $db->query("SELECT id, url, uxt, picture, gallery "
                      ."FROM {map} WHERE parent = ?i AND state = 1 ORDER BY uxt DESC", array($mod['id']), 'assoc:id');
$translates = get_few_translates(
            'map', 
            'map_id', 
            $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($articles))))
        );
$content = '<div class="article_row">';

$i = 0;
foreach($articles as $article){
    $article = translates_for_page($lang, $def_lang, $translates[$article['id']], $article, true);
    $picture = '';
    if($article['picture']) {
        $picture = '<img class="article_image" src="'.$prefix.'images/'.$article['gallery'].'/'.str_replace('_m.', '.', $article['picture']).'" alt="'.$article['name'].'">';
    }
    
    if($i == 3){
        $content .= '</div><div class="article_row">';
        $i = 0;
    }
    
    $description = explode('<!-- pagebreak -->', $article['content']);
    $content .= '
        <a href="'.$prefix.$url_lang.$arrequest[0].'/'.$mod['url'].'/'.$article['url'].'" class="article_block">
            <div class="article_block_inner">
                <time datetime="'.date('Y-m-d', strtotime($article['uxt'])).'">'.date('d.m.Y', strtotime($article['uxt'])).'</time>
                <h1>'.$article['name'].'</h1>
                '. $picture
                .'<p>'.strip_tags($description[0]).'</p>
            </div>
        </a>
    ';
    $i++;
}

$content .= '</div>';

if ($mod['gallery']){
    $input['head_pics'] = get_big_pics($mod['gallery'], 3);
} 

$input['content'] = $content;
?>