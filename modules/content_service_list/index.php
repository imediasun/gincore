<?php
/**
 * модуль вывода простого контента
 *
 */


//echo 'content_default';


global $mod, $prefix, $url_lang, $arrequest, $db, $cfg, $path, $settings, $lang, $def_lang, $template_vars, $user_ukraine,/* $visitors, $visit,*/ $user_ip, $mobile;

$configs = Configs::get();
//$input['page_tree'] = gen_page_tree();
$content = '';
$picture1 = '';
$buy_old_block = '';
$buy_old_popup = '';
$id = $mod['parent'];
$service_list='';
$service_listblock = '';

include 'consult/inc_consult.php';
//include_once 'class_visitors.php';

//не используется, но в файле есть др. функции
//помощник +50 грн к ценам в таблицу 1 и 2 
include_once 'inc_helper_price.php';


if(!isset($arrequest[2])){
    $text = gen_content_array($mod['content']);
    $title = $mod['name'];
    // заменяем обычные таблицы на респонсив
//    $text[0] = str_replace("<table>", '<div class="table-responsive"><table class="table">', $text[0]);
//    $text[0] = str_replace("</table>", '</table></div>', $text[0]);
    $content .= '
                <div class="article_block_inner service_desc '.(($arrequest[1]==$mod['url']) ? 'active' : '').'" data-rel="'.$mod['url'].'">'
                    .$text[0]
                .'</div>
                ';
    $id = $mod['id'];
    $arrequest[2] = '';
    $buy_old_block = gen_buy_old_block($mod['name'], $mod['buy_old'], $mod['hotline_price']);
    $buy_old_popup = gen_buy_old_popup($mod['name'], $mod['buy_old'], $mod['hotline_price'], $mod['id']);
    $input_html['flayers'] = '';
}

$cat_name = $mod['name'];

require_once $path . 'manage/class_auth.php';
require_once $path . 'shop/access_system.class.php';
$oRole = new Role(array('db' => $db), $cfg['tbl']);
$location = $oRole->is_active() ? 'location' : '';

$category = null;
if (intval($mod['category_id']) > 0) {
    // достаем категорию
    $category = $db->query('SELECT * FROM {categories} WHERE id=?i', array(intval($mod['category_id'])))->row();

    if ($category) {
        $user_reviwes = '';
        $marks = $db->query("SELECT id FROM {reviews_marks} ORDER BY id", array(), 'assoc:id');
        $translates = get_few_translates(
            'reviews_marks', 
            'mark_id', 
            $db->makeQuery("mark_id IN (?q)", array(implode(',', array_keys($marks))))
        );
        $marks_array = array();
        foreach ($marks as $mark){
            $mark = translates_for_page($lang, $def_lang, $translates[$mark['id']], $mark, true);
            $marks_array[$mark['id']] = $mark['name'];
        }
        $comments = $db->query("SELECT * FROM {reviews} WHERE service_id = ? "
                              ."ORDER BY id DESC", array($mod['id']), 'assoc:id');
        foreach ($comments as $c){
            $user_reviwes .= 
                '<div class="comment">
                            <div class="name">'
                    .htmlspecialchars($c['user'])
                    .'</div>
                            <div class="message">'
                    .htmlspecialchars($c['comment'])
                    .'</div>
                            <div class="time">
                                <time datetime="'.date('Y-m-d', strtotime($c['uxt'])).'">'.date('d.m.Y', strtotime($c['uxt'])).'</time>
                            </div>
                            <div class="mark_'.$c['mark'].'">'
                    .$marks_array[$c['mark']]
                    .'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-triangle">
                                    <polygon points="0,0 7,0 0,7"></polygon>
                                  </svg>'
                    .'</div>
                </div>';
        }

//                <div class="service_review_left">
//                    <table class="table table-condensed" itemscope itemtype="http://data-vocabulary.org/Review">
//                        <thead>
//                            <tr><td colspan="2">'.$template_vars['l_service_review_title'].'</td></tr>
//                        </thead>
//                        <tbody>
//                            <tr>
//                                <td>'.$template_vars['l_service_review_author'].':</td>
//                                <td itemprop="reviewer">' . htmlspecialchars($category['title']) . '</td>
//                            </tr>
//                            <tr>
//                                <td>'.$template_vars['l_service_review_date'].':</td>
//                                <td><time itemprop="dtreviewed" datetime="' . date("Y-m-d", strtotime($category['date_add'])) . '">' . date("Y-m-d", strtotime($category['date_add'])) . '</time></td>
//                            </tr>
//                            <tr>
//                                <td>'.$template_vars['l_service_review_text'].'</td>
//                                <td itemprop="itemreviewed">' . htmlspecialchars($category['title']) . ', ' . htmlspecialchars($category['content']) . '</td>
//                            </tr>
//                            <tr>
//                                <td>'.$template_vars['l_service_review_mark'].':</td>
//                                <td><span class="hidden" itemprop="rating">' . (round($category['rating']) <= 0 ? 4.1 : $category['rating']) . '</span>'
//                                  . '<div data-score="' . (round($category['rating']) <= 0 ? 4.1 : $category['rating']) . '" class="star-rating"></div></td>
//                            </tr>
//                        </tbody>
//                    </table>
//                </div>
//                <div class="service_review_right">
        $input['snippets'] =
            '<br />
            <div class="service_reviews">
                    '.$user_reviwes.'
            </div>
        ';
//                </div>
        
        
        $youtube_videos = $db->query('SELECT youtube_videos FROM {map} WHERE category_id=?i AND  youtube_videos != "" AND state=1', array(intval($mod['id'])))->col();
        $youtube_videos_html = '';

        $youtube_videos_block = '';
        if (isset($youtube_videos) && is_array($youtube_videos) && count($youtube_videos) > 0) {

            foreach ($youtube_videos as $v) {
                if ($v) {
                    $youtube_videos_html .= 
                        '<div class="service-video-blog embed-responsive embed-responsive-16by9">
                            <iframe class="embed-responsive-item"  src="https://www.youtube.com/embed/' . trim($v) . '" frameborder="0" allowfullscreen></iframe>
                        </div>';
                }
            }
            $youtube_videos_block = '
                <div class="video_block video_blob_block">
                    <div class="video-word">'
                        .'<h2>'.$template_vars['video_blog_services'].'</h2>'
                    .'</div>
                     '.$youtube_videos_html.'
                </div>';
        }
        $input_html['video_blog_block'] = $youtube_videos_block;
    }
}

