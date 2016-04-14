<?php

abstract class abstract_import_handler
{
    protected $all_configs;
    protected $import_settings;
    protected $rows = array();

    /** @var  gincore_orders */
    protected $provider;

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
    }

    /**
     * @param $results
     * @return string
     */
    protected function gen_result_table($results)
    {
        $rows = array();
        foreach ($results as $row_result) {
            $type = 'success';
            if (isset($row_result['state']) && !$row_result['state']) {
                $type = 'danger';
            }
            if (isset($row_result['state_type']) && $row_result['state_type'] === 1) {
                $type = 'info';
            }
            $rows[] = "<tr class='{$type}'>" . $this->get_result_row($row_result) . "</tr>";
        }
        return '<h3>' . l('Результат импорта:') . '</h3>
            <table class="table table-stripped table-hover">' . implode('', $rows) . '</table>';
    }

    /**
     * @param $row
     * @return bool
     */
    public function check_format($row)
    {
        if (!empty($this->provider) && is_a($this->provider, 'abstract_import_provider')) {
            return $this->provider->check_format($row);
        }
        return false;
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
    abstract protected function get_result_row($row);
}