<?php

require_once __DIR__ . '/abstract_import_handler.php';
require_once $this->all_configs['sitepath'] . 'mail.php';

/**
 * Class import_gincore_items
 *
 * @property exported_gincore_items $provider
 * @property  MCategories Categories
 */
class import_gincore_items extends abstract_import_handler
{
    protected $sendNotice = false;
    protected $items = array();
    public $userAsManager = true;
    protected $userId;
    protected $logQuery = array();
    public $uses = array(
        'Categories'
    );

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
                if (!empty($id) && isset($goods[$id])) {
                    $data = $this->getItemData($goods[$id], $row);
                    if (!empty($data)) {
                        $results[] = $this->updateItem($id, $data);
                    } else {
                        $results[] = array(
                            'state' => true,
                            'id' => $id,
                            'message' => l('Данные товара не изменились')
                        );
                    }
                }
            }
        }
        $this->flushLog();
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
                if ($field != 'category') {
                    if (empty($query)) {
                        $query = db()->makeQuery('?q=?', array($field, $value));
                    } else {
                        $query = db()->makeQuery('?q, ?q=?', array($query, $field, $value));
                    }
                } else {
                    $this->setCategory($id, $value);
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
        $this->logQuery[] = $this->all_configs['db']->makeQuery('(?i, ?, ?i, ?i)',
            array($userId, $work, $modId, $itemId));
    }

    /**
     *
     */
    private function flushLog()
    {
        if (!empty($this->logQuery)) {
            $this->all_configs['db']->query('INSERT INTO {changes} (user_id, work, map_id, object_id) VALUES ?q',
                array(implode(',', $this->logQuery)));
        }
    }

    /**
     * @param $good
     * @param $row
     * @return array
     */
    private function getItemData($good, $row)
    {
        $data = array();
        $cols = $this->provider->get_cols();
        foreach ($cols as $field => $title) {
            $value = $this->provider->$field($row);
            if (strpos($field, 'price') !== false) {
                $value *= 100;
            }
            if (strpos($field, 'category') !== false && $value === false) {
                $value = $this->createCategory($this->provider->getColValue('category', $row));
            }
            if ($value !== false && $good[$field] != $value) {
                $data[$field] = $value;
            }
        }
        return $data;
    }

    /**
     * @param $id
     * @param $value
     */
    private function setCategory($id, $value)
    {
        if (((int)$value) > 0) {
            db()->query('DELETE FROM {category_goods} WHERE goods_id=?i', array($id));
            db()->query('INSERT INTO {category_goods} (goods_id, category_id) VALUES (?i, ?i)', array($id, $value));
        }
    }

    /**
     * @param $title
     * @return bool|int
     */
    private function createCategory($title)
    {
        return $this->Categories->insert(array(
            'title' => $title,
            'url' => transliturl($title),
            'content' => '',
            'parent_id' => 0,
            'avail' => 1

        ));
    }
}
