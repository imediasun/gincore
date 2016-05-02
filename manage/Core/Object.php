<?php


abstract class Object
{
    /**
     *
     */
    public function applyUses()
    {
        if (!empty($this->uses)) {
            foreach ($this->uses as $use) {
                $class = 'M' . $use;
                if (file_exists(__DIR__ . "/{$use}.php")) {
                    require_once __DIR__ . "/{$use}.php";
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