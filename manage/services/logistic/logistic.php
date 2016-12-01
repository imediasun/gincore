<?php namespace services\logistic;

class logistic extends \service
{

    private static $instance = null;

    // создаем запись в таблице chains_moves если не в цепочке при попадании товара на скад "откуда"
    // или привязываем к цепочке со статусов -1, но только если в ней 
    // совпал склад откуда и других складов нету
    /**
     * @param $item_id
     * @param $item_type
     * @param $move_id
     * @param $wh_id
     * @param $wh_location_id
     */
    private function create_chain_move($item_id, $item_type, $move_id, $wh_id, $wh_location_id)
    {
        $chain = $this->get_chain_by_wh($wh_id, $wh_location_id);
        if ($chain) {
            $in_decayed_chain = $this->all_configs['db']->query(
                "SELECT * FROM {chains_moves} "
                . "WHERE item_id = ?i AND item_type = ?i AND state = -1 AND chain_id = ? "
                . "AND from_move_id IS NOT NULL "
                . "AND logistics_move_id IS NULL "
                . "AND to_move_id IS NULL", array($item_id, $item_type, $chain['id']), 'row');
            if ($in_decayed_chain) {
                $this->all_configs['db']->query("UPDATE {chains_moves} SET state = 1, from_move_id = ?i "
                    . "WHERE id = ?i", array($move_id, $in_decayed_chain['id']));
            } else {
                // создаем привязку к цепочке
                $this->all_configs['db']->query("INSERT INTO {chains_moves}"
                    . "(item_id,item_type,chain_id,from_move_id) VALUES"
                    . "(?i, ?i, ?i, ?i)", array($item_id, $item_type, $chain['id'], $move_id));

                $this->sendNotification($item_id, $item_type, $chain);
            }
        }
    }

    // при перемещении чего-либо идет вызов этой функции
    // и здесь проверяем совпадение с шаблоном перемещений
    // или смотрим на счет того, находится ли это изделие уже в цепочке
    // и перемещается ли оно по ней
    // {chains_moves} state
    //      1 - активна
    //      0 - закрыта
    //     -1 - не закрыта (товар выпал с цепочки)
    /**
     * @param $item_id
     * @param $item_type
     * @param $move_id
     * @param $wh_id
     * @param $wh_location_id
     */
    public function item_move($item_id, $item_type, $move_id, $wh_id, $wh_location_id)
    {
        // проверим привязан ли айтем к какому-то маршруту
        $in_chain = $this->all_configs['db']->query(
            "SELECT * FROM {chains_moves} "
            . "WHERE item_id = ?i AND item_type = ?i AND state = 1", array($item_id, $item_type), 'row');
        if ($in_chain) {
            $chain = $this->get_chain($in_chain['chain_id']);
            $current_move_position = $this->current_move_position($in_chain);
            $wh_in_chain = $this->wh_in_chain($chain, $wh_id, $wh_location_id);
            if ($wh_in_chain !== false) {
                // если попал со склада откуда на логистику
                if ($current_move_position == 'from' && $wh_in_chain == 'logistic') {
                    $this->all_configs['db']->query("UPDATE {chains_moves} SET logistics_move_id = ?i "
                        . "WHERE id = ?i", array($move_id, $in_chain['id']));
                }
                // если попал со склада откуда на куда
                // или с логистики на куда
                // в таком случае закрываем цепочку
                if (in_array($current_move_position, array('from', 'logistic')) && $wh_in_chain == 'to') {
                    $this->all_configs['db']->query("UPDATE {chains_moves} SET to_move_id = ?i, state = 0 "
                        . "WHERE id = ?i", array($move_id, $in_chain['id']));
                }
            } else {
                // помечаем что товар выпал с цепочки
                $this->all_configs['db']->query("UPDATE {chains_moves} SET state = -1 "
                    . "WHERE id = ?i", array($in_chain['id']));
                // и смотрим попал ли в другую
                $this->create_chain_move($item_id, $item_type, $move_id, $wh_id, $wh_location_id);
            }
        } else {
            // если не в цепочке то создаем
            $this->create_chain_move($item_id, $item_type, $move_id, $wh_id, $wh_location_id);
        }
    }

