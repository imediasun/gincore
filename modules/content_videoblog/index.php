<?php
global $mod, $prefix, $url_lang, $db, $arrequest, $lang, $def_lang, $template_vars;


if( isset($_GET['p']) ){
    $current_page = $_GET['p']-1;
}else{
    $current_page = 0;
}

$search_query = isset($_GET['s']) ? trim($_GET['s']) : '';
$input['search_query'] = htmlspecialchars($search_query);
$search = $page_block_search = $search_join = '';
if($search_query){
    $page_block_search = '&s='.htmlspecialchars($search_query);
    $search_join = $db->makeQuery("LEFT JOIN {map_strings} as s "
                                 ."ON s.map_id = m.id AND s.lang = ?", array($lang));
    $search = $db->makeQuery(" s.name LIKE '%?e%' AND ", array($search_query));
}

$all_news = $db->query('SELECT count(*) '
                      .'FROM {map} as m '
                      .$search_join
                      .'WHERE ?q parent=?i AND  youtube_videos != "" AND state = 1', array($search, 1475))->el();    


$count_on_page = 21;
$count_pages = ceil($all_news/$count_on_page);
$pages = '';
if ($count_pages > 1) {
    include('shop/products.class.php');
    $products = new Products();
    $pages = $products->page_block($count_pages, $page_block_search);
}
$start = $current_page * $count_on_page;

$html = '';

$youtube_videos = $db->query('SELECT m.id, uxt, youtube_videos, url '
                            .'FROM {map} as m '
                            .$search_join
                            .'WHERE ?q parent=?i AND  youtube_videos != "" AND state = 1 '
                            .'ORDER BY uxt DESC  LIMIT ?i, ?i', array($search, 1475, $start, $count_on_page))->assoc('id');

if ($youtube_videos) {
    $translates = get_few_translates(
        'map', 
        'map_id', 
        $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($youtube_videos))))
    );
    $count = 0;
    $html .= '<div class="row">';
    foreach ($youtube_videos as $v) {
        if($count == 3){
            $html .= '</div><div class="row">';
            $count = 0;
        }
        $count ++;
        $v = translates_for_page($lang, $def_lang, $translates[$v['id']], $v, true);
        $html .= '<div class="col-sm-4 video_blog">
                      <a href="'.$prefix.$url_lang.'restore/'.$mod['url'].'/'.$v['url'].'">
                          <img src="//img.youtube.com/vi/'.trim($v['youtube_videos']).'/sddefault.jpg" alt="'.$v['name'].'" width="100%">
                      </a>
                      <h2>
                          <a href="'.$prefix.$url_lang.'restore/'.$mod['url'].'/'.$v['url'].'">
                              '. $v['name'] . '
                          </a>
                      </h2>
                  </div>';
    }
    $html .= '</div>';
}elseif($search_query){
    $html = str_replace("%query%", htmlspecialchars($search_query), $template_vars['video_blog_serach_not_found']);
}


$input['content'] = '<h1 class="video_blog_title">'.$mod['name'].'</h1>'
                    . '<div class="page_news_list">'
                    . $html
                    . '</div>'
                    . '<div class="page_news_pages">'
                    . $pages
                    . '</div>';

$input_html['news_block'] = '123';

