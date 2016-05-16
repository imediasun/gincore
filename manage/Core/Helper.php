<?php

require_once __DIR__ . '/Object.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Session.php';

abstract class Helper
{
    /** @var View */
    protected $view;
    protected $all_configs;
    public $uses = array();

    /**
     * Helper constructor.
     * @param $all_configs
     */
    public function __construct(&$all_configs = null)
    {
        if(empty($all_configs)) {
           global $all_configs;
        }
        $this->all_configs = $all_configs;
        $this->view = new View($all_configs);
    }
}
