<?php

//include $path.'modules/settings/langs.php';
//
//$lang_arr = array_merge($lang_arr, $settings_lang);
// нужные переводы для шаблона
// настройки
$modulename[] = 'seo';
$modulemenu[] = 'SEO';  //карта сайта

$moduleactive[] = !$ifauth['is_2'];

class seo {
     
    protected $all_configs;
    private $lang;
    private $def_lang;
    function __construct($all_configs, $lang, $def_lang){
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->all_configs = $all_configs;
        
        global $input_html, $arrequest, $ifauth, $db, $sitepath;
        
        $this->db = $db;
 
        
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }


        if($ifauth['is_2']) return false;

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }


    private function genmenu() {
        global $db, $prefix, $arrequest;

        $out = '<h4>Инструменты</h4>' .
                '<ul>' .
//                '<li><a' . (isset($arrequest[1]) && $arrequest[1] == 'images' ? ' style="font-weight:bold"' : '') . ' href="' . $prefix . 'seo/images">Изображения</a></li>' .
//                '<li><a' . (isset($arrequest[1]) && $arrequest[1] == 'robots' ? ' style="font-weight:bold"' : '') . ' href="' . $prefix . 'seo/robots">robots.txt</a></li>' .
                '<li><a' . (isset($arrequest[1]) && $arrequest[1] == 'glue' ? ' style="font-weight:bold"' : '') . ' href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/glue">Склейка</a></li>' .
//                '<li><a' . (isset($arrequest[1]) && $arrequest[1] == 'notifications' ? ' style="font-weight:bold"' : '') . ' href="' . $prefix . 'seo/notifications">Уведомления</a></li>' .
                '<li><a' . (isset($arrequest[1]) && $arrequest[1] == 'map' ? ' style="font-weight:bold"' : '') . ' href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/map">Страницы</a></li>' .
                '</ul>'
        ;
        
//         <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $client['id'] . '">' . $client['id'] . '</a></td>
//                    <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $client['id'] . '">' . htmlspecialchars($client['email']) . '</a></td>

        return $out;
    }

    private function gencontent() {
        global $arrequest;
        if (!isset($this->all_configs['arrequest'][1])) {
            $out = '<h2>Модуль СЕО для сайта</h2>';
        } else {
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

//    private function gen_notifications() {
//        global $prefix, $arrequest, $db, $settings;
//        $out = '';
//        if (isset($arrequest[2])) {
//            if ($arrequest[2] == 'update') {
//                $page_seo_email = isset($_POST['page_seo_email']) ? $_POST['page_seo_email'] : '';
//                if (isset($settings['seo_page_emails'])) {
//                    $db->query("UPDATE {settings} SET value = ? "
//                            . "WHERE name = 'seo_page_emails'", array($page_seo_email));
//                } else {
//                    if ($page_seo_email) {
//                        $db->query("INSERT INTO {settings}(name,value,title,ro) "
//                                . "VALUES('seo_page_emails',?,'Уведомление об изменениях в Title или Description страниц',1)", array($page_seo_email));
//                    }
//                }
//            }
//            header('Location: ' . $prefix . 'seo/notifications');
//            exit;
//        } else {
//            $emails = isset($settings['seo_page_emails']) ? $settings['seo_page_emails'] : '';
//            $out = '
//                            <h3>Уведомления</h3>
//                            <form action="' . $prefix . 'seo/notifications/update" method="post">
//                                Эмейлы для уведомлений об изменениях в Title или Description страниц<br>
//                                <input type="text" class="input-xxlarge" name="page_seo_email" value="' . $emails . '">
//                                    <br>
//                                <input type="submit" class="btn btn-primary" value="Сохранить">
//                            </form>
//                        ';
//        }
//        return $out;
//    }
//
    private function gen_glue() {
        global $arrequest, $prefix;
        $out = '';
        if (isset($this->all_configs['arrequest'][2])) {
            
            if ($this->all_configs['arrequest'][2] == 'del' && isset($this->all_configs['arrequest'][3])) {
                if (is_numeric($this->all_configs['arrequest'][3])) {
                    $this->db->query("DELETE FROM {map_glue} WHERE id = ?i LIMIT 1", array($this->all_configs['arrequest'][3]));
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
            header('Location: ' . $this->all_configs['prefix'] .  'seo/glue');
            exit;
        } else {
            

            $out = "<h3>Уже выполняется переадресация</h3>";
            $out.= $this->makeTable();
            $out.= "<a href='" . $this->all_configs['prefix'] . "seo/glue/del/all' onclick='return confirm(\"Удалить все записи?\")'>Удалить все</a>";
            $out.='<h3>Добавление ссылок для переадресации</h3>';
            $out.= "<form action = '" .  $this->all_configs['prefix'] . "seo/glue/add' method='POST'>
                            <div>
                            <textarea rows='10' style='width:300px' name='linkfrom'></textarea>
                            <textarea rows='10' style='width:300px' name='linkto'></textarea>
                            </div>
                            <p><input type='submit' value='Добавить' style='width:120px; height:40px;'></p>
                            </form>";
        }
        return $out;
    }
//
//    private function gen_robots() {
//        global $arrequest, $sitepath, $prefix;
//        $file_path = $sitepath . 'robots.txt';
//        $robots_exists = file_exists($file_path);
//        if ($robots_exists) {
//            $robots_data = file_get_contents($file_path);
//        } else {
//            $robots_data = '';
//        }
//        if (!isset($arrequest[2])) {
//            $out = '<h2>Редактирование файла robots.txt</h2>' .
//                    '<p><b>Внимание!</b> Править можно только после установки сайта на отдельный хостинг.<br>'
//                    . 'На рабочем сервере этот файл общий для всех сайтов.</p>';
//            $out .=
//                    'robots.txt<br>' .
//                    '<form action="' . $prefix . 'seo/robots/save" method="post">' .
//                    '<textarea rows="10" cols="70" style="width:auto" name="robots">' . htmlspecialchars($robots_data) . '</textarea>' .
//                    '<br><input type="submit" class="btn btn-primary" value="Сохранить">' .
//                    '</form>'
//            ;
//        } else {
//            if ($arrequest[2] == 'save') {
//                $file_data = trim($_POST['robots']);
//                file_put_contents($file_path, $file_data);
//                header("Location: " . $prefix . 'seo/robots');
//                exit;
//            }
//        }
//        return $out;
//    }
//
//    private function gen_images() {
//        global $arrequest, $db, $prefix;
//        if (!isset($arrequest[2])) {
//            $out = '<h2>Галереи</h2>';
//
//            $out .= $this->get_galleries();
//        } else {
//            $gallery = $arrequest[2];
//
//            if (isset($arrequest[3]) && $arrequest[3] == 'save') {
//                foreach ($_POST['pictures'] as $file => $picture) {
//                    $seo_alt = trim($picture['seo_alt']);
//                    $seo_title = trim($picture['seo_title']);
//                    $name = trim($picture['name']);
//                    $id = $db->query("SELECT id FROM {image_titles} WHERE gallery = ? AND image = ?", array(
//                        $gallery, $file
//                            ), 'el');
//                    if ($seo_alt || $seo_title || $name) {
//                        if (!$id) {
//                            $id = $db->query("INSERT INTO {image_titles}(gallery, image)"
//                                    . " VALUES(?, ?)", array($gallery, $file), 'id');
//                        }
//                        if ($id) {
//                            // insert translates
//                            $db->query("INSERT INTO {image_titles_strings}(image_id, name, seo_alt, seo_title, lang)
//                                        VALUES(?i, ?, ?, ?, ?) 
//                                        ON DUPLICATE KEY 
//                                        UPDATE name = ?, seo_alt = ?, seo_title = ?", array($id, $name, $seo_alt, $seo_title, $this->lang,
//                                $name, $seo_alt, $seo_title));
//                        }
//                    } else {
//                        $db->query("DELETE FROM {image_titles} WHERE id = ?i", array($id));
//                        $db->query("DELETE FROM {image_titles_strings} "
//                                . "WHERE image_id = ?i AND lang = ?", array($id, $this->lang));
//                    }
//                }
//                header("Location: " . $prefix . 'seo/images/' . $gallery);
//                exit;
//            } else {
//
//                $out = '<form action="' . $prefix . 'seo/images/' . $gallery . '/save" method="post">';
//                $out .= '<div><h2>Галерея «' . $gallery . '»</h2>' .
//                        ' <input type="submit" class="btn btn-primary" value="Сохранить изменения"></div><br>';
//                $out .= $this->get_images($gallery);
//            }
//        }
//        return $out;
//    }
//
//    private function get_galleries() {
//        global $prefix, $site_data_path;
//        $galleries = '';
//        $files = array();
//        if (is_dir($site_data_path . 'images/')) {
//            $files = scandir($site_data_path . 'images/');
//        }
//        foreach ($files as $file) {
//            if (is_dir($site_data_path . 'images/' . $file) && $file != '.' && $file != '..') {
//                $galleries .= '<a href="' . $prefix . 'seo/images/' . $file . '">' . $file . ' (' . $this->get_images($file, true) . ')</a><br>';
//            }
//        }
//        return $galleries;
//    }
//
//    private function get_images($gallery, $get_qty = false) {
//        global $site_data_path, $site_data_prefix, $db;
//        $images = '';
//        $qty = 0;
//        $dir = 'images/' . $gallery . '/';
//        if (is_dir($site_data_path . $dir)) {
//            $pictures = scandir($site_data_path . $dir);
//            $images_arr = array();
//            if (!$get_qty) {
//                $img_titles = $db->query("SELECT id, image FROM {image_titles} WHERE gallery = ?", array($gallery), 'assoc:image');
//                $img_ids = array();
//                foreach ($img_titles as $imt) {
//                    $img_ids[] = $imt['id'];
//                }
//                if ($img_titles) {
//                    $img_translates = get_few_translates(
//                            'image_titles', 'image_id', $db->makeQuery("image_id IN(?q)", array(implode(',', $img_ids)))
//                    );
//                }
//            }
//            foreach ($pictures as $picture) {
//                if (!in_array($picture, array('.', '..'))) {
//                    if (strpos($picture, '_m.') === false) {
//                        $img_info = pathinfo($picture);
//                        if (!in_array($img_info['filename'] . '_m.' . $img_info['extension'], $pictures)) {
//                            $image = $picture;
//                        } else {
//                            continue;
//                        }
//                    } else {
//                        $image = $picture;
//                    }
//
//                    $translates = array();
//                    if (isset($img_titles[$image]) && isset($img_translates[$img_titles[$image]['id']])) {
//                        $translates = translates_for_page($this->lang, $this->def_lang, $img_translates[$img_titles[$image]['id']], array(), false);
//                    }
//                    $images_arr[] = $image;
//                    $qty ++;
//                    if (!$get_qty) {
//                        $seo_alt = isset($translates['seo_alt']) ? $translates['seo_alt'] : '';
//                        $seo_title = isset($translates['seo_title']) ? $translates['seo_title'] : '';
//                        $name = isset($translates['name']) ? $translates['name'] : '';
//                        $images .=
//                                '<div class="picture">' .
//                                '<div class="picture_padding">' .
//                                '<div class="name">' . htmlspecialchars($picture) . '</div>' .
//                                '<div class="imaga">' .
//                                '<img src="' . $site_data_prefix . $dir . $picture . '">' .
//                                '</div>' .
//                                '<div class="contenta">' .
//                                'SEO alt:<br> '
//                                . '<input type="text" class="input-xlarge" name="pictures[' . $picture . '][seo_alt]" value="' . $seo_alt . '"><br>' .
//                                'SEO title:<br> '
//                                . '<input type="text" class="input-xlarge" name="pictures[' . $picture . '][seo_title]" value="' . $seo_title . '"><br>' .
//                                'Описание для сайта:<br> '
//                                . '<input type="text" class="input-xlarge" name="pictures[' . $picture . '][name]" value="' . $name . '">' .
//                                '</div>' .
//                                '</div>' .
//                                '</div>'
//                        ;
//                    }
//                }
//            }
//        }
//        if ($get_qty) {
//            return $qty;
//        } else {
//            return $images;
//        }
//    }
//
    public function makeTable() {
        global $prefix;
        $links = $this->db->query('SELECT * FROM {map_glue}')->assoc();
        if ($links) {
            $out = "<table class='table table-bordered table-hover'>\n";
            $out .= "<tr><td><b>Откуда</b></td>" .
                    "<td><b>Куда</b></td>" .
                    "<td></td></tr>";

            foreach ($links as $link) {
                $out .= '<tr>';
                $out .= '<td>' . $link['link_from'] . '</td>';
                $out .= '<td>' . $link['link_to'] . '</td>';
                $out .= '<td>' .
                        '<a onClick="return confirm(\'Удалить?\');" href="' . $this->all_configs['prefix'] . 'seo/glue/del/' . $link['id'] . '">Удалить</a>' .
                        '</td>';
                $out .= "</tr>";
            }

            $out .= '</table>';
        } else {
            $out = '<p style="padding:20px;">Текущих переадресаций нет.</p>';
        }
        return $out;
    }

    public function addLinks($arr) {
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

//    public function genlist() {
//        GLOBAL $ifauth;
//
//        $out = "<h3>Переадресация</h3>\n";
//        return $out;
//    }
//
    private function check_link($link) {
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

    private function gen_map() {
        
        
        if (isset($this->all_configs['arrequest'][2])) {
            if ($this->all_configs['arrequest'][2] == 'save') {
                
                foreach ($_POST['page'] as $id => $data) {
                    
//                    var_dump($_POST['page']);
//                    exit;
                    
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
                }//'f' => (isset($data['fullname']) ? $data['fullname'] : ''),
                header('Location: ' . $this->all_configs['prefix'] .  'seo/map');
                exit;
            }
        } else {
            $out = '
                <h2>Метаданные страниц сайта</h2>
                <!--<p>Отображаются только опубликованные страницы, <br>
                   которые не являются "составной частью страницы" и "ошибка 404", а также страницы, <br>
                   которые есть потомками главной страницы. Не выводятся страницы чьи разделы не опубликованы.</p>
                <br> -->';    
                             ////m.id, m.parent, ms.map_id, m.url,ms.fullname, ms.name, ms.metakeywords, ms.metadescription
            $pages = $this->db->query("SELECT m.id, m.parent, ms.map_id, m.url,ms.fullname, ms.name, ms.metakeywords, ms.metadescription
                                       FROM {map} as m
                                       LEFT JOIN {map_strings} as ms 
                                       ON m.id = ms.map_id AND ms.lang = ?
                                       ORDER BY m.id",array($this->lang))->assoc('map_id');

//            \
//            if ($pages) {
//                $translates = get_few_translates(
//                        'map', 'map_id', $this->db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($pages))))
//                );
//            }
            
            
            $out .= '
                <form method="post" action="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/map/save">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>Страница</th>
                            <th>url</th>
                            <th>title</th>
                            <th>keywords</th>
                            <th>description</th>
                        </tr>
                    </thead>
                    <tbody>
            ';
            foreach ($pages as $id => $page) {
             // $page = translates_for_page($this->lang, $this->def_lang, $translates[$id], $page, false);
                $full_link = '';//gen_full_link($page['parent']);
                $link = $this->all_configs['siteprefix'] . ($full_link ? $full_link . '/' : '') . $page['url'];
                $out .= '
                    <tr>
                        <td>' . $page['id'] . '</td>
                        <td>
                            <a href="' . $this->all_configs['prefix'] . 'map/' . $page['id'] . '" target="_blank"><i class="icon-pencil"></i></a> ' . $page['name'] . '<br>
                            <!--<a title="' . $this->all_configs['prefix'] . '" href="' . $link . '" target="_blank">ссылка на сайт</a>-->
                        </td>
                        <td><input type="text" name="page[' . $page['id'] . '][url]" value="' . htmlspecialchars($page['url']) . '"></td>
                        <td><textarea rows="3" class="input-xlarge" name="page[' . $page['id'] . '][fullname]">' . htmlspecialchars($page['fullname']) . '</textarea></td>
                        <td><textarea rows="3" class="input-xlarge" name="page[' . $page['id'] . '][metakeywords]">' . htmlspecialchars($page['metakeywords']) . '</textarea></td>
                        <td><textarea rows="3" class="input-xlarge" name="page[' . $page['id'] . '][metadescription]">' . htmlspecialchars($page['metadescription']) . '</textarea></td>  
                    </tr>
                ';
            }
            $out .= '</tbody></table><input type="submit" class="btn btn-primary save_fixed" value="Сохранить"><form>';
       }

        return $out;
    }

    private function ajax() {

        $data = array(
            'state' => false
        );

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

}