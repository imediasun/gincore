<?php

//include '../finediff/finediff.php';
//echo '
//    <style>
//        del{background-color:red;color:#fff;text-decoration:none}
//        ins{background-color:green;color:#fff;text-decoration:none}
//    </style>
//';
//$link_prev_data = '
//    Menu лендос Гарелея Цены Блог ололо ывывыв Create Landing Page lang. Русский English Sign Up | Login Share Join our Community Sign up for our newsletter Other materials 
//    статейка11 
//    статейка1122 статейкаdddddd
//    КАК НАЙТИ ИДЕАЛЬНУЮ НИШУ, ИЛИ 6 ПРАВИЛ ДЛЯ НОВИЧКА Free Landing Page. Support: info@lpg.tf 
//    Tweet Agreement Ask a questionConsultant offline×Specify your question and click sendQuestion:Type in your question and 
//    I will soon answer it.Email:Question:Expect a consultant ...
//';
//$link_last_data = '
//    Menu лендос Гарелея Цены Блог ололо ывывыв Create Landing Page lang. Русский English Sign Up | Login Share Join our Community Sign up for our newsletter Other materials 
//    статейка1122 статейкаdddddd
//    статейка11 
//    КАК НАЙТИ ИДЕАЛЬНУЮ НИШУ, ИЛИ 6 ПРАВИЛ ДЛЯ НОВИЧКА Free Landing Page. Support: info@lpg.tf 
//    Tweet Agreement Ask a questionConsultant offline×Specify your question and click sendQuestion:Type in your question and 
//    I will soon answer it.Email:Question:Expect a consultant ...
//';
//$link_last_data = preg_replace("/\s+/", " ", $link_last_data);
//$link_prev_data = preg_replace("/\s+/", " ", $link_prev_data);
//$opcodes = FineDiff::getDiffOpcodes($link_prev_data, $link_last_data, FineDiff::$wordGranularity);
//$to_text = FineDiff::renderDiffToHTMLFromOpcodes($link_prev_data, $opcodes);
//
//echo $to_text;
//
//exit;

// настройки
$modulename[190] = 'marketing';
$modulemenu[190] = 'Маркетинг';
$moduleactive[190] = true;

class marketing
{

    private $error = null;
    private $arrequest = null;
    private $db = null;
    private $prefix = null;

    /**
     * marketing constructor.
     * @param $all_configs
     */
    function __construct(&$all_configs)
    {
        $this->all_configs = $all_configs;
        $this->arrequest = $this->all_configs['arrequest'];
        $this->db = $this->all_configs['db'];
        $this->prefix = $this->all_configs['prefix'];
        global $input_html;

        require_once($this->all_configs['sitepath'] . 'shop/model.class.php');

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }


        $error = ($this->error ? '<p  class="text-error">' . $this->error . '</p>' : '');

