<?php
ini_set('memory_limit','512M');

include $all_configs['path'].'modules/translates/index.php';

// настройки
$modulename[145] = 'admin_translates';
$modulemenu[145] = l('Переводы админки');  //карта сайта

$moduleactive[145] = true;

class admin_translates extends translates{
    
    function __construct($all_configs, $lang, $def_lang, $langs){
        global $dbcfg, $manage_langs, $manage_lang, $manage_def_lang;
        $this->config = array(
            $dbcfg['_prefix'].'admin_translates' => array(
                'name' => l('Переводы для шаблонов'),
                'add_link' => '<a href="'.$all_configs['prefix'].'/admin_translates/'.$dbcfg['_prefix'].'admin_translates/add">+</a>',
                'var' => 'var',
                'key' => 'var_id',
                'fields' => array(
                    'text' => l('Значение')
                )
            )
        );
        $this->url = __CLASS__;
        parent::__construct($all_configs, $manage_lang, $manage_def_lang, $manage_langs);
    }

}
