<?php

/*
 * Функция, которая находила цены в таблице и увеличивала их
 * 
 * !!! Не используется !!!
 * 
 */
function helper_price_grow($table, $type) {

	$table=str_replace("(при условии последующего ремонта)", "", $table);// костыль по удалению фразы о доставке #549

    if (isset($_GET['price']) && $_GET['price']=='off') return $table;
    
    if ($type == 1){ //вся функция не используется
        
        $pattern = "|<td>.+</td>\s*<td>[^0-9]*([0-9]+).*</td>\s*<td>|";
        
        $table = preg_replace_callback($pattern, 'helper_price_plus', $table);
        
    }
    
    //
    if ($type == 2){
        $tablefirst = substr($table, 0, strpos($table, '</tr>')+5);
//        echo '<!-- '.$tablefirst.' -->';
        $table = substr($table, strpos($table, '</tr>')+5);
//        echo '<!-- '.$table.' -->';
        
        $pattern = "#<td>.*</td>[^<t]*<td>\D*?([0-9]+)[^</t]*</td>\s*<td>[^0-9]*([0-9]+)(.*|[^</t]+)</td>\s*<td>#";
        
//        print_r($table);
        
        $table = $tablefirst.preg_replace_callback($pattern, 'helper_price_plus2', $table);
    
//        print_r($table);
//        exit;
    
    }
    
    
    
    return $table;
}


function helper_price_plus($price) { //!!! Не используется !!!
   
    
    if ($price[1] == 0 ) {
        return $price[0];
    } else {
        $new_price = $price[1] + 50;
        return str_replace($price[1], $new_price, $price[0]);
    }
    
    
}

function helper_price_plus2($price) { // !!! Не используется !!!
//echo '<!--';
//    var_dump($price);
////    echo '<br>';
//echo '-->';
    
    if ($price[2] == 0 ) {
        return $price[0];
    } else {
        $new_price = $price[2] + 50;
//        echo '<!-- '.$new_price.' -->'."\n";
        $price[0] = str_replace($price[2], $new_price, $price[0]);
    }
    
    if ($price[1] == 0 ) {
        //$new_str = $price[0];
    } else {
        $new_price = $price[1] + 50;
//        echo '<!-- '.$new_price.' -->'."\n";
        $price[0] = str_replace($price[1], $new_price, $price[0]);
    }
    
    return $price[0];
}


// price table type 1
function gen_price_table_1($price_table/*, $competitor, $user_ukraine*/) {
    GLOBAL $settings, $lang, $def_lang, $db, $template_vars;
    $rows = null;
    
    $translates = get_few_translates(
        'map_prices', 
        'row_id', 
        $db->makeQuery("row_id IN (?q)", array(implode(',', array_keys($price_table))))
    );
    
    foreach ($price_table as $row) {
        $row = translates_for_page($lang, $def_lang, $translates[$row['id']], $row, true);
        if($row['hidden']) continue;
        
        /**
         * тут показываем одинаковые цены всем 
         */
        
        $credit_btn = credit_btn::getInstance()->get_btn($row['name'], $row['price']);
        $rows .= '<tr><td>' . $credit_btn . '</td>'
                . '<td>' . $row['price_mark'] . ' ' . $row['price'] . '</td>'
                . '<td>' . htmlspecialchars($row['time_required']) . '</td>'
                . '</tr>';
    }

    $tbl = '<table class="table">'
            . '<tbody>'
            . '<tr>'
            . '<td>'.$template_vars['l_pricing_table_work_type'].'</td>'
            . '<td>'.$template_vars['l_pricing_table_price'].'</td>'
            . '<td>'.$template_vars['l_pricing_table_time'].'</td>'
            . '</tr>'
            . $rows
            . '</tbody></table>';
    return $tbl;
}

