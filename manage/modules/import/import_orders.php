<?php

class import_orders extends import_helper{
    
    private $orders_objects = array();
    private $provider; // 
    
    function __construct($all_configs, $provider, $import_settings){
        $this->all_configs = $all_configs;
        $this->provider = $provider;
        $this->import_settings = $import_settings;
    }
    
    function run($rows){
        $this->rows = $rows;
        $this->accepters = array();
        $this->accepters_wh = array();
        $this->engineers = array();
        $this->clients = array();
        $this->categories = array();
        $this->devices = array();
        
        $scan = $this->scan_accepters_and_engineers();
        if(!$scan['state']){
            return $scan;
        }
        
        if(!empty($this->import_settings['clear_categories'])){
            $this->clear_and_add_categories();
        }
        
        $results = array();
        foreach($this->rows as $row){
//            $order = new order();
            $errors = array();
//            $messages = array();
            $error_type = null;
            $id = $this->provider->get_id($row);
            $id_exists = db()->query("SELECT 1 FROM {orders} WHERE id = ?i", array($id), 'el');
            if($id_exists){
                $error_type = 1;
                $errors[] = l('Заказ с данным айди уже существует в системе');
            }else{
                $date_add = $this->format_date($this->provider->get_date_add($row));
                $date_end = $this->format_date($this->provider->get_date_end($row));
                $accepter = $this->remove_whitespace($this->provider->get_accepter($row));
                if($accepter){
                    $accepter_id = $this->accepters[$accepter];
                }else{
                    $accepter_id = 0;
    //                $order->set_error(l('Не указан приемщик'));
                    $errors[] = l('Не указан приемщик');
                }
                $engineer = $this->remove_whitespace($this->provider->get_engineer($row));
                if($engineer){
                    $engineer_id = $this->engineers[$engineer];
                }else{
    //                $order->set_error(l('Не указан инженер'));
//                    $errors[] = l('Не указан инженер');
                    $engineer_id = 0;
                }
                $manager = $this->remove_whitespace($this->provider->get_manager($row));
                $manager_id = 0;
                if(!$manager && !empty($this->import_settings['accepter_as_manager'])){
                    $manager_id = $accepter_id;
                }
                $status_id = $this->provider->get_status_id($row);
                $client_fio = $this->remove_whitespace($this->provider->get_client_fio($row));
                $client_phone = $this->clear_phone($this->provider->get_client_phone($row));
                $address = $this->remove_whitespace($this->provider->get_address($row));
                $category = $this->remove_whitespace($this->provider->get_category($row));
                if(!$category){
                    $errors[] = l('Не указана категория');
                }
                $device = $this->remove_whitespace($this->provider->get_device($row));
                if(!$device){
                    $errors[] = l('Не указано устройство');
                }
                $serial = $this->remove_whitespace($this->provider->get_serial($row));
                $equipment = $this->remove_whitespace($this->provider->get_equipment($row));
                $appearance = $this->remove_whitespace($this->provider->get_appearance($row));
                $defect = $this->remove_whitespace($this->provider->get_defect($row));
                $summ = $this->provider->get_summ($row);
                $summ_prepaid = $this->provider->get_summ_prepaid($row);
                $comments = $this->provider->get_comments($row);
                
                if(!$errors){
                    // добавляем клиента
                    $create_client = $this->get_client_id($client_fio, $client_phone);
                    if(!$create_client['state']){
                        $errors[] = $create_client['message'];
                    }else{
                        $client_id = $create_client['id'];
                    }
                    // добавляем категорию
                    $create_category = $this->get_category_id($category);
                    if(!$create_category['state']){
                        $category_id = 0;
    //                    $errors[] = $create_category['message'];
                    }else{
                        $category_id = $create_category['id'];
                    }
                    // добавляем устройство
                    $create_device = $this->get_device_id($device, $category_id);
                    if(!$create_device['state']){
                        $errors[] = $create_device['message'];
                    }else{
                        $device_id = $create_device['id'];
                    }
                    // создаем заказа
                    if(!$errors){
                        try{
                            db()->query("INSERT INTO {orders} "
                                       ."(id,date_add,accepter,status,user_id,fio,"
                                       ." phone,courier,category_id,serial,equipment,"
                                       ." comment, date_readiness, approximate_cost, "
                                       ." prepay, defect, engineer, manager, title, wh_id, location_id) "
                                       ." VALUES "
                                       ." (?i, ?, ?i, ?i, ?i, ?,"
                                       ."  ?, ?, ?i, ?, ?, "
                                       ."  ?, ?, ?, ?, ?, ?i, ?i, ?, ?i, ?i)", array(
                                           $id, $date_add, $accepter_id, $status_id, $client_id, $client_fio,
                                           $client_phone, $address, $device_id, $serial, $equipment, 
                                           $appearance, $date_end, $summ*100, $summ_prepaid*100, $defect, 
                                           $engineer_id, $manager_id, $device, 
                                           $this->accepters_wh[$accepter]['wh_id'], $this->accepters_wh[$accepter]['location_id']
                                       ));
                            if($comments){
                                $comments_query = array();
                                foreach($comments as $who => $comments){
                                    switch($who){
                                        case 'acceptor':
                                            $comment_user_id = $accepter_id;
                                        break;
                                        case 'engineer':
                                            $comment_user_id = $engineer_id;
                                        break;
                                        default:
                                            $comment_user_id = null;
                                        break;
                                    }
                                    if($comment_user_id){
                                        foreach($comments as $comment){
                                            $comments_query[] = 
                                                db()->makeQuery("(?,?,?i,?i,1)", array(
                                                    $date_add, $comment, $comment_user_id, $id
                                                ));
                                        }
                                    }
                                }
                                if($comments_query){
                                    db()->query("INSERT INTO {orders_comments} "
                                               ."(date_add,text,user_id,order_id,private) "
                                               ."VALUES ?q", array(implode(',',$comments_query)));
                                }
                            }
                        }catch(Exception $e){
                            $errors[] = l('Ошибка создания заказа');//.$e->getMessage();
                        }
                    }
                }
            }
            $results[] = array(
                'id' => $id,
                'state_type' => $error_type,
                'state' => !$errors,
                'message' => !$errors ? l('Добавлен') : implode('<br>', $errors)
            );
        }
        return array(
            'state' => true,
            'message' => $this->gen_result_table($results)
        );
    }
    