        $input_html['mcontent'] = $error . $this->gencontent();
    }

    /**
     * @return mixed
     */
    private function gencontent()
    {
        global $input_html;

        $out = $this->monitoring();
        $input_html['modals'] = $out[1];

        return $out[0];
    }

    /**
     * @return array|string
     */
    private function monitoring()
    {
        if (!$this->all_configs['oRole']->hasPrivilege('monitoring')) {
            return '<p class="text-error">' . l('У Вас нет прав для мониторинга') . '</p></div>';
        }

        if (isset($this->arrequest[1])) {
            switch ($this->arrequest[1]) {
                case 'add_site':
                    $site = isset($_POST['site']) ? trim($_POST['site']) : '';
                    if ($site) {
                        $this->db->query("INSERT INTO {monitoring}(site) VALUES(?)", array($site));
                    }
                    header('Location: ' . $this->prefix . 'marketing');
                    exit;
                    break;
                case 'add_link_to_site':
                    $site = isset($_POST['site']) ? (int)$_POST['site'] : 0;
                    $link = isset($_POST['link']) ? trim($_POST['link']) : '';
                    if ($site && $link) {
                        $this->db->query("INSERT INTO {monitoring_links}(site_id,link) VALUES(?i,?)",
                            array($site, $link));
                    }
                    header('Location: ' . $this->prefix . 'marketing');
                    exit;
                    break;
                case 'del_site':
                    $site = isset($_POST['site']) ? (int)$_POST['site'] : 0;
                    if ($site) {
                        $this->db->query("DELETE h FROM {monitoring_diff_history} as h "
                            . "LEFT JOIN {monitoring_data} as l ON l.id = h.last_link_id "
                            . "LEFT JOIN {monitoring_links} as ml ON ml.id = l.link_id "
                            . "WHERE ml.site_id = ?i", array($site));
                        $this->db->query("DELETE d FROM {monitoring_data} as d "
                            . "LEFT JOIN {monitoring_links} as l ON l.id = d.link_id "
                            . "WHERE l.site_id = ?i", array($site));
                        $this->db->query("DELETE FROM {monitoring_links} WHERE site_id = ?i", array($site));
                        $this->db->query("DELETE FROM {monitoring} WHERE id = ?i", array($site));
                    }
                    header('Location: ' . $this->prefix . 'marketing');
                    exit;
                    break;
            }
        }

        $sites = $this->db->query("SELECT * FROM {monitoring} ORDER BY id")->assoc('id');
        $out = '';
        $modals = '';
        $i = 0;
        foreach ($sites as $site) {
            $i++;
            $links_count = $this->db->query("SELECT COUNT(*) FROM {monitoring_links} WHERE site_id = ?i",
                array($site['id']), 'el');
            $events_date = ($site['last_scan_date'] != '0000-00-00 00:00:00' ? do_nice_date($site['last_scan_date']) . ' - ' : '');
            if ($site['events']) {
                $events_link = ' <a data-toggle="modal" class="load_events" data-site="' . $site['id'] . '" href="#history' . $site['id'] . '">' .
                    '(' . $events_date . l('cобытий') . ': ' . $site['events'] . ')' .
                    '</a>';
            } else {
                $events_link = ' (' . $events_date . l('cобытий') . ': ' . $site['events'] . ')';
            }
            $out .= '
                <tr>
                    <td>' . $i . '</td>
                    <td>' .
                htmlspecialchars($site['site']) .
                $events_link .
                '</td>
                    <td>
                        <a href="#links' . $site['id'] . '" class="load_links" data-site="' . $site['id'] . '" data-toggle="modal">' . l('Ссылки') . ' (' . $links_count . ')</a> | '
                . '<a href="' . $this->all_configs['siteprefix'] . 'monitoring_service.php?act=monitoring_get_links&site_id=' . $site['id'] . '" '
                . '         onclick="return confirm(\'' . l('Запустить сканирование сайта') . ' ' . $site['site'] . '?\')">
                            ' . l('Загрузить ссылки') . ' (crawler)
                        </a>
                        <form action="' . $this->prefix . 'marketing/add_link_to_site" method="post" class="mb0 form-inline">
                            <input type="hidden" name="site" value="' . $site['id'] . '">
                            <div class="form-group">
                                <label>' . l('Добавить ссылку') . ':</label>
                                <div class="input-group">
                                    <input type="text" name="link" class="form-control"> 
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-default">' . l('Добавить') . '</button>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </td>
                    <td>
                        <form onsubmit="return confirm(\'' . l('Удалить конкурента') . ' ' . $site['site'] . ' ' . l('и все данные') . '?\')" action="' . $this->prefix . 'marketing/del_site" method="post" class="mb0 form-inline">
                            <input type="hidden" name="site" value="' . $site['id'] . '">
                            <button type="submit" class="btn btn-sm btn-default">' . l('Удалить сайт и все данные') . '</button>
                        </form>
                    </td>
                </tr>
            ';
            $modals .= '
                <div id="history' . $site['id'] . '" data-site="' . $site['id'] . '" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4>' . l('Изменения на сайте') . ' ' . $site['site'] . '</h4>
                            </div>
                            <div class="modal-body" id="site_events_' . $site['id'] . '"></div>
                        </div>
                    </div>
                </div>
                <div id="links' . $site['id'] . '" data-site="' . $site['id'] . '" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4>' . l('Ссылки на сайте') . ' ' . $site['site'] . '</h4>
                            </div>
                            <div class="modal-body" id="site_links_' . $site['id'] . '"></div>
                        </div>
                    </div>
                </div>
            ';
        }

        return array($out, $modals);
    }

    /**
     *
     */
    private function ajax()
    {
        header('Content-type: application/json; charset=utf-8');
        $out = array('state' => false, 'msg' => 'null');

        $act = isset($_POST['act']) ? $_POST['act'] : '';
        switch ($act) {

            // все ссылки сайта 
            case 'show_links':
                $site = isset($_POST['site']) ? $_POST['site'] : '';
                $sites_links_all = $this->db->query(
                    "SELECT id,link "
                    . "FROM {monitoring_links} "
                    . "WHERE site_id = ?i", array($site), 'vars');
                $content = implode('<br>', $sites_links_all);
                $out['state'] = true;
                $out['content'] = $content;
                break;

            // события
            case 'show_events':
                $site = isset($_POST['site']) ? $_POST['site'] : '';
                $history = $this->db->query("SELECT l.id, CONVERT(UNCOMPRESS(h.diff_data) USING utf8) as diff_data, "
                    . "dp.date as prev_ldate, d.date as last_ldate, "
                    . "l.link, m.last_scan_date as date "
                    . "FROM {monitoring_diff_history} as h "
                    . "LEFT JOIN {monitoring_data} as d ON d.id = h.last_link_id "
                    . "LEFT JOIN {monitoring_data} as dp ON dp.id = h.prev_link_id "
                    . "LEFT JOIN {monitoring_links} as l ON l.id = d.link_id "
                    . "LEFT JOIN {monitoring} as m ON m.id = l.site_id "
                    . "WHERE l.site_id = ?i AND diff_data != '' "
                    . "AND h.date >= m.last_scan_date", array($site))->assoc();
                $new_links = $this->db->query("SELECT l.id, l.date, l.link FROM {monitoring_links} as l "
                    . "LEFT JOIN {monitoring} as m ON l.site_id = m.id "
                    . "WHERE l.site_id = ?i AND l.date >= m.last_scan_date", array($site), 'assoc');
                if (!$history && !$new_links) {
                    $content = '<h3>Нет изменений</h3>';
                } else {
                    $content = '';
                    // новые ссылки
                    if ($new_links) {
                        $content .= '
                            <div class="panel-group" id="accordionlinks' . $site . '">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionlinks' . $site . '" href="#collapselinks' . $site . '">
                                        ' . l('Новые ссылки') . ' (' . count($new_links) . ')
                                      </a>
                                    </div>
                                
                                <div id="collapselinks' . $site . '" class="panel-collapse collapse">
                                  <div class="panel-body">
                        ';
                        foreach ($new_links as $link) {
                            $content .= htmlspecialchars($link['link']) . ' (' . $link['date'] . ') <br>';
                        }
                        $content .= '
                                  </div>
                                </div>
                            </div>
                            </div>
                        ';
                    }

                    // контент
                    $content .= '
                        <div class="site_diffs">
                    ';
                    $i = 0;
                    foreach ($history as $h) {
                        $i++;
                        $da = str_replace("&nbsp;", " ", $h['diff_data']);
                        $da = str_replace("\n", " ", $da);
                        $da = str_replace("\\n", " ", $da);
                        $content .= '<div class="row-fluid">';
                        $content .= '
                            <div class="span4 clearfix">
                                <div style="float:left;">' . $i . '.</div>
                                <div style="padding-left:35px;">
                                    <a href="' . htmlspecialchars($h['link']) . '" title="' . htmlspecialchars($h['link']) . '" target="_blank" '
                            . 'style="color: #111;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;display:block;">' .
                            htmlspecialchars($h['link']) .
                            '</a> '
                            . '<span style="font-size:12px">(' . l('сравнение') .
                            date('Y-m-d', strtotime($h['prev_ldate'])) .
                            ' ' . l('и') . ' ' .
                            date('Y-m-d', strtotime($h['last_ldate'])) .
                            ')</span>
                                </div>
                            </div>
                        ';
                        $content .= '
                            <div class="diff_body span8">
                              <div class="diff_body_inner">
                                <div class="diff_data">' .
                            $da .
                            '</div>
                              </div>
                            </div>
                        ';
                        $content .= '</div>';
                    }
                    $content .= '</div>';
                }
                $out['state'] = true;
                $out['content'] = $content;
                break;
        }

        echo json_encode($out);
        exit;
    }

}