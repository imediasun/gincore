<?php

require_once __DIR__ . '/Exceptions.php';
require_once __DIR__ . '/Log.php';

abstract class Object
{
    public $uses = array();

    /**
     * @param string $from
     */
    public function applyUses($from = 'Models')
    {
        $all_configs = isset($this->all_configs) ? $this->all_configs : null;
        if (!empty($this->uses)) {
            foreach ($this->uses as $use) {
                if ($from == 'Models') {
                    $class = 'M' . $use;
                } else {
                    $class = $use;
                }
                if (file_exists(__DIR__ . "/../{$from}/{$use}.php")) {
                    require_once __DIR__ . "/../{$from}/{$use}.php";
                    $this->$use = new $class($all_configs);
                }
            }
        }
    }


    /**
     * @return string
     */
    public function getUserId()
    {
        return isset($_SESSION['id']) ? $_SESSION['id'] : '';
    }
}