<?php

require_once __DIR__ . '/../../Core/Controller.php';

$modulename[200] = 'partners';
$modulemenu[200] = l('Партнеры');
$moduleactive[200] = !$ifauth['is_2'];

class partners extends Controller
{
    /**
     * @return string
     */
    public function gencontent()
    {
        $user_id = $this->getUserId();
        $query = '';

        if (!$this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $query = $this->all_configs['db']->query('AND o.partner_user_id=?i', array());
        }

        $orders = $this->all_configs['db']->query(
            'SELECT * FROM {orders} WHERE partner_code IS NOT NULL ?query ORDER BY date_add DESC',
            array($query))->assoc();

        if ($orders) {
            foreach ($orders as $order) {

            }
        }


        $partners = $this->all_configs['db']->query('SELECT * FROM {partners} as p ORDER BY date_add',
            array())->assoc();

        return $this->view->renderFile('partners/gencontent', array(
            'partners' => $partners
        ));
    }

    /**
     * @return string
     */
    public function ajax()
    {
        return '';
    }

    /**
     * @param array $post
     * @return string
     */
    public function check_post(Array $post)
    {
        return '';
    }

    /**
     * @return bool
     */
    public function can_show_module()
    {
        return true;
    }
}