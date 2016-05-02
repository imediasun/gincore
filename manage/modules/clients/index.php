<?php

require_once __DIR__ . '/../../Core/View.php';

$modulename[20] = 'clients';
$modulemenu[20] = l('Клиенты');
$moduleactive[20] = !$ifauth['is_2'];

class Clients
{
    /** @var View */
    protected $view;
    private $mod_submenu;
    public $error;
    public $all_configs;
    public $count_on_page;

    /**
     * clients constructor.
     * @param $all_configs
     */
    function __construct(&$all_configs)
    {
        $this->mod_submenu = self::get_submenu();
        $this->all_configs = $all_configs;
        $this->count_on_page = count_on_page();
        $this->view = new View($all_configs);
        global $input_html;

        require_once($this->all_configs['sitepath'] . 'shop/model.class.php');

        if (!$this->all_configs['oRole']->hasPrivilege('edit-goods')) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p class="alert alert-danger">' . l('У Вас нет прав для просмотра клиентов') . '</p></div>';
        }

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        if (isset($_POST) && !empty($_POST)) {
            $this->check_post($_POST);
        }

        $error = ($this->error ? '<p class="alert alert-danger">' . $this->error . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></p>' : '');

        $input_html['mcontent'] = $error . $this->gencontent();
    }


    /**
     * @param $post
     * @return string
     */
    private function check_post($post)
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['clients-manage-page'];

