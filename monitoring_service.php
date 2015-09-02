<?php

include 'inc_config.php';
include 'inc_func.php';
include 'mail.php';
include 'configs.php';

set_time_limit(21000); // 

$date_begin = date("Y-m-d H:i:s");

$error = '';

$all_configs = all_configs();

$sites = isset($_POST['sites']) ? $_POST['sites'] : array();
if($sites){
    $mail_sites = '<pre>'.print_r($sites, true).'</pre>';
//    send_mail('kv@fon.in.ua', 'Лог крона на restore 1 monitoring_update_scan_date', $mail_sites);
    monitoring_update_scan_date($sites);
//    send_mail('kv@fon.in.ua', 'Лог крона на restore 2 monitoring_get_links', $mail_sites);
    monitoring_get_links(array(), $sites);
//    send_mail('kv@fon.in.ua', 'Лог крона на restore 3 monitoring_links', $mail_sites);
    //monitoring_links($sites);
//    send_mail('kv@fon.in.ua', 'Лог крона на restore 4 monitoring_diff_links', $mail_sites);
    monitoring_diff_links($sites);
    //send_mail('kv@fon.in.ua', 'Лог крона на restore THE END!!', $mail_sites);
}
$act = isset($_GET['act']) ? $_GET['act'] : '';
if($act == 'monitoring_get_links' && isset($_GET['site_id'])){
    monitoring_get_links($_GET);
}

if($act == 'monitoring_download_link' && isset($_GET['site_id']) && is_numeric($_GET['site_id'])){
    //monitoring_update_scan_date(array($_GET['site_id'] => 1));
    monitoring_links(array($_GET['site_id'] => 1));
}

if($act == 'monitoring_diff_link' && isset($_GET['site_id']) && is_numeric($_GET['site_id'])){
    monitoring_diff_links(array($_GET['site_id'] => 1));
}

function monitoring_update_scan_date($sites = array()){
    global $all_configs;
    $db = $all_configs['db'];
    $where = '';
    
    if($sites){
        $where = $all_configs['db']->makeQuery(" WHERE id IN (?l)", array(array_keys($sites)));
    }
    $db->query("UPDATE {monitoring} SET last_scan_date = NOW(), events = 0 ".$where);
}

