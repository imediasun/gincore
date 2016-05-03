<?php

require_once __DIR__ . '/abstract_import_handler.php';
require_once $this->all_configs['sitepath'] . 'mail.php';

/**
 * Class import_gincore_items
 *
 * @property ItemsInterface $provider
 */
class import_gincore_items extends abstract_import_handler
{
    protected $sendNotice = false;
    protected $items = array();
    public $userAsManager = true;
    protected $userId;

    /**
     * @inheritdoc
     */
    public function __construct($all_configs, $provider, $import_settings)
    {
        parent::__construct($all_configs, $provider, $import_settings);
        $this->userId = isset($_SESSION['id']) ? $_SESSION['id'] : '';
    }

    /**
     * @param $rows
     * @return array
     */
    public function run($rows)
    {

        if (!$this->all_configs['oRole']->hasPrivilege('export-goods')) {
            return array(
                'state' => false,
                'message' => l('Не хватает прав')
            );
        }
        $results = array();
        if (!empty($rows)) {
            $goods = db()->query('SELECT * FROM {goods}')->assoc('id');
            foreach ($rows as $row) {
                $id = $this->provider->get_id($row);
                if (!empty($id)) {
                    $data = $this->getItemData($row);
                    if(isset($goods[$id])) {
                        $good = $goods[$id];
                        if($this->isGoodChanged($good, $data)) {
                            $results[] = $this->updateItem($id, $data);
                        } else {
                            $results[] = array(
                                'state' => true,
                                'id' => $id,
                                'message' => l('Данные товара не изменились')
                            );
                            $this->updateItem($id, $data);
                        }
                    }
                }
            }
        }
        return array(
            'state' => true,
            'message' => $this->gen_result_table($results)
        );
    }

    /**
     * @param $row
     * @return string
     */
    public function get_result_row($row)
    {
        return "<td>{$row['id']}</td><td>{$row['message']}</td>";
    }

    /**
     * @param $id
     * @param $data
     * @return array
     */
    public function updateItem($id, $data)
    {
        $result = array(
            'state' => true,
            'id' => $id,
            'message' => ''
        );
        $modId = $this->all_configs['configs']['products-manage-page'];
        try {
            $query = '';
            foreach ($data as $field => $value) {
                if (empty($query)) {
                    $query = db()->makeQuery('?q=?', array($field, $value));
                } else {
                    $query = db()->makeQuery('?q, ?q=?', array($query, $field, $value));
                }
            }
            if (!empty($query)) {
                db()->query('UPDATE {goods} SET ?q WHERE id=?i', array(
                    $query,
                    $id
                ));
                $this->addToLog($this->userId, 'update-goods', $modId, $id);
            }
            $result['message'] = l('Изменен успешно');
        } catch (Exception $e) {
            $result['state'] = false;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param $userId
     * @param $work
     * @param $modId
     * @param $itemId
     */
    private function addToLog($userId, $work, $modId, $itemId)
    {
        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
            array($userId, $work, $modId, $itemId));
    }

    /**
     * @param $row
     * @return array
     */
    private function getItemData($row)
    {
        $data = array();
        $cols = $this->provider->get_cols();
        foreach ($cols as $field => $title) {
            $value = $this->provider->$field($row);
            if (strpos($field, 'price') !== false) {
                $value *= 100;
            }
            if ($value !== false) {
                $data[$field] = $value;
            }
        }
        return $data;
    }


    /**
     * @param $good
     * @param $data
     * @return bool
     */
    private function isGoodChanged($good, $data)
    {
        $cols = $this->provider->get_cols();
        foreach ($cols as $field => $title) {
            if(isset($data[$field]) && $good[$field] != $data[$field]) {
                return true;
            }
        }
        return false;
    }
}