        // поиск товаров
        if (isset($_POST['search'])) {
            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0]
                . (array_key_exists(1,
                    $this->all_configs['arrequest']) ? '/' . $this->all_configs['arrequest'][1] : '');
            if (isset($_POST['text']) && mb_strlen(trim($_POST['text']), 'UTF-8') > 0) {
                $text = trim($_POST['text']);

                header("Location:" . $url . '?s=' . urlencode($text));
                exit;
            } else {
                header("Location:" . $url);
                exit;
            }
        }

        if (isset($post['edit-client'])) {
            // редактируем клиента

            if (!isset($this->all_configs['arrequest'][2]) || $this->all_configs['arrequest'][2] == 0) {

//                return 'error client';
                $email = mb_strlen(trim($post['email']), 'UTF-8') > 0 ? trim(htmlspecialchars($post['email'])) : null;
                $post['phone'] = trim(preg_replace('/[^0-9]/', '', $post['phone']));
                $id = '';

                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->error = l('Электронная почта указана неверно.');
                    return false;
                }

                if (empty($email) && empty($post['phone'])) {
                    $this->error = l('Укажите телефон или почту.');
                    return false;
                }

                if (!empty($email)) {
                    $id = $this->all_configs['db']->query('SELECT id FROM {clients} WHERE email=?', array($email),
                        'el');
                    if ($id) {
                        $this->error = l('Такой e-mail уже зарегистрирован.');
                        return false;
                    }
                }

                require_once($this->all_configs['sitepath'] . 'mail.php');
                require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
                require_once($this->all_configs['sitepath'] . 'shop/model.class.php');
                $access = new access($this->all_configs, false);
                $result = $access->registration($post);

                if ($result['new'] == false) {
                    $this->error = $result['msg'];
                    return false;
                }
                return header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $result['id']);
                exit;
            } else {

                require_once($this->all_configs['sitepath'] . 'mail.php');
                require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
                require_once($this->all_configs['sitepath'] . 'shop/model.class.php');
                $access = new access($this->all_configs, false);
                $post['id'] = $this->all_configs['arrequest'][2];
                $result = $access->edit($post);

                if ($result['state'] == false) {
                    $this->error = $result['msg'];
                    return false;
                }
            }

        } elseif (isset($post['edit-goods-reviews'])) {
            // редактирование комментария товара
            if (!isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 0) {
                return 'error comment';
            }
            $reviews = $this->all_configs['db']->query('SELECT avail, rating, goods_id, inform, user_id FROM {reviews} WHERE id=?i AND goods_id>0',
                array($this->all_configs['arrequest'][3]))->row();
            if (!$reviews) {
                return 'error comment';
            }

            if (isset($post['avail'])) {
                $avail = 1;
            } else {
                $avail = 0;
            }

            $ar = $this->all_configs['db']->query('UPDATE {reviews} SET text=?, advantages=?, disadvantages=?, rating=?i, avail=?i, usefulness_yes=?i,
                usefulness_no=?i WHERE id=?i',
                array(
                    trim($post['text']),
                    trim($post['advantages']),
                    trim($post['disadvantages']),
                    $post['rating'],
                    $avail,
                    $post['usefulness_yes'],
                    $post['usefulness_no'],
                    $this->all_configs['arrequest'][3]
                ))->ar();

            if ($ar) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'update-goods-reviews', $mod_id, $this->all_configs['arrequest'][3]));
            }

            $avg = $this->all_configs['db']->query('SELECT avg(rating) as avg, count(id) as count
                FROM {reviews} WHERE avail=1 AND goods_id=?i AND rating <= 5', array($reviews['goods_id']))->row();

            if ($avg) {
                $this->all_configs['db']->query('UPDATE {goods} SET rating=?, votes=?i WHERE id=?i',
                    array($avg['avg'], $avg['count'], $reviews['goods_id']));
            } else {
                $this->all_configs['db']->query('UPDATE {goods} SET rating=?, votes=?i WHERE id=?i',
                    array(0, 0, $reviews['goods_id']));
            }


        } elseif (isset($post['edit-shop-reviews'])) {
            // редактирование комментария магазина
            if (!isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 0) {
                return 'error comment';
            }

            $reviews = $this->all_configs['db']->query('SELECT * FROM {reviews} WHERE shop=1 AND id=?i',
                array($this->all_configs['arrequest'][3]))->row();

            if (!$reviews) {
                return 'error comment';
            }

            if (isset($post['avail'])) {
                $avail = 1;
            } else {
                $avail = 0;
            }

            $ar = $this->all_configs['db']->query('UPDATE {reviews} SET text=?, status=?i, become_status=?i, avail=?i WHERE id=?i',
                array(
                    $post['text'],
                    $post['status'],
                    $post['become_status'],
                    $avail,
                    $this->all_configs['arrequest'][3]
                ))->ar();
            if ($ar) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'update-shop-reviews', $mod_id, $this->all_configs['arrequest'][3]));
            }
        } elseif (isset($post['add-goods-reviews'])) {

            $avail = 0;
            if (isset($post['avail'])) {
                $avail = 1;
            }

            if (!isset($post['clients']) || $post['clients'] == 0) {
                return l('Выберите клиента');
            }
            if (!isset($post['goods']) || $post['goods'] == 0) {
                return l('Выберите продукт');
            }

            $id = $this->all_configs['db']->query('INSERT INTO {reviews} (`user_id`, `goods_id`, `text`, `rating`, `usefulness_yes`, `usefulness_no`, `avail`) VALUES (?i, ?i, ?, ?, ?i, ?i, ?i)',
                array(
                    intval($post['clients']),
                    intval($post['goods']),
                    trim($post['text']),
                    trim($post['rating']),
                    intval($post['usefulness_yes']),
                    intval($post['usefulness_no']),
                    $avail
                ), 'id');
            if ($id) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'add-review', $mod_id, $this->all_configs['arrequest'][3]));
            }
            header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2] . '/' . $id);
        } elseif (isset($post['add-shop-reviews'])) {
            if (!isset($post['clients']) || intval($post['clients']) == 0) {
                return l('Выберите клинта');
            }

            $avail = 0;
            if (isset($post['avail'])) {
                $avail = 1;
            }

            $id = $this->all_configs['db']->query('INSERT INTO {reviews} (client, status, become_status, text, avail, shop) VALUES (?i, ?i, ?i, ?, ?i, ?i)',
                array(
                    intval($post['clients']),
                    intval($post['status']),
                    intval($post['become_status']),
                    trim($post['text']),
                    $avail,
                    1
                ), 'id');

            header("Location:" . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2] . '/' . $id);
        } elseif (isset($post['edit-approve-reviews'])) {
            // редактирование не утвержденного комментария
            if (!isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 0) {
                return 'error comment';
            }

            $reviews = $this->all_configs['db']->query('SELECT * FROM {parser_comments_approval} WHERE id=?i',
                array($this->all_configs['arrequest'][3]))->row();

            if (!$reviews) {
                return 'error comment';
            }

            $ar = $this->all_configs['db']->query('UPDATE {parser_comments_approval} SET fio=?, content=?, advantages=?, disadvantages=?,
                  rating=?i, usefulness_yes=?i, usefulness_no=?i, date_add=? WHERE id=?i',
                array(
                    trim($post['fio']),
                    trim($post['text']),
                    trim($post['advantages']),
                    $post['disadvantages'],
                    $post['rating'],
                    $post['usefulness_yes'],
                    $post['usefulness_no'],
                    date("Y-m-d", strtotime($post['date_add'])),
                    $this->all_configs['arrequest'][3]
                ))->ar();
            if ($ar) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'update-shop-reviews', $mod_id, $this->all_configs['arrequest'][3]));
            }

            header("Location:" . $this->all_configs['prefix'] . 'clients/approve-reviews#comment_parse_edit-' . $this->all_configs['arrequest'][3]);
            exit;
        }

        header("Location:" . $_SERVER['REQUEST_URI']);
    }

    /**
     * @return string
     */
    private function genmenu()
    {
        return $this->view->renderFile('clients/genmenu', array(
            'arrequest' => $this->all_configs['arrequest']
        ));
    }


    /**
     * @return string
     */
    private function gencontent()
    {
        if (!isset($this->all_configs['arrequest'][1])) {
            return $this->main_page();
        }

        switch ($this->all_configs['arrequest'][1]) {
            case 'create':
                return $this->create_client();
            case 'inactive_clients':
                if (!isset($this->all_configs['arrequest'][2])) {
                    return $this->clients_list(true);
                }
                break;
            case 'goods-reviews':
                if (!isset($this->all_configs['arrequest'][2])) {
                    return $this->goods_reviews();
                }

                if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create' && !isset($this->all_configs['arrequest'][3])) {
                    return $this->add_goods_reviews();
                }

                if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create') {
                    return $this->create_goods_reviews();
                }
                break;
            case 'shop-reviews':
                if (!isset($this->all_configs['arrequest'][2])) {
                    return $this->shop_reviews();
                }
                if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create' && !isset($this->all_configs['arrequest'][3])) {
                    return $this->add_shop_reviews();
                }

                if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create') {
                    return $this->create_shop_reviews();
                }
                break;
            case 'approve-reviews':
                if (!isset($this->all_configs['arrequest'][2])) {
                    return $this->approve_reviews();
                }
                if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create') {
                    return $this->create_approve_reviews();
                }
                break;

            case 'group_clients':
                return $this->group_clients();
            default:
        }

    }

    /**
     * @return string
     * @throws Exception
     */
    private function main_page()
    {
        if (!empty($_GET['export'])) {
            $this->export();
        }
        $tab = isset($_GET['tab']) ? $_GET['tab'] : '';
        if (!$tab) {
            header('Location: ' . $this->all_configs['prefix'] . 'clients?tab=clients' . (isset($_GET['s']) ? '&s=' . $_GET['s'] : ''));
            exit;
        } else {
            switch ($tab) {
                case 'clients':
                    $content = $this->clients_list();
                    break;
                case 'calls':
                    $content = get_service('crm/calls')->get_all_calls_list();
                    break;
                case 'requests':
                    $content = get_service('crm/requests')->get_all_requests_list();
                    break;
                case 'statistics':
                    $content = get_service('crm/statistics')->get_stats();
                    break;
                case 'group_clients':
                    $content = $this->group_clients();
                    break;
                default:
                    $content = '';
                    break;
            }
        }
        return $this->view->renderFile('clients/main_page', array(
            'content' => $content,
            'mod_submenu' => $this->mod_submenu
        ));
    }

    /**
     *
     */
    private function export()
    {
        $export_fields = array(
            // id and phones exports by default
            'email',
            'fio',
            'legal_address',
            'date_add'
        );
        $clients = db()->query("SELECT id," . implode(',', $export_fields) . ", "
            . "(SELECT GROUP_CONCAT(phone) "
            . "FROM {clients_phones} WHERE client_id = c.id) as phones "
            . "FROM {clients} as c "
            . "WHERE id > 1 ORDER BY c.id")->assoc();
        $data = array();
        $data[] = array_merge(array(
            'id',
            'phones'
        ), $export_fields);
        foreach ($clients as $client) {
            $client_data = array();
            $client_data[] = $client['id'];
            $client_data[] =  't. '.$client['phones'];
            foreach ($export_fields as $exf) {
                $client_data[] = in_array($exf, array('fio', 'legal_address')) ? iconv('UTF-8', 'CP1251', $client[$exf]) : $client[$exf];
            }
            $data[] = $client_data;
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=clients.csv');
        $out = fopen('php://output', 'w');
        foreach ($data as $row) {
            fputcsv($out, $row, ';', '"');
        }
        fclose($out);
        exit;
    }

    /**
     * @return string
     */
    private function group_clients()
    {
        return $this->view->renderFile('clients/group_clients');
    }

    /**
     * @param bool $inactive
     * @return string
     */
    private function clients_list($inactive = false)
    {
        $count_on_page = $this->count_on_page;//50;
        $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;

        // активен/неактивен
        $query = '';
        // поиск
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            // 0xA0 deleted because search not work  if search string contain russian letter 'P'
            $s = str_replace(array('&nbsp;', ' '), '%', trim($_GET['s']));
            $query = $this->all_configs['db']->makeQuery('?query AND (cl.fio LIKE "%?e%" OR cl.email LIKE "%?e%"
                    OR cl.phone LIKE "%?e%" OR p.phone LIKE "%?e%")',
                array($query, $s, $s, $s, $s));
        }
        $clients = $this->all_configs['db']->query('SELECT cl.* FROM {clients} as cl
                LEFT JOIN {clients_phones} as p ON p.client_id=cl.id AND p.phone<>cl.phone
                WHERE 1=1 ?query GROUP BY cl.id ORDER BY cl.date_add DESC LIMIT ?i, ?i',
            array($query, $skip, $count_on_page))->assoc();
        $count = $this->all_configs['db']->query('SELECT COUNT(DISTINCT cl.id) FROM {clients} as cl
                LEFT JOIN {clients_phones} as p ON p.client_id=cl.id AND p.phone<>cl.phone
                WHERE 1=1 ?query',
            array($query))->el();

        return $this->view->renderFile('clients/clients_list', array(
            'count' => $count,
            'count_page' => ceil($count / $count_on_page),
            'clients' => $clients,
            'arrequest' => $this->all_configs['arrequest'],
            'tags' => $this->getTags()
        ));
    }

    /**
     * @return string
     */
    private function goods_reviews()
    {
        return $this->get_goods_reviews();
    }

    /**
     * @return string
     */
    private function shop_reviews()
    {
        return $this->get_shop_reviews();
    }

    /**
     * @return string
     */
    private function create_new_client()
    {
        $contractors = $this->all_configs['db']->query('SELECT title, id FROM {contractors} ORDER BY title',
            array())->assoc();
        return $this->view->renderFile('clients/create_new_client', array(
            'contractors' => $contractors,
            'tags' => $this->getTags()
        ));
    }

    /**
     * @param      $user_id
     * @param bool $show_inputs
     * @return array|string
     */
    function phones($user_id, $show_inputs = true)
    {
        $phones = array();

        if ($user_id > 0) {
            $phones = $this->all_configs['db']->query('SELECT p.id, p.phone FROM {clients_phones} as p, {clients} as c
                    WHERE p.client_id=?i AND c.id=p.client_id',
                array($user_id))->vars();
        }

        return $show_inputs ? $this->view->renderFile('clients/phones', array(
            'phones' => $phones
        )) : $phones;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function create_client()
    {
        if (!isset($this->all_configs['arrequest'][2]) || $this->all_configs['arrequest'][2] < 1) {
            return
                '<a class="btn btn-default" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '">' . l('Список клиентов') . '</a><br><br>' .
                $this->create_new_client();
        }

        // достаем инфу о клиенте
        $client = $this->all_configs['db']->query('SELECT * FROM {clients} WHERE id=?i',
            array($this->all_configs['arrequest'][2]))->row();

        if (!$client) {
            return '<p  class="text-error">' . l('Нет такого клиента') . '</p>';
        }

        $new_call_id = isset($_GET['new_call']) ? $_GET['new_call'] : 0;

        return $this->view->renderFile('clients/create_client', array(
            'ordersList' => $this->getOrdersList($client),
            'newCallForm' => $new_call_id ? $this->newCallForm($new_call_id, $client) : '',
            'contractorsList' => $this->getContractorsList($client),
            'tagsList' => $this->getTagsList($client),
            'new_call_id' => $new_call_id,
            'arrequest' => $this->all_configs['arrequest'],
            'phones' => $this->phones($client['id'], false),
            'tags' => $this->getTags(),
            'client' => $client
        ));
    }

    /**
     * @return string
     */
    private function create_goods_reviews()
    {
        if (!isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 1) {
            return '<p  class="text-error">' . l('Нет такого отзыва') . '</p>';
        }

        $review = $this->all_configs['db']->query('SELECT r.*, c.email, g.title, r.fio, c.id as user_id FROM {reviews} as r
            LEFT JOIN (SELECT email,id FROM {clients})c on r.user_id=c.id
            LEFT JOIN (SELECT title,id FROM {goods})g on r.goods_id=g.id
            WHERE r.id=?i AND r.goods_id>0 AND (r.parent_id IS NULL OR r.parent_id="")',
            array($this->all_configs['arrequest'][3]))->row();

        if (!$review) {
            return '<p  class="text-error">' . l('Нет такого отзыва') . '</p>';
        }
        // комментарии к отзыву о товаре
        $comments = $this->all_configs['db']->query('SELECT r.`user_id`, r.`id`, r.`text`, r.`avail`, r.`date`, c.`email`, c.id as client_id FROM {reviews} as r
            LEFT JOIN (SELECT `email`, `id` FROM {clients})c ON c.`id`=r.`user_id`
            WHERE r.parent_id=?i', array($this->all_configs['arrequest'][3]))->assoc();

        return $this->view->renderFile('clients/create_goods_reviews', array(
            'arrequest' => $this->all_configs['arrequest'],
            'comments' => $comments,
            'review' => $review,
        ));
    }

    /**
     * @return string
     */
    private function add_shop_reviews()
    {
        return $this->view->renderFile('clients/add_shop_reviews');
    }

    /**
     * @return string
     */
    private function approve_reviews()
    {
        $limit = $this->count_on_page;//50;
        $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? (($_GET['p'] - 1) * $limit) : 0;

        $count_comments = $this->all_configs['db']->query('SELECT count(ca.id) FROM {parser_comments_approval} as ca, {goods} as g
            WHERE ca.approve IS NULL AND g.id=ca.goods_id', array())->el();

        $comments = $this->all_configs['db']->query('SELECT ca.id, ca.market_id, ca.fio, ca.content, ca.advantages, ca.disadvantages,
            ca.rating, ca.usefulness_yes, ca.usefulness_no, ca.goods_id, g.title, ca.goods_id
            FROM {parser_comments_approval} as ca, {goods} as g 
            WHERE ca.approve IS NULL AND g.id=ca.goods_id 
            ORDER BY ca.date_add DESC 
            LIMIT ?i, ?i', array($skip, $limit))->assoc();

        require_once($this->all_configs['path'] . 'parser/configs_parse.php');
        $parser_configs = Configs_Parse::get();


        return $this->view->renderFile('clients/approve_reviews', array(
            'count_page' => ceil($count_comments / $limit),
            'count_comments' => $count_comments,
            'parser_configs' => $parser_configs,
            'comments' => $comments
        ));
    }

    /**
     * @return string
     */
    private function create_approve_reviews()
    {
        if (!isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 1) {
            return '<p  class="text-error">' . l('Нет такого отзыва') . '</p>';
        }

        $review = $this->all_configs['db']->query('SELECT r.*, g.title FROM {parser_comments_approval} as r
            LEFT JOIN (SELECT title,id FROM {goods})g on r.goods_id=g.id
            WHERE r.id=?i AND r.goods_id>0', array($this->all_configs['arrequest'][3]))->row();

        return $this->view->renderFile('clients/create_approve_reviews', array(
            'review' => $review
        ));
    }

    /**
     * @return string
     */
    private function create_shop_reviews()
    {
        if (!isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 1) {
            return '<p  class="text-error">' . l('Нет такого отзыва') . '</p>';
        }

        $review = $this->all_configs['db']->query('SELECT r.*, c.email, r.fio, c.id as user_id FROM {reviews} as r
            LEFT JOIN (SELECT email, id FROM {clients})c ON c.id=r.user_id
            WHERE r.id=?i AND r.shop=1', array($this->all_configs['arrequest'][3]))->row();

        return $this->view->renderFile('clients/create_shop_reviews', array(
            'review' => $review
        ));
    }

    /**
     * @param null $user_id
     * @return string
     */
    private function get_goods_reviews($user_id = null)
    {
        $limit = $this->count_on_page;//100;
        $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? (($_GET['p'] - 1) * $limit) : 0;
        // достаем все отзывы о товарах
        if (!$user_id) {
            $count_reviews = $this->all_configs['db']->query('SELECT COUNT(r.id)
                FROM {reviews} as r
                WHERE r.goods_id > 0 AND r.parent_id IS NULL', array())->el();
            $reviews = $this->all_configs['db']->query('SELECT r.*, c.email, if(c.id>0, c.fio, r.fio) as fio, c.phone, c.id as user_id
                FROM {reviews} as r
                LEFT JOIN (SELECT email, id, fio, phone FROM {clients})c ON c.id=r.user_id
                WHERE r.goods_id > 0 AND parent_id IS NULL ORDER BY `date` DESC LIMIT ?i, ?i',
                array($skip, $limit))->assoc();
        } else {
            $count_reviews = $this->all_configs['db']->query('SELECT COUNT(r.id)
                FROM {reviews} as r
                WHERE r.goods_id > 0 AND r.user_id=?i  AND parent_id IS NULL',
                array($user_id))->el();
            $reviews = $this->all_configs['db']->query('SELECT r.*, c.email, if(c.id>0,c.fio,r.fio) as fio, c.phone, c.id as user_id
                FROM {reviews} as r
                LEFT JOIN (SELECT email, id, fio, phone FROM {clients})c ON c.id=r.user_id
                WHERE r.goods_id > 0 AND r.user_id=?i  AND parent_id IS NULL ORDER BY `date` DESC LIMIT ?i, ?i',
                array($user_id, $skip, $limit))->assoc();
        }

        return $this->view->renderFile('clients/get_goods_reviews', array(
            'reviews' => $reviews,
            'count_page' => ceil($count_reviews / $limit),
            'count_reviews' => $count_reviews
        ));
    }

    /**
     * @param null $user_id
     * @return string
     */
    private function get_shop_reviews($user_id = null)
    {
        // достаем все отзывы о магазине
        if ($user_id) {
            $reviews = $this->all_configs['db']->query('SELECT r.*, c.email, if(c.id>0,c.fio,r.fio) as fio, c.phone, c.id as user_id FROM {reviews} as r
                LEFT JOIN (SELECT email, id, fio, phone FROM {clients})c ON c.id=r.user_id
                WHERE r.shop=1 AND r.user_id=?i ORDER BY `date` DESC', array($user_id))->assoc();
        } else {
            $reviews = $this->all_configs['db']->query('SELECT r.*, c.email, if(c.id>0,c.fio,r.fio) as fio, c.phone, c.id as user_id FROM {reviews} as r
                LEFT JOIN (SELECT email, id, fio, phone FROM {clients})c ON c.id=r.user_id
                WHERE r.shop=1 ORDER BY `date` DESC')->assoc();
        }

        return $this->view->renderFile('clients/get_shop_reviews', array(
            'reviews' => $reviews
        ));
    }

    /**
     * @return string
     */
    private function add_goods_reviews()
    {
        return $this->view->renderFile('clients/add_goods_reviews');
    }

    /**
     * @return bool
     */
    function ajax()
    {
        $act = isset($_GET['act']) ? $_GET['act'] : '';
        if (!$act) {
            $act = isset($_POST['act']) ? $_POST['act'] : '';
        }

        $data = array(
            'state' => false
        );

        // подтвреждение комментария
        if ($act == 'confirm_parse_comment') {
            if (!isset($_POST['comment_id']) || $_POST['comment_id'] == 0) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого комментария не существует'), 'error' => true));
                exit;
            }
            $comment = $this->all_configs['db']->query('SELECT content, goods_id, date_publish, usefulness_yes, usefulness_no,
                    rating, advantages, disadvantages, fio, date_publish
                FROM {parser_comments_approval} WHERE id=?i', array($_POST['comment_id']))->row();
            $avail = (isset($_POST['avail']) && $_POST['avail'] == 1) ? 1 : null;

            if (!$comment) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого комментария не существует'), 'error' => true));
                exit;
            }

            $this->all_configs['db']->query('UPDATE {parser_comments_approval} SET approve=?i WHERE id=?i',
                array(2, $_POST['comment_id']));
            $id = $this->all_configs['db']->query('INSERT INTO {reviews} (text, avail, goods_id, date, usefulness_yes, usefulness_no,
                    rating, advantages, disadvantages, fio) VALUES (?, ?n, ?i, ?, ?i, ?i, ?i, ?, ?, ?)',
                array(
                    $comment['content'],
                    $avail,
                    $comment['goods_id'],
                    $comment['date_publish'],
                    $comment['usefulness_yes'],
                    $comment['usefulness_no'],
                    $comment['rating'],
                    $comment['advantages'],
                    $comment['disadvantages'],
                    $comment['fio']
                ), 'id');

            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array(
                'message' => 'Успешно',
                'response' => '<a href="' . $this->all_configs['prefix'] . 'clients/goods-reviews/create/' . $id . '">' . l('Редактировать') . '</a>'
            ));
            exit;
        }

        // соединение клиентов
        if ($act == 'group-clients') {

            require_once($this->all_configs['sitepath'] . 'mail.php');
            require_once($this->all_configs['sitepath'] . 'shop/access.class.php');

            $c1_id = isset($_POST['clients'][1]) ? $_POST['clients'][1] : 0;
            $c2_id = isset($_POST['clients'][2]) ? $_POST['clients'][2] : 0;

            $client_1 = $this->all_configs['db']->query("SELECT * FROM {clients} WHERE id = ?i", array($c1_id), 'row');
            $client_2 = $this->all_configs['db']->query("SELECT * FROM {clients} WHERE id = ?i", array($c2_id), 'row');

            if (!$client_1) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Клиент 1 не найден')));
                exit;
            }
            if (!$client_2) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Клиент 2 не найден')));
                exit;
            }
            if ($client_1 && $client_2 && $client_1['id'] != $client_2['id']
            ) {

                $master_id = $slave_id = $phone = null;

                $master_id = $client_1['id'];
                $slave_id = $client_2['id'];
                if ($master_id && $slave_id) {
                    // телефон
                    $this->all_configs['db']->query('UPDATE {clients_phones} SET client_id = ?i
                                                     WHERE client_id = ?i',
                        array($master_id, $slave_id));
                    // заказы
                    $this->all_configs['db']->query('UPDATE {orders} SET user_id=?i WHERE user_id=?i',
                        array($master_id, $slave_id));
                    // звонки
                    $this->all_configs['db']->query('UPDATE {crm_calls} SET client_id=?i WHERE client_id=?i',
                        array($master_id, $slave_id));
                    // фио, email, контрагент
                    $personal_data = array();
                    if (!$client_1['fio'] && $client_2['fio']) {
                        $personal_data[] = $this->all_configs['db']->makeQuery(" fio = ? ", array($client_2['fio']));
                    }
                    if (!$client_1['email'] && $client_2['email']) {
                        $personal_data[] = $this->all_configs['db']->makeQuery(" email = ? ",
                            array($client_2['email']));
                    }
                    if (!$client_1['legal_address'] && $client_2['legal_address']) {
                        $personal_data[] = $this->all_configs['db']->makeQuery(" legal_address = ? ",
                            array($client_2['legal_address']));
                    }
                    if (!$client_1['contractor_id'] && $client_2['contractor_id']) {
                        $personal_data[] = $this->all_configs['db']->makeQuery(" contractor_id = ?i ",
                            array($client_2['contractor_id']));
                    }
                    if ($personal_data) {
                        $this->all_configs['db']->query("UPDATE {clients} SET ?q WHERE id = ?i",
                            array(implode(',', $personal_data), $master_id));
                    }
                    // удаляем клиента 2
                    $this->all_configs['db']->query('DELETE FROM {clients} WHERE id=?i LIMIT 1',
                        array($slave_id));

                    header("Content-Type: application/json; charset=UTF-8");
                    echo json_encode(array('message' => l('Операция прошла успешно')));
                    exit;
                }
            }
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => l('Этих клиентов соединить нельзя')));
            exit;
        }

        // изменить пароль
        if ($act == 'change-client-password') {

            if (isset($_POST['pk']) && is_numeric($_POST['pk']) && isset($_POST['value'])) {

                $ar = $this->all_configs['db']->query('UPDATE {clients} SET pass=?
                    WHERE id=?i LIMIT 1', array(sha1($_POST['value']), $_POST['pk']))->ar();

                header("Content-Type: application/json; charset=UTF-8");
                exit;
            }

        }

        // опроверждение комментария
        if ($act == 'refute_parse_comment') {
            if (!isset($_POST['comment_id']) || $_POST['comment_id'] == 0) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого комментария не существует'), 'error' => true));
                exit;
            }

            $ar = $this->all_configs['db']->query('UPDATE {parser_comments_approval} SET approve=?i WHERE id=?i',
                array(1, $_POST['comment_id']))->ar();

            if ($ar) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Успешно'), 'response' => l('Комментарий успешно удален')));
                exit;
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого комментария не существует'), 'error' => true));
                exit;
            }
        }

        if ($act == 'short_update_client') {
            require_once($this->all_configs['sitepath'] . 'mail.php');
            require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
            require_once($this->all_configs['sitepath'] . 'shop/model.class.php');
            $access = new access($this->all_configs, false);
            $post = $_GET; // хз, аджакс жквери отправляет почему-то гетом
            $post['id'] = $_GET['client_id'];
            $result = $access->edit($post);

            if ($result['state'] == false) {
                $data['state'] = false;
                $data['msg'] = $result['msg'];
                return false;
            } else {
                $call_id = !empty($_GET['call_id']) ? $_GET['call_id'] : null;
                $code = !empty($_GET['code']) ? $_GET['code'] : null;
                $referer_id = !empty($_GET['referer_id']) ? $_GET['referer_id'] : null;
                $code = $code ? $this->all_configs['db']->makeQuery(" ? ", array($code)) : 'null';
                $referer_id = $referer_id ? $this->all_configs['db']->makeQuery(" ?i ", array($referer_id)) : 'null';
                // записываем в звонок источник и реферер
                $this->all_configs['db']->query("
                    UPDATE {crm_calls} 
                    SET code = ?q, referer_id = ?q 
                    WHERE id = ?i
                ", array($code, $referer_id, $call_id));
                $data['state'] = true;
                $data['msg'] = l('Изменения сохранены');
            }
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    /**
     * @return array
     */
    public static function get_submenu()
    {
        return array(
            array(
                'url' => '?tab=clients',
                'name' => l('Клиенты')
            ),
            array(
                'url' => '?tab=calls',
                'name' => l('Звонки')
            ),
            array(
                'url' => '?tab=requests',
                'name' => l('Заявки')
            ),
            array(
                'url' => '?tab=statistics',
                'name' => l('Отчеты')
            ),
            array(
                'url' => '?tab=group_clients',
                'name' => l('Склеить клиентов')
            ),
        );
    }

    /**
     * @param $new_call_id
     * @param $client
     * @return string
     * @throws Exception
     */
    private function newCallForm($new_call_id, $client)
    {
        $calldata = get_service('crm/calls')->get_call($new_call_id);
        // ставим статус принят
        if (isset($_GET['get_call'])) {
            $operator_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
            $this->all_configs['db']->query("UPDATE {crm_calls} SET type = null, operator_id = ?i "
                . "WHERE id = ?i", array($operator_id, $new_call_id));
        }
        $code = (isset($calldata['code']) ? $calldata['code'] : null);
        return $this->view->renderFile('clients/new_call_form', array(
            'client' => $client,
            'new_call_id' => $new_call_id,
            'calldata' => $calldata,
            'code' => $code,
            'code_exists' => get_service('crm/calls')->code_exists($code),
            'phones' => $this->phones($client['id'])
        ));
    }

    /**
     * @param $client
     * @return string
     */
    private function getContractorsList($client)
    {
        $contractors = $this->all_configs['db']->query('SELECT title, id FROM {contractors} ORDER BY title',
            array())->assoc();

        return $this->view->renderFile('clients/contractors_list', array(
            'contractors' => $contractors,
            'client' => $client
        ));
    }

    /**
     * @param $client
     * @return string
     */
    private function getOrdersList($client)
    {
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('c_id' => $client['id']));
        $skip = $queries['skip'];
        $count_on_page = $this->count_on_page;//$queries['count_on_page'];
        $query = $queries['query'];
        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page);
        $count = $this->all_configs['manageModel']->get_count_clients_orders($query);

        return $this->view->renderFile('clients/get_orders_list', array(
            'orders' => $orders,
            'count' => $count,
            'count_on_page' => $count_on_page
        ));
    }

    /**
     * @param $client
     * @return string
     */
    private function getTagsList($client)
    {
        return $this->view->renderFile('clients/tags_list', array(
            'tags' => $this->getTags(),
            'client' => $client
        ));
    }

    /**
     * @return mixed
     */
    private function getTags()
    {
        return $this->all_configs['db']->query('SELECT color, title, id FROM {tags} ORDER BY title',
            array())->assoc('id');
    }
}

