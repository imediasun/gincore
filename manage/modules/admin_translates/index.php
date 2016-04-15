<?php
ini_set('memory_limit', '512M');

include $all_configs['path'] . 'modules/translates/index.php';

// настройки
$modulename[145] = 'admin_translates';
$modulemenu[145] = l('Переводы админки');  //карта сайта

$moduleactive[145] = true;

class admin_translates extends translates
{

    function __construct($all_configs, $lang, $def_lang, $langs)
    {
        global $manage_langs, $manage_lang, $manage_def_lang;
        parent::__construct($all_configs, $manage_lang, $manage_def_lang, $manage_langs);
    }

    protected function set_config()
    {
        if (is_null($this->config)) {
            $this->config = array(
                $this->dbcfg['_prefix'] . 'admin_translates' => array(
                    'name' => l('Переводы для шаблонов'),
                    'add_link' => '<a href="' . $this->all_configs['prefix'] . '/admin_translates/' . $this->dbcfg['_prefix'] . 'admin_translates/add">+</a>',
                    'var' => 'var',
                    'key' => 'var_id',
                    'fields' => array(
                        'text' => l('Значение')
                    )
                ),
/** @todo уточнить и исправить */
                $this->tbl_prefix . 'template_vars' => array(
                    'name' => l('Переводы для печатных форм'),
                    'add_link' => '<a href="' . $this->all_configs['prefix'] . '/translates/' . $this->dbcfg['_prefix'] . 'translates/add">+</a>',
                    'var' => 'var',
                    'key' => 'var_id',
                    'fields' => array(
                        'text' => l('Значение')
                    )
                ),
            );
            $this->url = __CLASS__;
        }
    }
}
