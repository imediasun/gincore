<?php

require_once __DIR__ . '/abstract_import_handler.php';

class import_orders extends abstract_import_handler
{
    public $acceptors;
    public $acceptors_wh;
    public $engineers;
    public $clients;
    public $categories;
    public $devices;

    /**
     * @param $rows
     * @return array
     */
    function run($rows)
    {
        $this->rows = $rows;
        $this->acceptors = array();
        $this->acceptors_wh = array();
        $this->engineers = array();
        $this->clients = array();
        $this->categories = array();
        $this->devices = array();

        $scan = $this->scan_accepters_and_engineers();
        if (!$scan['state']) {
            return $scan;
        }

        $results = array();
        foreach ($this->rows as $row) {
            $errors = array();
            $error_type = null;
            $id = $this->provider->get_id($row);
            try {
                $id_exists = db()->query("SELECT 1 FROM {orders} WHERE id = ?i", array($id), 'el');
                if ($id_exists) {
                    $error_type = 1;
                    throw new Exception(l('Заказ с данным айди уже существует в системе'));
                }
                $date_add = import_helper::format_date($this->provider->get_date_add($row));
                $date_end = import_helper::format_date($this->provider->get_date_end($row));
                $acceptor_id = $this->getAcceptorId($row);
                $engineer_id = $this->getEngineerId($row);
                $manager_id = $this->getManagerId($row, $acceptor_id);
                $status_id = $this->provider->get_status_id($row);
                $client_fio = import_helper::remove_whitespace($this->provider->get_client_fio($row));
                $client_phone = import_helper::clear_phone($this->provider->get_client_phone($row));
                $serial = import_helper::remove_whitespace($this->provider->get_serial($row));
                $equipment = import_helper::remove_whitespace($this->provider->get_equipment($row));
                $appearance = import_helper::remove_whitespace($this->provider->get_appearance($row));
                $defect = import_helper::remove_whitespace($this->provider->get_defect($row));
                $summ = $this->provider->get_summ($row);
                $client_id = $this->getClientId($client_fio, $client_phone);
                $device = $this->getDevice($row);
                $device_id = $this->getDeviceId($device);


                // создаем заказа
                $order = array(
                    $id,
                    $date_add,
                    $acceptor_id,
                    $status_id,
                    $client_id,
                    $client_fio,
                    $client_phone,
                    '',
                    $device_id['id'],
                    $serial,
                    $equipment,
                    $appearance,
                    $date_end,
                    $summ * 100,
                    0,
                    $defect,
                    $engineer_id,
                    $manager_id,
                    $device,
                    $this->acceptors_wh[$acceptor_id]['wh_id'],
                    $this->acceptors_wh[$acceptor_id]['location_id']
                );
                $this->createNewOrder($order);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
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

    /**
     * @param $row
     * @return string
     */
    protected function get_result_row($row)
    {
        return '<td>id ' . $row['id'] . '</td> <td>' . $row['message'] . '</td>';
    }

    /**
     * @param $device
     * @return array
     * @throws Exception
     */
    private function getDeviceId($device)
    {
        if (empty($device)) {
            throw new Exception(l('Не указано устройство'));
        }

        if (!isset($this->devices[$device])) {
            throw new Exception(l('Устройство отсутствует в базе'));
        }
        return $this->devices[$device];
    }

    /**
     * @param $fio
     * @param $phone
     * @return array
     * @throws Exception
     */
    private function getClientId($fio, $phone)
    {
        if (!isset($this->clients[$fio])) {
            $client = db()->query("SELECT client_id FROM {clients_phones} "
                . "WHERE phone LIKE '%?e'", array(substr($phone, -7)), 'el');
            if (!$client) {
                $client = $this->createNewClient($fio, $phone);
            }
            $this->clients[$fio] = $client;
        }
        return $this->clients[$fio];
    }

    /**
     * @return array
     */
    private function scan_accepters_and_engineers()
    {
        $not_found_acceptors = array();
        $not_found_engineers = array();
        foreach ($this->rows as $row) {
            $acceptor = import_helper::remove_whitespace($this->provider->get_acceptor($row));
            if ($acceptor && !array_key_exists($acceptor, $this->acceptors)) {
                // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
                $acceptor_id = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ?", array($acceptor),
                    'el');
                if (!$acceptor_id) {
                    $not_found_acceptors[] = htmlspecialchars($acceptor);
                } else {
                    $a_whs = $this->all_configs['db']->query("SELECT wh_id,location_id FROM {warehouses_users} "
                        . "WHERE user_id = ?i AND main = 1", array($acceptor_id), 'row');
                    $this->acceptors_wh[$acceptor] = $a_whs;
                }
                $this->acceptors[$acceptor] = $acceptor_id;
            }
            $engineer = import_helper::remove_whitespace($this->provider->get_engineer($row));
            if ($engineer && !array_key_exists($engineer, $this->engineers)) {
                // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
                $engineer_id = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ?", array($engineer),
                    'el');
                if (!$engineer_id) {
                    $not_found_engineers[] = htmlspecialchars($engineer);
                }
                $this->engineers[$engineer] = $engineer_id;
            }
        }
        if ($not_found_acceptors || $not_found_engineers) {
            $message = '';
            if ($not_found_acceptors) {
                $message .= '<label>' . l('Добавьте приемщиков') . '</label>:' .
                    '<ol><li>' . implode('</li><li>', $not_found_acceptors) . '</li></ol>';
            }
            if ($not_found_engineers) {
                $message .= '<label>' . l('Добавьте инженеров') . '</label>:' .
                    '<ol><li>' . implode('</li><li>', $not_found_engineers) . '</li></ol>';
            }
            return array('state' => false, 'message' => $message);
        } else {
            return array('state' => true);
        }
    }

    /**
     * @param $order
     * @return Exception
     * @throws Exception
     */
    private function createNewOrder($order)
    {
        try {
            db()->query("INSERT INTO {orders} "
                . "(id,date_add,accepter,status,user_id,fio,"
                . " phone,courier,category_id,serial,equipment,"
                . " comment, date_readiness, approximate_cost, "
                . " prepay, defect, engineer, manager, title, wh_id, location_id) "
                . " VALUES "
                . " (?i, ?, ?i, ?i, ?i, ?,"
                . "  ?, ?, ?i, ?, ?, "
                . "  ?, ?, ?, ?, ?, ?i, ?i, ?, ?i, ?i)",
                $order
            );
        } catch (Exception $e) {
            throw new Exception(l('Ошибка создания заказа'));
        }
    }

    /**
     * @param $row
     * @return string
     * @throws Exception
     */
    private function getAcceptorId($row)
    {
        $acceptor = import_helper::remove_whitespace($this->provider->get_acceptor($row));
        if (empty($acceptor)) {
            throw new Exception(l('Не указан приемщик'));
        }
        return $this->acceptors[$acceptor];
    }

    /**
     * @param $row
     * @return int
     */
    private function getEngineerId($row)
    {
        $engineer = import_helper::remove_whitespace($this->provider->get_engineer($row));
        return empty($engineer) || !isset($this->engineers[$engineer]) ? 0 : $this->engineers[$engineer];
    }

    /**
     * @param $row
     * @param $acceptorId
     * @return int
     */
    private function getManagerId($row, $acceptorId)
    {
        $manager = import_helper::remove_whitespace($this->provider->get_manager($row));
        return (!$manager && !empty($this->import_settings['accepter_as_manager'])) ? $acceptorId : 0;
    }

    /**
     * @param $row
     * @return string
     * @throws Exception
     */
    private function getDevice($row)
    {
        $device = import_helper::remove_whitespace($this->provider->get_device($row));
        if (!$device) {
            throw new Exception(l('Не указано устройство'));
        }
        return $device;
    }

    /**
     * @param $client_fio
     * @param $client_phone
     * @return mixed
     * @throws Exception
     */
    private function createNewClient($client_fio, $client_phone)
    {
        require_once $this->all_configs['sitepath'] . 'shop/access.class.php';
        $access = new access($this->all_configs, false);
        $user = $access->registration(array(
            'email' => null,
            'phone' => $client_phone,
            'fio' => $client_fio
        ));
        if ($user['id'] <= 0) {
            throw new Exception(l('Клиент не создан') . ': ' . $user['msg']);
        }
        return $user['id'];
    }
}