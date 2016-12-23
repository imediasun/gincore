<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MBrands
 *
 */
class MBrands extends AModel
{
    public $table = 'brands';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'title',
        );
    }
}
