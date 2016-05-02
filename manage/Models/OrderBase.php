<?php
require_once __DIR__ . '/../Core/AModel.php';

class MOrderBase extends AModel
{
    public $table = 'orders';

    /**
     * @param $options
     * @return bool|int
     */
    public function save($options)
    {
        return $this->insert($options);
    }
    
    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'status_id',
            'user_id',
            'fio',
            'email',
            'status',
            'type',
            'approximate_cost',
            'sum',
            'prepay',
            'prepay_comment',
            'discount',
            'manager',
            'accepter',
            'engineer',
            'comment',
            'phone',
            'date_add',
            'sum_paid',
            'title',
            'category_id',
            'serial',
            'note',
            'battery',
            'charger',
            'cover',
            'box',
            'repair',
            'urgent',
            'np_accept',
            'notify',
            'client_took',
            'partner',
            'date_readiness',
            'defect',
            'location_id',
            'wh_id',
            'send_sms',
            'course_key',
            'course_value',
            'date_pay',
            'replacement_fund',
            'is_replacement_fund',
            'return_id',
            'nonconsent',
            'is_waiting',
            'courier',
            'warranty',
            'accept_location_id',
            'accept_wh_id',
            'code',
            'referer_id',
            'color',
            'equipment',
            'total_as_sums',
            'total_as_sum',
            'cashless'

        );
    }
}
