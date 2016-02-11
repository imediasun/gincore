<?php

class import_clients extends import_helper{
    
    private $provider; // 
    
    function __construct($all_configs, $provider, $import_settings){
        $this->all_configs = $all_configs;
        $this->provider = $provider;
        $this->import_settings = $import_settings;
    }
    
    function run($rows){
        $this->rows = $rows;
        $results = array();
        $num = 2;
        
        require_once $this->all_configs['sitepath'] . 'shop/access.class.php';
        $this->access = new access($this->all_configs, false);
        
        if(!empty($this->provider->has_custom_data_handler)){
            $data = $this->provider->get_data($this->rows);
            foreach($data as $client_data){
                $phones = $client_data['phones'];
                $fio = $client_data['fio'];
                $email = $client_data['email'];
                $address = $client_data['address'];
                $results[] = $this->process_client_data($phones, $fio, $email, $address, $num);
                $num ++;
            }
        }else{
            foreach($this->rows as $row){
                $phones = $this->provider->get_phones($row);
                $fio = $this->remove_whitespace($this->provider->get_fio($row));
                $email = $this->remove_whitespace($this->provider->get_email($row));
                $address = $this->remove_whitespace($this->provider->get_address($row));
                $results[] = $this->process_client_data($phones, $fio, $email, $address, $num);
                $num ++;
            }
        }
        return array(
            'state' => true,
            'message' => $this->gen_result_table($results)
        );
    }
    
    private function process_client_data($phones, $fio, $email, $address, $num){
        $errors = array();
        $error_type = 0;
        if(!$phones){
            $errors[] = l('Не указан телефон');
        }
        if(!$email){
            $email = null;
        }
        if(!$errors){
            // берем первый телефон как основной
            $phone = $phones[0];
            $create = $this->create_client($phone, $fio, $email, $address);
            if(!$create['state']){
                $errors[] = $create['message'];
            }else{
                if(count($phones) > 1){
                    // добавляем доп телефоны
                    foreach($phones as $i => $tel){
                        if(!$i) continue;
                        $tel = $this->access->is_phone($this->clear_phone($tel));
                        if($tel !== false){
                            $this->all_configs['db']->query('INSERT IGNORE INTO {clients_phones} (phone, client_id) '
                                                           .'VALUES (?, ?i)',
                                    array($tel[0], $create['id']));
                        }
                    }
                }
            }
            $error_type = $create['state_type'];
        }
        return array(
            'num' => $num,
            'fio' => $fio,
            'state_type' => $error_type,
            'state' => !$errors,
            'message' => !$errors ? l('Добавлен') : implode('<br>', $errors)
        );
    }
    
    private function create_client($client_phone, $client_fio, $client_email, $client_address){
        $state_type = 0;
        $phone_part = substr($this->clear_phone($client_phone), -9);
        $find_client = db()->query("SELECT client_id FROM {clients_phones} "
                                  ."WHERE phone LIKE '%?e'", array($phone_part), 'el');
        if(!$find_client){
            // создаем
            $data = $this->access->registration(array(
                'email' => $client_email,
                'phone' => $client_phone,
                'fio' => $client_fio,
                'legal_address' => $client_address
            ));
            if ($data['id'] > 0) {
                $client_id = $data['id'];
            }else{
                $client_id = false;
                $message = l('Клиент не создан').': '.$data['msg'];
            }
        }else{
            $client_id = false;
            $state_type = 1;
            $message = l('Клиент уже зарегистрирован в системе');
        }
        return array(
            'state' => $client_id !== false,
            'id' => $client_id,
            'state_type' => $state_type,
            'message' => $client_id === false ? $message : ''
        );
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
                    <td># '.$row_result['num'].' '
                           .(!empty($row_result['fio']) ? '('.htmlspecialchars($row_result['fio']).')' : '').'</td>
                    <td>'.$row_result['message'].'</td>
                </tr>
            ';
        }
        return '
            <h3>'.l('Результат импорта:').'</h3>
            <table class="table table-stripped table-hover">'.$rows.'</table>';
    }
    
}