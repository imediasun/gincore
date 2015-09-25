<?php

// auth
if (!isset($_GET['merchant']) || $_GET['merchant'] != 'f239fj4329fj3iofj3') {
    //exit;
}

// configs
include __DIR__ . '/inc_config.php';
include __DIR__ . '/inc_func.php';
include __DIR__ . '/inc_settings.php';
global $all_configs;

//a basic API class
class tl_exch extends SoapClient
{
    protected $all_configs;
    private $mod_id = 25;

    function __construct()
    {
        global $all_configs;
        $this->all_configs = $all_configs;
    }

    public static function v4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // write requests
    private function logger($request = array(), $xml_response = '')
    {
        $data = array(
            'server' => json_encode($_SERVER),
            'request' => json_encode($_REQUEST),
            'data' => json_encode($request),
            'xml_request' => file_get_contents('php://input'),
            'xml_response' => trim($xml_response),
            'order_id' => is_array($request) && isset($request['number']) ? $request['number'] : null,
            'uid' => is_array($request) && isset($request['uid']) ? $request['uid'] : null,
        );

        $logger_id = null;

        try {
            // try to logger
            $logger_id = $this->all_configs['db']->query(
                'INSERT INTO {merchant_logger} (server, request, `data`, xml_request, xml_response, order_id, uid)
                VALUES (?, ?, ?, ?, ?, ?n, ?n)', array_values($data), 'id');
        } catch (Exception $e) {
            // duplicate uid
            $text = '<p><b>server: </b> ' . $data['server'] . '</p>' .
                '<p><b>request: </b> ' . $data['request'] . '</p>' .
                '<p><b>data: </b> ' . $data['data'] . '</p>' .
                '<p><b>xml_request: </b> ' . $data['xml_request'] . '</p>' .
                '<p><b>xml_response: </b> ' . $data['xml_response'] . '</p>' .
                '<p><b>order_id: </b> ' . $data['order_id'] . '</p>' .
                '<p><b>uid: </b> ' . $data['uid'] . '</p>';

            // send to email duplicate exception
            send_mail($this->all_configs['settings']['email'], 'Повторная транзакция', $text);
        }

        return $logger_id;
    }

    // array to xml
    function array_to_xml($array)
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><ClientsOrder xmlns=\"http://xmembers.net\" xmlns:xs=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"></ClientsOrder>");

        $recursive = function ($array, &$xml) use (&$recursive) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    if (!is_numeric($key)) {
                        $subnode = $xml->addChild("$key");
                        $recursive($value, $subnode);
                    } else {
                        //$subnode = $xml->addChild("$key");
                        //$recursive($value, $subnode);
                        $recursive($value, $xml);
                    }
                } else {
                    $xml->addChild("$key", htmlspecialchars("$value"));
                }
            }
        };

        $recursive($array, $xml);

        $xml = $xml->asXML();

        return $xml;
    }

    // get order and return array
    function get_order($data)
    {
        $query = '';
        $number = is_array($data) && isset($data['number']) ? $data['number'] : null;
        $order = null;

        // filer by date add
        if (isset($this->all_configs['settings']['terminal_orders_days']) && intval($this->all_configs['settings']['terminal_orders_days']) > 0) {
            $query = $this->all_configs['db']->makeQuery('AND o.date_add>DATE_ADD(NOW(), INTERVAL -?i DAY)',
                array(intval($this->all_configs['settings']['terminal_orders_days'])));
        }

        if ($number) {
            // find order by id
            $order = $this->all_configs['db']->query('SELECT o.* FROM {orders} as o WHERE o.id=? ?query',
                array($number, $query))->row();

            if ($order) {
                // get order goods
                $order = $this->order_goods($order, $number);
            } else {
                // find serial
                $serial = suppliers_order_generate_serial(array('serial' => mb_strtolower($number, 'UTF-8')), false);
                $order = $this->all_configs['db']->query(
                        'SELECT i.* FROM {warehouses_goods_items} as i, {warehouses} as w
                        WHERE ?query AND i.order_id IS NULL AND w.id=i.wh_id AND w.consider_all=1',
                    array(is_integer($serial) ? 'i.id=' . $serial : 'i.serial=' . $number))->row();
                if ($order) {
                    $order['item_id'] = $order['id'];
                    $order['id'] = $serial;
                    $order['result'] = 1;
                    $order['goods'][$order['goods_id']] = $this->all_configs['db']->query(
                        'SELECT * FROM {goods} WHERE id=?i', array($order['goods_id']))->row();
                    $order['fio'] = $this->all_configs['db']->query('SELECT fio FROM {clients} WHERE id=?',
                        array($this->all_configs['configs']['erp-so-client-terminal']))->el();
                    $order['goods'][$order['goods_id']]['count'] = 1;
                    $order['sum_paid'] = 0;
                    $course = $this->all_configs['settings']['grn-cash'] / 100;
                    $order['sum'] = $order['debt'] = $order['price'] * $course;
                }
            }
        }

        return $order;
    }

    function price($price, $course = 100, $x = 5)
    {
        $n = $price / $course;
        return (ceil($n) % $x === 0) ? ceil($n) : round(($n + $x / 2) / $x) * $x;
    }

    function order_goods($order, $number)
    {
        if ($order) {
            $order['goods'] = $this->all_configs['db']->query('SELECT * FROM {categories} WHERE id=?i',
                array($order['category_id']))->assoc();

            if ($order['goods']) {
                // if order is closed
                if ($order['sum'] == $order['sum_paid']) {
                    $order['result'] = 1;
                    return $order;
                }
                // if pay
                if ($order['sum'] > $order['sum_paid']) {
                    $order['result'] = 1;
                    $order['debt'] = $order['sum'] - $order['sum_paid'];
                    return $order;
                }
                // if return
                if ($order['sum'] < $order['sum_paid']) {
                    if ($number == $order['id'] . '-' . $order['return_id']) {
                        $order['result'] = 2;
                        $order['debt'] = $order['sum'] - $order['sum_paid'];
                        return $order;
                    }
                }
            }
        }

        return null;
    }

    // order to xml
    private function return_order($order, $number)
    {
        if ($order) {
            $goods = isset($order['goods']) && is_array($order['goods']) ? $order['goods'] : array();

            $response = array(
                'id' => isset($order['result']) ? $order['result'] : 3,
                'uid' => $this->v4(),
                'number' => $order['id'],
                'date' => date('Y-m-d H:i:s'), //$order['date_add'],
                'costumer_fio' => ($order['fio']),
                'currency' => 'Гривна',
                'summ' => $this->price($order['sum']),
                'count' => count($goods),
                'debt' => isset($order['debt']) ? $this->price($order['debt']) : 0, // долг по заказу
                'paid' =>  $this->price($order['sum_paid']),
            );
            if ($goods) {
                $n = 1;
                foreach ($goods as $product) {
                    $img = 'images/logo.jpg';
                    /*$product_img = $this->all_configs['configs']['images-path-sc'] . $product['goods_id'] . '/' . $product['attachment'];
                    if (file_exists($this->all_configs['sitepath'] . $product_img) && exif_imagetype($this->all_configs['sitepath'] . $product_img) == IMAGETYPE_JPEG) {
                        $img = $product_img;
                    }*/
                    $path_parts = full_pathinfo($img);
                    $extension = $path_parts['extension'];
                    $binary = base64_encode(file_get_contents($this->all_configs['sitepath'] . $img));
                    $response[$product['id']]['products'] = array(
                        'n' => $n,
                        'name' => $product['title'],
                        'image' => array(
                            'name' => 'http://' . $_SERVER['HTTP_HOST'] . $this->all_configs['siteprefix'] . $img,
                            'extension' => $extension,
                            'binaryData' => $binary,
                        ),
                        'count' => $product['count'],
                        'price' => $this->price($order['sum']),
                        'summ' => $this->price($product['price'] * $product['count']),
                    );
                    $n++;
                }
            }
        } else {
            $response = array(
                'id' => 0,
                'uid' => 'Order can\'t be fined by string [' . $number . ']',
            );
        }

        return $this->array_to_xml($response);
    }

    function GetOrder($number)
    {
        $order = $this->get_order(array('number' => $number));
        $xml_response = $this->return_order($order, $number);

        $this->logger(array('number' => $number), $xml_response);

        return $xml_response;
    }

    function ClientPayment($number, $uid, $in = 0, $out = 0, $acceptsumm = 0, $outsumm1 = 0, $outsumm2 = 0, $reject = 0, $rejected = 0)
    {
        $response = 'Заказ не найден';
        $result = null;

        $data = array(
            'number' => $number,
            'uid' => $uid,
            'in' => $in, // сумма внесенная в купюроприемник
            'out' => $out, // сумма сдачи выданная из диспенсера
            'acceptsumm' => $acceptsumm, // сумма в купюроприемнике после оплаты
            'outsumm1' => $outsumm1, // сумма в верхней кассете диспенсера после оплаты
            'outsumm2' => $outsumm2, // сумма в нижней кассете диспенсера после оплаты
            'reject' => $reject, // сумма отбракованных купюр в диспенсере во время выдачи сдачи
            'rejected' => $rejected // сумма в кассете отбракованных купюр диспенсера после оплаты
        );

        $logger_id = $this->logger($data);

        if (!$logger_id) {
            $response = 'Повторная транзакция';
            return $response;
        }

        $order = $this->get_order($data);

        if ($order) {
            $state = true;
            // sold item
            if (array_key_exists('item_id', $order)) {
                // create order to sold
                $result = $this->all_configs['chains']->sold_items(array(
                    'items' => $order['item_id'],
                    'clients' => $this->all_configs['configs']['erp-so-client-terminal'],
                    'price' => $this->price($order['sum']),
                ), $this->mod_id);

                // result
                if ($result && isset($result['id']) && isset($result['state']) && $result['state'] == true) {
                    $order['id'] = $result['id'];
                } else {
                    $state = false;
                    $result['msg'] = isset($result['message']) ? $result['message'] : 'Произошла ошибка в системе';
                }
            }

            if ($state == true) {
                // add transaction in
                $result = $this->all_configs['chains']->create_transaction(array(
                    'transaction_type' => 2,
                    'cashbox_from' => $this->all_configs['configs']['erp-so-cashbox-terminal'],
                    'cashbox_to' => $this->all_configs['configs']['erp-so-cashbox-terminal'],
                    'amount_from' => 0,
                    'amount_to' => $in - $out,
                    'cashbox_currencies_from' => $this->all_configs['suppliers_orders']->currency_clients_orders,
                    'cashbox_currencies_to' => $this->all_configs['suppliers_orders']->currency_clients_orders,
                    'client_order_id' => $order['id'],
                    'date_transaction' => date("Y-m-d H:i:s"),
                    'confirm' => true,
                ), $this->mod_id);
            }
        }

        // result
        if ($result && isset($result['state']) && $result['state'] == true) {
            $response = 0;
        } else {
            $response = $result && isset($result['msg']) ? $result['msg'] : 'Произошла ошибка в системе';

            // send accountant message
            require_once $this->all_configs['sitepath'] . 'mail.php';
            $messages = new Mailer($this->all_configs);
            $content = 'Заказ №' . $number . ' сумма ' . ($in - $out) . ''.viewCurrency().'';
            $messages->send_message($content, 'Терминал, ошибка при создании транзакции внесении', 'accounting', 1);
        }

        // update logger xml_response
        $this->all_configs['db']->query('UPDATE {merchant_logger} SET xml_response=? WHERE id=?i',
            array($response, $logger_id));

        return $response;
    }

    function PaymentOf($number, $uid, $out = 0, $acceptsumm = 0, $outsumm1 = 0, $outsumm2 = 0, $reject = 0, $rejected = 0)
    {
        $response = 'Заказ не найден';
        $result = null;

        $data = array(
            'number' => $number,
            'uid' => $uid,
            'out' => $out,
            'acceptsumm' => $acceptsumm,
            'outsumm1' => $outsumm1,
            'outsumm2' => $outsumm2,
            'reject' => $reject,
            'rejected' => $rejected
        );

        $logger_id = $this->logger($data);

        if (!$logger_id) {
            $response = 'Повторная транзакция';
            return $response;
        }

        $order = $this->get_order($data);

        if ($order) {
            // add transaction out
            $result = $this->all_configs['chains']->create_transaction(array(
                'transaction_type' => 1,
                'cashbox_from' => $this->all_configs['configs']['erp-so-cashbox-terminal'],
                'cashbox_to' => $this->all_configs['configs']['erp-so-cashbox-terminal'],
                'amount_from' => $out,
                'amount_to' => 0,
                'cashbox_currencies_from' => $this->all_configs['suppliers_orders']->currency_clients_orders,
                'cashbox_currencies_to' => $this->all_configs['suppliers_orders']->currency_clients_orders,
                'client_order_id' => $order['id'],
                'date_transaction' => date("Y-m-d H:i:s"),
                'confirm' => true,
            ), $this->mod_id);
        }

        // result
        if ($result && isset($result['state']) && $result['state'] == true) {
            $response = 0;
        } else {
            $response = $result && isset($result['msg']) ? $result['msg'] : 'Произошла ошибка в системе';

            // send accountant message
            require_once $this->all_configs['sitepath'] . 'mail.php';
            $messages = new Mailer($this->all_configs);
            $content = 'Заказ №' . $number . ' сумма ' . $out . ''.viewCurrency().'';
            $messages->send_message($content, 'Терминал, ошибка при создании транзакции выплаты', 'accounting', 1);
        }

        // update logger xml_response
        $this->all_configs['db']->query('UPDATE {merchant_logger} SET xml_response=? WHERE id=?i',
            array($response, $logger_id));

        return $response;
    }

    function FixChanges($in = 0, $out = 0, $acceptsumm = 0, $outsumm1 = 0, $outsumm2 = 0, $reject = 0, $rejected = 0)
    {
        $response = 0;

        $data = array(
            'in' => $in,
            'out' => $out,
            'acceptsumm' => $acceptsumm,
            'outsumm1' => $outsumm1,
            'outsumm2' => $outsumm2,
            'reject' => $reject,
            'rejected' => $rejected
        );

        $this->logger($data);

        return $response;
    }

}

