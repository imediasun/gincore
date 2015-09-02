<?php

/* compare visitors information
 * 
 */

class Visitors {

    // default values
    public $visit_max_limit = 2; // visit max limit to show hight price
    private $visit_lastvisit_interval = 2592000; // if > then reset visit flag
    private $visit_increment_interval = 600; // if < then don't increment
//    private $visit_qty = 30;

    // user code
    private $code = null; // код юзера
    private $code_update_time = 86400; // апдейтим код через (сек)
    
    // user
    private $user_id; // id посетителя после init_visitors()
    private $ip; // ip юзера после init_visitors()
    private $visit = 0; // текущее количество посещений юзера после init_visitors()
    private $ip_visit = 0; // текущее количество посещений по апишнику юзера после init_visitors()
    
    private $coolie_qty_visit = 'qtyv';
    private $cookie_visitor_id='vid'; // visit id
    private $cookie_visitor_code='cvid'; // cookie for vid
    private $cookie_expired=1209600;
//    private $table;
    /** @todo move to configs 
     * 
     * write ranges in IP
     */
//    private $allowed_ip_range; // ip string 128.1.1.0, 12.12.0.1-12.12.0.55
    private $allowed_ip; // ip complex array
    private $reset_flag; // $_GET command received to reset user visits
    private $set_flag; // $_GET command received to set max visits to user
    private $allow_reset; // allow reset?
    
    
    private function init_vars() {
        global $cfg, $db, $template_vars, $settings, $user_ukraine, $mobile;
        require_once('configs.php');
        
        $this->db = $db;
        $this->template_vars = $template_vars;
        $this->settings = $settings;
        $this->user_ukraine = $user_ukraine;
        $this->mobile = $mobile;
        $this->configs = Configs::get();
        
        $this->visit_max_limit = (isset($settings['visit_max_limit']) && $settings['visit_max_limit']>0) ? $settings['visit_max_limit'] : $this->visit_max_limit;
        $this->visit_lastvisit_interval = (isset($settings['visit_lastvisit_interval']) && $settings['visit_lastvisit_interval']>0) ? $settings['visit_lastvisit_interval'] : $this->visit_lastvisit_interval;
        $this->visit_increment_interval = (isset($settings['visit_increment_interval']) && $settings['visit_increment_interval']>0) ? $settings['visit_increment_interval'] : $this->visit_increment_interval;

        $this->cookie_visitor_id = $cfg['tbl'].$this->cookie_visitor_id;
        $this->cookie_visitor_code = $cfg['tbl'].$this->cookie_visitor_code;
        $this->coolie_qty_visit = $cfg['tbl'].$this->coolie_qty_visit;
        $this->ip = static::get_ip();
        
        $this->allow_reset = $this->configs['reset-visits-allow'];
        $this->reset_flag = $this->configs['reset-visits-command'];
        $this->allowed_ip = $this->configs['reset-visits-ip'];
        $this->set_flag = $this->configs['set-visits-command'];
    }
        
    /**
     * Используя данные $_SERVER
     * 
     * получает количество визитов пользователя
     * максимум 20
     * для Курл 30-50,
     * googlebot 60-65,
     * 
     * устанавливает $this->visit = текущее количество посещений юзера
     */
    public function init_visitors(){
        // если уже инициализировали, то больше не дергаем инфу
        if(!empty($this->user_id)){
            return;
        }
        $this->init_vars();
        // get user info $_SERVER
        $arr['ip'] = $this->ip;
        $arr['user_agent'] = (isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'');
        $arr['accept'] = (isset($_SERVER['HTTP_ACCEPT'])?$_SERVER['HTTP_ACCEPT']:'');
        $arr['accept_language'] = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'');
        $arr['accept_encoding'] = (isset($_SERVER['HTTP_ACCEPT_ENCODING'])?$_SERVER['HTTP_ACCEPT_ENCODING']:'');
        $arr['cookies_enable'] = (isset($_SERVER['HTTP_COOKIE'])?1:0);
        $arr['cookies_cid'] = (isset($_COOKIE[$this->cookie_visitor_code])?$_COOKIE[$this->cookie_visitor_code]:'');
        $arr['id'] =  (isset($_COOKIE[$this->cookie_visitor_id])?$_COOKIE[$this->cookie_visitor_id]:'');
        $arr['referer'] =  (isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'(direct)');

