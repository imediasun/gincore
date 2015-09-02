<?php

header("Content-Type: application/xml; charset=UTF-8");

include 'inc_config.php';
include 'inc_func.php';
require_once 'configs.php';


if (!$debug)  error_reporting(0);

$total_items = 1000000;
$serv = 'http://'.$_SERVER['SERVER_NAME'];
$current_date = date('c');

echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';

/*
// generate for items
function generate_url_tag($link, $serv, $date=''){
    global $current_date;
    $xmlout='<url>';
    $xmlout.='<loc>'.$serv.$link.'</loc>';
    
    if (!$date)
        $date = $current_date;
    $xmlout.='<lastmod>'
                .date('c', strtotime($date))
            .'</lastmod>';
    
    $xmlout.='</url>'."\n";
    return $xmlout;
}

$arr_items = $db->query('SELECT DISTINCT g.id, g.url, g.date_add, h.date_update
                        FROM {goods} as g
                        LEFT JOIN (SELECT goods_id, date_update FROM {goods_extended})h ON h.goods_id=g.id,
                        {goods_images} as i
                        WHERE g.avail=1 AND g.id=i.goods_id',
                        array(), 'assoc');
//echo '<count>'.count($arr_items).'</count>';
$xml = '';
foreach ($arr_items as $item){
    $link = htmlspecialchars($item['url']).'/p/'.$item['id'];
    $date = ($item['date_update'] ? $item['date_update'] : $item['date_add']);
    $xml .= generate_url_tag($link, $serv.'/', $date);
}
echo $xml;

// generate for categories
$arr_cat = $db->query('SELECT url FROM {categories} WHERE avail=1',
                        array(), 'assoc');
//echo '<count>'.count($arr_cat).'</count>';
$xml = '';
foreach ($arr_cat as $item) {
    $link = htmlspecialchars($item['url']).'/c';
    $xml .= generate_url_tag($link, $serv.'/');
}
echo $xml;
*/

// generate for map
function gen_this_level($prefix, $parent)
{
    GLOBAL $db, $cfg;
    $configs = Configs::get();
    
    $map_arr=$db->query('SELECT id, url, parent, redirect, uxt, is_page, page_type
            FROM {map}
            WHERE state=1 AND parent=?i ORDER BY prio',
            array($parent,  ), 'assoc');

    foreach ($map_arr as $pp) {
        
        $filtr = array('error404', 'contacts1', 'kontakty-v-servisah', 
            'comebacker', 'tsentr');
        if (in_array($pp['url'], $filtr)) continue;
        
        if ($pp['uxt'] == '0000-00-00 00:00:00') $pp['uxt'] = time(); 
        
        $xmlout='<url>';
        $xmlout.='<loc>'.strtolower($prefix.$pp['url']).'</loc>
                  <lastmod>'
                    .date('c', strtotime($pp['uxt']))
                    //.$pp['uxt']
                .'</lastmod>';
        $xmlout.='</url>';

        if ($pp['page_type']!=$configs['service-type-page'] && !$pp['redirect']){
            echo $xmlout;
        }

        gen_this_level($prefix.$pp['url'].'/', $pp['id']);
    }
}

//$serv = 'http://restore.kiev.ua';
gen_this_level($serv.'/', 0);


echo '</urlset>';


?>