// спрос
$demand_html = '';
if (intval($mod['category_id']) > 0 && $oRole->is_active()) {
    $date = $query = '';
    if (isset($_GET['date'])) {
        $date = urldecode($_GET['date']);
        $d = explode(' ', $date);
        if (isset($d[0]) && strtotime($d[0]) > 0) {
            $query = $db->makeQuery('?query AND UNIX_TIMESTAMP(date_add)>=?i', array($query, strtotime($d[0])));
        }
        if (isset($d[1]) && strtotime($d[1]) > 0) {
            $query = $db->makeQuery('?query AND UNIX_TIMESTAMP(date_add)<=?i', array($query, strtotime($d[1])));
        }
    }
    $goods = $db->query('SELECT g.id, g.title FROM {category_goods} as cg, {goods} as g
            WHERE g.id=cg.goods_id AND cg.category_id=?i AND g.avail=?i ORDER BY prio DESC',
        array(intval($mod['category_id']), 1))->assoc('id');

    $demand_html = '<p><span style="color: #7496ff;"><strong>Спрос</strong></span></p>';
    $demand_html .= '<form method="get" class="row"><div class="col-sm-8 col-sm-offset-2 input-group"><input name="date" class="form-control daterange" placeholder="Фильтровать по дате" type="text" value="' . $date . '" />';
    $demand_html .= '<span class="input-group-btn"><input class="btn btn-info" type="submit" value="Ok" /></span></div><br><br></form>';
    $demand_html .= '<div class="table-responsive"><table class="table"><tbody><tr><td>Запчасть</td><td>Количество</td><td>Добавить</td></tr>';
    if ($goods) {
        $demand = $db->query(
            'SELECT goods_id, COUNT(goods_id) FROM {goods_demand} WHERE goods_id IN (?li) ?query GROUP BY goods_id',
            array(array_keys($goods), $query))->vars();
        foreach ($goods as $goods_id=>$product) {
            $qty = (isset($demand[$goods_id]) ? intval($demand[$goods_id]) : 0);
            $demand_html .= '<tr><td>' . htmlspecialchars($product['title']) . '</td>';
            $demand_html .= '<td><span id="demand-product-' . $goods_id . '">' . $qty . '</span></td>';
            $demand_html .= '<td><span data-product_id="' . $goods_id . '" class="add_product_demand">+</span></td></tr>';
        }

    } else {
        $demand_html .= '<tr><td>Запчастей не найдено</td><td></td><td></td></tr>';
    }
    $demand_html .= '</tbody></table></div>';
}

