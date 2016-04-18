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
                $this->tbl_prefix . 'print_template_vars' => array(
                    'name' => l('Переводы для печатных форм'),
                    'add_link' => '<a href="' . $this->all_configs['prefix'] . '/admin_translates/' . $this->dbcfg['_prefix'] . 'print_translates/add">+</a>',
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

    /**
     * @return mixed|string
     */
    protected function gencontent()
    {
        $languages = $this->langs;
        $def_lang = $this->def_lang;

        $out = l('Выберите таблицу слева.');
        if (isset($this->all_configs['arrequest'][1])) {

            if (isset($this->config[$this->all_configs['arrequest'][1]])) {


                $config = $this->config[$this->all_configs['arrequest'][1]];

                $filter_query = '';
                $filter_query_2 = '';
                if (isset($this->all_configs['arrequest'][2]) && is_numeric($this->all_configs['arrequest'][2])) {
                    $filter_query = $this->all_configs['db']->makeQuery(" WHERE id = ?i ",
                        array($this->all_configs['arrequest'][2]));
                    $filter_query_2 = $this->all_configs['db']->makeQuery(" WHERE ?q = ?i ",
                        array($config['key'], $this->all_configs['arrequest'][2]));
                }

                if(strpos($this->all_configs['arrequest'][1],'print_template_vars') !== false) {
                    $table_name = $this->tbl_prefix.'template_vars';
                    if (!empty($filter_query)) {
                       $filter_query =  $this->all_configs['db']->makeQuery(" ?q AND  var like '?e%' ",
                        array($filter_query, 'print'));
                        $filter_query_2 =  $this->all_configs['db']->makeQuery(" ?q AND  var like '?e%' ",
                            array($filter_query_2, 'print'));
                    } else {
                        $filter_query = $this->all_configs['db']->makeQuery(" WHERE var like '?e%' ",
                            array('print'));
                        $filter_query_2 = $this->all_configs['db']->makeQuery(" WHERE var like '?e%' ",
                            array('print'));
                    }
                } else {
                    $table_name = $this->all_configs['arrequest'][1];
                }
                $table = $this->all_configs['db']->query("SELECT * FROM ?q ?q",
                    array($table_name, $filter_query), 'assoc:id');
                $table_translates = $this->all_configs['db']->query("SELECT * FROM ?q_strings as ts JOIN ?q as tv ON tv.id = ts.var_id  ?q",
                    array($table_name, $table_name, $filter_query_2), 'assoc');

                $translates = array();
                foreach ($table_translates as $trans) {
                    if (!isset($translates[$trans[$config['key']]])) {
                        $translates[$trans[$config['key']]] = array();
                    }
                    $translates[$trans[$config['key']]][$trans['lang']] = $trans;
                }
                // добвим поля c недостающими языками
                foreach ($languages as $l => $v) {
                    foreach ($translates as $id => $t) {
                        if (!isset($translates[$id][$l])) {
                            $translates[$id][$l] = array_map('clear_values_in_array',
                                isset($translates[$id][$def_lang]) ? $translates[$id][$def_lang] : array_shift($translates[$id]));
                        }
                    }
                }

                if (isset($this->all_configs['arrequest'][2]) && !is_numeric($this->all_configs['arrequest'][2])) {
                    $_POST['translates'] = $translates;
                    $out = $this->check_post($_POST);
                } else {
                    $out = $this->edit($config, $translates, $table, $languages, strpos($this->all_configs['arrequest'][1],'print_template_vars') !== false);
                }
            } else {
                switch ($this->all_configs['arrequest'][1]) {
                    // история изменений контента
                    case 'changes':
                        $out = $this->changeHistory();
                        break;
                }
            }
        }

        return $out;
    }
}
