<?php

require_once __DIR__ . '/abstract_import_handler.php';
require_once $this->all_configs['sitepath'] . 'mail.php';

/**
 * Class import_categories
 *
 * @property gincore_categories $provider
 * @property  MCategories Categories
 */
class import_categories_with_subs extends abstract_import_handler
{
    protected $sendNotice = false;
    public $categories = array();
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
                if (!empty($title) && !isset($this->provider->categories[$title])) {
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
            $this->provider->categories[$title] = array(
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
                $value = $this->provider->get_parent_id($row);
            }
            if ($value !== false) {
                $data[$field] = $value;
            }
        }
        return $data;
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
        $data = array(
            array(
                l('Родитель') . ' 1',
                l('Подкатегория уровень') . ' 1',
                l('Подкатегория уровень') . ' 2',
                l('Подкатегория уровень') . ' 3',
                l('Добавляемая категория') . ' 1',
            ),
            array(
                l('Родитель') . ' 2',
                l('Подкатегория уровень') . ' 1',
                l('Добавляемая категория') . ' 2',
            ),
            array(
                l('Родитель') . ' 2',
                l('Добавляемая категория') . ' 3',
            ),
            array(
                l('Добавляемая категория') . ' 4',
            ),
        );
        return $this->provider->example($data);
    }
}