function monitoring_get_links($params = array(), $sites = array()){
    global $all_configs;
    $db = $all_configs['db'];
    
    $mailer = new Mailer($all_configs);
    
    include "PHPCrawl/libs/PHPCrawler.class.php"; 
    class MyCrawler extends PHPCrawler{
        var $links = array();
        var $links_cnt = 0;
        var $max_links = 5000; // максимальное количество ссылок для одного сайта
//        protected $time_limit = 1200; // если после 20 минут сайт продолжает колбасится переходим к следующему
//        protected $start_time;
//        function handleDocumentInfo(PHPCrawlerDocumentInfo $PageInfo) {
        function handleDocumentInfo($DocInfo) {
            global $all_configs;
            $db = $all_configs['db'];
//            if ($this->start_time == null){
//                $this->start_time = time(); 
//            }
            if($DocInfo->http_status_code == 200){
                $this->links_cnt ++;
                if($this->links_cnt > $this->max_links){
                    return -1; // Abort crawling-process
                }
                
                
                $url = $DocInfo->url;
                if(substr($url, -1, 1) == '/'){
                    $url = substr($url, 0, strlen($url) - 1);
                }
//                try{
//                    $id = $db->query("INSERT IGNORE INTO {monitoring_links}(site_id, link) "
//                          ."VALUES(?i, ?)", array($site['id'], $url), 'id');
//                }catch(Exception $e){
//                    send_mail('ragenoir@gmail.com', 'Ошибка крон restore: monitoring_get_links', $e->getMessage());
//                }
                
                
//                file_put_contents('monitoring_service.txt', $url."\n", FILE_APPEND);
                if(isset($_GET['site_id'])){
                    echo $url.'<br>';
                    flush();
                    ob_flush();
                }
                $this->links[] = $url;

                if ($url){
                    try {
                        $db->query("INSERT INTO {monitoring_data}(link_id,date,html) "
                                . "VALUES ((SELECT id FROM {monitoring_links} WHERE link=?), NOW(), COMPRESS(?))", array($url, $DocInfo->source));
                    } catch (Exception $e) {
        //            send_mail('kv@fon.in.ua', 'Ошибка крон restore: monitoring_links', $e->getMessage());
                    }
                }
//                echo '<pre>';
//                echo htmlspecialchars($DocInfo->source);
//                echo '</pre>';
//                echo '<hr>';
//                flush();
            }
            
//            if (time() - $this->start_time > $this->time_limit){
//                return -1; // Abort crawling-process
//            }
        }
        function get_links(){
            return $this->links;
        }
    }

    $site_id = isset($params['site_id']) ? $params['site_id'] : 0;
    
    if(!$sites){
        $sites = $db->query("SELECT * FROM {monitoring}".
                               ($site_id ? $db->makeQuery(" WHERE id = ?i", array($site_id)) : '')
                           )->assoc();
    }
    
    //monitoring_update_scan_date($sites);
    
    $added_links = array();
    
    
    foreach($sites as $site){
        $crawler = new MyCrawler(); 
        $crawler->setUrlCacheType(PHPCrawlerUrlCacheTypes::URLCACHE_SQLITE); 
        $crawler->requestGzipContent(true); 
        $crawler->setURL($site['site']); 
        $crawler->addLinkExtractionTags("href"); 
        $crawler->disableExtendedLinkInfo(true); 
        $crawler->setAggressiveLinkExtraction(false); 
        $crawler->addContentTypeReceiveRule("/text\/html/"); 
        //$crawler->addURLFilterRule("/.(jpg|jpeg|gif|png|js|css|mp3|xml|xls|doc|docx|xlsx|pdf|feed|flv)[\d\D]*$/ i"); 
        $crawler->addURLFilterRule("/\.(jpg|jpeg|gif|png|js|css|mp3|xml|xls|doc|docx|xlsx|pdf|feed|flv|ico)[\d\D]*$|replytocom|\/feed|print=1|format=feed|\/login|\/search|page=shop.ask|\/sitemap|xmlrpc/ i"); 
        $crawler->enableCookieHandling(true); 
        $crawler->go(); 
        $links = $crawler->get_links();
        unset($crawler);
        $added_links = array();
        foreach($links as $i => $link){
//            $links_queries[] = $db->makeQuery("(?i, ?)", array($site['id'], $link));
            $id = 0;
            try{
                $id = $db->query("INSERT IGNORE INTO {monitoring_links}(site_id, link) "
                          ."VALUES(?i, ?)", array($site['id'], $link), 'id');
            }catch(Exception $e){
                send_mail('ragenoir@gmail.com', 'Ошибка крон restore: monitoring_get_links', $e->getMessage());
            }
            if($id){
                $added_links[] = htmlspecialchars($link);
            }
            usleep(30000);
        }
        
        echo '<hr>Done: '.count($added_links).'/'.count($links).'  links';
        if($added_links){
            $db->query("UPDATE {monitoring} SET events = events + ?i WHERE id = ?i", array(count($added_links), $site['id']));
            $mailer->send_message('<a href="'.$all_configs['prefix'].'manage/marketing">Перейти в мониторинг</a>'
                                 .'<br>Новые ссылки: <br>'.implode('<br>',$added_links), 'Новые страницы на сайте '.$site['site'], 
                    'monitoring', 1);
        }
    }
//    if($added_links){
//        foreach($added_links as $site_id => $links){
//            $db->query("UPDATE {monitoring} SET events = events + ?i WHERE id = ?i", array(count($links['links']), $site_id));
//            $mailer->send_message('<a href="'.$all_configs['prefix'].'manage/marketing">Перейти в мониторинг</a>'
//                                 .'<br>Новые ссылки: <br>'.implode('<br>',$links['links']), 'Новые страницы на сайте '.$links['name'], 
//                    'monitoring', 1);
//        }
//    }
//    if($links_queries){
//        $db->query("INSERT IGNORE INTO {monitoring_links}(site_id, link) "
//                  ."VALUES?q", array(implode(',', $links_queries)));
//    }
    //monitoring_diff_links($sites);
}

