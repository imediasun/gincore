<?php namespace services\logistic;

class logistic extends \service{
    
    private static $instance = null;
    
    // создаем запись в таблице chains_moves если не в цепочке при попадании товара на скад "откуда"
    // или привязываем к цепочке со статусов -1, но только если в ней 
    // совпал склад откуда и других складов нету
    private function create_chain_move($item_id, $item_type, $move_id, $wh_id, $wh_location_id){
        $chain = $this->get_chain_by_wh($wh_id, $wh_location_id);
        if($chain){
            $in_decayed_chain = $this->all_configs['db']->query(
                                    "SELECT * FROM {chains_moves} "
                                   ."WHERE item_id = ?i AND item_type = ?i AND state = -1 AND chain_id = ? "
                                    . "AND from_move_id IS NOT NULL "
                                    . "AND logistics_move_id IS NULL "
                                    . "AND to_move_id IS NULL", array($item_id, $item_type, $chain['id']), 'row');
            if($in_decayed_chain){
                $this->all_configs['db']->query("UPDATE {chains_moves} SET state = 1, from_move_id = ?i "
                                               ."WHERE id = ?i", array($move_id, $in_decayed_chain['id']));
            }else{
                // создаем привязку к цепочке
                $this->all_configs['db']->query("INSERT INTO {chains_moves}"
                                                . "(item_id,item_type,chain_id,from_move_id) VALUES"
                                                . "(?i, ?i, ?i, ?i)", array($item_id, $item_type, $chain['id'], $move_id));
                /** @todo send mail to couriers */
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
    public function item_move($item_id, $item_type, $move_id, $wh_id, $wh_location_id){
        // проверим привязан ли айтем к какому-то маршруту
        $in_chain = $this->all_configs['db']->query(
                        "SELECT * FROM {chains_moves} "
                       ."WHERE item_id = ?i AND item_type = ?i AND state = 1", array($item_id, $item_type), 'row');
        if($in_chain){
            $chain = $this->get_chain($in_chain['chain_id']);
            $current_move_position = $this->current_move_position($in_chain);
            $wh_in_chain = $this->wh_in_chain($chain, $wh_id, $wh_location_id);
            if($wh_in_chain !== false){
                // если попал со склада откуда на логистику
                if($current_move_position == 'from' && $wh_in_chain == 'logistic'){
                    $this->all_configs['db']->query("UPDATE {chains_moves} SET logistics_move_id = ?i "
                                                   ."WHERE id = ?i", array($move_id, $in_chain['id']));
                }
                // если попал со склада откуда на куда
                // или с логистики на куда
                // в таком случае закрываем цепочку
                if(in_array($current_move_position, array('from','logistic')) && $wh_in_chain == 'to'){
                    $this->all_configs['db']->query("UPDATE {chains_moves} SET to_move_id = ?i, state = 0 "
                                                   ."WHERE id = ?i", array($move_id, $in_chain['id']));
                }
            }else{
                // помечаем что товар выпал с цепочки
                $this->all_configs['db']->query("UPDATE {chains_moves} SET state = -1 "
                                                   ."WHERE id = ?i", array($in_chain['id']));
                // и смотрим попал ли в другую
                $this->create_chain_move($item_id, $item_type, $move_id, $wh_id, $wh_location_id);
            }
        }else{
            // если не в цепочке то создаем
            $this->create_chain_move($item_id, $item_type, $move_id, $wh_id, $wh_location_id);
        }
    }

    // текущее место в перемещении по цепочке
    private function current_move_position($chain_move){
        if($chain_move['to_move_id']){
            return 'to';
        }
        if($chain_move['logistics_move_id']){
            return 'logistic';
        }
        if($chain_move['from_move_id']){
            return 'from';
        }
    }
    
    // есть ли склад в цепочке и какой у него тип
    private function wh_in_chain($chain, $wh, $wh_location){
        switch($wh){
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
    
    private function get_chain($id){
        return $this->all_configs['db']->query("SELECT * FROM {chains} WHERE id = ?i AND avail = 1", array($id), 'row');
    }
    
    private function get_chain_by_wh($from_wh_id, $from_wh_location_id){
        return $this->all_configs['db']->query(
                "SELECT * FROM {chains} "
               ."WHERE from_wh_id = ?i AND from_wh_location_id = ?i AND avail = 1", array($from_wh_id, $from_wh_location_id), 'row');
    }
    
    public static function getInstanse(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct(){}
    
}