$articles = $db->query(
    "SELECT id, lower(url) as url, uxt, picture, gallery, buy_old, hotline_price "
    ."FROM {map} WHERE parent = ?i AND state = 1 ORDER BY prio", array($id), 'assoc:id');
//print_r($mod);
if($articles){
    $translates = get_few_translates(
        'map', 
        'map_id', 
        $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($articles))))
    );
}
foreach($articles as $article){
    $article = translates_for_page($lang, $def_lang, $translates[$article['id']], $article, true);
    $picture_url = '';
    if($article['picture']) {
            $picture_url = $prefix.get_photo_by_lang('images/'.$article['gallery'].'/'.str_replace('_m.', '.', $article['picture']));
    }

    $service_list .= '
        <div class="item '.(($arrequest[2]==$article['url']) ? ' active' : '').'">
            <a href="'.$prefix.$url_lang.$arrequest[0].'/'.$arrequest[1].'/'.$article['url'].'" class="remont pjax ' . $location . '"
                data-rel="'.$article['url'].'" data-title="'.($article['fullname'] ? $article['fullname'] : $article['name']).'">
                <span>'.$article['name'].'</span>
            </a>
        </div>';

        $picture1 .= '
                <div class="item '.(($arrequest[2]==$article['url']) ? ' active' : '').'">
                    <a href="'.$prefix.$url_lang.$arrequest[0].'/'.$arrequest[1].'/'.$article['url'].'" class="remont pjax ' . $location . '"
                        data-rel="'.$article['url'].'" data-title="'.($article['fullname'] ? $article['fullname'] : $article['name']).'">
                        <span class="remont_image"'
                        .($picture_url ? ' style="background-image: url(\''.$picture_url.'\')"' : '').'></span>
                        <span>'.$article['name'].'</span>
                    </a>
                </div>
                ';

    if($arrequest[2]==$article['url']) {
        
        //инициируется только на этой странице, переносим в индекс, и тут создаем вызов. в голобалс :(
        //$visitors = new Visitors();
        //$visit = $visitors->init_visitors();
        
        $input_html['flayers'] = gen_service_banners($article['id']);
        $text = gen_content_array($article['content']);
        //$high_prices = '';
        $counter = '';
        $show_counter = true;
//        $competitor = false; // trigger for competitor prices ;
        $competitor = Visitors::getInstance()->is_competitor(); // trigger for competitor prices ;
		
        
        //echo $visit; exit;
        //$visit = 1;
        // Функционал по определению конкурента, использует класс new Visitors(), см выше.
//        if($visit > $visitors->visit_max_limit){ //если конкурент
//			$competitor = true ;
//        }

        //не показываем акцию если конкурент
        //в других случаях показываем.
        if ($competitor) {
                $show_counter = false; 
        }

        
        
        //var_dump($_SERVER['HTTP_REFERER']);
        
//        //костылек, для прямого захода с моб устройств чтоб был как псевдоклиент
//        if ($mobile && isset($_SERVER['HTTP_REFERER'])) {
//            $referer = $db->query('SELECT referer FROM {visitors}
//                WHERE'
//                .' ip = INET_ATON(?) '
//                . ' ORDER BY id DESC'
//                .' LIMIT 1',
//                array($user_ip), 'el');
//
//            if ($referer == '(direct)' || !$referer) $user_ukraine = false;
//
//        }

        //костыль 2 для прямого захода с моб устройств - псевдоклиент
//        if ($mobile && (!isset($_SERVER['HTTP_REFERER']) || !$_SERVER['HTTP_REFERER']) ) $user_ukraine = false;

        //echo '<!-- '.print_r($mobile . ' ! ' . $user_ukraine . ' - '. $referer .' - ' . $_SERVER['HTTP_REFERER'], true).'-->';
        
    //    if (isset($_SERVER['HTTP_REFERER'])) {
    //        echo '<!-- '.$_SERVER['HTTP_REFERER'].'-->';
    //    }
        
        
        
        
		// add service advantages and replace html price tables ;
        //Добавляется таблица 1, преимущества, таблица 1.
        //В таблицах проверка на цены дял колнкурнетов
        $text[0] = gen_service_advantages($text[0]/*, $competitor, $user_ukraine*/);

        if($show_counter){
            $counter = gen_counter(/*$user_ukraine*/);
        }

        $title = 'Ремонт '.($article['chat_caption'] ?: $article['name']) . ($oRole->is_active() ? '&nbsp;<a href="'.$prefix.'manage/map/'.$article['id'].'#prices"><img src="'.$prefix.'images/karandash.png"></a>' : '');
        $content .= '
                    <div class="article_block_inner '.(($arrequest[2]==$article['url']) ? 'active' : '').'" data-rel="'.$article['url'].'">'
                        .$counter
//                        .$visit
                        .$text[0] // content_prices
                        .$demand_html
                        // добавляем формы для кнопок купить в рассрочку
                        .credit_btn::getInstance()->modals_view()
                    .'</div>'
                    ;
        $buy_old_block = gen_buy_old_block($article['name'], $article['buy_old'], $article['hotline_price']);
        $buy_old_popup = gen_buy_old_popup($article['name'], $article['buy_old'], $article['hotline_price'], $article['id']);
    }
}

