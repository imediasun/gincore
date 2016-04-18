<?php

require_once __DIR__ . '/../../View.php';
require_once __DIR__ . '/../../Response.php';

ini_set('memory_limit', '512M');

// настройки
$modulename[140] = 'translates';
$modulemenu[140] = l('trans_modulemenu');  //карта сайта

$moduleactive[140] = $ifauth['is_1'];

class translates
{

    protected $tbl_prefix;
    protected $config = null;
    protected $url = null;

    protected $all_configs;
    protected $lang;
    protected $def_lang;
    protected $langs;
    /** @var View */
    protected $view;
    protected $dbcfg;

    /**
     * translates constructor.
     * @param $all_configs
     * @param $lang
     * @param $def_lang
     * @param $langs
     */
    function __construct($all_configs, $lang, $def_lang, $langs)
    {
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->all_configs = $all_configs;
        $this->view = new View($all_configs);

        global $input_html, $dbcfg;

        $this->dbcfg = $dbcfg;
        $this->tbl_prefix = $dbcfg['_prefix'];

        $this->set_config();

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    /**
     *
     */
    protected function set_config()
    {
        if (is_null($this->config)) {
            $this->url = __CLASS__;
            $this->config = array(
                $this->tbl_prefix . 'map' => array(
                    'name' => l('Переводы для карты сайта'),
                    //            'var' => 'url',
                    'key' => 'map_id',
                    'fields' => array(
                        'name' => l('Название'),
                        'fullname' => l('Заголовок'),
                        'content' => l('Контент')
                    )
                ),
                $this->tbl_prefix . 'forms' => array(
                    'name' => l('Переводы для форм'),
                    //            'var' => 'url',
                    'key' => 'forms_id',
                    'fields' => array(
                        'name' => l('Название'),
                        'user_result_text' => l('Текст письма пользователю'),
                        'user_result_title' => l('Заголовок письма пользователю')
                    )
                ),
                $this->tbl_prefix . 'reviews_marks' => array(
                    'name' => l('Переводы оценок отзывов'),
                    //            'var' => 'url',
                    'key' => 'mark_id',
                    'fields' => array(
                        'name' => l('Название')
                    )
                ),
                $this->tbl_prefix . 'template_vars' => array(
                    'name' => l('Переводы для шаблона сайта'),
                    'var' => 'var',
                    'key' => 'var_id',
                    'fields' => array(
                        'text' => l('Значение')
                    )
                ),
                $this->tbl_prefix . 'sms_templates' => array(
                    'name' => l('Шаблоны смс'),
                    'key' => 'sms_templates_id',
                    'fields' => array(
                        'body' => l('Текст')
                    )
                ),
            );
        }
    }

    /**
     * @return string
     */
    protected function genmenu()
    {
        return $this->view->renderFile('translates/genmenu', array(
            'config' => $this->config,
            'url' => $this->url
        ));
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
                $table = $this->all_configs['db']->query("SELECT * FROM ?q ?q",
                    array($this->all_configs['arrequest'][1], $filter_query), 'assoc:id');
                $table_translates = $this->all_configs['db']->query("SELECT * FROM ?q_strings ?q",
                    array($this->all_configs['arrequest'][1], $filter_query_2), 'assoc');

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
                    $out = $this->edit($config, $translates, $table, $languages);
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
     *
     */
    protected function ajax()
    {
        Response::json(array(
            'state' => false
        ));
    }

    /**
     * @param $post
     * @return array|string
     */
    public function check_post($post)
    {
        $out = '';
        $languages = $this->langs;
        $translates = empty($post['translates']) ? array() : $post['translates'];
        $config = $this->config[$this->all_configs['arrequest'][1]];
        if ($this->all_configs['arrequest'][2] == 'copy') {
            $out = $this->copyLanguage($post, $languages, $config);
        }

        // add to table
        if ($this->all_configs['arrequest'][2] == 'add') {
            $out = $this->addToTable($post, $config, $languages);
        }
        if ($this->all_configs['arrequest'][2] == 'save') {
            $this->saveTable($post, $translates, $config);
        }
        return $out;
    }

    /**
     * @param $post
     * @param $translates
     * @param $config
     */
    private function saveTable($post, $translates, $config)
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
                            $this->all_configs['arrequest'][1],
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

    /**
     * @param $post
     * @param $languages
     * @param $config
     * @return array
     */
    private function copyLanguage($post, $languages, $config)
    {
        $success = false;
        if (isset($this->all_configs['arrequest'][3]) && $this->all_configs['arrequest'][3] == 'make_magic') {
            $from = isset($post['from']) ? $post['from'] : '';
            $to = isset($post['to']) ? $post['to'] : '';
            if (!isset($languages[$from]) || !isset($languages[$to])) {
                Response::redirect($this->all_configs['prefix'] . '' . $this->url . '/' . $this->all_configs['arrequest'][1] . '/copy');
            }
            $tbl_fields = array_keys($config['fields']);
            $tbl_fields_1 = array();
            $update = array();
            foreach ($tbl_fields as $tbl_field) {
                $tbl_fields_1[] = '`' . $tbl_field . '` as ' . $tbl_field . '1';
                $update[] = '`' . $tbl_field . '` = IF(`' . $tbl_field . '` = "", VALUES(`' . $tbl_field . '`), `' . $tbl_field . '`)';
            }
            $this->all_configs['db']->query("INSERT INTO ?q:tbl(?q:key, ?q:fields, lang) 
                                            SELECT * FROM (SELECT ?q:key, ?q:fields1, ?:lang_to FROM ?q:tbl as t WHERE lang = ?:lang_from) as t
                                            ON DUPLICATE KEY UPDATE ?q:update",
                array(
                    'tbl' => $this->all_configs['arrequest'][1] . '_strings',
                    'lang_from' => $from,
                    'lang_to' => $to,
                    'key' => $config['key'],
                    'fields1' => implode(',', $tbl_fields_1),
                    'fields' => implode(',', $tbl_fields),
                    'update' => implode(',', $update)
                ));
            $success = true;
        }
        return $this->view->renderFile('translates/copy_language', array(
            'success' => $success,
            'from' => isset($from) ? $from : '',
            'to' => isset($to) ? $to : '',
            'url' => $this->url,
            'languages' => $languages
        ));
    }

    /**
     * @param $post
     * @param $config
     * @param $languages
     * @return string
     */
    private function addToTable($post, $config, $languages)
    {
        if (isset($this->all_configs['arrequest'][3]) && $this->all_configs['arrequest'][3] == 'save') {
            if ($post['data']) {
                $f = implode(',', array_keys($post['data']));
                $v = array();
                foreach ($post['data'] as $fld => $d) {
                    $v[] = $this->all_configs['db']->makeQuery("?", array($d));
                }
                $id = $this->all_configs['db']->query("INSERT INTO ?q(?q) VALUES (?q)",
                    array($this->all_configs['arrequest'][1], $f, implode(',', $v)), 'id');
            } else {
                $id = $this->all_configs['db']->query("INSERT INTO ?q() VALUES ()",
                    array($this->all_configs['arrequest'][1]), 'id');
            }
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
                    $this->all_configs['arrequest'][1],
                    $config['key'],
                    implode(',', $all_fields),
                    implode(',', $values)
                ));
            Response::redirect($this->all_configs['prefix'] . '' . $this->url . '/' . $this->all_configs['arrequest'][1]);
        }
        $columns = $this->all_configs['db']->query("SHOW COLUMNS FROM ?q",
            array($this->all_configs['arrequest'][1]), 'assoc');
        return $this->view->renderFile('translates/save_form', array(
            'columns' => $columns,
            'url' => $this->url,
            'config' => $config,
            'languages' => $languages
        ));
    }

