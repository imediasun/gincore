<?php
require_once __DIR__ . '/../../../Core/View.php';
require_once __DIR__ . '/../../../Core/Log.php';

abstract class AbstractTemplate
{
    protected $view;
    protected $all_configs;
    protected $variables = '';
    protected $manage_lang;
    private $cur_lang;
    private $templateTable;
    public $editor = false;

    abstract public function draw_one($object);

    /**
     * @return string
     */
    public function draw()
    {
        $result = '';
        if (isset($_GET['object_id']) && !empty($_GET['object_id'])) {
            $objects = array_filter(explode(',', $_GET['object_id']));
            foreach ($objects as $object) {
                if ($object !== 0) {
                    $result .= $this->draw_one($object);
                }
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function act()
    {
        return get_class($this);
    }

    public function __construct(&$all_configs, $templateTable, $cur_lang)
    {
        global $manage_lang;

        $this->manage_lang = $manage_lang;
        $this->all_configs = $all_configs;
        $this->cur_lang = $cur_lang;
        $this->templateTable = $templateTable;
        $this->view = new View($all_configs);
    }

    /**
     * @param $act
     * @return mixed
     */
    public function get_template($act)
    {
        $template = $this->all_configs['db']->query("SELECT text FROM {?q_strings} as s "
            . "LEFT JOIN {?q} as t ON t.id = s.var_id "
            . "WHERE s.lang = ? AND t.var = ?",
            array($this->templateTable, $this->templateTable, $this->cur_lang, 'print_template_' . $act), 'el');
        if (empty($template)) {
            $template = $this->all_configs['db']->query("SELECT text FROM {?q_strings} as s "
                . "LEFT JOIN {?q} as t ON t.id = s.var_id "
                . "WHERE s.lang = ? AND t.var = ?",
                array('admin_translates', 'admin_translates', $this->manage_lang, 'print_template_' . $act), 'el');
        }
        return $template;
    }

    /**
     * @param $arr
     */
    public function generateVariables($arr)
    {
        foreach ($arr as $k => $v) {
            $this->variables .= '<p><b>{{' . $k . '}}</b> - ' . $v['name'] . '</p>';
        }
    }

    /**
     * @param      $arr
     * @param      $act
     * @param bool $generateVariable
     * @return mixed
     */
    public function generate_template($arr, $act, $generateVariable = true)
    {
        $print_html = $this->get_template($act);

        if ($generateVariable) {
            $this->generateVariables($arr);
        }

        // адрес и телефон по-умолчанию
        if (isset($this->all_configs['configs']['manage-print-default-service-restore']) &&
            $this->all_configs['configs']['manage-print-default-service-restore']
        ) {
            $address = 'г.Киев ул. Межигорская 63';
            $phone = 'тел./факс: (044)393-47-42';
        } else {
            $address = '';
            $phone = '';
        }
        if (empty($arr['wh_address']['value'])) {
            $arr['wh_address']['value'] = $address;
        }
        if (empty($arr['wh_phone']['value'])) {
            $arr['wh_phone']['value'] = $phone;
        }

        $print_html = preg_replace_callback(
            "/\{\{([a-zA-Z0-9_\-]{0,100})\}\}/",
            function ($m) use ($arr) {
                $value = '';
                if (isset($arr[$m[1]])) {
                    $value = $arr[$m[1]]['value'];
                }
                return '<span data-key="' . $m[1] . '" class="template">' . $value . '</span>';
            },
            $print_html
        );

        return $print_html;
    }

    /**
     * @param $print_html
     * @return string
     */
    public function add_edit_form($print_html)
    {
        if ($print_html && $this->editor && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $print_html = $this->view->renderFile('prints/add_edit_form', array(
                'tpl' => $this->get_template($this->act()),
                'variables' => $this->variables,
                'print_html' => $print_html
            ));
        }
        return $print_html;
    }

    /**
     * @return string
     */
    public function dateAsWord()
    {
        require_once __DIR__ . '/../../../classes/php_rutils/struct/TimeParams.php';
        require_once __DIR__ . '/../../../classes/php_rutils/Dt.php';
        require_once __DIR__ . '/../../../classes/php_rutils/Numeral.php';
        require_once __DIR__ . '/../../../classes/php_rutils/RUtils.php';
        $params = new \php_rutils\struct\TimeParams();
        $params->date = null;
        $params->format = 'd F Y';
        $params->monthInflected = true;
        return \php_rutils\RUtils::dt()->ruStrFTime($params);
    }

    /**
     * @param $amount
     * @return string
     */
    public function amountAsWord($amount)
    {
        if ($this->all_configs['settings']['lang'] == 'ru') {
            require_once __DIR__ . '/../../../classes/php_rutils/struct/TimeParams.php';
            require_once __DIR__ . '/../../../classes/php_rutils/Dt.php';
            require_once __DIR__ . '/../../../classes/php_rutils/Numeral.php';
            require_once __DIR__ . '/../../../classes/php_rutils/RUtils.php';
            $result = \php_rutils\RUtils::numeral()->getRubles($amount, false,
                $this->all_configs['configs']['currencies'][$this->all_configs['settings']['currency_orders']]['rutils']['gender'],
                $this->all_configs['configs']['currencies'][$this->all_configs['settings']['currency_orders']]['rutils']['words']);
        } else {
            $result = convert_number_to_words($amount);
        }
        return $result;
    }
}