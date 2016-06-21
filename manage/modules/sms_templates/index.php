<?php

require_once $all_configs['path'] . 'modules/translates/index.php';

// настройки
$modulename[210] = 'sms_templates';
$modulemenu[210] = l('Шаблоны для sms');  //карта сайта

$moduleactive[210] = true;

class sms_templates extends translates
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
                $this->tbl_prefix . 'sms_templates' => array(
                    'table' => $this->dbcfg['_prefix'] . 'sms_templates',
                    'like' => 'print',
                    'name' => l('Шаблоны для sms'),
                    'add_link' => '<a href="' . $this->all_configs['prefix'] . '/sms_templates/' . $this->dbcfg['_prefix'] . 'sms_templates/add">+</a>',
                    'var' => 'var',
                    'key' => 'sms_templates_id',
                    'fields' => array(
                        'body' => l('Значение')
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

                $table_name = $this->getTableName();
                $table = $this->all_configs['db']->query("SELECT * FROM ?q ?q",
                    array($table_name, $filter_query), 'assoc:id');
                $table_translates = $this->all_configs['db']->query("SELECT * FROM ?q_strings as ts JOIN ?q as tv ON tv.id = ts.sms_templates_id  ?q",
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
                    $out = $this->edit($config, $translates, $table, $languages, isset($config['like']));
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
     * @param $config
     * @param $languages
     * @return string
     */
    protected function addToTable($post, $config, $languages)
    {
        $tableName = $this->getTableName();
        if (isset($this->all_configs['arrequest'][3]) && $this->all_configs['arrequest'][3] == 'save') {
            $id = $this->saveTemplateVars($tableName, $post);
            $all_fields = array();
            $values = array();
            foreach ($post['translates'] as $lng => $fields) {
                if (!$all_fields) {
                    $all_fields[] = implode(',', array_keys($fields));
                }
                $vals = array();
                foreach ($fields as $field => $translate) {
                    $vals[] = $this->all_configs['db']->makeQuery('?', array($translate));
                }
                $values[] = $this->all_configs['db']->makeQuery('(?, ?q, ?)',
                    array($id, implode(',', $vals), $lng));
            }

            $this->all_configs['db']->query("INSERT INTO ?q_strings(?q, ?q, lang) VALUES ?q",
                array(
                    $tableName,
                    $config['key'],
                    implode(',', $all_fields),
                    implode(',', $values)
                ));
            Response::redirect($this->all_configs['prefix'] . '' . $this->url . '/' . $this->all_configs['arrequest'][1]);
        }
        $columns = $this->all_configs['db']->query("SHOW COLUMNS FROM ?q",
            array($tableName), 'assoc');
        return $this->view->renderFile('sms_templates/save_form', array(
            'columns' => $columns,
            'url' => $this->url,
            'config' => $config,
            'manage_lang' => $this->lang,
            'types' => $this->all_configs['configs']['sms-types']
        ));
    }

    /**
     * @param $tableName
     * @param $post
     * @return null
     */
    private function saveTemplateVars($tableName, $post)
    {
        $id = null;
        try {
            if (empty($post['data']) || empty($post['data']['var']) || empty($post['data']['type'])) {
                throw new ExceptionWithMsg(l('Заполните все поля'));
            }
            $id = $this->all_configs['db']->query('SELECT id FROM ?q WHERE var=?',
                array($tableName, $post['data']['var']))->el();
            if (!empty($id)) {
                throw new ExceptionWithMsg(l('Переменная уже существует'));
            }
            
            $f = implode(',', array_keys($post['data']));
            $v = array();
            foreach ($post['data'] as $fld => $d) {
                $v[] = $this->all_configs['db']->makeQuery("?", array($d));
            }
            $id = $this->all_configs['db']->query("INSERT INTO ?q(?q) VALUES (?q)",
                array($tableName, $f, implode(',', $v)), 'id');
            if (empty($id)) {
                throw new ExceptionWithMsg(l('Проблемы при создании переменной'));
            }

        } catch (ExceptionWithMsg $e) {
            FlashMessage::set($e->getMessage(), FlashMessage::INFO);
            Response::redirect($this->all_configs['prefix'] . '' . $this->url . '/' . $this->all_configs['arrequest'][1]);
        }
        return $id;
    }

    /**
     * @param      $config
     * @param      $translates
     * @param      $table
     * @param      $languages
     * @param bool $textarea
     * @return string
     */
    protected function edit($config, $translates, $table, $languages, $textarea = false)
    {
        return $this->view->renderFile('sms_templates/edit', array(
            'config' => $config,
            'translates' => $translates,
            'url' => $this->url,
            'table' => $table,
            'languages' => $languages,
            'textarea' => false,
            'manage_lang' => $this->lang,
            'types' => array_flip($this->all_configs['configs']['sms-types'])
        ));
    }
}