$picture_block = '';
if ($picture1)
$picture_block = '<div class="service_container" data-left="0">
    <div class="fotos_left_arrow" data-nav="prev"></div>
        <div class="fotos_container">
            <div class="content_images">
            '.$picture1.'
            </div>
        </div>
    <div class="fotos_right_arrow" data-nav="next"></div>
</div>';

// бренд вычисляем как первое слово до пробела
$brand = strtok(trim($article['name']), ' ');
// заменяем слово ремонт на наш бренд - где модуль генерит список для ремонта.
$cat_name = $cat_name == 'Ремонт' ? $cat_name : $brand;

if($service_list){
    $devices_listblock = '
    <div class="service_listblock devices_block">
        <div class="service_title">
            <span>'.$template_vars['l_service_page_left_menu_title'].' '
                .strtok(str_replace($template_vars['l_service_page_title_words'], '', $cat_name),' ')
            .'</span>
        </div>
        <div class="service_content">
            '.$service_list.'
        </div>
    </div>';
}

// если к странице привязана категория и сотрудник
if (intval($mod['category_id']) > 0 && $oRole->is_active()) {
    $url = $prefix . 'manage/products/create/';

    // достаем все товары по (свободным) складам по конкретной категории
    $items = $db->query('SELECT g.id, g.title, g.qty_store FROM {category_goods} as cg, {goods} as g
          WHERE cg.category_id=?i AND cg.goods_id = g.id GROUP BY g.id ORDER BY g.prio DESC',
        array(intval($mod['category_id'])))->assoc();

    if ($items) {
        $service_listblock .=
        '<div class="service_listblock">
            <div class="service_title">
                <span>Свой склад</span>
            </div>
            <div class="service_content">
                <table class="table table-condensed">';
        foreach ($items as $item) {
            $service_listblock .=
                    '<tr' . ($item['qty_store'] > 0 ? ' class="color-link-2"' : '') . '>' .
                        '<td><a href="' . $url . $item['id'] . '"><span>' . htmlspecialchars($item['title']) . ' </span></a></td>' .
                        '<td title="Свободный остаток">' . $item['qty_store'] . '</td>' .
                    '</tr>';
        }
        $service_listblock .=
                '</table>
             </div>
        </div>';
    }

    // заказы поставщикам по конкретной категории
    $data = $db->query('SELECT o.count, o.date_wait, g.title, o.goods_id, COUNT(l.client_order_id) as qty_occupied, o.id
            FROM {category_goods} as cg, {goods} as g, {contractors_suppliers_orders} as o
            LEFT JOIN {orders_suppliers_clients} as l ON l.supplier_order_id=o.id
            WHERE g.id=o.goods_id AND o.goods_id=cg.goods_id AND cg.category_id=?i AND o.confirm<>?i AND o.unavailable<>?i
              AND o.avail=?i AND o.count_debit=?i AND o.supplier>?i GROUP BY o.id ORDER BY g.prio DESC, o.goods_id, o.date_add',
        array(intval($mod['category_id']), 1, 1, 1, 0, 0))->assoc();

    if ($data) {
        $url_supp_order = $prefix . 'manage/orders/edit/';
        $service_listblock .=
        '<div class="service_listblock">
            <div class="service_title">
                <span>Ожидаемые поступления</span>
            </div>
            <div class="service_content">
                <table class="table table-condensed">';
        foreach ($data as $order) {
            $service_listblock .=
                    '<tr>' .
                        '<td><a href="' . $url . $order['goods_id'] . '">' . htmlspecialchars($order['title']) . '</a></td>' .
                        '<td title="Общий остаток">' . intval($order['count']) . '</td>' .
                        '<td title="Свободный остаток">' . ($order['count'] - $order['qty_occupied']) . '</td>' .
                        '<td><a href="' . $url_supp_order . $order['id'] . '#create_supplier_order">' . date('d.m', strtotime($order['date_wait'])) . '</a></td>' .
                    '</tr>';
        }
        $service_listblock .=
                '</table>
            </div>
        </div>';
    }

    $warehouses_suppliers = isset($settings['service-page-warehouses-suppliers']) && mb_strlen(trim($settings['service-page-warehouses-suppliers']), 'utf-8') > 0 ? $settings['service-page-warehouses-suppliers'] : null;
    $information = isset($settings['service-page-information']) && mb_strlen(trim($settings['service-page-information']), 'utf-8') > 0 ? $settings['service-page-information'] : null;

    if ($category) {
        if ($category['warehouses_suppliers'] && mb_strlen(trim($category['warehouses_suppliers']), 'utf-8') > 0) {
            $warehouses_suppliers = trim($category['warehouses_suppliers']);
        }
        if ($category['information'] && mb_strlen(trim($category['information']), 'utf-8') > 0) {
            $information = trim($category['information']);
        }
    }

    if ($category) {

        // достаем ссылки на склады поставщиков Киев
        $goods = $db->query('SELECT l.link, g.title, l.goods_id, l.id
                FROM {goods_suppliers} as l, {goods} as g, {category_goods} as cg
                WHERE l.goods_id=g.id AND cg.goods_id=g.id AND cg.category_id=?i ORDER BY g.prio DESC',
            array(intval($mod['category_id'])))->assoc();

        if ($goods) {
            $links = array();
            foreach ($goods as $product) {
                if (!isset($links[$product['goods_id']])) {
                    $links[$product['goods_id']]['title'] = $product['title'];
                }
                $links[$product['goods_id']]['links'][$product['id']] = $product['link'];
            }

            $service_listblock .=
            '<div class="service_listblock">
                <div class="service_title">
                    <span>Склады поставщиков Киев</span>
                </div>
                <div class="service_content">
                <table class="table table-condensed">';
            foreach ($links as $goods_id=>$product) {
                $service_listblock .=
                    '<tr>
                        <td>
                        <a href="' . $url . $goods_id . '"><span>' . htmlspecialchars($product['title']) . '</span></a>
                        </td>
                        <td style="white-space:nowrap;text-align:right;">';
                $i = 1;
                foreach ($product['links'] as $link) {
                    $service_listblock .=
                            '<a target="_blank" class="color-link-' . $i . '" href="' . $link . '">o</a> ';
                    $i++;
                }
                $service_listblock .=
                        '</td>
                    </tr>';
            }
            $service_listblock .=
                '</table></div>
            </div>';
        }
    }

    if ($warehouses_suppliers) {
        $service_listblock .=
        '<div class="service_listblock">
            <div class="service_title">
                <span>Склады поставщиков США/Китай</span>
            </div>
            <div class="service_content">
                <div class="item">' . $warehouses_suppliers . '</div>
            </div>
        </div>';
    }

    if ($information) {
        $service_listblock .=
        '<div class="service_listblock">
            <div class="service_title">
                <span>Важная информация</span>
            </div>
            <div class="service_content">
                <div class="item">' . $information . '</div>
            </div>
        </div>';
    }

    $mod['template_inner'] = 'services_user';
} else {
    $mod['template_inner'] = 'services';
}

