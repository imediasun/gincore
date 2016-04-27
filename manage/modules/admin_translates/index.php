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

    /**
     * 
     */
    protected function set_config()
    {
        if (is_null($this->config)) {
            $this->config = array(
                $this->dbcfg['_prefix'] . 'admin_translates' => array(
                    'table' => $this->dbcfg['_prefix'] . 'admin_translates',
                    'name' => l('Переводы для шаблонов'),
                    'add_link' => '<a href="' . $this->all_configs['prefix'] . '/admin_translates/' . $this->dbcfg['_prefix'] . 'admin_translates/add">+</a>',
                    'var' => 'var',
                    'key' => 'var_id',
                    'fields' => array(
                        'text' => l('Значение')
                    )
                ),
                $this->tbl_prefix . 'print_translates' => array(
                    'table' => $this->dbcfg['_prefix'] . 'admin_translates',
                    'like' => 'print',
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

                if (isset($config['like'])) {
                    if (!empty($filter_query)) {
                        $filter_query = $this->all_configs['db']->makeQuery(" ?q AND  var like '?e%' ",
                            array($filter_query, $config['like']));
                        $filter_query_2 = $this->all_configs['db']->makeQuery(" ?q AND  var like '?e%' ",
                            array($filter_query_2, $config['like']));
                    } else {
                        $filter_query = $this->all_configs['db']->makeQuery(" WHERE var like '?e%' ",
                            array($config['like']));
                        $filter_query_2 = $this->all_configs['db']->makeQuery(" WHERE var like '?e%' ",
                            array($config['like']));
                    }
                }
                $table_name = $this->getTableName();
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
                    if ($this->all_configs['arrequest'][2] != 'add') {
                        $_POST['translates'] = $translates;
                    }
                    $out = $this->check_post($_POST);
                } else {
                    $out = $this->edit($config, $translates, $table, $languages,  isset($config['like']));
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

    /**
     * @param $post
     * @param $translates
     * @param $config
     */
    protected function saveTable($post, $translates, $config)
    {
        foreach ($post['data'] as $id => $transl) {
            foreach ($transl as $lng => $fields) {
                $all_fields = array();
                $vals = array();
                $update_vals = array();
                foreach ($fields as $field => $translate) {
                    // обновляем поля только на которых были изменения
                    if (isset($translates[$id][$lng][$field]) && $translates[$id][$lng][$field] != $translate) {
                        $update_vals[] = $this->all_configs['db']->makeQuery($field . ' = ?',
                            array($translate));
                        $vals[] = $this->all_configs['db']->makeQuery('?', array($translate));
                        $all_fields[] = $field;
                    }
                }
                if ($update_vals) {
                    $data_q = $this->all_configs['db']->makeQuery('(?, ?q, ?)',
                        array($id, implode(',', $vals), $lng));
                    $this->all_configs['db']->query("INSERT INTO ?q_strings(?q, ?q, lang) VALUES ?q 
                                              ON DUPLICATE KEY UPDATE ?q",
                        array(
                            $this->getTableName(),
                            $config['key'],
                            implode(',', $all_fields),
                            $data_q,
                            implode(',', $update_vals)
                        ));
                }
            }
        }

        Response::redirect($this->all_configs['prefix'] . '' . $this->url . '/' . $this->all_configs['arrequest'][1]);
    }
}