    private function clear_and_add_categories(){
        db()->query("SET FOREIGN_KEY_CHECKS=0;");
        db()->query("TRUNCATE TABLE {goods}");
        db()->query("TRUNCATE TABLE {category_goods}");
        db()->query("TRUNCATE TABLE {categories}");
        db()->query("SET FOREIGN_KEY_CHECKS=1;");
        foreach($this->rows as $row){
            $category = $this->remove_whitespace($this->provider->get_category($row));
            $device = $this->remove_whitespace($this->provider->get_device($row));
            $create_category = $this->get_category_id($category);
            $category_id = 0;
            if($create_category['state']){
                $category_id = $create_category['id'];
            }
            $this->get_device_id($device, $category_id);
        }
    }
    
    private function gen_result_table($results){
        $rows = '';
        foreach($results as $row_result){
            $type = 'success';
            if(!$row_result['state']){
                $type = 'danger';
            }
            if($row_result['state_type'] === 1){
                $type = 'info';
            }
            $rows .= '
                <tr class="'.$type.'">
                    <td>id '.$row_result['id'].'</td>
                    <td>'.$row_result['message'].'</td>
                </tr>
            ';
        }
        return '
            <h3>'.l('Результат импорта:').'</h3>
            <table class="table table-stripped table-hover">'.$rows.'</table>';
    }
    
