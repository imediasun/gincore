<?php

error_reporting(E_ALL);

################################################################################
################################################################################
if (!isLocalRequest()) {
	//header("HTTP/1.0 404 Not Found");
	//exit;
}

/**
 *  Проверка локальности выполнения запроса (предотвращение вызова скрипта GET запросом не из крона)
 * @return bool
 */
function isLocalRequest()
{
	return $_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'];
}
################################################################################
################################################################################





$timezone_set_off = true;

include 'inc_config.php';
include 'inc_func.php';
include 'mail.php';
include 'manage/configs.php';
include 'manage/inc_func_lang.php';
$all_configs = all_configs();
require_once 'manage/core_langs.php';

set_time_limit(14400); // 4 часа
$date_begin = date("Y-m-d H:i:s");
$error = '';
$all_configs = all_configs();
$db = $all_configs['db'];

$act = isset($_GET['act']) ? $_GET['act'] : '';


switch($act){

    // достаем аналитику с гугла
    case 'crm_ga_analitics':
        
        $date_first = '2015-08-03'; // дата старта

        // каждый день начиная с даты старта кроме тех дней что уже спарсены
        $dates = $db->query('SELECT date FROM (
              SELECT date_add(?, INTERVAL n5.num * 10000 + n4.num * 1000 + n3.num * 100 + n2.num * 10 + n1.num DAY) as date FROM
                (SELECT 0 as num UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                  UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) n1,
                (SELECT 0 as num UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                  UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) n2,
                (SELECT 0 as num UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                  UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) n3,
                (SELECT 0 as num UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                  UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) n4,
                (SELECT 0 as num UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                  UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) n5
              ) a WHERE date >= ? AND date < DATE(NOW()) AND date NOT IN (SELECT date FROM {crm_analytics}) ORDER BY date',
            array($date_first, $date_first))->vars();

        if ($dates) {

            // гугл настройки
            $analytics = getService();
            $profileId = 72717248; //из #report/visitors-overview/a41153725w70527458p72717248/
            
            $metrics = array(
                'ga:users',
                'ga:newUsers',
                'ga:sessions',
                'ga:pageviews',
                'ga:bounces',
                'ga:bounceRate',
            );
            $metrics = implode(",", $metrics);
            $optParams = array(
                'dimensions' => 'ga:sourceMedium',
            );

            // патерны для referer_id
            $referers = get_referers_config();

            foreach ($dates as $date) {
                // достаем аналитику
                
                $results = $analytics->data_ga->get(
                    'ga:' . $profileId, $date, $date, $metrics, $optParams);
                
                //$analitics = $google->get_analitics($date, $date, $metrics, $optParams);

                if (gettype($results) == 'object') {

                    $users = $newUsers = $sessions = $pageviews = $bounces = $bounceRate = 0;
                    $socialNetwork = $referer_id = null;

                    if (gettype($results->rows) == 'array' && count($results->rows) > 0) {
                        foreach($results->rows as $row) {

                            $socialNetwork = isset($row[0]) ? $row[0] : null;
                            $users = isset($row[1]) ? $row[1] : 0;
                            $newUsers = isset($row[2]) ? $row[2] : 0;
                            $sessions = isset($row[3]) ? $row[3] : 0;
                            $pageviews = isset($row[4]) ? $row[4] : 0;
                            $bounces = isset($row[5]) ? $row[5] : 0;
                            $bounceRate = isset($row[6]) ? $row[6] : 0;
                            $referer_id = get_referer_id($referers, $socialNetwork);

                            $db->query('INSERT INTO {crm_analytics}
                                    (users, newUsers, sessions, pageviews, bounces, bounceRate, socialNetwork, referer_id, date)
                                    VALUES (?i, ?i, ?i, ?i, ?i, ?, ?n, ?n, ?) ON DUPLICATE KEY UPDATE users=VALUES(users),
                                    newUsers=VALUES(newUsers), sessions=VALUES(sessions), pageviews=VALUES(pageviews),
                                    bounces=VALUES(bounces), bounceRate=VALUES(bounceRate), referer_id=VALUES(referer_id)',
                                array($users, $newUsers, $sessions, $pageviews, $bounces, $bounceRate, $socialNetwork, $referer_id, $date));
                        }
                    } else {
                        $db->query('INSERT INTO {crm_analytics}
                                (users, newUsers, sessions, pageviews, bounces, bounceRate, socialNetwork, date)
                                VALUES (?i, ?i, ?i, ?i, ?i, ?, ?n, ?) ON DUPLICATE KEY
                                UPDATE users=VALUES(users), newUsers=VALUES(newUsers), sessions=VALUES(sessions),
                                pageviews=VALUES(pageviews), bounces=VALUES(bounces), bounceRate=VALUES(bounceRate)',
                            array($users, $newUsers, $sessions, $pageviews, $bounces, $bounceRate, $socialNetwork, $date));
                    }
                } else {
                    break;
                }
            }
        }

        break;
        
    // источник на код на скидку
    case 'crm_ga_discount_code':
        
        //$date = '2015-08-03'; // дата старта - вчера
        //$date = date("Y-m-d");
        $date = date("Y-m-d", strtotime('-1 day'));
        
                // гугл настройки
        $analytics = getService();
        $profileId = 72717248; //из #report/visitors-overview/a41153725w70527458p72717248/
        
            $metrics = array(
//                'ga:users',
//                'ga:newUsers',
//                'ga:sessions',
                'ga:pageviews',
//                'ga:bounces',
//                'ga:bounceRate',

            );
            $metrics = implode(",", $metrics);
            $optParams = array(
                'dimensions' => 'ga:dimension1, ga:source, ga:medium, ga:keyword, ga:dimension2'
            );
            
            $results = $analytics->data_ga->get(
                    'ga:' . $profileId, $date, $date, $metrics, $optParams);

            if (gettype($results) == 'object') {

                    if (gettype($results->rows) == 'array' && count($results->rows) > 0) {
                        foreach($results->rows as $row) {
                         
                             $db->query('UPDATE {visitors_code} '
                                     . 'SET ga_source=?, ga_medium=?, ga_keyword=? '
                                     . 'WHERE visitor_id = ?i LIMIT 1', 
                                     array($row[1], $row[2], $row[3], $row[0]));
                            
                        }
                    }
            }
        
        break;
        
        // проставляем источники звонкам и заказам по коду
        case 'crm_calls_referers_by_discount_code':
            // mode 1 - апдейтим все звонки у которых есть код и нет источника
            // mode 2 - апдейтим все звонки у которых есть код
            $mode = isset($_GET['mode']) ? $_GET['mode'] : 1;
            $mode_q_calls = $mode_q_orders = '';
            switch($mode){
                case 1:
                    $mode_q_calls = $db->makeQuery(" AND (cl.referer_id IS NULL OR cl.referer_id = 0)", null);
                    $mode_q_orders = $db->makeQuery(" AND (o.referer_id IS NULL OR o.referer_id = 0)", null);
                break;
                case 2:
                break;
            }
            
            // звонки 
            $calls = $db->query("SELECT cl.id, upper(cl.code) as code, c.ga_source, c.ga_medium, sc.referer_id "
                               ."FROM {crm_calls} as cl "
                               ."LEFT JOIN {visitors_code} as c ON upper(c.code) = upper(cl.code) "
                               ."LEFT JOIN {visitors_system_codes} as sc ON upper(sc.code) = upper(cl.code) "
                               ."WHERE cl.code IS NOT NULL AND cl.code != '' ?q", array($mode_q_calls), 'assoc:id');
            $referers = get_referers_config();
            foreach($calls as $id => $call){
                if($call['referer_id']){
                    $referer_id = $call['referer_id'];
                }else{
                    $network = $call['ga_source'].' / '.$call['ga_medium'];
                    $referer_id = get_referer_id($referers, $network);
                } 
                $db->query("UPDATE {crm_calls} SET referer_id = ?i WHERE id = ?i", array($referer_id, $id));
            }
            
            // заказы (без привязаных заявок) 
            $orders = $db->query("SELECT o.id, upper(o.code) as code, c.ga_source, c.ga_medium, sc.referer_id "
                                ."FROM {orders} as o "
                                ."LEFT JOIN {crm_requests} as r ON r.order_id = o.id "
                                ."LEFT JOIN {visitors_code} as c ON upper(c.code) = upper(o.code) "
                                ."LEFT JOIN {visitors_system_codes} as sc ON upper(sc.code) = upper(o.code) "
                                ."WHERE r.id IS NULL AND o.code IS NOT NULL "
                                                   ."AND o.code != '' ?q", array($mode_q_orders), 'assoc:id');
            foreach($orders as $id => $order){
                if($order['referer_id']){
                    $referer_id = $order['referer_id'];
                }else{
                    $network = $order['ga_source'].' / '.$order['ga_medium'];
                    $referer_id = get_referer_id($referers, $network);
                }
                $db->query("UPDATE {orders} SET referer_id = ?i WHERE id = ?i", array($referer_id, $id));
            }
            
        break;
        
        // удаляем старые коды
        case 'remove_old_codes':
            $decayed_time = 86400 * 14; // время 