// imitation merchant
if (isset($_GET['merchant'])) {//exit;
    header('Content-Type: text/html; charset=utf-8');
    ini_set('display_errors',1);
    ini_set('display_startup_errors',1);
    error_reporting(-1);

    echo '<p>**********************************help*******************************************</p>';
    echo '<p><b>&init=GetOrder|ClientPayment|PaymentOf|FixChanges</b> - function request</p>';
    echo '<p><b>&in=</b> - sum entered in the cash acceptor</p>';
    echo '<p><b>&out=</b> - sum issued of the cash acceptor</p>';
    echo '<p><b>&number=</b> - order id or phone number</p>';
    echo '<p><b>&uid=</b> - unique ID</p>';
    echo '<p>*********************************************************************************</p>';

    $number = isset($_GET['number']) ? $_GET['number'] : 0;
    $uid = isset($_GET['uid']) ? $_GET['uid'] : null;
    $in = isset($_GET['in']) ? $_GET['in'] : 0;
    $out = isset($_GET['out']) ? $_GET['out'] : 0;
    $acceptsumm = isset($_GET['acceptsumm']) ? $_GET['acceptsumm'] : 0;
    $outsumm1 = isset($_GET['outsumm1']) ? $_GET['outsumm1'] : 0;
    $outsumm2 = isset($_GET['outsumm2']) ? $_GET['outsumm2'] : 0;
    $reject = isset($_GET['reject']) ? $_GET['reject'] : 0;
    $rejected = isset($_GET['rejected']) ? $_GET['rejected'] : 0;

    if (isset($_GET['init']) && $_GET['init'] == 'GetOrder') {
        $api = new tl_exch();
        echo '<pre>';
        print_r(simplexml_load_string($api->GetOrder($number)));
        echo '</pre>';
    }
    if (isset($_GET['init']) && $_GET['init'] == 'ClientPayment') {
        $api = new tl_exch();
        echo $api->ClientPayment($number, $uid, $in, $out, $acceptsumm, $outsumm1, $outsumm2, $reject, $rejected);
    }
    if (isset($_GET['init']) && $_GET['init'] == 'PaymentOf') {
        $api = new tl_exch();
        echo $api->PaymentOf($number, $uid, $out, $acceptsumm, $outsumm1, $outsumm2, $reject, $rejected);
    }
    if (isset($_GET['init']) && $_GET['init'] == 'FixChanges') {
        $api = new tl_exch();
        echo $api->FixChanges($in, $out, $acceptsumm, $outsumm1, $outsumm2, $reject, $rejected);
    }
    exit;
}

// отключаем кеширование WSDL-файла для тестирования
ini_set("soap.wsdl_cache_enabled", "0");

// options
$opts = array(
    'uri' => "http://{$_SERVER['HTTP_HOST']}{$all_configs['prefix']}orderservice.php",
    'wsdl' => "http://{$_SERVER['HTTP_HOST']}{$all_configs['prefix']}orderservice.wsdl.php",
    //'login' => 'terminal',
    //'password' => 'lanimret',
);

// create a new SOAP server
$server = new SoapServer(null, $opts);

// attach the API class to the SOAP Server
$server->setClass('tl_exch');

// start the SOAP requests handler
$server->handle();
exit;