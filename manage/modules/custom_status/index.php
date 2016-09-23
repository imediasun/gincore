<?php

require_once __DIR__ . '/../../Core/Controller.php';
// настройки
$modulename[240] = 'custom_status';
$modulemenu[240] = l('Пользовательские статусы');  //карта сайта

global $all_configs;
$moduleactive[240] = $all_configs['oRole']->hasPrivilege('edit-users');

/**
 * @property  MStatus Status
 */
class custom_status extends Controller
{
    protected $current;
    private $lang;
    private $def_lang;
    private $langs;
    private $errors = array();

    public $uses = array(
        'Status'
    );

    /**
     * widgets constructor.
     * @param $all_configs
     * @param $lang
     * @param $def_lang
     * @param $langs
     */
    public function __construct(&$all_configs, $lang, $def_lang, $langs)
    {
        parent::__construct($all_configs);
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->current = isset($_GET['type']) ? $_GET['type'] : 'repair';
    }

    /**
     * @return string
     */
    public function render()
    {
        global $input_html;
        $result = parent::render();
        $input_html['mmenu'] = $this->genmenu();
        return $result;
    }

    /**
     * @return string
     */
    private function genmenu()
    {
        return $this->view->renderFile('custom_status/genmenu', array(
            'current' => $this->current
        ));
    }

    /**
     * @return mixed|string
     */
    public function gencontent()
    {
        return $this->view->renderFile('custom_status/gencontent', array(
            'title' => l('Пользовательские статусы'),
            'statuses' => $this->Status->getAll(strcmp($this->current, 'repair') === 0 ? ORDER_REPAIR : ORDER_SELL),
            'current' => $this->current
        ));
    }

    /**
     *
     */
    public function ajax()
    {
        $data = array(
            'state' => false
        );

        Response::json($data);
    }

    /**
     * @param $post
     * @return mixed|string
     */
    public function check_post(Array $post)
    {
        if (isset($post['update-status'])) {
            $this->changeStatus($_POST);
        }
        Response::redirect(Response::referrer());
    }

    /**
     * @return bool
     */
    public function can_show_module()
    {
        return ($this->all_configs['oRole']->hasPrivilege('edit-users'));
    }

    /**
     * @param $post
     */
    private function changeStatus($post)
    {
        if (!empty($post['ids'])) {
            $all = $this->Status->getAll(strcmp($post['order-type'], 'repair') === 0 ? ORDER_REPAIR : ORDER_SELL);
            foreach ($post['ids'] as $id) {
                if (strcmp($id, 'new') === 0) {
                    $this->appendStatus($id, $post);
                } elseif ($post['delete'][$id] === 'on') {
                    $this->deleteStatus($id);
                } else {
                    $this->updateStatus($id, $post, $all);
                }
            }
        }
    }

    /**
     * @param $id
     * @param $post
     */
    private function appendStatus($id, $post)
    {
        if (!empty($post['name'][$id])) {
            $type = strcmp($post['order-type'], 'repair') === 0 ? ORDER_REPAIR : ORDER_SELL;
            $data = array(
                'name' => $post['name'][$id],
                '`from`' => json_encode(array()),
                'color' => ltrim($post['color'][$id], '#'),
                'status_id' => $this->Status->getNextStatusId($type),
                'order_type' => $type,
                'system' => 0,
                'use_in_manager' => (int)(isset($post['use_in_manager'][$id]) && $post['use_in_manager'][$id] === 'on'),
                'active' => (int)(isset($post['active'][$id]) && $post['active'][$id] === 'on'),
            );
            $this->Status->insert($data);
        }
    }

    /**
     * @param $id
     */
    private function deleteStatus($id)
    {
        $status = $this->Status->getByPk($id);
        if (!empty($status)) {
            try {
                if ($status['system']) {
                    throw new ExceptionWithMsg(l('Нельзя удалить системный статус'));
                }
                $count = $this->all_configs['db']->query('SELECT count(*) FROM {orders} WHERE status=?i',
                    array($status['status_id']))->el();
                if ($count != 0) {
                    throw new ExceptionWithMsg(l('Статус используется в закзах'));
                }
                $this->Status->delete($id);
            } catch (ExceptionWithMsg $e) {
                $this->errors[] = $e->getMessage() . ': ' . h($status['name']);
            }
        }
    }

    /**
     * @param $id
     * @param $post
     * @param $all
     */
    private function updateStatus($id, $post, $all)
    {
        if (isset($all[$id])) {
            $status = $all[$id];
            $color = ltrim($post['color'][$id], '#');
            $data = $status['system'] ? array(
                'color' => $color
            ) : array(
                'name' => $post['name'][$id],
                '`from`' => json_encode(array()),
                'color' => $color,
                'use_in_manager' => (int)(isset($post['use_in_manager'][$id]) && $post['use_in_manager'][$id] === 'on'),
                'active' => (int)(isset($post['active'][$id]) && $post['active'][$id] === 'on'),
            );

            $this->Status->update($data, array(
                'id' => $id
            ));
        }
    }
}