//            $decayed_time = 1; // время 
            $decayed_date = date('Y-m-d H:i:s', time() - $decayed_time);
            
            $db->query("DELETE vc FROM {visitors_code} as vc "
//            $codes = $db->query("SELECT vc.* FROM {visitors_code} as vc "
                      ."LEFT JOIN {orders} as o ON o.code = vc.code "
                      ."LEFT JOIN {crm_calls} as c ON c.code = vc.code "
                      ."WHERE o.id IS NULL AND c.id IS NULL AND vc.updated_at <= ?", 
                            array($decayed_date));
//                            array($decayed_date), 'assoc');
//            print_r($codes);
//            exit;
        break;
}



function get_referers_config(){
    $referers = array(
        1 => array('name' => 'Google Adwords', 'regexp' => '/( *google *\/ *cpc *)/'),
        2 => array('name' => 'Google Organic', 'regexp' => '/( *\( *not *set *\) *\/ *\( *not *set *\) *)|( *google *\/ *organic *)/'),
        3 => array('name' => 'Yandex Direct', 'regexp' => '/( *yandex *\/ *cpc *)/'),
        4 => array('name' => 'Yandex Organic', 'regexp' => '/( *yandex *\/ *(referral|organic) *)/'),
        5 => array('name' => 'VK', 'regexp' => '/( *vk\.com *\/ *referral *)/'),
        //6 => array('name' => 'VK Ad', 'regexp' => '//'),
        7 => array('name' => 'Twitter', 'regexp' => '/( *t\.co *\/ *referral *)/'),
        8 => array('name' => 'Forum, Blog', 'regexp' => '/(.*\/ *forum *)|( *forum *\/.*)/'),
        9 => array('name' => 'Facebook', 'regexp' => '/(.*\.facebook\.com *\/ *referral *)/'),
        //10 => array('name' => 'Facebook Ad', 'regexp' => '//'),
        11 => array('name' => '(Direct)', 'regexp' => '/( *\(direct\) *\/ *\(none\) *)/'),
        12 => array('name' => 'Other', 'regexp' => '/(.*)/'), // (,*/ *referral *)
        13 => array('name' => 'Email', 'regexp' => '/(.*\/ *email *)/'),
        14 => array('name' => 'Youtube', 'regexp' => '/( *youtube\.com *\/ *referral *)/'),
        15 => array('name' => 'Other organic', 'regexp' => '/(.*\/ *organic *)/'),
        24 => array('name' => 'Nanofantiki', 'regexp' => '/(.*\/ *nanofantiki*)/'),
    );
    return $referers;
}
function get_referer_id($referers, $network){
    $referer_id = null;
    if($network){
        foreach($referers as $ref_id => $referer){
            preg_match($referer['regexp'], $network, $matches);
            if(count($matches) > 1){
                $referer_id = $ref_id;
                break;
            }
        }
    }
    return $referer_id;
}


