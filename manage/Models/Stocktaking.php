<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MStocktaking
 *
 */
class MStocktaking extends AModel
{
    const ACTIVE = 0;
    const BACKUP = 1;
    public $table = 'stocktaking';

    public function appendSerial($orderId, $commenterId, $field, $value)
    {
    }

    /**
     * @param $warehouseId
     * @param $locationId
     * @return bool|int
     */
    public function newStocktaking($warehouseId, $locationId)
    {
        return $this->insert(array(
            'created_at' => date('Y-d-m H:i'),
            'warehouse_id' => $warehouseId,
            'location_id' => $locationId,
            'checked_serials' => json_encode(array())
        ));
    }

    /**
     * @param $id
     */
    public function backup($id)
    {
        $stocktaking = $this->getByPk($id);
        if (!empty($stocktaking) && $stocktaking['history'] == self::ACTIVE) {
            unset($stocktaking['id']);
            $stocktaking['saved_at'] = date('Y-d-m H:i');
            $stocktaking['history'] = self::BACKUP;
            $this->insert($stocktaking);
        }
    }

    /**
     * @param $id
     */
    public function restore($id)
    {
        $stocktaking = $this->getByPk($id);
        if (!empty($stocktaking) && $stocktaking['history'] == self::BACKUP) {
            $current = $this->query('SELECT id FROM ?t WHERE history=? AND location_id=?i AND warehouse_id=?i ORDER BY id DESC LIMIT 1',
                array(
                    self::ACTIVE,
                    $stocktaking['location_id'],
                    $stocktaking['warehouse_id']
                ));
            if (!empty($current)) {
                $this->update(array(
                    'history' => self::BACKUP,
                    'saved_at' => date('Y-d-m H:i')
                ), array(
                    'id' => $current
                ));
            }
            $stocktaking['history'] = self::ACTIVE;
            unset($stocktaking['id']);
            unset($stocktaking['saved_at']);
            $this->insert($stocktaking);
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function load($id)
    {
        return $this->getByPk($id);
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'warehouse_id',
            'location_id',
            'checked_serials',
            'created_at',
            'saved_at',
            'history',
        );
    }
}
