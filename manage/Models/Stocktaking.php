<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MStocktaking
 *
 * @property  MStocktakingLocations StocktakingLocations
 */
class MStocktaking extends AModel
{
    const ACTIVE = 0;
    const BACKUP = 1;
    public $table = 'stocktaking';
    public $uses = array(
        'StocktakingLocations'
    );

    /**
     * @param $serial
     * @param $stocktaking
     * @return bool|int
     */
    public function appendSerialToBoth($serial, $stocktaking)
    {
        return $this->appendSerialTo('both', $serial, $stocktaking);
    }

    /**
     * @param $serial
     * @param $stocktaking
     * @return bool|int
     */
    public function appendSerialToSurplus($serial, $stocktaking)
    {
        return $this->appendSerialTo('surplus', $serial, $stocktaking);
    }

    /**
     * @param $to
     * @param $serial
     * @param $stocktaking
     * @return bool|int
     */
    protected function appendSerialTo($to, $serial, $stocktaking)
    {
        $stocktaking['checked_serials'][$to][] = $serial;
        return $this->update(array(
            'checked_serials' => json_encode($stocktaking['checked_serials'])
        ), array(
            'id' => $stocktaking['id']
        ));
    }

    /**
     * @param $warehouseId
     * @param $locationIds
     * @return bool|int
     */
    public function newStocktaking($warehouseId, $locationIds)
    {
        $id = $this->insert(array(
            'created_at' => date('Y-d-m H:i'),
            'warehouse_id' => $warehouseId,
            'checked_serials' => json_encode(array())
        ));
        foreach ($locationIds as $locationId) {
            $this->StocktakingLocations->insert(array(
                'location_id' => $locationId,
                'stocktaking_id' => $id
            ));
        }
        return $id;
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
            $backupId = $this->insert($stocktaking);
            $this->StocktakingLocations->copyFromTo($id, $backupId);
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function restore($id)
    {
        $stocktaking = $this->load($id);
        if (!empty($stocktaking) && $stocktaking['history'] == self::BACKUP) {
            $this->setBackupCurrents($stocktaking);

            $stocktaking['history'] = self::ACTIVE;
            unset($stocktaking['id']);
            unset($stocktaking['saved_at']);
            $stocktaking['checked_serials'] = json_encode($stocktaking['checked_serials']);
            $backupId = $this->insert($stocktaking);
            $this->StocktakingLocations->copyFromTo($id, $backupId);
            $stocktaking = $this->load($backupId);
        }
        return $stocktaking;
    }

    /**
     * @param $id
     * @return array
     */
    public function load($id)
    {
        $stocktaking = $this->query('
            SELECT s.*, w.title as warehouse
            FROM ?t as s 
            JOIN {warehouses} as w ON w.id=s.warehouse_id
            WHERE s.id=?i', array($this->table, $id))->row();
        $stocktaking['locations'] = $this->StocktakingLocations->getByStocktakingId($id);
        $stocktaking['checked_serials'] = json_decode($stocktaking['checked_serials'], true);
        return $stocktaking;
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'warehouse_id',
            'checked_serials',
            'created_at',
            'saved_at',
            'history',
        );
    }

    /**
     * @param $warehouseId
     * @param $locationIds
     * @return array
     */
    protected function getByWarehouseAndLocations($warehouseId, $locationIds)
    {
        return $this->query('
            SELECT t.* 
            FROM ?t as t 
            JOIN {stocktaking_locations} as l ON t.id=l.stocktaking_id
            WHERE history=? AND l.location_id in (?li) AND warehouse_id=?i ORDER BY id DESC LIMIT 1',
            array(
                $this->table,
                self::ACTIVE,
                $locationIds,
                $warehouseId
            ))->row();

    }

    /**
     * @param $stocktaking
     */
    protected function setBackupCurrents($stocktaking)
    {
        $this->update(array(
            'history' => self::BACKUP,
            'saved_at' => date('Y-d-m H:i')
        ), array(
            'warehouse_id' => $stocktaking['warehouse_id'],
            'history' => self::ACTIVE
        ));
    }
}