function getService()
{
    // Creates and returns the Analytics service object.
    // Load the Google API PHP Client Library.
    require_once 'src/google-api-php-client/src/Google/autoload.php';

    // Use the developers console and replace the values with your
    // service account email, and relative location of your key file.
    $service_account_email = '922219840654-i6nvdprgvehl9jslb62684r1afkt6ors@developer.gserviceaccount.com';
    $key_file_location = 'src/google-api-php-client/Keys/My Project-bea99941c6d0.p12';

    // Create and configure a new client object.
    $client = new Google_Client();
    $client->setApplicationName("RestoreAnalytics");
    $analytics = new Google_Service_Analytics($client);

    // Read the generated client_secrets.p12 key.
    $key = file_get_contents($key_file_location);
    $cred = new Google_Auth_AssertionCredentials(
            $service_account_email, array(Google_Service_Analytics::ANALYTICS_READONLY), $key
    );
    $client->setAssertionCredentials($cred);
    if ($client->getAuth()->isAccessTokenExpired()) {
        $client->getAuth()->refreshTokenWithAssertion($cred);
    }

    return $analytics;
}

################################################################################
// имитация конфига
function all_configs()
{
    global $db, $prefix, $path;

    // обновляем для языка
    Configs::getInstance()->set_configs();
    $configs = Configs::getInstance()->get();
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


$url = trim((string)$_SERVER['REQUEST_URI']);
$errors = trim((string)is_array($error) ? implode(' ', $error) : $error);

$all_configs['db']->query('INSERT INTO {cron_history} (date_begin, url, errors) VALUES (?, ?, ?)',
    array($date_begin, $url, $errors));