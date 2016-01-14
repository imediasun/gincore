<?php

$modulename[20] = 'clients';
$modulemenu[20] = l('Клиенты');
$moduleactive[20] = !$ifauth['is_2'];

class clients
{
    private $mod_submenu;
    public $error;
    public $all_configs;
    public $count_on_page;

    function __construct(&$all_configs)
    {
        $this->mod_submenu = self::get_submenu();
        $this->all_configs = $all_configs;
        $this->count_on_page = count_on_page();
        global $input_html;

        require_once($this->all_configs['sitepath'] . 'shop/model.class.php');

        if ( !$this->all_configs['oRole']->hasPrivilege('edit-goods') ) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p class="alert alert-danger">' . l('У Вас нет прав для просмотра клиентов') . '</p></div>';
        }

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }

        if ( isset($_POST) && !empty($_POST) )
            $this->check_post($_POST);

        $error = ($this->error ? '<p class="alert alert-danger">'.$this->error.'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></p>' : '');

        //if($ifauth['is_2']) return false;

        //$input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $error . $this->gencontent();
    }


    private function check_post($post)
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['clients-manage-page'];

        // поиск товаров
        if (isset($_POST['search'])) {
            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0]
                . (array_key_exists(1, $this->all_configs['arrequest']) ? '/' . $this->all_configs['arrequest'][1] : '');
            if (isset($_POST['text']) && mb_strlen(trim($_POST['text']), 'UTF-8') > 0) {
                $text = trim($_POST['text']);

                header("Location:" . $url . '?s=' . urlencode($text));
                exit;
            } else {
                header("Location:" . $url);
                exit;
            }
        }

        if ( isset($post['edit-client']) ) {
            // редактируем клиента
            /*            if ( !filter_var($post['email'], FILTER_VALIDATE_EMAIL) )
                            return 'error email';

                        if ( !preg_replace( '/[^0-9]/', '', $post['phone']) )
                            return 'error phone';
            */

            if ( !isset($this->all_configs['arrequest'][2]) || $this->all_configs['arrequest'][2] == 0 ) {

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
                    $id = $this->all_configs['db']->query('SELECT id FROM {clients} WHERE email=?', array($email), 'el');
                    if($id) {
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
                /*try {
                    $new_id = $this->all_configs['db']->query('INSERT INTO {clients} SET email=?n, phone=?, fio=?, contractor_id=?i',
                        array($email, trim($post['phone']), trim($post['fio']), intval($post['contractor_id'])), 'id');
                } catch (Exception $e) {
                    $this->error = 'Такой клиент уже зарегистрирован.';
                    return false;
                }
                if($new_id){
                    return header("Location:". $_SERVER['REQUEST_URI'].'/'.$new_id);
                }*/
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
                /*try {

                    $phones = is_phone($post['phone']);

                    if ($phones) {
                        $this->all_configs['db']->query('DELETE FROM {clients_phones} WHERE client_id=?i AND phone NOT IN (?list)',
                            array($this->all_configs['arrequest'][2], $phones));
                        foreach ($phones as $phone) {
                            $this->all_configs['db']->query('INSERT IGNORE INTO {clients_phones} (client_id, phone)
                                VALUES (?i, ?)', array($this->all_configs['arrequest'][2], $phone));
                        }
                        reset($phones);
                    } else {
                        $this->all_configs['db']->query('DELETE FROM {clients_phones} WHERE client_id=?i',
                            array($this->all_configs['arrequest'][2]));
                    }

                    $email = is_email($post['email']) ? is_email($post['email']) : null;
                    $phone = $phones ? current($phones) : null;

                    // клиенты которые используются системой
                    if (array_key_exists('manage-system-clients', $this->all_configs['configs'])
                        && is_array($this->all_configs['configs']['manage-system-clients'])
                        && count($this->all_configs['configs']['manage-system-clients']) > 0
                        && in_array($this->all_configs['arrequest'][2], $this->all_configs['configs']['manage-system-clients'])
                        && !$this->all_configs['oRole']->hasPrivilege('site-administration')) {

                        header("Location:". $_SERVER['REQUEST_URI']);
                    }
                    $this->all_configs['db']->query('UPDATE {clients} SET email=?n, phone=?n, fio=?, contractor_id=?i
                            WHERE id=?i', array($email, $phone, trim($post['fio']), intval($post['contractor_id']),
                        $this->all_configs['arrequest'][2]));

                } catch (Exception $e) {
                    $this->error = 'Такой клиент уже зарегистрирован.';
                    return false;
                }*/
            }

        } elseif ( isset($post['edit-goods-reviews']) ) {
            // редактирование комментария товара
            if ( !isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 0 )
                return 'error comment';
            $reviews = $this->all_configs['db']->query('SELECT avail, rating, goods_id, inform, user_id FROM {reviews} WHERE id=?i AND goods_id>0', array($this->all_configs['arrequest'][3]))->row();
            if ( !$reviews )
                return 'error comment';

            if ( isset($post['avail']) )
                $avail = 1;
            else
                $avail = 0;

            $ar = $this->all_configs['db']->query('UPDATE {reviews} SET text=?, advantages=?, disadvantages=?, rating=?i, avail=?i, usefulness_yes=?i,
                usefulness_no=?i WHERE id=?i',
                array(trim($post['text']), trim($post['advantages']), trim($post['disadvantages']), $post['rating'], $avail, $post['usefulness_yes'],
                    $post['usefulness_no'], $this->all_configs['arrequest'][3]))->ar();

            if ( $ar ) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'update-goods-reviews', $mod_id, $this->all_configs['arrequest'][3]));
            }

            $avg = $this->all_configs['db']->query('SELECT avg(rating) as avg, count(id) as count
                FROM {reviews} WHERE avail=1 AND goods_id=?i AND rating <= 5', array($reviews['goods_id']))->row();

            if ($avg)
                $this->all_configs['db']->query('UPDATE {goods} SET rating=?, votes=?i WHERE id=?i', array($avg['avg'], $avg['count'], $reviews['goods_id']));
            else
                $this->all_configs['db']->query('UPDATE {goods} SET rating=?, votes=?i WHERE id=?i', array(0, 0, $reviews['goods_id']));


            /*if ( isset($post['clients']) && isset($post['clients']) ) {
                foreach ( $post['clients'] as $k=>$client) {
                    if ( $client < 1 )
                        continue;

                    $avail = 0;
                    if ( isset($post['comments_avail']) && isset($post['comments_avail'][$k]))
                        $avail = 1;
                    $comments = '';
                    if ( isset($post['comments_avail']) && isset($post['clients'][$k]) )
                        $comments = $post['clients'][$k];

                    $id = $this->all_configs['db']->query('SELECT id FROM {reviews} WHERE id=?i AND parent_id=?i', array($k, $this->all_configs['arrequest'][3]))->el();
                    if ( $id ){
                        $ar = $this->all_configs['db']->query('UPDATE {reviews} SET avail=?, goods_id=?i, text=? WHERE id=?i',
                            array($avail, $reviews['goods_id'], $comments, $k))->ar();
                        if ( $ar ) {
                            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                                array($user_id, 'edit-comment', $mod_id, $this->all_configs['arrequest'][3]));
                        }
                    } else {
                        $id = $this->all_configs['db']->query('INSERT INTO {reviews} (avail, text, user_id, parent_id, goods_id) VALUES (?i, ?, ?i, ?i, ?i)',
                            array($avail, $comments, $client, $this->all_configs['arrequest'][3], $reviews['goods_id']), $id);
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'add-comment', $mod_id, $this->all_configs['arrequest'][3]));
                    }
                    if ( $id && $avail == 1 && $reviews['inform'] == 1 ) {
                        $did = $this->all_configs['db']->query('SELECT id FROM {clients_notices} WHERE object_id=?i AND user_id=?i AND type=?',
                            array($id, $reviews['user_id'], "comment-inform"))->row();
                        if ( !$did ) {
                            $this->all_configs['db']->query('INSERT INTO {clients_notices} (user_id, object_id, type, date_send) VALUES (?i, ?i, ?, NOW())',
                                array($reviews['user_id'], $id, "comment-inform"));

                            require_once($this->all_configs['sitepath'] . 'mail.php');
                            $mailer = new Mailer($this->all_configs['db'], $this->all_configs['siteprefix'], $this->all_configs['sitepath'], $this->all_configs['configs']);
                            $email = $this->all_configs['db']->query('SELECT email FROM {clients} WHERE id=?i',array($reviews['user_id']))->el();
                            $mailer->group('comment-inform', $email);
                            $mailer->go();

                        }
                    }
                }
            }*/
        } elseif ( isset($post['edit-shop-reviews']) ) {
            // редактирование комментария магазина
            if ( !isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 0 )
                return 'error comment';

            $reviews = $this->all_configs['db']->query('SELECT * FROM {reviews} WHERE shop=1 AND id=?i', array($this->all_configs['arrequest'][3]))->row();

            if ( !$reviews )
                return 'error comment';

            if ( isset($post['avail']) )
                $avail = 1;
            else
                $avail = 0;

            $ar = $this->all_configs['db']->query('UPDATE {reviews} SET text=?, status=?i, become_status=?i, avail=?i WHERE id=?i',
                array($post['text'], $post['status'], $post['become_status'], $avail, $this->all_configs['arrequest'][3]))->ar();
            if ( $ar ) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'update-shop-reviews', $mod_id, $this->all_configs['arrequest'][3]));
            }
        } elseif ( isset($post['add-goods-reviews']) ) {

            $avail = 0;
            if (isset($post['avail']))
                $avail = 1;

            if ( !isset($post['clients']) || $post['clients'] == 0 )
                return l('Выберите клиента');
            if ( !isset($post['goods']) || $post['goods'] == 0 )
                return l('Выберите продукт');

            $id = $this->all_configs['db']->query('INSERT INTO {reviews} (`user_id`, `goods_id`, `text`, `rating`, `usefulness_yes`, `usefulness_no`, `avail`) VALUES (?i, ?i, ?, ?, ?i, ?i, ?i)',
                array(intval($post['clients']), intval($post['goods']), trim($post['text']), trim($post['rating']), intval($post['usefulness_yes']),
                    intval($post['usefulness_no']), $avail), 'id');
            if ( $id ) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'add-review', $mod_id, $this->all_configs['arrequest'][3]));
            }
            header("Location:". $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2] . '/' . $id);
        } elseif ( isset($post['add-shop-reviews']) ) {
            if ( !isset($post['clients']) || intval($post['clients']) == 0 )
                return l('Выберите клинта');

            $avail = 0;
            if ( isset($post['avail']) )
                $avail = 1;

            $id = $this->all_configs['db']->query('INSERT INTO {reviews} (client, status, become_status, text, avail, shop) VALUES (?i, ?i, ?i, ?, ?i, ?i)',
                array(intval($post['clients']), intval($post['status']), intval($post['become_status']), trim($post['text']), $avail, 1), 'id');

            header("Location:". $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/' . $this->all_configs['arrequest'][2] . '/' . $id);
        }elseif ( isset($post['edit-approve-reviews']) ) {
            // редактирование не утвержденного комментария
            if ( !isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 0 )
                return 'error comment';

            $reviews = $this->all_configs['db']->query('SELECT * FROM {parser_comments_approval} WHERE id=?i', array($this->all_configs['arrequest'][3]))->row();

            if ( !$reviews )
                return 'error comment';

            $ar = $this->all_configs['db']->query('UPDATE {parser_comments_approval} SET fio=?, content=?, advantages=?, disadvantages=?,
                  rating=?i, usefulness_yes=?i, usefulness_no=?i, date_add=? WHERE id=?i',
                array(trim($post['fio']), trim($post['text']), trim($post['advantages']), $post['disadvantages'], $post['rating'],
                    $post['usefulness_yes'], $post['usefulness_no'], date("Y-m-d", strtotime($post['date_add'])), $this->all_configs['arrequest'][3]))->ar();
            if ( $ar ) {
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($user_id, 'update-shop-reviews', $mod_id, $this->all_configs['arrequest'][3]));
            }

            header("Location:" . $this->all_configs['prefix'] . 'clients/approve-reviews#comment_parse_edit-' . $this->all_configs['arrequest'][3]);
            exit;
        }

        header("Location:". $_SERVER['REQUEST_URI']);
    }

    private function genmenu()
    {
        $out = '';

        $out = '<ul class="nav nav-list">';

        $active = ''; if ( !isset($this->all_configs['arrequest'][1]) || $this->all_configs['arrequest'][1] == 'create') $active = 'active';
        $out .= '<li class="' . $active . '"><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '">' . l('Список клиентов') . '</a></li>';

        $active = ''; if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'inactive_clients' ) $active = 'active';
        $out .= '<li class="' . $active . '"><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/inactive_clients">' . l('Неактивные клиенты') . '</a></li>';

        $active = ''; if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'goods-reviews' ) $active = 'active';
        $out .= '<li class="' . $active . '"><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/goods-reviews">' . l('Отзывы о товаре') . '</a></li>';

        $active = ''; if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'shop-reviews' ) $active = 'active';
        $out .= '<li class="' . $active . '"><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/shop-reviews">' . l('Отзывы о магазине') . '</a></li>';

        $active = ''; if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'approve-reviews' ) $active = 'active';
        $out .= '<li class="' . $active . '"><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/approve-reviews">' . l('Утверждение отзывов') . '</a></li>';

        $out .= '</ul>';

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'create' && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] > 0)
            $out .= '<a class="btn add-order" href="' . $this->all_configs['prefix'] . 'orders?client_id=' . $this->all_configs['arrequest'][2] . '#create_order">' . l('Создать заказ') . '</a>';
        elseif (!isset($this->all_configs['arrequest'][1]) || $this->all_configs['arrequest'][1] != 'create' )
            $out .= '<br><a class="btn btn-default" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create">' . l('Создать клиента') . '</a>';

        return $out;
    }


    private function gencontent()
    {
        if ( !isset($this->all_configs['arrequest'][1]) )
            return $this->main_page();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'create' )
            return $this->create_client();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'inactive_clients' && !isset($this->all_configs['arrequest'][2]) )
            return $this->clients_list(true);

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'goods-reviews' && !isset($this->all_configs['arrequest'][2]) )
            return $this->goods_reviews();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'goods-reviews' && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create' && !isset($this->all_configs['arrequest'][3]) )
            return $this->add_goods_reviews();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'goods-reviews' && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create' )
            return $this->create_goods_reviews();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'shop-reviews' && !isset($this->all_configs['arrequest'][2]) )
            return $this->shop_reviews();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'shop-reviews' && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create' && !isset($this->all_configs['arrequest'][3]) )
            return $this->add_shop_reviews();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'shop-reviews' && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create' )
            return $this->create_shop_reviews();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'approve-reviews' && !isset($this->all_configs['arrequest'][2]) )
            return $this->approve_reviews();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'approve-reviews' && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'create' )
            return $this->create_approve_reviews();

        if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'group_clients' )
            return $this->group_clients();
    }

    private function main_page(){
        if(!empty($_GET['export'])){
            $this->export();
        }
        $tab = isset($_GET['tab']) ? $_GET['tab'] : '';
        if(!$tab){
            header('Location: '.$this->all_configs['prefix'].'clients?tab=clients'.(isset($_GET['s']) ? '&s='.$_GET['s'] : ''));
            exit;
        }else{
            switch($tab){
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
        return '
            <a class="btn btn-default" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create">' . l('Создать клиента') . '</a>
            <form class="form-horizontal" method="post" style="margin-bottom:0;float:left;max-width:300px">
                <div class="input-group">
                    <input class="form-control" type="text" value="' . (isset($_GET['s']) ? htmlspecialchars($_GET['s']) : '') . '" name="text">
                    <div class="input-group-btn">
                        <input class="btn btn-default" type="submit" value="' . l('Поиск') . '" name="search">
                    </div>
                </div>
            </form>
                <br><br>
            <div class="tabbable clearfix">
                <ul class="nav nav-tabs pull-left">
                    <li'.($_GET['tab'] == 'clients' ? ' class="active"' : '').'><a href="'.$this->all_configs['prefix'].'clients'.$this->mod_submenu[0]['url'].'">'.$this->mod_submenu[0]['name'].'</a></li>
                    <li'.($_GET['tab'] == 'calls' ? ' class="active"' : '').'><a href="'.$this->all_configs['prefix'].'clients'.$this->mod_submenu[1]['url'].'">'.$this->mod_submenu[1]['name'].'</a></li>
                    <li'.($_GET['tab'] == 'requests' ? ' class="active"' : '').'><a href="'.$this->all_configs['prefix'].'clients'.$this->mod_submenu[2]['url'].'">'.$this->mod_submenu[2]['name'].'</a></li> 
                    <li'.($_GET['tab'] == 'statistics' ? ' class="active"' : '').'><a href="'.$this->all_configs['prefix'].'clients'.$this->mod_submenu[3]['url'].'">'.$this->mod_submenu[3]['name'].'</a></li> 
                    <li'.($_GET['tab'] == 'group_clients' ? ' class="active"' : '').'><a href="'.$this->all_configs['prefix'].'clients'.$this->mod_submenu[4]['url'].'">'.$this->mod_submenu[4]['name'].'</a></li> 
                </ul>
                <div class="pull-right">
                    <form style="margin-right:30px" action="'.$this->all_configs['prefix'].'clients" method="get">
                        <input type="hidden" name="export" value="1">
                        <input type="submit" class="btn btn-info" value="'.l('Экспорт').'">
                    </form>
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-pane active">
                    '.$content.'
                </div>
            </div>
        ';
    }
    
    private function export(){
        $export_fields = array(
            // id and phones exports by default
            'email', 'fio', 'legal_address', 'date_add'
        );
        $clients = db()->query("SELECT id,".implode(',', $export_fields).", "
                                    ."(SELECT GROUP_CONCAT(phone) "
                                     ."FROM {clients_phones} WHERE client_id = c.id) as phones "
                              ."FROM {clients} as c "
                              ."WHERE id > 1 ORDER BY c.id")->assoc();
        $data = array();
        $data[] = array_merge(array(
            'id', 'phones'
        ), $export_fields);
        foreach($clients as $client){
            $client_data = array();
            $client_data[] = $client['id'];
            $client_data[] = $client['phones'];
            foreach($export_fields as $exf){
                $client_data[] = $client[$exf];
            }
            $data[] = $client_data;
        }
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=clients.csv');
        $out = fopen('php://output', 'w');
        foreach($data as $row){
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }
    
    private function group_clients()
    {
        $out = '
            <form method="POST" class="form-horizontal" id="group_clients_form">
            <p>К клиенту 1 будут перенесены данные (телефоны, звонки, заказы и т.д.) клиента 2. <br>
               Эл. адрес, фио и контрагент переносятся от клиента 2 в том случае, если у клиента 1 эти поля пустые. <br>
               После этого клиент 2 будет удален.</p><br>
        ';
        
        $out .= '<div class="control-group"><label class="control-label">' . l('Клиент') . ' 1: </label><div class="controls">';
//        $out .= '<input value="' . (isset($_GET['client_1']) ? $_GET['client_1'] : '') . '" name="client_1" /></div></div>';
        $out .= '' . typeahead($this->all_configs['db'], 'clients', false, isset($_GET['client_1']) ? $_GET['client_1'] : 0, 1, 'input-medium', 'input-small', '', true, false, '1') . '</div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Клиент') . ' 2: </label><div class="controls">';
//        $out .= '<input value="' . (isset($_GET['client_2']) ? $_GET['client_2'] : '') . '" name="client_2" /></div></div>';
        $out .= '' . typeahead($this->all_configs['db'], 'clients', false, isset($_GET['client_2']) ? $_GET['client_2'] : 0, 2, 'input-medium', 'input-small', '', true, false, '2') . '</div></div>';
        $out .= '<div class="control-group"><label class="control-label"></label><div class="controls">';
        $out .= '<input type="button" value="' . l('Склеить') . '" onclick="group_clients(this)" class="btn btn-default" /></div></div>';
        $out .= '</form>';

        return $out;
    }

    private function clients_list($inactive = false)
    {
        $count_on_page = $this->count_on_page;//50;
        $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;

        // активен/неактивен
        $query = '';
        /*if ($inactive == true) {
            $query = $this->all_configs['db']->makeQuery('cl.confirm IS NOT NULL', array());
        } else {
            $query = $this->all_configs['db']->makeQuery('cl.confirm IS NULL', array());
        }*/
        // поиск
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $s = str_replace(array("\xA0", '&nbsp;', ' '), '%', trim($_GET['s']));
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
        $out = '';
        if ($clients && count($clients) > 0) {
            $out .= '<table class="table table-striped"><thead><tr>';
            $out .= "<td>ID</td><td>" . l('Эл.почта') . "</td><td>" . l('Адрес') . "</td><td>" . l('Ф.И.О.') . "</td><td>" . l('Телефон') . "</td><td>" . l('Дата регистрации') . "</td></tr><tbody>";
            foreach ($clients as $client) {
                // клиенты которые используются системой
                if (array_key_exists('manage-system-clients', $this->all_configs['configs'])
                        && is_array($this->all_configs['configs']['manage-system-clients'])
                        && count($this->all_configs['configs']['manage-system-clients']) > 0
                        && in_array($client['id'], $this->all_configs['configs']['manage-system-clients'])) {

                    continue;
                }
                $out .= '<tr>
                    <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $client['id'] . '">' . $client['id'] . '</a></td>
                    <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $client['id'] . '">' . htmlspecialchars($client['email']) . '</a></td>
                    <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $client['id'] . '">' . htmlspecialchars($client['legal_address']) . '</a></td>
                    <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $client['id'] . '">' . htmlspecialchars($client['fio']) . '</a></td>
                    <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $client['id'] . '">' . htmlspecialchars($client['phone']) . '</a></td>
                    <td><span title="' . do_nice_date($client['date_add'], false) . '">' . do_nice_date($client['date_add']) . '</span></td></tr>';
            }
            $out .= '</tbody></table>';

            // количество заказов клиентов
            $count_page = ceil($count/$count_on_page);

            // строим блок страниц
            $out .= page_block($count_page);
        } else {
            $out .= '<p  class="text-error">' . l('Нет ни одного клиента') . '</p>';
        }

        return $out;
    }

    private function goods_reviews()
    {
        return $this->get_goods_reviews();
    }

    private function shop_reviews()
    {
        return $this->get_shop_reviews();
    }

    private function create_new_client()
    {
        $out  = '<form class="form-horizontal" method="post"><fieldset><legend>' . l('Добавление клиента') . '</legend>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Электронная почта') . ': </label>
            <div class="controls"><input value="'.(isset($_POST['email'])?htmlspecialchars($_POST['email']):'').'" name="email" class="form-control" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Телефон') . ': </label>
            <div class="controls"><input value="'.(isset($_POST['phone']) ? htmlspecialchars($_POST['phone']):'').'" name="phone" class="form-control" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Ф.И.О.') . ': </label>
            <div class="controls"><input value="'.(isset($_POST['fio'])?htmlspecialchars($_POST['fio']):'').'" name="fio" class="form-control" /></div></div>';
        $contractors = $this->all_configs['db']->query('SELECT title, id FROM {contractors} ORDER BY title', array())->assoc();
        if ($contractors) {
            $out .= '<div class="control-group"><label class="control-label">' . l('Контрагент') . ': </label><div class="controls">';
            $out .= '<select name="contractor_id" class="multiselect"><option value="">' . l('Не выбран') . '</option>';
            foreach ($contractors as $contractor) {
                $out .= '<option value="' . $contractor['id'] . '">' . htmlspecialchars($contractor['title']) . '</option>';
            }
            $out .= '</select></div></div>';
        }
        $out .= '<div class="control-group"><div class="controls"><input id="save_all_fixed" class="btn btn-primary" type="submit" value="' . l('Сохранить изменения') . '" name="edit-client"></div></div>';
        $out .= '</div>';
        $out .= '</form>';
        return $out;
    }

    function phones($user_id, $show_inputs = true)
    {
        $phones = array();

        if ($user_id > 0) {
            $phones = $this->all_configs['db']->query('SELECT p.id, p.phone FROM {clients_phones} as p, {clients} as c
                    WHERE p.client_id=?i AND c.id=p.client_id',
                array($user_id))->vars();
        }

        if ($show_inputs == true) {
            $phones_html = '';
            if ($phones && $phones > 0) {
                foreach ($phones as $phone) {
                    $phones_html .= '<input class="form-control clone_clear_val" type="text" onkeydown="return isNumberKey(event)" name="phone[]" value="' . htmlspecialchars($phone) . '" />';
                }
            } else {
                $phones_html .= '<input class="form-control clone_clear_val" type="text" onkeydown="return isNumberKey(event)" name="phone[]" value="" />';
            }

            return $phones_html;
        } else {
            return $phones;
        }
    }

    private function create_client()
    {
        if ( !isset($this->all_configs['arrequest'][2]) || $this->all_configs['arrequest'][2] < 1 )
//            return '<p  class="text-error">Нет такого клиента</p>';
            return 
                '<a class="btn btn-default" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '">' . l('Список клиентов') . '</a><br><br>'.
                $this->create_new_client();

        // достаем инфу о клиенте
        $client = $this->all_configs['db']->query('SELECT * FROM {clients} WHERE id=?i', array($this->all_configs['arrequest'][2]))->row();

        if (!$client)
            return '<p  class="text-error">' . l('Нет такого клиента') . '</p>';

        $out = '
            <a class="btn btn-default" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '">' . l('Список клиентов') . '</a>
            <a class="btn btn-default" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create">' . l('Создать клиента') . '</a>
            <br><br>
        ';
        
        $out .= l('Редактирование клиента') .  ' ID: ' . $client['id'] 
                . '<fieldset><legend>'
                . htmlspecialchars($client['fio'])
                . ', ' . l('тел') . ': '
                . implode(', ', $this->phones($client['id'], false))
                . '</legend></fieldset>';
        
        $new_call_id = isset($_GET['new_call']) ? $_GET['new_call'] : 0;
        
        $out .= '<div class="tabbable"><ul class="nav nav-tabs">';
        $out .= '<li'.(!$new_call_id ? ' class="active"' : '').'><a href="#main" data-toggle="tab">' . l('Основные') . '</a></li>';
        $out .= '<li><a href="#calls" data-toggle="tab">' . l('Звонки') . '</a></li>';
        $out .= '<li><a href="#requests" data-toggle="tab">' . l('Заявки') . '</a></li>';
        $out .= '<li class=""><a href="#orders" data-toggle="tab">' . l('Заказы') . '</a></li>';
        /*$out .= '<li class=""><a href="#goods_reviews" data-toggle="tab">Отзывы о товаре</a></li>';
        $out .= '<li class=""><a href="#shop_reviews" data-toggle="tab">Отзывы о магазине</a></li>';
        $out .= '<li class=""><a href="#wishlist" data-toggle="tab">Список желаний</a></li>';
        $out .= '<li class=""><a href="#address" data-toggle="tab">Адреса</a></li></ul>';
        */
        if($new_call_id){
            $out .= '<li class="active"><a href="#new_call" data-toggle="tab">' . l('Новый звонок') . '</a></li>';
        }
        
        $out .= '</ul></div>';
        $out .= '<div class="tab-content">';
        
        // страница нового звонка
        if($new_call_id){
            $calldata = get_service('crm/calls')->get_call($new_call_id);
            // ставим статус принят
            if(isset($_GET['get_call'])){
                $operator_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
                $this->all_configs['db']->query("UPDATE {crm_calls} SET type = null, operator_id = ?i "
                                               ."WHERE id = ?i", array($operator_id, $new_call_id));
            }
            $code = (isset($calldata['code']) ? $calldata['code'] : null);
            $code_exists = get_service('crm/calls')->code_exists($code);
            $out .= '
                <div id="new_call" class="tab-pane active">
                    <div style="max-width:800px;">
                        <h3>' . l('Звонок') . ' №'.$new_call_id.'</h3>
                        <form class="ajax_form" method="get" action="'.$this->all_configs['prefix'].'clients/ajax/">
                            <input type="hidden" name="act" value="short_update_client">
                            <input type="hidden" name="client_id" value="'.$client['id'].'">
                            <input type="hidden" name="call_id" value="'.$new_call_id.'">
                            <div class="row-fluid">
                                <div class="span4">
                                   ' . l('Заказчик') . ': <br>
                                    <input class="form-control" type="text" value="' . htmlspecialchars($client['fio']) . '" name="fio" /><br>
                                </div>
                                <div class="span4">
                                    ' . l('Телефоны') . ':  <br>
                                    '.$this->phones($client['id']) . '
                                    <i style="display:inline-block!important;position:relative;margin:-5px 0 0 0!important" class="cloneAndClear icon-plus"></i>
                                </div>
                                <div class="span4">
                                    ' . l('Эл. адрес') . ': <br>
                                    <input class="form-control" type="text" value="' . htmlspecialchars($client['email']) . '" name="email" />
                                </div>
                            </div>
                            <div class="row-fluid">
                                <div class="span4" style="position:relative">
                                    ' . l('Код') . ': 
                                    <span class="text-success '.(!$code_exists || !$code? 'hidden' : '').' code_exists">(' . l('найден') . ')</span>
                                    <span class="text-error '.($code_exists || !$code? 'hidden' : '').' code_not_exists">(' . l('не найден') . ')</span>
                                    <br>
                                    <input style="margin-right:100px;max-width:85%;background-color:'.($code ? (!$code_exists ? '#F0BBC5' : '#D8FCD7') : '').'" class="form-control call_code_mask" type="text" name="code" value="'.$code.'"><br>
                                    <div style="position: absolute;top: 25px;right: -5px;">
                                        или
                                    </div>
                                </div>
                                <div class="span4">
                                    ' . l('Источник') . ': <br>
                                    '.get_service('crm/calls')->get_referers_list(isset($calldata['referer_id']) ? $calldata['referer_id'] : null).'<br>
                                </div>
                                <div class="span4">
                                    <br>
                                    <input type="submit" value="' . l('Сохранить данные о звонке') . '" class="btn btn-info"><br>
                                </div>
                            </div>
                        </form>
                        <hr>
                        '.get_service('crm/requests')->get_new_request_form_for_call($client['id'], $new_call_id).'
                    </div>
                </div>
            ';
        }
        
        // ----- основные настройки
        $out .= '<div id="main" class="tab-pane'.(!$new_call_id ? ' active' : '').'">';
            $out .= '<form method="post"><div class="form-group"><label class="control-label">' . l('Электронная почта') . ': </label>
                <div class="controls"><input value="' . htmlspecialchars($client['email']) . '" name="email" class="form-control " /></div></div>';


            $out .= '<div class="form-group"><label class="control-label">' . l('Телефон') . ': </label><div class="relative">';
            $out .= ''.$this->phones($client['id']) . ' <i class="cloneAndClear glyphicon glyphicon-plus"></i></div></div>';
            //    <input value="' . htmlspecialchars($client['phone']) . '" name="phone" class="span5" />';*/
            $out .= '<div class="form-group"><label class="control-label">' . l('Дата регистрации') . ': </label>
                <div class="controls"><span title="' . do_nice_date($client['date_add'], false) . '">' . do_nice_date($client['date_add']) . '</span></div></div>';
            $out .= '<div class="form-group"><label class="control-label">' . l('Ф.И.О.') . ': </label>
                <div class="controls"><input value="' . htmlspecialchars($client['fio']) . '" name="fio" class="form-control" /></div></div>';
            $out .= '<div class="form-group"><label class="control-label">' . l('Адрес') . ': </label>
                <div class="controls"><input value="' . htmlspecialchars($client['legal_address']) . '" name="legal_address" class="form-control" /></div></div>';
            $contractors = $this->all_configs['db']->query('SELECT title, id FROM {contractors} ORDER BY title', array())->assoc();
            if ($contractors) {
                $out .= '<div class="form-group"><label class="control-label">' . l('Контрагент') . ': </label><div class="controls">';
                $out .= '<select name="contractor_id" class="multiselect form-control "><option value="">' . l('Не выбран') . '</option>';
                foreach ($contractors as $contractor) {
                    if ($contractor['id'] == $client['contractor_id'])
                        $out .= '<option selected value="' . $contractor['id'] . '">' . htmlspecialchars($contractor['title']) . '</option>';
                    else
                        $out .= '<option value="' . $contractor['id'] . '">' . htmlspecialchars($contractor['title']) . '</option>';
                }
                $out .= '</select></div></div>';
                if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                    $out .= '<div class="form-group"><label class="control-label">' . l('Пароль') . ': </label>'
                    . '<i class="glyphicon glyphicon-warning-sign editable-click" data-type="text" '
                    . 'data-pk="'.$this->all_configs['arrequest'][2].'" '
                    . 'data-type="password" '
                    . 'data-url="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/ajax?act=change-client-password" '
                    . 'data-title="' . l('Введите новый пароль') . '" data-display="false"></i></div>';
                }
            }

        $out .= '
            <div class="form-group">
                    <div class="controls">
                    <input id="save_all_fixed" class="btn btn-primary" type="submit" value="' . l('Сохранить изменения') . '" name="edit-client">
                    </div>
                </div>
            </form>
            </div>
        ';
        // --- основные настройки - конец
            
        // ----- звонки
        $out .= '
            <div id="calls" class="tab-pane">
                '.get_service('crm/calls')->calls_list_table($client['id']).'
            </div>
        ';
        // ----- звонки - конец
       
        // ----- заявки
        $out .= '
            <div id="requests" class="tab-pane">
                '.get_service('crm/requests')->requests_list($client['id']).'
            </div>
        ';
        // ----- заявки - конец
       
        // ----- заказы
        $out .= '
            <div id="orders" class="tab-pane">
        ';
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('c_id' => $client['id']));
        $skip = $queries['skip'];
        $count_on_page = $this->count_on_page;//$queries['count_on_page'];
        $query = $queries['query'];
        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page);
        if ($orders && count($orders) > 0) {
            $model = new Model($this->all_configs['db'], $this->all_configs['configs']);

            $out .= '<table class="table table-compact"><thead><tr><td></td><td>' . l('номер заказа') . '</td><td>'.l('Дата').'</td><td>'.l('Приемщик').'</td>';
            $out .= '<td>' . l('manager') . '</td><td>'.l('Статус').'</td><td>' . l('Устройство') . '</td><td>' . l('Стоимость') . '</td><td>' . l('Оплачено') . '</td>';
            $out .= '<td>Клиент</td><td>' . l('Контактный тел.') . '</td><td>' . l('Сроки') . '</td>';
            $out .= '<td>' . l('Склад') . '</td></tr></thead><tbody id="table_clients_orders">';
            foreach ( $orders as $order ) {
                $out .= display_client_order($order, $model);
            }
            $out .= '</tbody></table>';
            // количество заказов клиентов
            $count = $this->all_configs['manageModel']->get_count_clients_orders($query);
            $count_page = ceil($count/$count_on_page);
            // строим блок страниц
            $out .= page_block($count_page);
        } else {
            $out .= '<div class="span9"><p  class="text-error">' . l('Заказов не найдено') . '</p></div>';
        }
        $out .= '
            </div>
        ';
        // ----- заказы - конец
            
        $out .= '
                
        </div>';

        return $out;
    }

    private function create_goods_reviews()
    {
        if ( !isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 1 )
            return '<p  class="text-error">' . l('Нет такого отзыва') . '</p>';

        $review = $this->all_configs['db']->query('SELECT r.*, c.email, g.title, r.fio, c.id as user_id FROM {reviews} as r
            LEFT JOIN (SELECT email,id FROM {clients})c on r.user_id=c.id
            LEFT JOIN (SELECT title,id FROM {goods})g on r.goods_id=g.id
            WHERE r.id=?i AND r.goods_id>0 AND (r.parent_id IS NULL OR r.parent_id="")', array($this->all_configs['arrequest'][3]))->row();

        if ( !$review )
            return '<p  class="text-error">' . l('Нет такого отзыва') . '</p>';

        $out = '<form class="form-horizontal" method="post"><fieldset><legend>' . l('Редактирование отзыва о товаре') . ' ID: ' . $review['id'] . '.</legend>';
        $out .= '<div class="tabbable"><ul class="nav nav-tabs">
            <li class="active"><a href="#review" data-toggle="tab">' . l('Отзыв') . '</a></li>
            <li><a href="#comments" data-toggle="tab">' . l('Комментарии') . '</a></li></ul>';

        $out .= '<div class="tab-content"><div id="review" class="tab-pane active">';
        $out .= '<div class="control-group"><label class="control-label">' . l('Клиент') . ': </label>
            <div class="controls">' . (($review['user_id']>0) ? '<a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $review['user_id'] . '/">' . htmlspecialchars($review['email']) . '</a>' : htmlspecialchars($review['fio'])) . '</div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Товар') . ': </label>
            <div class="controls"><a href="' . $this->all_configs['prefix'] . 'products/create/' . $review['goods_id'] . '/#imt-comments">' . htmlspecialchars($review['title']) . '</a></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Комментарий') . ': </label>
            <div class="controls"><textarea class="span5" name="text">' . htmlspecialchars($review['text']) . '</textarea></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Плюсы') . ': </label>
            <div class="controls"><textarea class="span5" name="advantages">' . htmlspecialchars($review['advantages']) . '</textarea></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Минусы') . ': </label>
            <div class="controls"><textarea class="span5" name="disadvantages">' . htmlspecialchars($review['disadvantages']) . '</textarea></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Рейтинг') . ': </label>
            <div class="controls"><select name="rating" class="span5">';
        for ( $i=1; $i<=5; $i++ ) {
            if ( $review['rating'] == $i )
                $out .= '<option value="' . $i . '" selected>' . $i . '</option>';
            else
                $out .= '<option value="' . $i . '">' . $i . '</option>';
        }
        $out .= '</select></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Полезность') . ': </label>
            <div class="controls"><input type="text" class="span5" onkeydown="return isNumberKey(event)" name="usefulness_yes" value="' . (1*$review['usefulness_yes']) .'" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Бесполезность') . ': </label>
            <div class="controls"><input type="text" class="span5" onkeydown="return isNumberKey(event)" name="usefulness_no" value="' . (1*$review['usefulness_no']) .'" /></div></div>';
        $checked = ''; if ( $review['avail'] == 1 ) $checked = 'checked';
        $out .= '<div class="control-group"><label class="control-label">' . l('Одобрен') . ': </label>
            <div class="controls"><input type="checkbox" ' . $checked .' name="avail" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">'.l('Дата').': </label>
            <div class="controls"><span title="'.do_nice_date($review['date'], false).'">' . do_nice_date($review['date']) . '</span></div></div>';

        // комментарии к отзыву о товаре
        $comments = $this->all_configs['db']->query('SELECT r.`user_id`, r.`id`, r.`text`, r.`avail`, r.`date`, c.`email`, c.id as client_id FROM {reviews} as r
            LEFT JOIN (SELECT `email`, `id` FROM {clients})c ON c.`id`=r.`user_id`
            WHERE r.parent_id=?i', array($this->all_configs['arrequest'][3]))->assoc();
        $out .= '</div><div id="comments" class="tab-pane">';
        $out .= '<table class="table table-striped"><thead><td>' . l('Клиент') . '</td><td>' . l('Комментарий') . '</td><td>'.l('Дата').'</td><td>' . l('Одобрен') . '</td></tr></thead><tbody><tr>';
        if ( $comments && count($comments) > 0 ) {
            foreach ( $comments as $comment ) {
                $avail = '';
                if ( $comment['avail'] == 1 )
                    $avail = 'checked';
                $out .= '<tr><input type="hidden" name="comments_client[' . $comment['id'] . ']" value="' . $comment['id'] . '"/>
                    <td><a href="' . $this->all_configs['prefix'] . 'clients/create/' . $comment['client_id'] . '">' . htmlspecialchars($comment['email']) . '</td></a>
                    <td><textarea сlass="span5" name="comments_text[' . $comment['id'] . ']">' . htmlspecialchars($comment['text']) . '</textarea></td>
                    <td><span title="' . do_nice_date($comment['date'], false) . '">' . do_nice_date($comment['date']) . '</span></td>
                    <td><input сlass="span5" ' . $avail . ' type="checkbox" name="comments_avail[' . $comment['id'] . ']" /></td></tr>';
            }
        } else {
            $out .= '<tr><td colspan="4"><p  class="text-error">' . l('Нет ни одного комментария') . '</p></td></tr>';
        }
        /*$out .= '<tr><td>' . typeahead($this->all_configs['db'], 'clients') . '</td>
            <td><textarea сlass="span5" name="comments_text[]"></textarea></td><td>#</td>
            <td><input сlass="span5" type="checkbox" name="avail[]" /></td></tr>';*/
        $out .= '</tbody></table>';
        $out .= '</div>';

        $out .= '</div></div>';

        $out .= '<div class="control-group"><div class="controls"><input id="save_all_fixed" class="btn btn-primary" type="submit" value="' . l('Сохранить изменения') . '" name="edit-goods-reviews"></div></div>';
        $out .= '</fieldset></form>';

        return $out;
    }

    private function add_shop_reviews()
    {
        $out = '<form class="form-horizontal" method="post"><fieldset><legend>' . l('Новый отзыв о магазине') .'</legend>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Клиент') . ': </label>
            <div class="controls">' . typeahead($this->all_configs['db'], 'clients', false, 0, 2) . '</div></div>';
        $out .= '<div class="control-group"><div class="controls"><label class="radio"><input type="radio" name="status" value="1" />' . $this->all_configs['configs']['reviews-shop-status'][1] . ',</label>';
        $out .= '<label class="radio"><input type="radio" name="status" value="2" />' . $this->all_configs['configs']['reviews-shop-status'][2] . ',</label>';
        $out .= '<label class="radio"><input type="radio" name="status" value="3" />' . $this->all_configs['configs']['reviews-shop-status'][3] . '</label></div></div>';
        $out .= '<div class="control-group"><div class="controls"><label class="radio"><input type="radio" name="become_status" value="1" />' .$this->all_configs['configs']['reviews-shop-become_status'][1] . ',</label>';
        $out .= '<label class="radio"><input type="radio" name="become_status" value="2" />' . $this->all_configs['configs']['reviews-shop-become_status'][2] . ',</label>';
        $out .= '<label class="radio"><input type="radio" name="become_status" value="3" />' . $this->all_configs['configs']['reviews-shop-become_status'][3] . '</label></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Комментарий') . ': </label>
            <div class="controls"><textarea class="span5" name="text"></textarea></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Одобрен') .': </label>
            <div class="controls"><input type="checkbox" name="avail" /></div></div>';
        $out .= '<div class="control-group"><div class="controls"><input class="btn btn-primary" type="submit" value="'.l('Добавить').'" name="add-shop-reviews"></div></div>';
        $out .= '</fieldset></form>';

        return $out;
    }

    private function approve_reviews()
    {
        $out = '';
        $limit = $this->count_on_page;//50;
        $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? (($_GET['p']-1)*$limit) : 0;

        $count_comments = $this->all_configs['db']->query('SELECT count(ca.id) FROM {parser_comments_approval} as ca, {goods} as g
            WHERE ca.approve IS NULL AND g.id=ca.goods_id', array())->el();
        $count_page = ceil($count_comments / $limit);

        $page = '';
        // строим блок страниц
        if ($count_page > 1) {
            $page = page_block($count_page);
        }

        $comments = $this->all_configs['db']->query('SELECT ca.id, ca.market_id, ca.fio, ca.content, ca.advantages, ca.disadvantages,
            ca.rating, ca.usefulness_yes, ca.usefulness_no, ca.goods_id, g.title, ca.goods_id
            FROM {parser_comments_approval} as ca, {goods} as g 
            WHERE ca.approve IS NULL AND g.id=ca.goods_id 
            ORDER BY ca.date_add DESC 
            LIMIT ?i, ?i', array($skip, $limit))->assoc();

        if ( $comments && count($comments) > 0 ) {
            $out .= '<table class="table table-striped small-font"><thead><td>' . l('Маркет') .'</td><td>' . l('Товар') .'</td><td>' . l('ФИО') .'</td><td>' . l('Текст') .'</td><td>Р</td><td>' . l('Да') .'</td>
                <td>Нет</td><td></td></tr></thead><tbody><tr>';

            require_once($this->all_configs['path'] . 'parser/configs_parse.php');
            $parser_configs = Configs_Parse::get();

            foreach ( $comments as $comment ) {
                if ( array_key_exists('markets', $parser_configs) && array_key_exists($comment['market_id'], $parser_configs['markets']) ) {
                    $out .= '<tr id="comment_parse_remove-' . $comment['id'] . '">
                            <td>' . htmlspecialchars($parser_configs['markets'][$comment['market_id']]['market-name']) . '</td>
                            <td><a href="' . $this->all_configs['prefix'] . 'products/create/' . $comment['goods_id'] . '">' . htmlspecialchars($comment['title']) . '</a></td>
                            <td>' . htmlspecialchars($comment['fio']) . '</td>
                            <td id="comment_parse_edit-' . $comment['id'] . '">' .
                        /*((mb_strlen($comment['content'], 'UTF-8') < 250) ?*/
                        nl2br(htmlspecialchars($comment['content'])) /*:
                                    nl2br(htmlspecialchars(mb_substr($comment['content'], 0, 250, 'UTF-8'))) . '<a href="' . $this->all_configs['prefix'] . 'clients/approve-reviews/create/' . $comment['id'] . '">...</a>') */
                        .(!empty($comment['advantages']) ? '<br><br><strong>' . l('Плюсы') .':</strong> '. nl2br(htmlspecialchars($comment['advantages'])) : '')
                        .(!empty($comment['disadvantages']) ? '<br><br><strong>' . l('Минусы') .':</strong> '. nl2br(htmlspecialchars($comment['disadvantages'])) : '')
                        . '
                            </td>
                            <td>' . htmlspecialchars($comment['rating']) . '</td>
                            <td>' . htmlspecialchars($comment['usefulness_yes']) . '</td>
                            <td>' . htmlspecialchars($comment['usefulness_no']) . '</td>
                            <td id="comment_parse_empty-' . $comment['id'] . '">
                                <!--<div class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">' . l('Настройки') .'<b class="caret"></b></a>-->
                                    <ul class="<!--dropdown-menu--> sett-dd-btns">
                                        <li><input onclick="window.location.href=\'' . $this->all_configs['prefix'] . 'clients/approve-reviews/create/' . $comment['id'] . '\'" class="btn btn-info btn-mini" type="button" value="' . l('Редактировать') . '" /></li>
                                        <li><input onclick="confirm_parse_comment(' . $comment['id'] . ', 0)" class="btn btn-success btn-mini" type="button" value="' . l('Подтвердить') .' (' . l('откл') .')" /></li>
                                        <li><input onclick="confirm_parse_comment(' . $comment['id'] . ', 1)" class="btn btn-success btn-mini" type="button" value="' . l('Подтвердить') .' (' . l('вкл') .')" /></li>
                                        <li><input onclick="refute_parse_comment(' . $comment['id'] . ')" class="btn btn-danger btn-mini" type="button" value="' . l('Удалить') . '" /></li>
                                    </ul>
                                <!--</div>-->
                            </td>
                        </tr>';
                }
            }
            $out .= '</tbody></table>';
        } else {
            $out .= '<tr><td colspan="4"><p  class="text-error">' . l('Нет ни одного комментария') .'</p></td></tr>';
        }

        $out .= $page;

        return $out;
    }

    private function create_approve_reviews()
    {
        if ( !isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 1 )
            return '<p  class="text-error">' . l('Нет такого отзыва') .'</p>';

        $review = $this->all_configs['db']->query('SELECT r.*, g.title FROM {parser_comments_approval} as r
            LEFT JOIN (SELECT title,id FROM {goods})g on r.goods_id=g.id
            WHERE r.id=?i AND r.goods_id>0', array($this->all_configs['arrequest'][3]))->row();

        if ( !$review )
            return '<p  class="text-error">' . l('Нет такого отзыва') .'</p>';

        $out = '<form class="form-horizontal" method="post"><fieldset><legend>' . l('Редактирование не подтвержденного отзыва о товаре') .' ID: ' . $review['id'] . '.</legend>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Клиент') . ': </label>
            <div class="controls"><input type="text" value="' . htmlspecialchars($review['fio']) . '" class="span5" name="fio" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Товар') . ': </label>
            <div class="controls"><a href="' . $this->all_configs['prefix'] . 'products/create/' . $review['goods_id'] . '/">' . htmlspecialchars($review['title']) . '</a></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Комментарий') . ': </label>
            <div class="controls"><textarea class="span5" name="text">' . htmlspecialchars($review['content']) . '</textarea></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Плюсы') .': </label>
            <div class="controls"><textarea class="span5" name="advantages">' . htmlspecialchars($review['advantages']) . '</textarea></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Минусы') .': </label>
            <div class="controls"><textarea class="span5" name="disadvantages">' . htmlspecialchars($review['disadvantages']) . '</textarea></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Рейтинг') . ': </label>
            <div class="controls"><select name="rating" class="span5">';
        for ( $i=1; $i<=5; $i++ ) {
            if ( $review['rating'] == $i )
                $out .= '<option value="' . $i . '" selected>' . $i . '</option>';
            else
                $out .= '<option value="' . $i . '">' . $i . '</option>';
        }
        $out .= '</select></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Полезность') .': </label>
            <div class="controls"><input type="text" class="span5" onkeydown="return isNumberKey(event)" name="usefulness_yes" value="' . (1*$review['usefulness_yes']) .'" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Бесполезность') .': </label>
            <div class="controls"><input type="text" class="span5" onkeydown="return isNumberKey(event)" name="usefulness_no" value="' . (1*$review['usefulness_no']) .'" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">'.l('Дата').': </label>
            <div class="controls"><input type="text" value="' . date("d.m.Y", strtotime($review['date_add'])) . '" class="span5 edit_date" name="date_add" /></div></div>';
        $out .= '<div class="control-group"><div class="controls"><input id="save_all_fixed" class="btn btn-primary" type="submit" value="' . l('Сохранить изменения') . '" name="edit-approve-reviews"></div></div>';
        $out .= '</fieldset></form>';

        return $out;
    }

    private function create_shop_reviews()
    {
        if ( !isset($this->all_configs['arrequest'][3]) || $this->all_configs['arrequest'][3] < 1 )
            return '<p  class="text-error">' . l('Нет такого отзыва') .'</p>';

        $review = $this->all_configs['db']->query('SELECT r.*, c.email, r.fio, c.id as user_id FROM {reviews} as r
            LEFT JOIN (SELECT email, id FROM {clients})c ON c.id=r.user_id
            WHERE r.id=?i AND r.shop=1', array($this->all_configs['arrequest'][3]))->row();

        if ( !$review )
            return '<p  class="text-error">' . l('Нет такого отзыва') .'</p>';

        $out = '<form class="form-horizontal"  method="post"><fieldset><legend>' . l('Редактирование комментария о магазине') .' ID: ' . $review['id'] . '.</legend>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Клиент') . ': </label>
            <div class="controls">' . (($review['user_id']>0) ? '<a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $review['user_id'] . '/">' . htmlspecialchars($review['email']) . '</a>' : htmlspecialchars($review['fio'])) . '</div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Комментарий') . ': </label>
            <div class="controls"><textarea class="span5" name="text">' . htmlspecialchars($review['text']) . '</textarea></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Оценка') .': </label>';
        $checked = ''; if ( $review['status'] == 1 ) $checked = 'checked'; $out .= '<div class="controls"><input type="radio" name="status" value="1" ' . $checked . ' /> ' . $this->all_configs['configs']['reviews-shop-status'][1] . '</div>';
        $checked = ''; if ( $review['status'] == 2 ) $checked = 'checked'; $out .= '<div class="controls"><input type="radio" name="status" value="2" ' . $checked . ' /> ' . $this->all_configs['configs']['reviews-shop-status'][2] . '</div>';
        $checked = ''; if ( $review['status'] == 2 ) $checked = 'checked'; $out .= '<div class="controls"><input type="radio" name="status" value="3" ' . $checked . ' /> ' . $this->all_configs['configs']['reviews-shop-status'][3] . '</div>';
        $out .= '</div><div class="control-group"><label class="control-label">' . l('Оценка изменения') .': </label>';
        $checked = ''; if ( $review['become_status'] == 1 ) $checked = 'checked'; $out .= '<div class="controls"><input type="radio" name="become_status" value="1" ' . $checked . ' /> ' . $this->all_configs['configs']['reviews-shop-become_status'][1] . '</div>';
        $checked = ''; if ( $review['become_status'] == 2 ) $checked = 'checked'; $out .= '<div class="controls"><input type="radio" name="become_status" value="2" ' . $checked . ' /> ' . $this->all_configs['configs']['reviews-shop-become_status'][2] . '</div>';
        $checked = ''; if ( $review['become_status'] == 2 ) $checked = 'checked'; $out .= '<div class="controls"><input type="radio" name="become_status" value="3" ' . $checked . ' /> ' . $this->all_configs['configs']['reviews-shop-become_status'][3] . '</div>';
        $checked = ''; if ( $review['avail'] == 1 ) $checked = 'checked';
        $out .= '</div><div class="control-group"><label class="control-label">Одобрен: </label>
            <div class="controls"><input type="checkbox" ' . $checked .' name="avail" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">'.l('Дата').': </label>
            <div class="controls"><span title="' . do_nice_date($review['date'], false) . '">' . do_nice_date($review['date']) . '</span></div></div>';
        $out .= '<div class="control-group"><div class="controls"><input id="save_all_fixed" class="btn btn-primary" type="submit" value="' . l('Сохранить изменения') . '" name="edit-shop-reviews"></div></div>';
        $out .= '</fieldset></form>';


        return $out;
    }

    private function get_goods_reviews($user_id=null)
    {
        $limit = $this->count_on_page;//100;
        $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? (($_GET['p']-1)*$limit) : 0;
        // достаем все отзывы о товарах
        if ( !$user_id ) {
            $count_reviws = $this->all_configs['db']->query('SELECT COUNT(r.id)
                FROM {reviews} as r
                WHERE r.goods_id > 0 AND r.parent_id IS NULL', array())->el();
            $reviews = $this->all_configs['db']->query('SELECT r.*, c.email, if(c.id>0, c.fio, r.fio) as fio, c.phone, c.id as user_id
                FROM {reviews} as r
                LEFT JOIN (SELECT email, id, fio, phone FROM {clients})c ON c.id=r.user_id
                WHERE r.goods_id > 0 AND parent_id IS NULL ORDER BY `date` DESC LIMIT ?i, ?i', array($skip, $limit))->assoc();
        } else {
            $count_reviws = $this->all_configs['db']->query('SELECT COUNT(r.id)
                FROM {reviews} as r
                WHERE r.goods_id > 0 AND r.user_id=?i  AND parent_id IS NULL',
                array($user_id))->el();
            $reviews = $this->all_configs['db']->query('SELECT r.*, c.email, if(c.id>0,c.fio,r.fio) as fio, c.phone, c.id as user_id
                FROM {reviews} as r
                LEFT JOIN (SELECT email, id, fio, phone FROM {clients})c ON c.id=r.user_id
                WHERE r.goods_id > 0 AND r.user_id=?i  AND parent_id IS NULL ORDER BY `date` DESC LIMIT ?i, ?i',
                array($user_id, $skip, $limit))->assoc();
        }

        $count_page = ceil($count_reviws / $limit);

        $page = '';
        // строим блок страниц
        $page = page_block($count_page);

        $out = '<a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/create">' . l('Создать новый') .'</a>';
        if ( $reviews ) {
            $out .= '<table class="table table-striped"><thead><td>' . l('Клиент') .'</td><td>' . l('Комментарий') .'</td><td>'.l('Дата').'</td><td>' . l('Рейтинг') . '</td><td>' . l('Полезный') .'</td><td>' . l('Бесполезный') .'</td><td>' . l('Одобрен') .'</td></tr></thead><tbody><tr>';
            foreach ( $reviews as $comment ) {
                $out .= '<tr>
                    <td>' . (($comment['user_id']>0) ? '<a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $comment['user_id'] . '">' . htmlspecialchars($comment['email']) . ', ' . htmlspecialchars($comment['phone']) . ', ' . htmlspecialchars($comment['fio']) . '</a>' : htmlspecialchars($comment['fio'])) . '</td>
                    <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/goods-reviews/create/' . $comment['id'] . '">' . ((mb_strlen($comment['text'], 'UTF-8')>20)?htmlspecialchars(mb_substr($comment['text'], 0, 20, 'UTF-8')).'...':htmlspecialchars($comment['text'])) . '</a></td>
                    <td><span title="' . do_nice_date($comment['date'], false) . '">' . do_nice_date($comment['date']) . '</span></td>
                    <td>' . $comment['rating'] . '</td>
                    <td>' . (1*$comment['usefulness_yes']) . '</td>
                    <td>' . (1*$comment['usefulness_no']) . '</td>
                    <td>' . $comment['avail'] . '</td></tr>';
            }
            $out .= '</tbody></table>' . $page;
        } else {
            return '<p  class="text-error">' . l('Нет ни одного отзыва о товаре') .'</p>';
        }

        return $out;
    }

    private function get_shop_reviews($user_id=null)
    {
        // достаем все отзывы о магазине
        if ( $user_id ) {
            $reviews = $this->all_configs['db']->query('SELECT r.*, c.email, if(c.id>0,c.fio,r.fio) as fio, c.phone, c.id as user_id FROM {reviews} as r
                LEFT JOIN (SELECT email, id, fio, phone FROM {clients})c ON c.id=r.user_id
                WHERE r.shop=1 AND r.user_id=?i ORDER BY `date` DESC', array($user_id))->assoc();
        } else {
            $reviews = $this->all_configs['db']->query('SELECT r.*, c.email, if(c.id>0,c.fio,r.fio) as fio, c.phone, c.id as user_id FROM {reviews} as r
                LEFT JOIN (SELECT email, id, fio, phone FROM {clients})c ON c.id=r.user_id
                WHERE r.shop=1 ORDER BY `date` DESC')->assoc();
        }
        $out = '<a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/create">' . l('Создать новый') .'</a>';
        if ( $reviews && count($reviews) > 0 ) {
            $out .= '<table class="table table-striped"><thead><td>' . l('Клиент') .'</td><td>' . l('Комментарий') .'</td><td>'.l('Дата').'</td><td>' . l('Оценка') .'</td><td>' . l('Оценка изменения') .'</td><td>' . l('Одобрен') .'</td></tr></thead><tbody><tr>';
            foreach ( $reviews as $comment ) {
                $out .= '<tr>
                    <td>' . (($comment['user_id']>0) ? '<a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $comment['user_id'] . '">' . htmlspecialchars($comment['email']) . ', ' . htmlspecialchars($comment['phone']) . ', ' . htmlspecialchars($comment['fio']) . '</a>' : htmlspecialchars($comment['fio'])) . '</td>
                    <td><a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] . '/create/' . $comment['id'] . '">' . ((mb_strlen($comment['text'], 'UTF-8')>20)?htmlspecialchars(mb_substr($comment['text'], 0, 20, 'UTF-8')).'...':htmlspecialchars($comment['text'])) . '</a></td>
                    <td><span title="' . do_nice_date($comment['date'], false) . '">' . do_nice_date($comment['date']) . '</span></td>
                    <td>' . ((array_key_exists($comment['become_status'], $this->all_configs['configs']['reviews-shop-become_status']))?$this->all_configs['configs']['reviews-shop-become_status'][$comment['become_status']]:'') . '</td>
                    <td>' . ((array_key_exists($comment['status'], $this->all_configs['configs']['reviews-shop-status']))?$this->all_configs['configs']['reviews-shop-status'][$comment['status']]:'') . '</td>
                    <td>' . $comment['avail'] . '</td></tr>';
            }
            $out .= '</tbody></table>';
        } else {
            return '<p  class="text-error">' . l('Нет ни одного отзыва о магазине') .'</p>';
        }

        return $out;
    }

    private function add_goods_reviews()
    {
        $out = '<form class="form-horizontal" method="post"><fieldset><legend>' . l('Новый отзыв') .'</legend>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Клиент') . ': </label>
            <div class="controls">' . typeahead($this->all_configs['db'], 'clients', false, 0, 3) . '</div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Товар') . ': </label>
            <div class="controls">';

        $out .= typeahead($this->all_configs['db'], 'goods', true) . '</div></div>';

        $out .= '<div class="control-group"><label class="control-label">' . l('Комментарий') . ': </label>
            <div class="controls"><textarea class="span5" name="text"></textarea></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Рейтинг') . ': </label>
            <div class="controls"><select name="rating" class="span5">';
        for ( $i=1; $i<=5; $i++ ) {
            $out .= '<option value="' . $i . '">' . $i . '</option>';
        }
        $out .= '</select></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Полезность') .': </label>
            <div class="controls"><input type="text" class="span5" onkeydown="return isNumberKey(event)" name="usefulness_yes" value="" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Бесполезность') .': </label>
            <div class="controls"><input type="text" class="span5" onkeydown="return isNumberKey(event)" name="usefulness_no" value="" /></div></div>';
        $out .= '<div class="control-group"><label class="control-label">' . l('Одобрен') .': </label>
            <div class="controls"><input type="checkbox" name="avail" /></div></div>';
        $out .= '<div class="control-group"><div class="controls"><input class="btn btn-primary" type="submit" value="'.l('Добавить').'" name="add-goods-reviews"></div></div>';
        $out .= '</fieldset></form>';

        return $out;
    }

    function ajax()
    {
        $act = isset($_GET['act']) ? $_GET['act'] : '';
        if(!$act){
            $act = isset($_POST['act']) ? $_POST['act'] : '';
        }

        $data = array(
            'state' => false
        );

        // подтвреждение комментария
        if ( $act == 'confirm_parse_comment' ) {
            if ( !isset($_POST['comment_id']) || $_POST['comment_id'] == 0 ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого комментария не существует'), 'error'=>true));
                exit;
            }
            $comment = $this->all_configs['db']->query('SELECT content, goods_id, date_publish, usefulness_yes, usefulness_no,
                    rating, advantages, disadvantages, fio, date_publish
                FROM {parser_comments_approval} WHERE id=?i', array($_POST['comment_id']))->row();
            $avail = (isset($_POST['avail']) && $_POST['avail'] == 1)?1:null;

            if ( !$comment ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого комментария не существует'), 'error'=>true));
                exit;
            }

            $this->all_configs['db']->query('UPDATE {parser_comments_approval} SET approve=?i WHERE id=?i', array(2, $_POST['comment_id']));
            $id = $this->all_configs['db']->query('INSERT INTO {reviews} (text, avail, goods_id, date, usefulness_yes, usefulness_no,
                    rating, advantages, disadvantages, fio) VALUES (?, ?n, ?i, ?, ?i, ?i, ?i, ?, ?, ?)',
                array($comment['content'], $avail, $comment['goods_id'], $comment['date_publish'], $comment['usefulness_yes'],
                    $comment['usefulness_no'], $comment['rating'], $comment['advantages'], $comment['disadvantages'], $comment['fio']), 'id');

            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Успешно', 'response' => '<a href="' . $this->all_configs['prefix'] . 'clients/goods-reviews/create/' . $id . '">' . l('Редактировать') . '</a>'));
            exit;
        }

        // соединение клиентов
        if ($act == 'group-clients') {

            require_once($this->all_configs['sitepath'] . 'mail.php');
            require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
//            $access = new access($this->all_configs, false);
//            $client_1 = $access->get_client(null, null, );
//            $client_2 = $access->get_client(null, null, isset($_POST['client_2']) ? $_POST['client_2'] : 0);

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
//                && (!is_email($client_1['email']) || !is_email($client_2['email']))
//                && (count($client_1['phones']) == 0 || count($client_2['phones']) == 0)
               ) {

                $master_id = $slave_id = $phone = null;

//                if (is_email($client_1['email'])) {
                    $master_id = $client_1['id'];
                    $slave_id = $client_2['id'];
//                    $phone = is_phone($client_2['phones']);
//                }
//                if (is_email($client_2['email'])) {
//                    $master_id = $client_2['id'];
//                    $slave_id = $client_1['id'];
//                    $phone = is_phone($client_1['phones']);
//                }
                
//var_dump($client_1);var_dump($client_2);exit;
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
                    if(!$client_1['fio'] && $client_2['fio']){
                        $personal_data[] = $this->all_configs['db']->makeQuery(" fio = ? ", array($client_2['fio']));
                    }
                    if(!$client_1['email'] && $client_2['email']){
                        $personal_data[] = $this->all_configs['db']->makeQuery(" email = ? ", array($client_2['email']));
                    }
                    if(!$client_1['legal_address'] && $client_2['legal_address']){
                        $personal_data[] = $this->all_configs['db']->makeQuery(" legal_address = ? ", array($client_2['legal_address']));
                    }
                    if(!$client_1['contractor_id'] && $client_2['contractor_id']){
                        $personal_data[] = $this->all_configs['db']->makeQuery(" contractor_id = ?i ", array($client_2['contractor_id']));
                    }
                    if($personal_data){
                        $this->all_configs['db']->query("UPDATE {clients} SET ?q WHERE id = ?i", array(implode(',',$personal_data), $master_id));
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

                if (intval($ar) > 0) {
                    //$this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    //    array($user_id, 'update-password', $mod_id, $_POST['pk']));
                }

                header("Content-Type: application/json; charset=UTF-8");
                exit;
            }

        }

        // опроверждение комментария
        if ( $act == 'refute_parse_comment' ) {
            if ( !isset($_POST['comment_id']) || $_POST['comment_id'] == 0 ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого комментария не существует'), 'error'=>true));
                exit;
            }

            $ar = $this->all_configs['db']->query('UPDATE {parser_comments_approval} SET approve=?i WHERE id=?i', array(1, $_POST['comment_id']))->ar();

            if ( $ar ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Успешно'), 'response' => l('Комментарий успешно удален')));
                exit;
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого комментария не существует'), 'error'=>true));
                exit;
            }
        }

        if($act == 'short_update_client'){
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
            }else{
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
            }
        }
        
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    public static function get_submenu(){
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


}