    private function get_device_id($device, $category_id){
        if($device){
            if(!isset($this->devices[$device])){
                $url = transliturl($device);
                $find_category = db()->query("SELECT id FROM {categories} "
                                            ."WHERE title = ? OR url = ? LIMIT 1", array($device, $url), 'el');
                if(!$find_category){
                    $id = db()->query("INSERT INTO {categories} (title,parent_id,url,content,avail) "
                               ."VALUE (?,?i,?,'',1)", array($device,$category_id,$url), 'id');
                }else{
                    $id = $find_category;
                }
            }else{
                $id = $this->devices[$device];
            }
        }else{
            $id = false;
            $message = l('Не указано устройство');
        }
        return array(
            'state' => $id !== false,
            'id' => $id,
            'message' => $id === false ? $message : ''
        );
    }
    
    private function get_category_id($category){
        if($category){
            if(!isset($this->categories[$category])){
                $url = transliturl($category);
                $find_category = db()->query("SELECT id FROM {categories} "
                                            ."WHERE title = ? OR url = ? LIMIT 1", array($category, $url), 'el');
                if(!$find_category){
                    $id = db()->query("INSERT INTO {categories} (title,parent_id,url,content,avail) "
                               ."VALUE (?,0,?,'',1)", array($category,$url), 'id');
                }else{
                    $id = $find_category;
                }
            }else{
                $id = $this->categories[$category];
            }
        }else{
            $id = 0;
        }
        return array(
            'state' => $id !== false,
            'id' => $id,
            'message' => $id === false ? $message : ''
        );
    }
    
    private function get_client_id($client_fio, $client_phone){
        if(!isset($this->clients[$client_fio])){
            $phone_part = substr($client_phone, -7);
            $find_client = db()->query("SELECT client_id FROM {clients_phones} "
                                      ."WHERE phone LIKE '%?e'", array($phone_part), 'el');
            if($find_client){
                $client_id = $this->clients[$client_fio] = $find_client;
            }else{
                // создаем
                require_once $this->all_configs['sitepath'] . 'shop/access.class.php';
                $access = new access($this->all_configs, false);
                $data = $access->registration(array(
                    'email' => null,
                    'phone' => $client_phone,
                    'fio' => $client_fio
                ));
                if ($data['id'] > 0) {
                    $client_id = $data['id'];
                }else{
                    $client_id = false;
                    $message = l('Клиент не создан').': '.$data['msg'];
                }
            }
        }else{
            $client_id = $this->clients[$client_fio];
        }
        return array(
            'state' => $client_id !== false,
            'id' => $client_id,
            'message' => $client_id === false ? $message : ''
        );
    }
    
    private function scan_accepters_and_engineers(){
        $not_found_accepters = array();
        $not_found_engineers = array();
        foreach($this->rows as $row){
            $accepter = $this->remove_whitespace($this->provider->get_accepter($row));
            if($accepter && !array_key_exists($accepter, $this->accepters)){
                // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
                $a_id = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ?", array($accepter), 'el');
                if(!$a_id){
                    $not_found_accepters[] = htmlspecialchars($accepter);
                }else{
                    $a_whs = $this->all_configs['db']->query("SELECT wh_id,location_id FROM {warehouses_users} "
                                                            ."WHERE user_id = ?i AND main = 1", array($a_id), 'row');
                    $this->accepters_wh[$accepter] = $a_whs;
                }
                $this->accepters[$accepter] = $a_id;
            }
            $engineer = $this->remove_whitespace($this->provider->get_engineer($row));
            if($engineer && !array_key_exists($engineer, $this->engineers)){
                // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
                $e_id = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ?", array($engineer), 'el');
                if(!$e_id){
                    $not_found_engineers[] = htmlspecialchars($engineer);
                }
                $this->engineers[$engineer] = $e_id;
            }
        }
        if($not_found_accepters || $not_found_engineers){
            $message = '';
            if($not_found_accepters){
                $message .= '<label>'.l('Добавьте приемщиков').'</label>:'.
                            '<ol><li>'.implode('</li><li>', $not_found_accepters).'</li></ol>';
            }
            if($not_found_engineers){
                $message .= '<label>'.l('Добавьте инженеров').'</label>:'.
                            '<ol><li>'.implode('</li><li>', $not_found_engineers).'</li></ol>';
            }
            return array('state' => false, 'message' => $message);
        }else{
            return array('state' => true);
        }
    }
    
}