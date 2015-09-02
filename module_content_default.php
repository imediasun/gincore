<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $url_lang, $arrequest, $db, $url_all_levels, $template_vars;

$text = gen_content_array($mod['content']);
$input['content'] = $text[0].( isset($text[1]) ? $text[1] :'');
$input['title'] = $mod['name'];

if ($mod['picture']){
    $input['image'] = '<img src="'.$prefix.'images/'.$mod['gallery'].'/'.str_replace('_m.', '.', $mod['picture']).'" alt="'.$input['title'].'">';
} else {
    $input['image'] = '';
}

require_once('configs.php');
$configs = Configs::get();

$is_news = isset($url_all_levels[1]['id']) && $url_all_levels[1]['id'] == $configs['news-page'];
$is_actions = isset($url_all_levels[1]['module']) && $url_all_levels[1]['module'] == 'content_actions';

// новость или акция
if($is_news || $is_actions){
    $page_title = 'Новости';
    
    $content = 
        '<div class="page_news_list">'.
            '<h2>'.$page_title.'</h2>';
    
    $default_page = false;
    
    include 'inc_func_news_actions.php';
    
    if($mod['gallery'] && $mod['picture']){
        $image = $mod['gallery'].'/'.$mod['picture'];
    }else{
        if($is_actions){
            $image = 'actions/default.jpg';
        }else{
            $image = 'news/default.jpg';
        }
    }
    if($is_actions){
        $content .= 
            '<a href="'.$prefix.$url_lang.$arrequest[0].'/'.$arrequest[1].'" class="font_size_14">'.$template_vars['l_return_to_actions'].'</a><br><br>';
    }else{
        $content .= 
            '<a href="'.$prefix.$url_lang.$arrequest[0].'/'.$arrequest[1].'" class="font_size_14">'.$template_vars['l_return_to_news'].'</a><br><br>';
    }
    
    $actions_products = '';
    if($is_actions){
        
        $ids = $db->query("SELECT products_id FROM {products2action} WHERE action_id = ?i", array($mod['id']), 'el');
        $ids_arr = explode(',', $ids);
        $true_ids = array();
        foreach($ids_arr as $key=>$id){
            $idz = (int)$id;
            if($idz){
                $true_ids[$idz] = $idz;
            }
        }
        
        if($true_ids){
            include_once 'shop/model.class.php';
            $model = new Model;
            $goods = $model->get_goods($true_ids);
            
            include 'shop/products.class.php';
            $products = new Products($db);
            $actions_products = 
                '<div class="action_products">'.
                    '<h2>Товары, участвующие в акции</h2>'.
                    '<ul class="small-heading-table">' . 
                        $products->show_heading_table($goods, $prefix, 1) . 
                    '</ul>'.
                    '<script type="text/javascript" src="'.$prefix.'extra/jquery.rating.js"></script>'.
                '</div>';
            $input_js['module_source'] = "
                $(document).ready(function() {
                    $('.over-big').mouseover(function() {
                        var position = $(this).position();
                        $('#over-big'+$(this).attr('id')).css({
                            top : position.top - 25
                        }).show();
                    });
                    $('.over-big').mouseleave(function() {
                        $('#over-big'+$(this).attr('id')).hide();
                    });
                });
            ";
            
        }
        $end = $mod['uxt'] - time();
        $footer =
                '<div class="pn_time">До окончания осталось <span>'.get_timeleft_txt($end).'</span></div>';
    }else{
        $footer =
                '<div class="pn_time">'.date('d.m.Y', $mod['uxt']).'</div>';
    }
    
    $content .= 
        '<div class="full_news">'
            .'<div class="fn_time">'.$footer.'</div>'
            .'<div class="fn_title"><div>'.$mod['name'].'</div></div>'
            .'<div class="fn_left">'
                .'<div class="fn_image"><img src="'.$prefix.'images/' . $image . '" alt=" "></div>'
                
            .'</div>'
            .'<div class="fn_right">'
                .$text[0].( isset($text[1]) ? $text[1] :'')
            .'</div>'
            .'<div class="clear_both"></div>'
        .'</div>'
        .$actions_products
        .'</div>'
    ;
    $input['content'] = $content;
    $input['title'] = '';
    $input_html['news_block']= '';
}