    // текущее место в перемещении по цепочке
    /**
     * @param $chain_move
     * @return string
     */
    private function current_move_position($chain_move)
    {
        if ($chain_move['to_move_id']) {
            return 'to';
        }
        if ($chain_move['logistics_move_id']) {
            return 'logistic';
        }
        if ($chain_move['from_move_id']) {
            return 'from';
        }
    }

    // есть ли склад в цепочке и какой у него тип
    /**
     * @param $chain
     * @param $wh
     * @param $wh_location
     * @return bool|string
     */
    private function wh_in_chain($chain, $wh, $wh_location)
    {
        switch ($wh) {
            case $chain['from_wh_id']:
                return 'from';
            case $chain['logistic_wh_id']:
                return 'logistic';
            case $chain['to_wh_id']:
                return 'to';
            default:
                return false;
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    private function get_chain($id)
    {
        return $this->all_configs['db']->query("SELECT * FROM {chains} WHERE id = ?i AND avail = 1", array($id), 'row');
    }

    /**
     * @param $from_wh_id
     * @param $from_wh_location_id
     * @return mixed
     */
    private function get_chain_by_wh($from_wh_id, $from_wh_location_id)
    {
        return $this->all_configs['db']->query(
            "SELECT * FROM {chains} "
            . "WHERE from_wh_id = ?i AND from_wh_location_id = ?i AND avail = 1", array($from_wh_id, $from_wh_location_id), 'row');
    }

    /**
     * @return null|logistic
     */
    public static function getInstanse()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * logistic constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param $item_id
     * @param $item_type
     * @param $chain
     */
    private function sendNotification($item_id, $item_type, $chain)
    {
        require_once $this->all_configs['sitepath'] . 'mail.php';
        $mailer = new \Mailer($this->all_configs);

        $users = $this->all_configs['db']->query('SELECT u.id, u.email FROM {users_permissions} as p
                        LEFT JOIN {users_role_permission}as rp ON rp.permission_id=p.id
                        LEFT JOIN {users} as u ON u.role=rp.role_id 
                        WHERE p.link=? AND u.id>0 AND u.avail=1 GROUP BY u.id',
            array('logistics-mess'))->assoc();

        if ($users) {
            if ($item_type == LOGISTIC_TYPE_IS_ITEM && $item_id) {
                $item = $this->all_configs['db']->query('SELECT g.title FROM {warehouses_goods_items} as i
                    LEFT JOIN {goods} as g ON g.id=i.goods_id WHERE i.id=?i',
                    array($item_id))->el();
            }
            $chainInfo = $this->all_configs['db']->query('
            SELECT 
              wf.title as from_wh, 
              wlf.location as from_location, 
              wt.title as to_wh, 
              wlt.location as to_location 
            FROM {chains} as c
            LEFT JOIN {warehouses} as wf ON wf.id=c.from_wh_id
            LEFT JOIN {warehouses_locations} as wlf ON wlf.id=c.from_wh_location_id
            LEFT JOIN {warehouses} as wt ON wt.id=c.to_wh_id
            LEFT JOIN {warehouses_locations} as wlt ON wlt.id=c.to_wh_location_id
            WHERE c.id=?i
            ', array($chain['id']))->row();
            foreach ($users as $user) {
                if (!empty($user['email'])) {
                    $mailer->group('logistic-notification', $user['email'],
                        array(
                            'type' => $item_type == LOGISTIC_TYPE_IS_ORDER ? l('заказ') : l('товар'),
                            'cargo' => $item_type == LOGISTIC_TYPE_IS_ORDER ? '№ ' . $item_id : $item,
                            'from' => $chainInfo['from_wh'] . '(' . $chainInfo['from_location'] . ')',
                            'to' => $chainInfo['to_wh'] . '(' . $chainInfo['to_location'] . ')',
                        ));
                    $mailer->go();
                }
            }
        }
    }

}