// показываем счетчик скидки для обычных цен
function gen_counter(/*$user_ukraine*/){
    global $template_vars, $settings;
    $counter = '';
    $current_hour = (int)date('H');
    
//    $template_vars['l_service_best_prices_client_code'] = str_replace('{-txt-code-client}', $settings['price_code_client'], $template_vars['l_service_best_prices_client_code']);
//    $template_vars['l_service_best_prices_pseudo_code'] = str_replace('{-txt-code-pseudoclient}', $settings['price_code_pseudoclient'], $template_vars['l_service_best_prices_pseudo_code']);
        
    // показываем с 8 до 21
    if($current_hour >= 8 && $current_hour <=20){
        $seconds_left = strtotime(date('Y-m-d 21:00:00')) - time();
        $act_text = str_replace(
                        "%timer%", 
                        '<span class="timer" id="prices_counter" data-seconds="'.$seconds_left.'">'.gmdate("H:i:s", $seconds_left).'</span>',
                        $template_vars['l_service_best_prices_counter_title']
                      );
        $act_text = str_replace('%code%', Visitors::getInstance()->get_code(true), $act_text);
        $counter =
            '<div class="prices_timer">'.
                '<div class="text">'
                    . $act_text
                    // было: $user_ukraine ? ...
                    //. (!Visitors::getInstance()->is_pseudo_client() ? '<br>'.$template_vars['l_service_best_prices_client_code'] : '<br>'.$template_vars['l_service_best_prices_pseudo_code']) . ''
                .'</div>'
            .'</div>'
        ;
    }
    return $counter;
}