        // set visited
        $qty = 0;
        if (isset($_COOKIE[$this->coolie_qty_visit])) {
            $data = explode('-', $_COOKIE[$this->coolie_qty_visit]);
            if (isset($data[0]) && isset($data[1]) && $data[1] > 0 && !$this->dont_increment_visitor_counter($data[0], false)) {
                $qty = $data[1];
            }
        }
        setcookie($this->coolie_qty_visit, time() . '-' . ($qty + 1), time() + $this->cookie_expired, '/');

        // make function to select on different params
        $visitor = $this->compare_visitor_params($arr);
//        print_r($visitor);
//        exit;
        // обновляем всю инфу о пользователе
        /** @todo
         * 
         * не обновлять всю инфу, а записывать нового Юзера
         * с данными параметрами + кол-во визитов 
         * + относительно какого юзера произошли изменения
         * id id_related visits params
         * 
         * не апдейтить accept если pjax queries
         * установить кукисы id & cid если отличаются
         */
        if($visitor) {
            $this->user_id = $visitor['id'];
            // update && return count
                
            if ($visitor['visit_count'] < 20 + $this->visit_max_limit
                    || ($visitor['visit_count'] >= 30 && $visitor['visit_count'] < 50)
                    || ($visitor['visit_count'] >= 60 && $visitor['visit_count'] < 65)
                )
                $arr['visit_count'] = $visitor['visit_count']+1;
            else
                $arr['visit_count'] = $visitor['visit_count'];
            
            ### if > inc interval then inc ###
            $no_increment = false;
            $no_increment = $this->dont_increment_visitor_counter($visitor['uxt']);
            if($no_increment)
                $arr['visit_count'] = $visitor['visit_count'];
            
            ##### reset user info ####
            $reset1 = false;
            $reset2 = false;
            if($this->allow_reset)
                $reset1 = $this->reset_visitor_counter();
            $reset2 = $this->reset_visitor_counter_by_lastvisit($visitor['uxt']);
            if($reset1 || $reset2)
                $arr['visit_count'] = 1;
            
            ##### set user counter to max ####
            $set = $this->set_visitor_counter();
            if($set)
                $arr['visit_count'] = $this->visit_max_limit+1;
            
            ##### update user info ####
                /*
                $this->db->query('UPDATE {visitors}
                    SET visit_count=visit_count+1 WHERE id=?i
                    ', array($visitor['id']), 'ar');
                */
                
            $query2 = '';
            //если cookie не совпадают - генерируем новые
            $hashed_cid = '';
            $cidgen = $arr['cookies_cid'];
//            $gen_new_cookie = false;
            // if user cookie differs
            if(!$cidgen){
//                $gen_new_cookie = true; // генерим новые куки принудительно если их нету
                $cidgen = $this->genCid(); // for cookie
            }
            $hashed_cid = $this->genHashedCid($cidgen); // for table
//            if(($arr['id']!=$visitor['id'] 
//                && $hashed_cid!=$visitor['cookies_cid']) || $gen_new_cookie) {
                $this->setCookies($visitor['id'], $cidgen);
//            }

            // if not pjax query write accept
            if(!isset($_SERVER['HTTP_X_PJAX'])) {
                $query2 = $this->db->makeQuery('accept = ?:accept,', $arr);
            }

            $arr['query2'] = $query2;
            $arr['cookies_cid'] = $hashed_cid;

            /**
             * если требуется апдейт после 1-го входа на страницу
             * закомментируйте uxt=NOW(), и добавте в таблицу
             * CURRENT TIMESTAMP ON UPDATE
             * в ином случае апдейтит дату при каждом входе на страницу
             */
            $this->db->query('UPDATE {visitors}
                SET uxt=NOW(),
                ip = INET_ATON(?:ip), visit_count = ?i:visit_count,
                cookies_cid = ?:cookies_cid, cookies_enable = ?i:cookies_enable,
                user_agent = ?:user_agent, ?q:query2
                accept_language = ?:accept_language, accept_encoding = ?:accept_encoding
                WHERE id=?i:id
                ', $arr, 'ar');
            
            $this->count_ip_visits();
            
            $this->visit = $arr['visit_count'] > $qty ? $arr['visit_count'] : $qty;
            return;
        }

//        check visitor params
        $arr = $this->check_visitor_params($arr);
        if(!isset($arr['visit_count']))
            $arr['visit_count'] = 1;

        // set cookies
        $cidgen = $this->genCid(); // for cookie
        $hashed_cid = $this->genHashedCid($cidgen); // for table
        $arr['cookies_cid'] = $hashed_cid;
        // save new
        $id = $this->db->query('INSERT INTO {visitors}
            (ip, visit_count, cookies_cid, cookies_enable,
            user_agent, accept, accept_language, accept_encoding, referer)
            VALUES (
            INET_ATON(?:ip), ?i:visit_count, ?:cookies_cid, ?i:cookies_enable,
            ?:user_agent, ?:accept, ?:accept_language, ?:accept_encoding, ?:referer
            )', $arr, 'id');
        $this->user_id = $id;
        $this->setCookies($id, $cidgen);
        
        $this->count_ip_visits();
        
        // устанавливаем количество посещений данного клиента
        if($id)
            $this->visit = $arr['visit_count'] > $qty ? $arr['visit_count'] : $qty;
        else
            $this->visit = 10+$this->visit_max_limit > $qty ? 10+$this->visit_max_limit : $qty;

    }

    // считаем кол-во заходов с данного айпи
    // вместе с текущим
    private function count_ip_visits(){
        $this->ip_visit = $this->db->query("SELECT sum(visit_count) FROM {visitors} "
                                          ."WHERE ip = INET_ATON(?)", array($this->ip), 'el');
    }
    
    public function get_user_id(){
        return $this->user_id;
    }

    public function get_user_type(){
        // 0 конкурент, 1 псевдо, 2 клиент
        $type = 2;
        if($this->is_competitor()){
            $type = 0;
        }elseif($this->is_pseudo_client()){
            $type = 1;
        }
        return $type;
    }
    
    public function get_visit(){
        return $this->visit;
    }
    
    // конкурент?
    public function is_competitor(){
//        return false; // для теста
        // проверяем чисто по айпи, селектим кол-во всех заходы с данного айпи
        return $this->ip_visit > $this->visit_max_limit;
        // раньше проверяло заходы найденого клиента
//        return $this->visit > $this->visit_max_limit;
    }
    // псевдоклиент?
    public function is_pseudo_client(){
//        return false; // для теста
        $is_pseudo = !$this->user_ukraine;
        //костылек, для прямого захода с моб устройств чтоб был как псевдоклиент
        if ($this->mobile && isset($_SERVER['HTTP_REFERER'])) {
            $referer = $this->db->query('SELECT id FROM {visitors}
                WHERE'
                .' ip = INET_ATON(?) AND (referer = "(direct)" OR referer = "")'
                . ' ORDER BY id DESC'
                .' LIMIT 1',
                array($this->ip), 'el');

            if ($referer) $is_pseudo = true;

        }
        //костыль 2 для прямого захода с моб устройств - псевдоклиент
        if ($this->mobile && (!isset($_SERVER['HTTP_REFERER']) || !$_SERVER['HTTP_REFERER']) ){
            $is_pseudo = true;
        }
        return $is_pseudo;
    }
    // клиент?
    public function is_client(){
        return !$this->is_competitor() && !$this->is_pseudo_client();
    }
    
    // возвращает цену смотря что за юзер (конкурент, псевдоклиент или клиент)
    public function get_price($price){
        $threshold = 1000;
        $price_delta = 0;
        $competitor = $this->is_competitor();
        $pseudo_client = $this->is_pseudo_client();
        //любой конкурент - макс наценка
        if ($competitor && $price < $threshold) {
            $price_delta = $this->settings['price_competitor_delta_under_1000'];
        }
        if ($competitor && $price >= $threshold) {
            $price_delta = $this->settings['price_competitor_delta_above_1000'];
        }
        //псевдоклиент - средняя наценка + псевдоакция
        if (!$competitor && $pseudo_client && $price < $threshold) {
            $price_delta = $this->settings['price_pseudoclient_under_1000'];
        }
        if (!$competitor && $pseudo_client && $price >= $threshold) {
            $price_delta = $this->settings['price_pseudoclient_above_1000'];
        }
        
        return $price + $price_delta;
    }
    
    // возвращает код юзера, смотря кто он такой (киент, псевдо или конкурент)
    public function get_code($only_code = false){
        if(is_null($this->code)){
            $user_type = $this->get_user_type();
            $current_code_row = $this->db->query("SELECT updated_at, code FROM {visitors_code} "
                                          ."WHERE visitor_id = ?", array($this->user_id), 'row');
            if(!$current_code_row || time()-strtotime($current_code_row['updated_at']) >= $this->code_update_time){
                // генерим новый код
                for($i = 1; $i <= 100; $i ++){
                    $code = $this->gen_discount_code($user_type);
                    $code_exists = $this->db->query(
                                            "SELECT SUM(t.c) FROM ("
                                               ."SELECT count(*) as c "
                                               ."FROM {visitors_code} WHERE code = ? "
                                               ."UNION "
                                               ."SELECT count(*) as c "
                                               ."FROM {visitors_system_codes} WHERE code = ?"
                                            .") AS t ", array($code, $code), 'el');
                    if(!$code_exists){
                        break;
                    }
                    if($i == 100){
                        $code = '';
                        send_mail("kv@fon.in.ua,ragenoir@gmail.com", 
                                  "ReStore: код не сгенерился юзеру!", 
                                  "За 100 итераций код юзеру не сгенерился<br><br>class_visitors.php -> get_code()");
                    }
                }
                // добавляем новую запись кода или апдейтим код
                $this->db->query("INSERT INTO {visitors_code} (visitor_id, code, created_at, updated_at) "
                          ."VALUES(?i:vid, ?:code, NOW(), NOW()) "
                          ."ON DUPLICATE KEY UPDATE code = ?:code, updated_at = NOW()", array(
                              'vid' => $this->user_id,
                              'code' => $code
                          ));
            }else{
                $code = $current_code_row['code'];
            }
            $this->code = $code;
        }
        if($only_code){
            return $this->code;
        }else{
            return '<div class="user_code">
                       '.str_replace("%code%", $this->code, $this->template_vars['l_user_code']).'
                   </div>';
        }
    }
    private function gen_discount_code($algo){
        // если менять наборы, то нужно прописать маску в manage/services/crm/calls/js/main.js
        $ar = array('Е', 'И', 'О', 'У', 'Ю', 'Я', 'В', 'Г', 'Д', 'К', 'Л', 'М', 'Н', 'П', 'Р', 'С', 'Т', 'Ф');
        $c1 = rand(0, count($ar)-1);
        $c2 = rand(0, count($ar)-1);
        switch($algo){
            case 0: //конкурент - Начинается не с А и не с Б
                $a = $ar[$c1];
                $b = $ar[$c2];
            break;
            case 1: //псевдоклиент - Первая Б
                $a = 'Б';
                $b = $ar[$c1];
            break;
            case 2: //клиент - первая А
                $a = 'А';
                $b = $ar[$c2];
            break;
        }
        $x = rand(0, 9);
        $y = rand(0, 9);
        $z = rand(0, 9);
        return $a.$b.'-'.$x.$y.$z;
    }

    /** @todo
     * 
     * сравнивать данные браузера/системы для проверки версионности.
     */
    
    function compare_visitor_params($arr) {
        // ORDER BY id DESC to set last user visits
        
        $query1 = '';
//        $query2 = '';
        
        // compare by cookie
//        if($arr['cookies_enable'] && $arr['cookies_cid']) {
            $arr['cookies_cid'] = $this->genHashedCid($arr['cookies_cid']);
//            $query1 = $this->db->makeQuery('OR (id = ?i:id AND cookies_cid = ?:cookies_cid)', $arr);
            $query1 = $this->db->makeQuery('AND cookies_cid = ?:cookies_cid', $arr);
//        }
        
        // compare pjax query (accept in pjax differs)
//        if(!isset($_SERVER['HTTP_X_PJAX'])) {
//            $query2 = $this->db->makeQuery('AND accept = ?:accept', $arr);
//        }
                
        $arr ['query1'] = $query1;
//        $arr ['query2'] = $query2;
        
        // compare by IP, by other params
        $plain_query = $this->db->makeQuery('SELECT * FROM {visitors}
            WHERE'
//            .'('
            .' ip = INET_ATON(?:ip)'
            /*
            .' AND (
                user_agent = ?:user_agent ?q:query2 AND
                accept_language = ?:accept_language AND accept_encoding = ?:accept_encoding
                )
            )
            */
            .' ?q:query1'
            .' LIMIT 0, 1',
            $arr);
//        echo $plain_query;
        $row = $this->db->plainQuery($plain_query)->row();
        
        return $row;
    }

    // check for curl file_get_contents() & others
    function check_visitor_params($arr) {
        /* curl sents browser curl & default accept */
        /* file_get_contents() sents browser no header || any manual header */
        if(strpos($arr['user_agent'], 'curl') !== false || $arr['accept'] == '*/*'
            || !$arr['accept_language'] || !$arr['accept_encoding']
            || !$arr['user_agent'] || !$arr['accept']
            ) {
            $arr['visit_count'] = 30;
        }

        /* googlebot agent */
        if(stripos($arr['user_agent'], 'googlebot') !== false) {
            $arr['visit_count'] = 60;
        }
                
        return $arr;
    }
    
    private function genCid() {
        mt_srand((double)microtime()*1000000);
        $cid = md5(mt_rand() + mt_rand().$this->ip);
        return $cid;
    }

    private function genHashedCid($cid) {
//        $hashed_cid = md5($this->ip).md5($cid);
        $hashed_cid = md5($cid); // no ip check
        return $hashed_cid;
    }
    
    private function setCookies($visitor_id='', $visitor_code='') {
        //        setcookie($this->cookie_session_name, '', time()-$this->cookie_expired, '/');
        setcookie($this->cookie_visitor_id, $visitor_id, time()+$this->cookie_expired, '/');
        setcookie($this->cookie_visitor_code, $visitor_code, time()+$this->cookie_expired, '/');
        return true;
    }
    
    /**
     * check if we have to reset
     * 
     * @return boolean
     */
    private function reset_visitor_counter() {
        if(isset($_GET[$this->reset_flag]))
            return true;
        /*
        if(!isset($_GET[$this->reset_flag]))
            return false;
        if (!$this->allowed_ip)
            return false;
        if(in_array($this->ip, $this->allowed_ip))
            return true;
        */
        return false;
    }
    private function set_visitor_counter() {
        if(isset($_GET[$this->set_flag]))
            return true;
        return false;
    }
    
    private function reset_visitor_counter_by_lastvisit($uxt) {
        if(time()-strtotime($uxt) > $this->visit_lastvisit_interval)
            return true;
        return false;
    }
    
    private function dont_increment_visitor_counter($uxt, $strtotime = true) {
        $uxt = $strtotime == true ? strtotime($uxt) : $uxt;
        if(time()-$uxt < $this->visit_increment_interval)
            return true;
        return false;
    }
    
    public function allow_reset(){
        $this->allow_reset = true;
    }
    
    static function get_ip(){
        $ip = '';
        if(isset($_SERVER['HTTP_X_REAL_IP'])){
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }elseif(isset($_SERVER['REMOTE_ADDR'])){
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    static $self = null;
    
    public static function getInstance(){
        if(is_null(self::$self)){
            self::$self = new self();
        }
        return self::$self;
    }
    
    private function __construct(){}
}


