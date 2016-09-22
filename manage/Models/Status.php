<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MStatus
 *
 */
class MStatus extends AModel
{
    public $table = 'status';

    /**
     * @return array
     */
    public function getAll()
    {
        if ($this->isEmpty()) {
            $this->setDefault();
        }
        return $this->query('SELECT * FROM ?t', array($this->table))->assoc('id');
    }

    /**
     *
     */
    protected function setDefault()
    {
        global $all_configs;
        foreach ($all_configs['configs']['order-status'] as $id => $status) {
            $this->insert(array(
                'name' => $status['name'],
                'from' => json_encode($status['from']),
                'color' => $status['color'],
                'status_id' => $id,
                'order_type' => ORDER_REPAIR,
                'system' => 1,
                'use_in_manager' => 1,
                'active' => 1,
            ));
        }
        foreach ($all_configs['configs']['sale-order-status'] as $id => $status) {
            $this->insert(array(
                'name' => $status['name'],
                'from' => json_encode($status['from']),
                'color' => $status['color'],
                'status_id' => $id,
                'order_type' => ORDER_SELL,
                'system' => 1,
                'use_in_manager' => 1,
                'active' => 1,
            ));
        }
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'name',
            'from',
            'color',
            'status_id',
            'order_type',
            'system',
            'use_in_manager',
            'active',
        );
    }
}
