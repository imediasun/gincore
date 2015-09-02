<?php


$modulename[] = 'partners';
$modulemenu[] = 'Партнеры';
$moduleactive[] = !$ifauth['is_2'];

class partners
{
    protected $all_configs;

    function __construct(&$all_configs)
    {
        $this->all_configs = $all_configs;

        global $input_html;

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        $input_html['mcontent'] = $this->gencontent();
    }

    private function gencontent()
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $query = '';
        $html = '';

        if (!$this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $query = $this->all_configs['db']->query('AND o.partner_user_id=?i', array());
        }

        $orders = $this->all_configs['db']->query(
            'SELECT * FROM {orders} WHERE partner_code IS NOT NULL ?query ORDER BY date_add DESC', array($query))->assoc();

        if ($orders) {
            foreach ($orders as $order) {

            }
        }


        $partners = $this->all_configs['db']->query('SELECT * FROM {partners} as p ORDER BY date_add', array())->assoc();
        $html .= '<table class="table"><thead></thead><tbody>';
        if ($partners) {
            foreach ($partners as $partner) {
                $html .= '<tr><td></td>';
                $html .= '<td></td>';
                $html .= '<td></td></tr>';
            }
        }
        $html .= '</tbody></table>';

        return $html;
    }

    private function ajax()
    {

    }
}