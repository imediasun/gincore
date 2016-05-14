<?php

require_once __DIR__ . '/Object.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Session.php';

abstract class Helper extends Object
{
    /** @var View */
    protected $view;
    protected $all_configs;
    public $uses = array();

    public function __construct()
    {
        global $all_configs;
        $this->all_configs = $all_configs;
        $this->applyUses();
        $this->view = new View($all_configs);
    }
}
