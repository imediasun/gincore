<?php
require_once __DIR__ . '/../Core/AModel.php';

class MContractors extends AModel
{
    public $table = 'contractors';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'code_1c',
            'title',
            'type',
            'comment',
            'date_add',
            'amount'
        );
    }
}
