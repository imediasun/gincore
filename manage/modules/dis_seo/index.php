<?php
require_once __DIR__ . '/../../Core/View.php';
require_once __DIR__ . '/../../Core/Response.php';

// настройки
$modulename[] = 'seo';
$modulemenu[] = 'SEO';  //карта сайта

$moduleactive[] = !$ifauth['is_2'];

class seo
{

    protected $all_configs;
    protected $view;
    private $lang;
    private $def_lang;

    /**
     * seo constructor.
     * @param $all_configs
     * @param $lang
     * @param $def_lang
     */
    function __construct($all_configs, $lang, $def_lang)
    {
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->all_configs = $all_configs;

        global $input_html, $ifauth, $db;

        $this->db = $db;
        $this->view = new View($all_configs);

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }


        if ($ifauth['is_2']) {
            return false;
        }

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }


    /**
     * @return string
     */
    private function genmenu()
    {
        global $arrequest;

        return $this->view->renderFile('dis_seo/genmenu', array(
            'arrequest' => $arrequest
        ));
    }

    /**
     * @return string
     */
    private function gencontent()
    {
        $out = '<h2>' . l('Модуль СЕО для сайта') . '</h2>';
        if (isset($this->all_configs['arrequest'][1])) {
            switch ($this->all_configs['arrequest'][1]) {
                // уведомления
                case 'notifications':
                    $out = $this->gen_notifications();
                    break;
                // склейка
                case 'glue':
                    $out = $this->gen_glue();
                    break;
                // robots.txt
                case 'robots':
                    $out = $this->gen_robots();
                    break;
                // изображения
                case 'images':
                    $out = $this->gen_images();
                    break;
                // правка страниц
                case 'map':
                    $out = $this->gen_map();
                    break;
            }
        }
        return $out;
    }

    /**
     * @return string
     */
    private function gen_glue()
    {
        if (isset($this->all_configs['arrequest'][2])) {

            if ($this->all_configs['arrequest'][2] == 'del' && isset($this->all_configs['arrequest'][3])) {
                if (is_numeric($this->all_configs['arrequest'][3])) {
                    $this->db->query("DELETE FROM {map_glue} WHERE id = ?i LIMIT 1",
                        array($this->all_configs['arrequest'][3]));
                } elseif ($this->all_configs['arrequest'][3] == 'all') {
                    $this->db->query("DELETE FROM {map_glue}");
                }
            }
            if ($this->all_configs['arrequest'][2] == 'add') {

                if ($_POST['linkfrom'] && $_POST['linkto']) {
                    $lf = $_POST['linkfrom'];
                    $lt = $_POST['linkto'];
                    $arrlf = explode("\n", $lf);
                    $arrlt = explode("\n", $lt);

                    $arrRes = array();
                    for ($i = 0; $i < count($arrlf); $i++) {
                        $from = $arrlf[$i];
                        $to = $arrlt[$i];
                        $arrRes[] = array(
                            'from' => $this->check_link($from),
                            'to' => $this->check_link($to)
                        );
                    }

                    $this->addLinks($arrRes);
                }
            }
            Response::redirect($this->all_configs['prefix'] . 'seo/glue');
        }


        return $this->view->renderFile('dis_seo/gen_glue', array(
            'links' => $this->db->query('SELECT * FROM {map_glue}')->assoc()
        ));
    }

    /**
     * @param $arr
     */
    public function addLinks($arr)
    {
        $values = array();
        foreach ($arr as $val) {
            if ($val['from'] && $val['to']) {
                $values[] = array($val['from'], $val['to']);
            }
        }
        if ($values) {
            $this->db->query("INSERT IGNORE INTO {map_glue} (link_from, link_to) VALUES?v", array($values));
        }
    }

    /**
     * @param $link
     * @return string
     */
    private function check_link($link)
    {
        if (strpos($link, '/') !== 0 && strpos($link, 'http') !== 0) {
            $link = '/' . $link;
        }
        if ($link != '/') {
            if (substr($link, -1, 1) == '/') {
                $link = substr($link, 0, strlen($link) - 1);
            }
        }
        return trim($link);
    }

    /**
     * @return string
     */
    private function gen_map()
    {
        if (isset($this->all_configs['arrequest'][2])) {
            if ($this->all_configs['arrequest'][2] == 'save') {

                foreach ($_POST['page'] as $id => $data) {

                    $this->db->query("UPDATE {map} SET url = ? WHERE id = ?i", array($data['url'], $id), 'ar');
                    $this->db->Query(
                        "INSERT INTO {map_strings}"
                        . " (map_id, fullname, metakeywords,metadescription,lang)"
                        . " VALUES(?i:id, ?:f, ?:k, ?:d, ?:lang) "
                        . "ON DUPLICATE KEY UPDATE "
                        . "fullname = ?:f,metakeywords = ?:k,metadescription = ?:d", array(
                        'lang' => $this->lang,
                        'id' => $id,
                        'f' => $data['fullname'],
                        'k' => $data['metakeywords'],
                        'd' => $data['metadescription'],
                    ));
                }
                Response::redirect($this->all_configs['prefix'] . 'seo/map');
            }
        }
        $pages = $this->db->query("SELECT m.id, m.parent, ms.map_id, m.url,ms.fullname, ms.name, ms.metakeywords, ms.metadescription
                                       FROM {map} as m
                                       LEFT JOIN {map_strings} as ms 
                                       ON m.id = ms.map_id AND ms.lang = ?
                                       ORDER BY m.id", array($this->lang))->assoc('map_id');
        return $this->view->renderFile('dis_seo/gen_map', array(
            'pages' => $pages
        ));
    }

    /**
     *
     */
    private function ajax()
    {
        $data = array(
            'state' => false
        );

        Response::json($data);
    }
}