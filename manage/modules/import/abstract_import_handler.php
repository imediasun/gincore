<?php
require_once __DIR__ . '/../../Core/View.php';
require_once __DIR__ . '/../../Core/Object.php';

abstract class abstract_import_handler extends Object
{
    protected $all_configs;
    protected $import_settings;
    protected $rows = array();
    protected $logQuery = array();

    /** @var  gincore_orders */
    protected $provider;
    protected $view;

    /**
     * import_clients constructor.
     * @param $all_configs
     * @param $provider
     * @param $import_settings
     */
    public function __construct($all_configs, $provider, $import_settings)
    {
        $this->all_configs = $all_configs;
        $this->provider = $provider;
        $this->import_settings = $import_settings;
        $this->view = new View($all_configs);
        $this->applyUses();
    }

    /**
     * @param     $results
     * @param int $onlyError
     * @return string
     */
    protected function gen_result_table($results, $onlyError = 0)
    {
        return $this->view->renderFile('import/gen_result_table', array(
            'results' => $results,
            'controller' => $this,
            'onlyError' => $onlyError
        ));
    }

    /**
     * @param $row
     * @return bool
     */
    public function check_format($row)
    {
        if (!empty($this->provider) && is_a($this->provider, 'abstract_import_provider')) {
            $this->provider->define_codepage($row);
            return $this->provider->check_format($row);
        }
        return false;
    }

    /**
     * @param $userId
     * @param $work
     * @param $modId
     * @param $itemId
     */
    protected function addToLog($userId, $work, $modId, $itemId)
    {
        $this->logQuery[] = $this->all_configs['db']->makeQuery('(?i, ?, ?i, ?i)',
            array($userId, $work, $modId, $itemId));
    }

    /**
     *
     */
    protected function flushLog()
    {
        if (!empty($this->logQuery)) {
            $this->all_configs['db']->query('INSERT INTO {changes} (user_id, work, map_id, object_id) VALUES ?q',
                array(implode(',', $this->logQuery)));
        }
    }

    /**
     * @param $rows
     * @return array
     */
    abstract public function run($rows);

    /**
     * @param $row
     * @return string
     */
    abstract public function get_result_row($row);

    /**
     * @return string
     */
    abstract public function getImportForm();

    /**
     * @return mixed
     */
    abstract public function example();
}