function monitoring_links($sites = array()){
    global $all_configs;
    $db = $all_configs['db'];
    
    $and = '';
    if($sites){
        $and = $db->makeQuery("AND l.site_id IN (?l)", array(array_keys($sites)));
    }
    
    try{
        #WTF?
        $links = $db->makeQuery("SELECT l.id, l.link, m.site, l.site_id 
            FROM {monitoring_links} as l 
            LEFT JOIN {monitoring} as m ON m.id = l.site_id 
            LEFT JOIN {monitoring_data} as d ON d.link_id = l.id 
            WHERE NOT EXISTS (SELECT id FROM {monitoring_data} WHERE date > CURDATE() AND link_id = l.id) 
            ?q
            GROUP BY l.id", array($and));
        
        print_r($links);
        echo '<hr>';
        
        $links = $db->query($links)->assoc();
        
        shuffle($links);
    }catch(Exception $e){
        $error = $e->getMessage();
    }
//    $site_ids = array();
    $data = array();
    foreach($links as $link){
        $error = '';
        $html = '';
        try{
//            if(!array_key_exists($link['site_id'], $site_ids)){
//                $db->query("UPDATE {monitoring} SET last_scan_date = NOW() WHERE id = ?i", array($link['site_id']));
//                $site_ids[$link['site_id']] = $link['site_id'];
//            }
            $html = curl_get($link['link']);
            echo $link['link'] . '<br>';
            flush();
            ob_flush();
            
        }catch(Exception $e){
            $error = $e->getMessage();
            echo $link['link'] . ' - ' . $error . '<br>';
            flush();
            ob_flush();
        }
//        $data[] = $db->makeQuery("(?i,NOW(),?,?)", array($link['id'], $html, $error));
        try{
            $db->query("INSERT INTO {monitoring_data}(link_id,date,html,error) "
                      ."VALUES (?i,NOW(),COMPRESS(?),?)", array($link['id'], $html, $error));
            usleep(rand(50000, 300000));
        }catch(Exception $e){
//            send_mail('kv@fon.in.ua', 'Ошибка крон restore: monitoring_links', $e->getMessage());
        }
    }
//    try{
//        $db->query("INSERT INTO {monitoring_data}(link_id,date,html,error) "
//                  ."VALUES ?q", array(implode(',', $data)));
//    }catch(Exception $e){ }
}

function monitoring_diff_links($sites = array()){
    global $all_configs;
    $db = $all_configs['db'];
    
    $where = '';
    if($sites){
        $where = $db->makeQuery(" WHERE l.site_id IN (?l) ", array(array_keys($sites)));
    }
    $links = $db->query("SELECT l.id, l.link, m.id as site_id, m.site FROM {monitoring_links} as l "
                       ."LEFT JOIN {monitoring} as m ON m.id = l.site_id ".$where)->assoc();
    
    include 'finediff/finediff.php';
    $mailer = new Mailer($all_configs);
    
    $data = array();
    $messages = array();
    foreach($links as $link_d){
        $id = $link_d['id'];
        $link = $link_d['link'];
        
        $link_data = $db->query("SELECT id, link_id, date, error, CONVERT(UNCOMPRESS(html) USING utf8) as html "
                               ."FROM {monitoring_data} WHERE link_id = ?i "
                               ."ORDER BY date DESC LIMIT 2", array($id), 'assoc');
        if(isset($link_data[1])){
            $link_last_id = $link_data[0]['id'];
            preg_match_all("/<body>([\d\D]+)<\/body>/", $link_data[0]['html'], $last_html);
            $link_last_data = strip_tags(isset($last_html[0][0]) ? $last_html[0][0] : $link_data[0]['html']);

            $link_prev_id = $link_data[1]['id'];
            preg_match_all("/<body>([\d\D]+)<\/body>/", $link_data[1]['html'], $prev_html);
            $link_prev_data = strip_tags(isset($prev_html[0][0]) ? $prev_html[0][0] : $link_data[1]['html']);

            $link_last_data = preg_replace("/\s+/", " ", $link_last_data);
            $link_prev_data = preg_replace("/\s+/", " ", $link_prev_data);
            
            if($link_last_data != $link_prev_data){
                $opcodes = FineDiff::getDiffOpcodes($link_prev_data, $link_last_data, FineDiff::$wordGranularity);
                $to_text = FineDiff::renderDiffToHTMLFromOpcodes($link_prev_data, $opcodes);
            }else{
                $to_text = '';
            }
//            $data[] = $db->makeQuery("(?i, ?i, ?, NOW())", array($link_last_id, $link_prev_id, $to_text));
            $indert_id = 0;
            try{
                $indert_id = $db->query("INSERT IGNORE INTO {monitoring_diff_history}(last_link_id,prev_link_id,diff_data,date) "
                          ."VALUES (?i, ?i, COMPRESS(?), NOW())", array($link_last_id, $link_prev_id, $to_text), 'id');
            }catch(Exception $e){
                send_mail('ragenoir@gmail.com', 'Ошибка крон restore: monitoring_diff_links', $e->getMessage());
            }
            if($to_text && $indert_id){
                $messages[$link_d['site_id']]['site'] = $link_d['site'];
                $messages[$link_d['site_id']]['links'][] = array(
                    'link' => $link
                );
            }
        }
    }
    if($messages){
        foreach($messages as $site_id => $mes){
            $l_content = '';
            foreach($mes['links'] as $l){
                $l_content .= htmlspecialchars($l['link']).'<br>';
            }
            $db->query("UPDATE {monitoring} SET events = events + ?i WHERE id = ?i", array(count($mes['links']), $site_id));
            $mailer->send_message('<a href="'.$all_configs['prefix'].'manage/marketing#history'.$site_id.'">Посмотреть</a>'
                                 .'<br>Измененилсь ссылки: <br>'.$l_content, 'Изменения на сайте '.$mes['site'], 
                    'monitoring', 1);
        }
    }
//    $db->query("INSERT IGNORE INTO {monitoring_diff_history}(last_link_id,prev_link_id,diff_data,date) "
//              ."VALUES ?q", array(implode(',', $data)));
}

// имитация конфига
function all_configs(){
    global $db, $prefix, $path;

    $configs = Configs::get();
    $settings = $db->query("SELECT name, value FROM {settings}", array())->vars();

    return array(
        'db' => $db,
        'prefix' => $prefix,
        'manageprefix' => $prefix . 'manage/',
        'path' => $path,
        'managepath' => $path . 'manage/',
        'settings' => $settings,
        'configs' => $configs,
    );
}