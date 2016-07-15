<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MStocktakingLocations
 *
 */
class MStocktakingLocations extends AModel
{
    public $table = 'stocktaking_locations';

    /**
     * @param $id
     * @return array
     */
    public function getByStocktakingId($id)
    {
        return $this->query('
            SELECT t.location_id, l.location 
            FROM ?t as t
            JOIN {warehouses_locations} as l ON l.id = t.location_id
            WHERE t.stocktaking_id=?
        ', array($this->table, $id))->vars();
    }

    /**
     * @param $fromId
     * @param $toId
     */
    public function copyFromTo($fromId, $toId)
    {
        $locations = $this->getByStocktakingId($fromId);
        foreach ($locations as $id => $location) {
            $this->insert(array(
                'location_id' => $id,
                'stocktaking_id' => $toId
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
            'stocktaking_id',
            'location_id',
        );
    }
}
