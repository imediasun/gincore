<?php

require_once __DIR__.'/abstract_import_handler.php';

class import_posting_items extends abstract_import_handler
{
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
        if (!$this->all_configs['oRole']->hasPrivilege('logistics')) {
            return array(
                'state' => false,
                'message' => l('Не хватает прав')
            );
        }
        $results = array();
        if (!empty($rows)) {
            /** @todo remove */
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
            /** @todo remove */
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
}