// вставка преимуществ после первой таблицы
function gen_service_advantages($content/*, $competitor, $user_ukraine*/) { 
    //конкурент - много раз заходит, и пользователь с Украины (а не с анонимайзеров)
    GLOBAL $db, $mod, $settings, $lang, $mobile; //$visitors,


    
    // enable replace mode @TODO config
    //@TODO 2 выключить данную настройку, включить генератор навсегда
    //$replace_mode = $settings['enable_conten_price_from_table'];

    $content = '';
    $sql = "SELECT * from {map_prices} WHERE map_id=?i and table_type=?i ORDER BY prio,id ASC";
    
// first table
    $price_table = $db->query($sql, array($mod['id'], 1))->assoc('id');
    if ($price_table) {
        $content = gen_price_table_1($price_table/*, $competitor, $user_ukraine*/);
    }

    $content .= gen_advantages_block('horizontal');
    
    // second table
    $price_table = $db->query($sql, array($mod['id'], 2))->assoc('id');
    if ($price_table) {
        $content .= gen_price_table_2($price_table/*, $competitor, $user_ukraine*/);
    }
    
    return $content;
}

function gen_block_service_description($title, $content){
    $content = trim($content);
    if(strpos($content, '</p>') === 0)
            $content = '<p>'.$content;
    if(strpos($content, '</span></p>') === 0)
            $content = '<p><span>'.$content;
    $block_html = '
        <div class="service_block">
            <div class="service_title">'
                .'<h2>'.$title.'</h2>'
            .'</div>
            <div class="service_content">'
                .$content
            .'</div>
        </div>';
    return $block_html;
}

function gen_buy_old_block($name, $buy, $price){
    global $template_vars;
    $block_html = '
        <div class="buy_old_block'
            .(($buy && $price)?'':' hidden').'">
            <div class="buy_old_title">
                <i></i>'
                .$template_vars['l_service_buy_old_title']
            .'</div>
            <div class="buy_old_content">'
                .str_replace('%name%', $name, $template_vars['l_service_buy_old_text'])
                .'<a class="on_load_popup" data-content="tradein-popup" href="#">'.$template_vars['l_service_buy_old_btn'].'</a>'
            .'</div>
        </div>';

    return $block_html;
}