// price table type 2
function gen_price_table_2($price_table/*, $competitor, $user_ukraine*/) {
    GLOBAL $settings, $lang, $def_lang, $db, $template_vars;
    $rows = null;
    $translates = get_few_translates(
        'map_prices', 
        'row_id', 
        $db->makeQuery("row_id IN (?q)", array(implode(',', array_keys($price_table))))
    );
    
    foreach ($price_table as $row) {
        $row = translates_for_page($lang, $def_lang, $translates[$row['id']], $row, true);
        if($row['hidden']) continue;
        
        //echo $_SERVER['HTTP_REFERER']; exit;
        
        $credit_btn = credit_btn::getInstance()->get_btn($row['name'], Visitors::getInstance()->get_price($row['price']));
        $rows .= '<tr>'
			. '<td>' . $credit_btn . '</td>'
			. '<td>' . $row['price_copy_mark'] . ' ' . Visitors::getInstance()->get_price($row['price_copy']) . '</td>'
			. '<td>' . $row['price_mark'] . ' ' . Visitors::getInstance()->get_price($row['price']) . '</td>'
			. '<td>' . htmlspecialchars($row['time_required']) . '</td>'
			. '</tr>';
    }

    $tbl = '<p>&nbsp;</p>'
            . '<p><span style="color: #7496ff;"><strong>'.$template_vars['l_pricing_table_2_title'].'</strong></span></p>'
            . '<table class="table"><tbody><tr>'
            . '<td>'.$template_vars['l_pricing_table_2_work_type'].'</td>'
            . '<td>'.$template_vars['l_pricing_table_2_copy_price'].'</td>'
            . '<td>'.$template_vars['l_pricing_table_2_price'].'</td>'
            . '<td>'.$template_vars['l_pricing_table_2_hour'].'</td>'
            . '</tr>'
             . $rows .
            '</tbody></table>';

    return $tbl;
}

class credit_btn{
    
    static $self = null;
    private $btns_data = array();
    
    public function get_btn($content, $price){
        global $settings;
        if($settings['service_btn_min_price'] <= $price){
            // можно вставить другую форму через админку в контент если шо
            // код {service_btn-(id страницы с админки)}
            // или автоматом для всех будет форма $settings['service_btn_default_form']
            preg_match_all('/{service_btn-([0-9]+)}/', $content, $btn);
            if(!empty($btn[1][0])){
                $btn = $btn[1][0];
                $data = $this->get_data($btn);
                $content = '<span class="service_name">'.str_replace('{service_btn-'.$btn.'}', '</span>'.$this->btn_view($data), $content);
            }else{
                $btn = $settings['service_btn_default_form'];
                $data = $this->get_data($btn);
                //var_dump($data); exit;
                $content = '<span class="service_name">'.$content.'</span>'.
                            $this->btn_view($data);
            }
        }else{
            $content = preg_replace('/({service_btn-[0-9]+})/', '', $content);
        }
        return $content;
    }
    
    private function get_data($id){
        global $lang, $def_lang;
        if(!isset($this->btns_data[$id])){
            global $db;
            $translates = $db->query("SELECT name, content, fullname, lang 
                                  FROM {map_strings} WHERE map_id = ?i", array($id), 'assoc:lang');
            $data = translates_for_page($lang, $def_lang, $translates, array(), true);
            $data['id'] = $id;
            $this->btns_data[$id] = $data;
        }
        return $this->btns_data[$id];
    }
    
    private function btn_view($data){
        return 
            '<button class="show_service_modal service_btn" data-target="service_modal-'.$data['id'].'">'.
                $data['name'].
            '</button>'
        ;
    }
    
    public function modals_view(){
        global $template_vars;
        $modals = '';
        foreach($this->btns_data as $data){
            $modals .= 
                '<div id="service_modal-'.$data['id'].'" class="service_modal">'.
                    '<div class="modal_content">'.
                        '<div class="modal_header">'.
                            seohide_html($data['fullname'] ?: $data['name']).
                            '<span class="close_service_modal">&times;</span>'.
                        '</div>'.
                        '<div class="modal_body">'.
                            '<div class="modal_body_text">'.
                                seohide_html($data['content']).
                            '</div>'.
                        '</div>'.
                        '<div class="modal_body_btn consult_inner">'.
                            '<input class="service_btn_request" type="button" value="'.$template_vars['l_service_credit_btn_form_btn'].'">'.
                            '<div class="service_btn_request_form">
                                <div class="service_form service_contacts credit_consult">
                                    <input type="hidden" value="" class="service">
                                    <div class="service_contacts_text"><p></p>
                                    <p>'.$template_vars['l_service_credit_btn_form_text'].'</p></div>
                                    <label>
                                        <input type="text" value="" name="phone" placeholder="380 (__) ___-__-__">
                                    </label>
                                    <div class="error_message"></div>
                                    <input type="button" value="'.$template_vars['l_service_credit_btn_form_submit'].'" class="service_send_btn">
                                </div>
                                <div class="service_recall">
                                    '.$template_vars['l_service_credit_btn_form_success'].'
                                </div>
                            </div>'.
                        '</div>'.
                    '</div>'.
                '</div>'
            ;
        }
        if($modals){
            return '<div class="service_modal_alpha" id="service_modal_alpha"></div>'.
                   $modals;
        }else{
            return '';
        }
    }
    
    public static function getInstance(){
        if(is_null(self::$self)){
            self::$self = new self();
        }
        return self::$self;
    }
    
    private function __construct(){}
}
