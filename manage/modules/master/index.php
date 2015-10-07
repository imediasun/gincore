<?php

$modulename[] = 'master';
$modulemenu[] = 'Мастер';
$moduleactive[] = true;

class master{

    protected $all_configs;
    private $lang;
    private $def_lang;
    private $langs;
    function __construct($all_configs, $lang, $def_lang, $langs){
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->all_configs = $all_configs;
        $this->db = $all_configs['db'];
        
        global $ifauth;

        if($ifauth['is_1']) return false;
        
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }

        $this->gencontent();
    }

    private function gencontent(){
        global $input_html;
        
        // должности юзеров
        $roles = $this->db->query("SELECT id, name FROM {users_roles} "
                                 ."WHERE avail = 1 ORDER BY name")->vars();
        $input_html['roles'] = '';
        foreach($roles as $id => $role){
            $input_html['roles'] .= '<option value="'.$id.'">'.$role.'</option>';
        }
        
        // валюты
        $input_html['currencies_select'] = '';
        $input_html['currencies'] = '';
        foreach($this->all_configs['configs']['currencies'] as $cid => $currency){
            $input_html['currencies_select'] .= '<option value="'.$cid.'">'.$currency['name'].'</option>';
            $input_html['currencies'] .= '
                <div class="clearfix checkbox-with-course">
                    <div class="checkbox pull-left">
                        <label>
                            <input class="toggle-currency-course" type="checkbox" name="currencies['.$cid.']" value="'.$cid.'">
                            '.$currency['name'].'
                        </label>
                    </div>
                    <div class="col-xs-3">
                        <input class="hidden form-control currencies-courses" type="text" name="currencies_courses['.$cid.']" placeholder="Укажите курс для '.$currency['name'].'">
                    </div>
                </div>
            ';
        }
    }
    
    private function save(){
        // email
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        // сервисные центры
        $services = isset($_POST['services']) ? $_POST['services'] : array();
        // юзеры
        $users = isset($_POST['users']) ? $_POST['users'] : array();
        // валюта расчетов за заказы
        $orders_currency = isset($_POST['orders_currency']) ? $_POST['orders_currency'] : 0;
        // валюта расчетов за заказы поставщикам
        $contractors_currency = isset($_POST['contractors_currency']) ? $_POST['contractors_currency'] : 0;
        // валюты в бухгалтерии
        $currencies = isset($_POST['currencies']) ? $_POST['currencies'] : array();
        // курсы для валют в бухгалтерии
        $currencies_courses = isset($_POST['currencies_courses']) ? $_POST['currencies_courses'] : array();
        
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return array('state' => false, 'msg' => 'Эл. адрес указан неверно');
        }
        
        if(!$orders_currency){
            return array('state' => false, 'msg' => 'Выберите валюту расчетов за заказы');
        }
        
        if(!$contractors_currency){
            return array('state' => false, 'msg' => 'Выберите валюту расчетов за заказы поставщикам');
        }
        
        // вписываем мыло
        $this->db->query("UPDATE {settings} SET value = ? "
                        ."WHERE name = 'content_email'", array($email));
        
        $this->db->query("INSERT INTO {settings}(section, name, value, title) "
                        ."VALUES(1,'currency_orders',?i,'Валюта заказов') "
                        ."ON DUPLICATE KEY UPDATE value = VALUES(value)", array($orders_currency));
        
        // создаем кассу основную
        $cashbox_id = $this->db->query("INSERT INTO {cashboxes}(id,cashboxes_type,avail,"
                                                              ."avail_in_balance,avail_in_orders,name) "
                                      ."VALUES(1,1,1,1,1,'Основная')")->id();
        
        $this->db->query("INSERT INTO {settings}(section, name, value, title) "
                        ."VALUES(1,'currency_suppliers_orders',?i,'Валюта заказов поставщикам') "
                        ."ON DUPLICATE KEY UPDATE value = VALUES(value)", array($contractors_currency));
        
        // добавляем валюты
        $currencies_added = array();
        $currencies[$orders_currency] = $orders_currency;
        $currencies_courses[$orders_currency] = 1;
        $currencies[$contractors_currency] = $contractors_currency;
        foreach($currencies as $curr){
            if(isset($this->all_configs['configs']['currencies'][$curr])){
                $course = isset($currencies_courses[$curr]) ? (float)$currencies_courses[$curr] : 1;
                $name = $this->all_configs['configs']['currencies'][$curr]['name'];
                $short_name = $this->all_configs['configs']['currencies'][$curr]['shortName'];
                $id = $this->db->query("INSERT IGNORE INTO {cashboxes_courses}(currency,name,short_name,course)"
                                ."VALUES(?i,?,?,?f)", array($curr, $name, $short_name, $course), 'id');
                // привязываем валюты в основную кассу
                $this->db->query("INSERT INTO {cashboxes_currencies}(cashbox_id,currency,amount) "
                                ."VALUES(?i,?i,0)", array($cashbox_id, $curr));
                $currencies_added[$curr] = $id;
            }
        }
        if(!$currencies_added){
            return array('state' => false, 'msg' => 'Выберите валюты для взаиморассчетов с поставщиками');
        }
        
        // добавляем сервис центры
        $added_services = array();
        foreach($services as $i => $service){
            if(trim($service['name'])){
                $color = preg_match('/^#[a-f0-9]{6}$/i', trim($service['color'])) ? trim($service['color']) : '#000000';
                $id = $this->db->query(
                    'INSERT IGNORE INTO {warehouses_groups} (name, color, user_id, address) VALUES (?, ?, ?i, ?)',
                    array($service['name'], $color, $_SESSION['id'], $service['address']), 'id');
                if($id){
                    // основной
                    $main_wh = $this->create_warehouse($service['name'], $service['address'], 1, $id);
                    // прикрепляем текущего админа к складу
                    $this->db->query("INSERT INTO {warehouses_users}(wh_id,location_id,user_id,main) "
                                    ."VALUES(?i,?i,?i,1)", 
                                array($id,
                                      $main_wh['loc_id'],
                                      $_SESSION['id']));
                    // недостача
                    $this->create_warehouse('Недостача '.$service['name'], '', 2, $id);
                    // логистика
                    $this->create_warehouse('Логистика '.$service['name'], '', 3, $id);
                    // клиент
                    $this->create_warehouse('Клиент '.$service['name'], '', 4, $id);
                    $added_services[$i] = array(
                        'id' => $id
                    ) + $main_wh;
                }
            }
        }
        if(!$added_services){
            return array('state' => false, 'msg' => 'Добавьте отделения');
        }
        // добавляем юзеров
        $users_added = array();
        foreach($users as $i => $user){
            if($user['login'] && $user['password']){
                $user['position'] = isset($user['position']) ? $user['position'] : 0;
                $user_id = $this->db->query("INSERT INTO {users}(login,pass,email,fio,is_adm,role,state,avail) "
                                           ."VALUES(?,?,?,?,1,?i,1,1)", array(
                                               $user['login'],$user['password'],$user['login'],
                                               $user['fio'],$user['position']
                                           ))->id();
                $users_added[$i] = $user_id;
                if($user_id && isset($user['service']) && isset($added_services[$user['service']])){
                    // прикрепляем в складу
                    $this->db->query("INSERT INTO {warehouses_users}(wh_id,location_id,user_id,main) "
                                    ."VALUES(?i,?i,?i,1)", 
                                array($added_services[$user['service']]['wh_id'],
                                      $added_services[$user['service']]['loc_id'],
                                      $user_id));
                }
            }
        }
        
        // создаем системных контрагентов
        // покупатель
        $this->db->query('INSERT IGNORE INTO {contractors}
                                        (title, type, comment) VALUES (?, ?i, ?)',
                                    array('Покупатель', 3, 'system'));
        // покупатель списания
        $this->db->query('INSERT IGNORE INTO {contractors}
                                        (title, type, comment) VALUES (?, ?i, ?)',
                                    array('Покупатель списания', 3, 'system'));
        // ввод денежных остатков
        $id = $this->db->query('INSERT IGNORE INTO {contractors}
                                        (title, type, comment) VALUES (?, ?i, ?)',
                                    array('Ввод денежных остатков', 1, 'system'), 'id');
        $this->db->query('INSERT IGNORE INTO {contractors_categories_links}
                                (contractors_categories_id, contractors_id) VALUES (?i, ?i)',
                                array(32, $id));
        
        // ставим отметку что мастер настройки закончен
        $this->db->query("UPDATE {settings} SET value = 1 WHERE name = 'complete-master'");
        
        global $prefix;
        return array('state' => true, 'redirect' => $prefix.'orders');
    }
    
    private function create_warehouse($name, $address, $type, $group_id){
        // создаем склад
        $warehouse_id = $this->db->query('INSERT IGNORE INTO {warehouses}
            (consider_all, consider_store, code_1c, title, print_address,
             print_phone, type, group_id, type_id) VALUES (0, 1, null, ?, ?, null, ?i, ?n, ?n)',
                array($name, $address, $type, $group_id, 1), 'id');
        // и локацию
        $location_id = $this->db->query("INSERT IGNORE INTO {warehouses_locations} (wh_id,location)"
                        ."VALUES(?i,?)", array($warehouse_id, $name), 'id');
        return array(
            'wh_id' => $warehouse_id,
            'loc_id' => $location_id
        );
    }
    
    private function ajax(){
        $data = array(
            'state' => false,
            'msg' => 'Error'
        );
        $act = isset($_POST['act']) ? $_POST['act'] : '';
        switch($act){
            case 'save_data':
                $data = $this->save();
            break;
        }
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

}