function gen_buy_old_popup($name, $buy, $price, $id){
    global $prefix, $template_vars;

    $tradein_html =
    '<div class="form">
        <form method="post" action="'.$prefix.'ajax.php?act=sell-tradein" id="tradein-form" data-validate="parsley">
            <div class="tradein_fields">'.
        '<b>'.$template_vars['l_service_buy_old_popup_status'].'</b>'.
        '<input type="hidden" name="goods_id" value="' . $id . '">'.
        '<table class="tradein-inputs">'.
            '<tr>'.
                '<td>'.
                    '<label>'.
                        '<input class="input-radio-state" type="radio" name="state" value="defects">'.
                        '<span>'.$template_vars['l_service_buy_old_popup_status_variant_1'].'</span>'.
                    '</label>'.
                '</td>'.
                '<td>'.
                    '<label>'.
                        '<input type="radio" class="input-radio-state" name="state" value="good">'.
                        '<span>'.$template_vars['l_service_buy_old_popup_status_variant_2'].'</span>'.
                    '</label>'.
                '</td>'.
                '<td>'.
                    '<label>'.
                        '<input type="radio" class="input-radio-state" name="state" value="ideal">'.
                        '<span>'.$template_vars['l_service_buy_old_popup_status_variant_3'].'</span>'.
                    '</label>'.
                '</td>'.
            '</tr>'.
        '</table>'.
        '<div class="moisture-block"><b>'.$template_vars['l_service_buy_old_popup_water'].'</b>'.
            '<table class="tradein-inputs width_40 margin_auto">'.
                '<tr>'.
                    '<td>'.
                        '<label>'.
                            '<input class="input-radio-moisture" type="radio" name="moisture" value="yes">'.
                            $template_vars['l_service_buy_old_popup_water_variant_1'].
                        '</label>'.
                    '</td>'.
                    '<td>'.
                        '<label>'.
                            '<input class="input-radio-moisture" type="radio" name="moisture" value="no">'.
                            $template_vars['l_service_buy_old_popup_water_variant_2'].
                        '</label>'.
                    '</td>'.
                '</tr>'.
            '</table>'.
        '</div>'.
        '<div id="tradein-for-noauthorized" style="display: none">'.
            '<table class="account_table">'.
                '<tbody>'.
                    '<tr>'.
                        '<td colspan="2"><b>'.$template_vars['l_service_buy_old_popup_form_title'].'</b></td>'.
                    '</tr>'.
                    '<tr>'.
                        '<td>'.$template_vars['l_service_buy_old_popup_form_email'].'</td>'.
                        '<td><input class="input" type="text" data-trigger="change" data-required="true" data-type="email" name="email" value=""></td>'.
                    '</tr>'.
                    '<tr>'.
                        '<td>'.$template_vars['l_service_buy_old_popup_form_phone'].'</td>'.
                        '<td><input class="input" type="text" data-trigger="change" data-type="phone" value="" name="phone"></td>'.
                    '</tr>'.
                '</tbody>'.
            '</table>'.
        '</div>'.
        '<div class="tradein-pay"></div>'.
        '<p id="sell-goods"><input type="submit" class="green_btn sell-goods" onclick="javascript:$(\'#tradein-form\').parsley( \'validate\' );" value="'.$template_vars['l_service_buy_old_popup_form_submit'].'"></p>'.
    '   </div>
    <div class="message"></div>
    </form>
     </div>';

    $popup_html = '
        <div class="top">
            <div class="sm_close"></div>
            '.$template_vars['l_service_buy_old_popup_btn'].'
        </div>
        <div class="bottom">
            <div class="error-popup"></div>
            <span class="tradein_product_title">' . $name . '</span>
            ' . $tradein_html . '
        </div>
        ';

    return $popup_html;
}

