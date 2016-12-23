<?php

require_once __DIR__.'/../Core/View.php';

class service
{
    protected $all_configs = null;

    /**
     * @param $all_configs
     */
    public function set_all_configs($all_configs)
    {
        $this->all_configs = $all_configs;
    }

    /**
     *
     */
    public static function getInstanse()
    {
    }
}
