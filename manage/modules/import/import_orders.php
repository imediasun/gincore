<?php

require_once __DIR__ . '/abstract_import_handler.php';

/**
 * Class import_orders
 *
 * @property MOrders Orders
 */
class import_orders extends abstract_import_handler
{
    public $acceptors;
    public $acceptors_wh;
    public $engineers;
    public $clients;
    public $categories;
    public $devices;
    public $managers;
    public $types;
    protected $not_found_acceptors;
    protected $not_found_engineers;
    protected $not_found_managers;
    public $uses = array(
        'Orders'
    );

    /**
     * @param $rows
     * @return array
     */
    function run($rows)
    {
        $this->acceptors = array();
        $this->managers = array();
        $this->acceptors_wh = array();
        $this->engineers = array();
        $this->clients = array();
        $this->categories = array();
        $this->devices = db()->query('SELECT title, id FROM {categories}')->vars();

        $this->types = array_flip($this->all_configs['configs']['order-types']);

        $scan = $this->scanAcceptorsAndEngineers($rows);
        if (!$scan['state']) {
            return $scan;
        }

        $results = array();
        $orders = array();
        foreach ($rows as $row) {
            $errors = array();
            $error_type = null;
            $id = $this->provider->get_id($row);
            try {
                if (empty($id)) {
                    throw new Exception(l('Остутствует ид заказа.'));
                }
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
                $summ_paid = $this->provider->get_summ_paid($row);
                $client_id = $this->getClientId($client_fio, $client_phone);
                $device = $this->getDevice($row);
                $device_id = $this->getDeviceId($device);
                $typeId = $this->getTypeOfRepairId($this->provider->get_type_of_repair($row));

                // создаем заказа
                $orders[] = array(
                    'id' => $id,
                    'date_add' => $date_add,
                    'accepter' => $acceptor_id,
                    'status' => $status_id,
                    'user_id' => $client_id,
                    'fio' => $client_fio,
                    'phone' => $client_phone,
                    'courier' => '',
                    'category_id' => $device_id,
                    'serial' => $serial,
                    'equipment' => $equipment,
                    'comment' => $appearance,
                    'date_readiness' => $date_end,
                    '`sum`' => $summ * 100,
                    'prepay' => 0,
                    'defect' => $defect,
                    'engineer' => $engineer_id,
                    'manager' => $manager_id,
                    'title' => $device,
                    'wh_id' => $this->acceptors_wh[$acceptor_id]['wh_id'],
                    'location_id' => $this->acceptors_wh[$acceptor_id]['location_id'],
                    'repair' => $typeId,
                    'sum_paid' => $summ_paid * 100,
                    'accept_wh_id' => $this->acceptors_wh[$acceptor_id]['wh_id'],
                );
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
        $this->Orders->insertAll($orders);
        return array(
            'state' => true,
            'message' => $this->gen_result_table($results)
        );
    }

    /**
     * @param $row
     * @return string
     */
    public function get_result_row($row)
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
            $this->devices[$device] = $this->addDevice($device);
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
     * @param $rows
     * @return array
     */
    private function scanAcceptorsAndEngineers($rows)
    {
        $this->not_found_acceptors = array();
        $this->not_found_engineers = array();
        $this->not_found_managers = array();
        foreach ($rows as $row) {
            $this->getAcceptorOrSetNotFound($row);
            $this->getEngineerOrSetNotFound($row);
            $this->getManagerOrSetNotFound($row);
        }
        if ($this->not_found_acceptors || $this->not_found_engineers || $this->not_found_managers) {
            return array(
                'state' => false,
                'message' => $this->view->renderFile('import/acceptors_engineers_error', array(
                    'acceptors' => $this->not_found_acceptors,
                    'engineers' => $this->not_found_engineers,
                    'managers' => $this->not_found_managers,
                ))
            );
        } else {
            return array('state' => true);
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
        if (!$manager && !empty($this->import_settings['accepter_as_manager'])) {
            return $acceptorId;
        }
        return !isset($this->managers[$manager]) ? 0 : $this->managers[$manager];
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

    /**
     * @param $row
     */
    private function getAcceptorOrSetNotFound($row)
    {
        $acceptor = import_helper::remove_whitespace($this->provider->get_acceptor($row));
        if ($acceptor && !array_key_exists($acceptor, $this->acceptors)) {
            // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
            $acceptorId = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ? OR login = ?",
                array($acceptor, $acceptor),
                'el');
            if (!$acceptorId) {
                $this->not_found_acceptors[] = htmlspecialchars($acceptor);
            } else {
                $a_whs = $this->all_configs['db']->query("SELECT wh_id,location_id FROM {warehouses_users} "
                    . "WHERE user_id = ?i AND main = 1", array($acceptorId), 'row');
                $this->acceptors_wh[$acceptorId] = $a_whs;
            }
            $this->acceptors[$acceptor] = $acceptorId;
        }
    }

    /**
     * @param $row
     */
    private function getEngineerOrSetNotFound($row)
    {
        $engineer = import_helper::remove_whitespace($this->provider->get_engineer($row));
        if ($engineer && !array_key_exists($engineer, $this->engineers)) {
            // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
            $engineerId = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ? OR login = ?",
                array($engineer, $engineer),
                'el');
            if (!$engineerId) {
                $this->not_found_engineers[] = htmlspecialchars($engineer);
            }
            $this->engineers[$engineer] = $engineerId;
        }
    }

    /**
     * @param $row
     */
    private function getManagerOrSetNotFound($row)
    {
        $manager = import_helper::remove_whitespace($this->provider->get_manager($row));
        if ($manager && !array_key_exists($manager, $this->managers)) {
            // проверить есть ли чувак в базе, если не то добавляем в сообщение юзеру шоб добавил
            $managerId = $this->all_configs['db']->query("SELECT id FROM {users} WHERE fio = ? OR login = ?",
                array($manager, $manager),
                'el');
            if (!$managerId) {
                $this->not_found_managers[] = htmlspecialchars($manager);
            }
            $this->managers[$manager] = $managerId;
        }
    }

    /**
     * @param $device
     * @return mixed
     */
    private function addDevice($device)
    {
        $url = transliturl($device);
        $id = db()->query("SELECT id FROM {categories} "
            . "WHERE title = ? OR url = ? LIMIT 1", array($device, $url), 'el');
        if (empty($id)) {
            $id = db()->query("INSERT INTO {categories} (title,parent_id,url,content,avail) "
                . "VALUE (?,0,?,'',1)", array($device, $url), 'id');
        }
        return $id;
    }

    /**
     * @param $typeOfRepair
     * @return int
     */
    private function getTypeOfRepairId($typeOfRepair)
    {
        return isset($this->types[$typeOfRepair]) ? $this->types[$typeOfRepair] : 0;
    }

    /**
     * @return string
     */
    public function getImportForm()
    {
        return $this->view->renderFile('import/forms/orders');
    }

    /**
     *
     */
    public function example()
    {
        $data = db()->query('
            SELECT 
            o.id, o.date_add as date_add, o.date_pay as date_pay, "" as f1, 
            c.title as device, "" as f2, o.serial as serial, "" as f3, o.defect, o.note, 
            o.sum/100, o.sum_paid/100, ua.fio as a_fio, 
            um.fio as m_fio, ue.fio as e_fio, o.fio, o.phone 
            FROM {orders} as o
            JOIN {users} as ua ON ua.id=o.accepter
            JOIN {users} as um ON um.id=o.manager
            JOIN {users} as ue ON ue.id=o.engineer
            JOIN {categories} as c ON c.id=o.category_id
            LIMIT 2;
        ')->assoc();
        return $this->provider->example($data);
    }
}