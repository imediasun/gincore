<?php

class Products
{

    public $configs;
    public $model;

    function __construct ()
    {
        require_once('manage/configs.php');
        $this->configs = Configs::getInstance()->get();

        require_once('shop/model.class.php');
        $this->model = new Model;

    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz_-';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    function show_heading_table($show_goods, $prefix, $compact = 0, $sc = 0, $count = null, $categories = null)
    {
        global $settings, $path;

        $goods = '';
        $yet = array();
        $i = 0;
        foreach ( $show_goods as $id=>$val ) {
            $i++;
            if ( $count && $count > 0 && $i > $count )
                break;

            if ($categories && is_array($categories) && count($categories) > 0 && !array_key_exists($val['category_id'], $categories) )
                continue;

            $rand = $this->generateRandomString(10);

            $path_parts = pathinfo($val['image']);
            if ( array_key_exists($val['id'], $yet) ) {
                /*$goods .= '<a style="display:none" rel="lightbox' . $val['id'] . '[plants]"
                    title="<p>' . htmlspecialchars($val['title']) . '</p>' . htmlspecialchars(mb_substr($val['content'], 0, 200, 'utf-8')) . '"
                    href="' . $prefix . $this->configs['goods-images-path'] .  $val['id'] . '/' .
                    htmlspecialchars($path_parts['filename']) . $this->configs['medium-image'] . $path_parts['extension'] .  '"></a>';*/
                continue;
            }
            $img = $this->configs['goods-images-path'] . $val['id'] . '/' .
                htmlspecialchars($path_parts['filename']) . $this->configs['medium-image'] . $path_parts['extension'];

            if ( file_exists($path . $img) ) {
                list($width, $height, $type, $attr) = getimagesize($path . $img);
                if ($width < $height)
                    $hw = 'height="100"';
                else
                    $hw = 'width="100"';
            } else {
                $hw = 'height="100"';
                $img = 'images/iconka_100x100.png';
            }

            $yet[$val['id']] = $val['id'];
            $goods .= '<li class="text-left over-big" id="' . $rand . 'g' . $val['id'] . '">';
                //'<div class="srtgs rt_' . $val['id'] . '"></div>';
            $goods .= $this->model->show_rating($val['rating']);

            $action = '';
            if (isset($val['action']) && $val['action'] == 1 )
                $action = '<img alt=" " class="action-png" src="' . $prefix . 'images/akciya.png" />';
            if ( $compact == 1) {
                $goods .= '
                    <table class="item_preview_image"><tr><td>' . $action . '
                        <a href="'.$prefix.urlencode($val['url']).'/' . $this->configs['product-page'] . '/' . $val['id'] . '">
                            <img title="' . htmlspecialchars($val['image_title']) . '" ' . $hw . '
                                src="' . $prefix . $img . '" alt=" " />
                        </a>
                    </td></tr></table>
                    <div>
                        <a href="'.$prefix.urlencode($val['url']).'/' . $this->configs['product-page'] . '/'.$val['id'].'">' .
                            htmlspecialchars(mb_substr($val['title'], 0, 40, 'utf-8')) . '
                        </a>
                    </div>
                    <div class="red-title goods-' . $val['id'] . '">' . $this->model->cur_currency($val['price']) . '</div>
                    <div class="over-big-show" id="over-big'  . $rand . 'g' . $val['id'] . '">' .
                        /*'<div class="srtgs rt_' . $val['id'] . '"></div>'*/ $this->model->show_rating($val['rating']) .
                        $this->for_show_heading_table($prefix, $val, $img, $sc, $hw) . '</div>
                ' ;
//                    <div>' . htmlspecialchars(mb_substr($val['content'], 0, 40, 'utf-8')) . '</div>
            } else {
                $goods .=  $this->for_show_heading_table($prefix, $val, $img, $sc, $hw);
            }
            $goods .= '</li>';
        }
        return $goods;
    }
    function for_show_heading_table($prefix, $val, $img, $sc, $hw)
    {
        global $settings;

        $product_status = 0;

        if ( $val['exist'] > 0 || (isset($val['foreign_warehouse']) && $val['foreign_warehouse'] == 1 )) {
//            $count_exist = 1;
            $product_status = 1;
            $exist = '<span class="item_in_stock"><span></span>В&nbsp;наличии</span>';
        } else {
//            $count_exist = 0;
            if(strtotime($val['wait']) < time()) {
                $exist = '<span>Нет в наличии</span>';
                if ( isset($_SESSION['user_id']) )
                    $scb = '<input goodsid="' . $val['id'] . '" type="button" class="btn green_btn btn-small text-right btn-mailme mailme" value="Сообщить о поступлении" />';
                else
                    $scb = '<input goodsid="' . $val['id'] . '" type="button" class="btn green_btn btn-small text-right btn-mailme on_load_popup" data-content="mail-me" value="Сообщить о поступлении" />';
            } else {
                $exist = '<span class="wait-goods">ожидается</span>';
                $product_status = 1;
            }
        }
        if ( $product_status == 1 ) {
            global $db;
            $cart = new Cart($db);
            $already_in = $cart->already_in_shopping_cart($val['id']);
            if ( $already_in )
                $scb = '<a href="' . $prefix . 'cart" class="btn btn-small orange_btn goto_cart"><span></span>Перейти в корзину</a>';
            else
                $scb = '<input goodsid="' . $val['id'] . '" type="button" class="btn green_btn text-right addToShoppingCartClass" value="Добавить в корзину" />';
        }

        if ( $sc == 1 ) {
            $scb = '<input class="change-goods-related change-goods-related-' . $val['id'] . '" type="checkbox" value="' . $val['id'] . '">';
        }
        $action = '';
        if (isset($val['action']) && $val['action'] == 1 )
            $action = '<img alt=" " class="action-png" src="' . $prefix . 'images/akciya.png" />';
//                <div class="pr_about">' . htmlspecialchars(mb_substr($val['content'], 0, 100, 'utf-8')) . '</div>
        return '<table class="item_preview_image"><tr><td>
                    <a href="'.$prefix.urlencode($val['url']).'/' . $this->configs['product-page'] . '/' . $val['id'] . '">' . $action . '
                        <img alt=" " title="' . htmlspecialchars($val['image_title']) . '" '.$hw.'
                            src="' . $prefix . $img. '" />
                    </a>
                </td></tr></table>
                <div class="pr_title">
                    <a href="'.$prefix.urlencode($val['url']).'/' . $this->configs['product-page'] . '/'.$val['id'].'">
                        <h4>' .
                            htmlspecialchars(mb_substr($val['title'], 0, 40, 'utf-8')) . '
                        </h4>
                    </a>
                </div>
                <div class="pr_footer">
                    <div class="pr_price_avail">
                        <span class="red-title">' .
                            $this->model->cur_currency($val['price']) . 
                        '</span>' . 
                        $exist . '
                    </div>
                    ' . $scb . '
                </div>'
            ;
    }
    function show_heading_list( $show_goods, $prefix, $img = 1 )
    {
        global $settings;
        $goods = '';

        foreach( $show_goods as $id=>$val ){

            if ( $val['exist'] > 0 || $val['foreign_warehouse'] == 1 ) {
                $count_exist = 1;
                $exist = '<span class="hlist_in_stock">В наличии <img src="'.$prefix.'images/ok_m.png" alt=" "></span>';
            } else {
                $count_exist = 0;
                if(strtotime($val['wait']) < time()) {
                    $exist = '<span class="hlist_in_stock">Нет в наличии</span>';
                } else {
                    $exist = '<span class="wait-goods hlist_in_stock">ожидается</span>';
                }
            }

            if ( !$img ) {
                $img_show = '';
            } else {
                $path_parts = pathinfo($val['image']);

                $action = '';
                if ( $val['action'] == 1 )
                    $action = '<img alt=" " class="action-png" src="' . $prefix . 'images/akciya.png" />';
                $img_show = '
                <div class="text-left">
                    <a href="'.$prefix.urlencode($val['url']).'/' . $this->configs['product-page'] . '/' . $val['id'] . '">' . $action . '
                        <img alt=" " title="' . htmlspecialchars($val['image_title']) . '" width="115" height="105"
                            src="' . $prefix . $this->configs['goods-images-path'] . $val['id'] . '/' .
                                htmlspecialchars($path_parts['filename']) . $this->configs['medium-image'] . $path_parts['extension'] . '" />
                    </a>
                </div>';
            }
            global $db;
            $cart = new Cart($db);
            $already_in = $cart->already_in_shopping_cart($val['id']);
            $btn = '';
            if($already_in){
                $btn = '<a href="'.$prefix.'cart" class="btn btn-small orange_btn goto_cart"><span></span>Перейти в корзину</a>';
            }else{
                $btn = '<input goodsid="' . $val['id'] . '" type="button" class="text-right green_btn btn addToShoppingCartClass" value="Добавить в корзину" />';
            }
            
            $goods .= '
                <li id="' . $val['id'] . '">
                    <div class="text-right list-buy-block">
                        <p class="red-title goods-' . $val['id'] . '">' . $this->model->cur_currency($val['price']) . '</p>
                        '.$btn.'
                    </div>
                    '.$img_show/*.'
                    <div class="srtgs rt_' . $val['id'] . '"></div>' */.'<div class="hlist_item_top">'. $this->model->show_rating($val['rating']) . $exist.'</div>
                    <div>
                        <a href="' . $prefix.urlencode($val['url']) . '/' . $this->configs['product-page'] . '/' . $val['id'].'">
                            <h4>' .
                                htmlspecialchars($val['title']) . '
                            </h4>
                        </a>
                    </div>
                    <p>' . htmlspecialchars(mb_substr($val['content'], 0, 150, 'utf-8')) . '</p>
                </li>' ;
        }
        return '<ul class="heading-list">' . $goods . '</ul>';
    }


    function page_block($count_page, $sort = '', $field = '' )
    {
        $page = '';

        foreach ( $this->check_page($count_page,(isset($_GET['p'])?$_GET['p']:1) , 1 ) as $p ) {
            if ( $p == (isset($_GET['p'])?$_GET['p']:1) ) {
                $page .= '<li><a href="' .'?p=' . $p . $sort . $field . '" class="text-bold current_page">' . $p . '</a></li>';
            } else {
                if ( intval($p)>0 ) {
                    $page .= '<li><a href="' .'?p=' . $p . $sort . $field . '">' . $p . '</a></li>';
                }else{
                    $page .= '<li>' . $p . '</li>';
                }
            }
        }
        $pageNumber = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($pageNumber == 1) {
            $page = '<ul><li class="disabled"><a href="?p=1' . $sort . $field . '">«&nbsp;' . l('Предыдущая') . '</a></li><li>&nbsp;|&nbsp;<span class="text-bold">Страница:</span>&nbsp;</li>' . $page .
                '<li>&nbsp;|&nbsp;</li><li><a href="?p=2' . $sort . $field . '">' . l('Следующая') . '&nbsp;»</a></li></ul>';
        } else {
            if ($count_page == $pageNumber) {
                $page = '<ul><li><a href="?p=' . ($pageNumber - 1) . $sort . $field . '">«&nbsp;' . l('Предыдущая') . '</a></li><li>&nbsp;|&nbsp;<span class="text-bold">Страница:</span>&nbsp;</li>' . $page .
                    '<li>&nbsp;|&nbsp;</li><li class="disabled"><a href="?p=' . $pageNumber . $sort . $field . '">' . l('Следующая') . '&nbsp;»</a></li></ul>';
            } else {
                $page = '<ul><li><a href="?p=' . ($pageNumber - 1) . '">«&nbsp;' . l('Предыдущая') . '</a></li><li>&nbsp;|&nbsp;<span class="text-bold">Страница:</span>&nbsp;</li>' . $page .
                    '<li>&nbsp;|&nbsp;</li><li><a href="?p=' . ($pageNumber + 1) . $sort . $field . '">' . l('Следующая') . '&nbsp;»</a></li></ul>';
            }
        }
        return '<div class="pagination">' . $page . '</div>';
    }

    function check_page($count, $cur=1, $need=1)
    {
        $ar = array();

        if( $cur == 1 || empty($cur) ) {
            //$ar[] = 'Previous | ';
            for ( $i=1; $i<2+$need; $i++ )
                $ar[] = $i;
            if ( 2+$need<=$count )
                $ar[] = '...';
            //$ar[] = ' | Next';
            return $ar;
        }
        if( $cur >= $need+2 )
            $ar[] = '...';
        for( $i=1; $i<=$count; $i++ ) {
            if ( $cur+$need >= $i && $cur <= $i+$need )
            {
                $ar[] = $i;
                continue;
            }
            if ( $count-2 == $i )
                $ar[] = $i;
        }
        if ( $cur+$need<= $count )
            $ar[] = '...';

        return $ar;
    }
}