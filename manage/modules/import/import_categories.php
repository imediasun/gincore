<?php

require_once __DIR__ . '/abstract_import_handler.php';
require_once $this->all_configs['sitepath'] . 'mail.php';

/**
 * Class import_categories
 *
 * @property gincore_categories $provider
 * @property  MCategories       Categories
 */
class import_categories extends abstract_import_handler
{
    protected $sendNotice = false;
    protected $categories = array();
    public $userAsManager = true;
    protected $userId;
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
        $this->categories = db()->query('select id, title from {categories}', array())->assoc('title');
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
            foreach ($rows as $row) {
                $title = $this->provider->get_title($row);
                if (!empty($title) && !isset($this->categories[$title])) {
                    $data = $this->getCategoryData($title, $row);
                    if (!empty($data)) {
                        $results[] = $this->createCategory($title, $data);
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
     * @param $title
     * @param $data
     * @return array
     */
    public function createCategory($title, $data)
    {
        $result = array(
            'state' => true,
            'message' => ''
        );
        $modId = $this->all_configs['configs']['products-manage-page'];
        try {
            $id = $this->Categories->insert($data);
            if (empty($id)) {
                throw new Exception(l('При создании категории возникли проблемы'));
            }
            $this->categories[$title] = array(
                'id' => $id,
                'title' => $title
            );
            $this->addToLog($this->userId, 'create-categories', $modId, $id);
            $result['id'] = $id;
            $result['message'] = l('Добавлена успешно');
        } catch (Exception $e) {
            $result['state'] = false;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * @param $title
     * @param $row
     * @return array
     */
    private function getCategoryData($title, $row)
    {
        $data = array(
            'url' => transliturl($title),
            'avail' => 1
        );
        $cols = $this->provider->get_cols();
        foreach ($cols as $field => $title) {
            $value = $this->provider->$field($row);
            if (strpos($field, 'fixed_payment') !== false) {
                $value *= 100;
            }
            if (strpos($field, 'parent_id') !== false) {
                $value = isset($this->categories[$value]) ? $this->categories[$value]['id'] : $this->createParentCategory($value);
            }
            if ($value !== false) {
                $data[$field] = $value;
            }
        }
        return $data;
    }

    /**
     * @param $title
     * @return bool|int
     */
    private function createParentCategory($title)
    {
        $this->categories[$title] = array(
            'id' => $this->Categories->insert(array(
                'title' => $title,
                'url' => transliturl($title),
                'content' => '',
                'parent_id' => 0,
                'avail' => 1

            )),
            'title' => $title
        );
        return $this->categories[$title]['id'];
    }

    /**
     * @return string
     */
    public function getImportForm()
    {
        return '';
    }

    /**
     *
     */
    public function example()
    {
        $data = db()->query('
            SELECT c.title, p.title as parent, c.content, c.information, c.percent_from_profit, c.fixed_payment/100 as fixed_payment
            FROM {categories} as c
            JOIN {categories} as p ON p.id=c.parent_id
            LIMIT 2;
        ')->assoc();
        return $this->provider->example($data);
    }
}