function gen_service_banners($id){
    global $db, $prefix;
    $out='';
    $flayers = $db->query("SELECT * FROM {banners}
        WHERE active = 1 AND is_double=3 AND page_id=?i
        ORDER BY prio", array($id), 'assoc');
    foreach($flayers as $flayer) {
            $img = $flayer && $flayer['image'] ? '<img alt="'.$flayer['name'].'"
            src="'.$prefix.'flayers/'.$flayer['image'].'">' : $flayer['name'];

            $out .='<div>'.
                    '<a href="'.$flayer['url'].'">'.
                        $img.
                    '</a>'.
                '</div>';
    }
    return $out;
}

function gen_service_form($page_id=''){
    global $prefix, $settings, $db, $lang, $def_lang, $template_vars;
    $translates = $db->query("SELECT content, lang 
                                  FROM {map_strings} WHERE map_id = ?i", array($page_id), 'assoc:lang');
    $content = translates_for_page($lang, $def_lang, $translates, array(), true);
    $content = $content['content'];
    $text = explode('<!-- pagebreak -->', $content);
    $text[1] = isset($text[1]) ? $text[1] : '';

    $items = $db->query("SELECT id,picture,gallery FROM {map} "
                   ."WHERE parent = ? AND state = 1 ORDER BY prio", array($page_id), 'assoc:id');
    $service_contacts_items = '';
    $translates = get_few_translates(
        'map', 
        'map_id', 
        $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($items))))
    );
    $service_contacts_items .= '
        <table class="service_contact_item">
    ';
    foreach($items as $item){
        $item = translates_for_page($lang, $def_lang, $translates[$item['id']], $item, false);
        if($item['lat'] && $item['lng']){
            $service_contacts_items .= '
                    <tr>
                        <td class="sci_name">'.$item['name'].'</td>
                        <td class="sci_content">'.$item['content'].'</td>
                    </tr>
            ';
        }
    }
    $service_contacts_items .= '</table>';
    
    $out = '<div class="service_contacts row">
                <div class="col-sm-8">'
                    .$template_vars['l_service_contacts_city_select'].' '.gen_city_select(true).'
                    <div class="service_contacts_items">
                        '.$service_contacts_items.'
                        <div class="clearfix"></div>
                    </div>
                </div>

                <div class="col-sm-4 consult_inner">
                    <div class="service_form">
                        <div class="service_contacts_text"><p>'
                        .$text[1]
                        .'</div>
                        <label>
                            <input type="text" value="" name="phone" placeholder="380 (__) ___-__-__">
                        </label>
                        <span class="error_message"></span>
                        <input type="button" value="'.$template_vars['l_consult_send_btn'].'" class="service_send_btn">
                    </div>
                    <div class="service_recall">
                        '.$template_vars['l_consult_success_text'].'
                    </div>
                </div>
                <div class="clearfix"></div>
             </div>';

    return $out;
}

//function OFF_gen_high_prices ($id, $service_page_type) { // не используется (в настройке убрать переключатель)
//    global $db, $lang, $def_lang;
//    
//    $content = $db->query("SELECT id FROM {map} WHERE parent = ?i AND state = 1 AND page_type = ?i", array($id, $service_page_type), 'el');
//    
//    if(!$content) return '';
//    
//    $translates = $db->query("SELECT * 
//                              FROM {map_strings} WHERE map_id = ?i", array($content), 'assoc:lang');
//    $content = translates_for_page($lang, $def_lang, $translates, array(), true);
//    $content = $content['content'];
//    $text = explode('<!-- pagebreak -->', $content);
//    
//    if(!$text[0]) {
//        return '';
//    }
//    
//    if(strpos($content,'<!-- pagebreak -->') !== false) {
//        $text[0] .= '</p>';
//    }
//    
//    return $text[0];
//}



/*
if ($mod['gallery']){
    $input['head_pics'] = get_big_pics($mod['gallery'], 3);
}
*/

$content .= '<div id="false_content" class="hidden"></div>';
$buy_old_popup = '<div class="tradein-popup sm_content">'.$buy_old_popup.'</div>';

$service_form = gen_service_form(152);
$content_block = $content;

$input['title'] = $title;
$input['content'] = $content_block;
$input['content_images'] = $picture_block;
$input['service_form'] = $service_form;

$input_html['css_extra'] = '<link type="text/css" rel="stylesheet" href="'. $prefix .'extra/fancy/jquery.fancybox.css">';
$input_html['css_extra'] .= '<link type="text/css" rel="stylesheet" href="'. $prefix .'extra/service_modals.css?2">';
$input_js['extra_files'] = '<script type="text/javascript" src="'. $prefix .'extra/fancy/jquery.fancybox.js"></script>';
$input_js['extra_files'] .= '<script type="text/javascript" src="'. $prefix .'extra/service_modals.js?5"></script>';

$input_html['news_block'] = '';

$input_html['devices_listblock'] = $devices_listblock;
$input_html['service_listblock'] = $service_listblock;
$input_html['service_block'] = gen_block_service_description(($mod['description_name']?$mod['description_name']:$mod['name']),(isset($text[1])?$text[1]:''));
$input_html['buyold_block'] = $buy_old_block;
$input_html['buyold_popup'] = $buy_old_popup;

global $user_kiev;
if(!$user_kiev){
    $input['banner'] = '<div class="service_city_flayer" id="service_city_flayer"><img src="'.$prefix.get_photo_by_lang('images/remont_po_vsey_Ukraine.jpg').'"></div>';
}