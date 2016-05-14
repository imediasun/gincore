<?php

require_once __DIR__ . '/Exceptions.php';

abstract class Object
{
    public $uses = array();

    /**
     * @param string $from
     */
    public function applyUses($from = 'Models')
    {
        if (!empty($this->uses)) {
            foreach ($this->uses as $use) {
                if ($from == 'Models') {
                    $class = 'M' . $use;
                } else {
                    $class = $use;
                }
                if (file_exists(__DIR__ . "/../{$from}/{$use}.php")) {
                    require_once __DIR__ . "/../{$from}/{$use}.php";
                    $this->$use = new $class();
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