    /**
     * @return string
     */
    protected function changeHistory()
    {
        $query = '';
        $query_parts = array();
        foreach ($this->config as $table => $conf) {
            $query_parts[] =
                $this->all_configs['db']->makeQuery(
                    "(SELECT " . $conf['key'] . " as id,lang,change_date,'" . $table . "' as tbl "
                    . "FROM " . $table . "_strings WHERE change_date >= DATE_ADD(CURDATE(), INTERVAL -21 DAY))",
                    array());
        }
        $query .= implode(' UNION ', $query_parts) . ' ORDER BY change_date DESC';

        $data = $this->all_configs['db']->plainQuery($query)->assoc();

        $changes = array();
        foreach ($data as $change) {
            $tbl_no_prefix = substr($change['tbl'], strlen($this->tbl_prefix), strlen($change['tbl']));
            switch ($tbl_no_prefix) {
                case 'map':
                    $changes[] = array(
                        'change' => $change,
                        'data' => $this->all_configs['db']->query("SELECT url FROM {map} WHERE id = ?i",
                            array($change['id']), 'el'),
                        'link' => 'map/' . $change['id']
                    );
                    break;
                case 'template_vars':
                    $changes[] = array(
                        'change' => $change,
                        'data' => $this->all_configs['db']->query("SELECT var FROM {template_vars} WHERE id = ?i",
                            array($change['id']), 'el'),
                        'link' => $this->url . '/' . $change['tbl'] . '/' . $change['id']
                    );
                    break;
                default:
                    $changes[] = array(
                        'data' => '',
                        'change' => $change,
                        'link' => $this->url . '/' . $change['tbl'] . '/' . $change['id']
                    );
                    break;
            }
        }
        return $this->view->renderFile('translates/change_history', array(
            'changes' => $changes,
            'config' => $this->config
        ));
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
        return $this->view->renderFile('translates/edit', array(
            'config' => $config,
            'translates' => $translates,
            'url' => $this->url,
            'table' => $table,
            'languages' => $languages,
            'textarea' => $textarea
        ));
    }
}
