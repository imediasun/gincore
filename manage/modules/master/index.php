<?php

$modulename[] = 'master';
$modulemenu[] = 'Мастер';
$moduleactive[] = true;

class master
{

    protected $all_configs;
    private $lang;
    private $def_lang;
    private $langs;

    /**
     * master constructor.
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
        $this->db = $all_configs['db'];

        global $ifauth;

//        if ($ifauth['is_1']) {
//            return false;
//        }

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        $this->gencontent();
    }

    /**
     *
     */
    private function gencontent()
    {
        global $input_html;

        // должности юзеров
        $roles = $this->db->query("SELECT id, name FROM {users_roles} "
            . "WHERE avail = 1 ORDER BY name")->vars();
        $input_html['roles'] = '';
        foreach ($roles as $id => $role) {
            $input_html['roles'] .= '<option value="' . $id . '">' . $role . '</option>';
        }

        // валюты
        $input_html['currencies_select'] = '';
        $input_html['currencies'] = '';
        foreach ($this->all_configs['configs']['currencies'] as $cid => $currency) {
            $input_html['currencies_select'] .= '<option data-symbol="' . htmlspecialchars($currency['symbol']) . '" value="' . $cid . '">' . $currency['name'] . '</option>';
            $input_html['currencies'] .= '
                <div class="clearfix checkbox-with-course">
                    <div class="checkbox pull-left">
                        <label>
                            <input class="toggle-currency-course" type="checkbox" name="currencies[' . $cid . ']" value="' . $cid . '">
                            ' . $currency['name'] . '
                        </label>
                    </div>
                    <div class="col-xs-3">
                        <input class="hidden form-control currencies-courses" type="text" name="currencies_courses[' . $cid . ']" placeholder="' . l('Укажите курс для') . ' ' . $currency['name'] . '">
                    </div>
                </div>
            ';
        }

        // страны
        $input_html['country_select'] = '';
        foreach ($this->all_configs['configs']['countries'] as $id => $country) {
            $input_html['country_select'] .= '<option value="' . $id . '">' . $country['name'] . '</option>';
        }
    }

    /**
     * @return array
     */
    private function save()
    {
        
        //запускаем квериз
        require_once __DIR__ . '../setup/queries/queries.php';
        
        // business
        $business = isset($_POST['business']) ? $_POST['business'] : '';
        // country
        $country = isset($_POST['country']) ? $_POST['country'] : '';
        // название сервисного центра
        $site_name = isset($_POST['site_name']) ? $_POST['site_name'] : '';
        // phone
        $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
        // email
//        $email = isset($_POST['email']) ? $_POST['email'] : '';
        // сервисные центры
        $services = isset($_POST['services']) ? $_POST['services'] : array();
        // юзеры
        $users = isset($_POST['users']) ? $_POST['users'] : array();
        // валюта расчетов за заказы
        $orders_currency = isset($_POST['orders_currency']) ? $_POST['orders_currency'] : 0;
        // валюта расчетов за заказы поставщикам
        $contractors_currency = isset($_POST['contractors_currency']) ? $_POST['contractors_currency'] : 0;
        $contractors_currency_course = isset($_POST['course']) ? $_POST['course'] : 0;

        // проверка на ошибки
        if (!isset($this->all_configs['configs']['countries'][$country])) {
            return array('state' => false, 'msg' => l('Выберите страну'));
        }
        if (empty($site_name)) {
            return array('state' => false, 'msg' => l('Укажите название сервисного центра'));
        }
//        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//            return array('state' => false, 'msg' => l('Эл. адрес указан неверно'));
//        }

        if (!$orders_currency || !isset($this->all_configs['configs']['currencies'][$orders_currency])) {
            return array('state' => false, 'msg' => l('Выберите валюту расчетов за заказы'));
        }

        if (!$contractors_currency || !isset($this->all_configs['configs']['currencies'][$contractors_currency])) {
            return array('state' => false, 'msg' => l('Выберите валюту расчетов за заказы поставщикам'));
        }
        $added_services = 0;
        $added_services_names = array();
        $added_services_colors = array();
        foreach ($services as $i => $service) {
            if (!trim($service['name'])) {
                return array('state' => false, 'msg' => l('Не указано название отделения'));
            }
            if (!$service['color']) {
                return array(
                    'state' => false,
                    'msg' => l('Не выбран цвет отделения') . ' ' . htmlspecialchars($service['name'])
                );
            }
            if (!$service['address']) {
                return array(
                    'state' => false,
                    'msg' => l('Не выбран адрес отделения') . ' ' . htmlspecialchars($service['name'])
                );
            }
            if (!$service['phone']) {
                return array(
                    'state' => false,
                    'msg' => l('Не выбран телефон отделения') . ' ' . htmlspecialchars($service['name'])
                );
            }
            if (in_array($service['name'], $added_services_names)) {
                return array('state' => false, 'msg' => l('У отделений не могут быть одинаковые названия'));
            }
            if (in_array($service['color'], $added_services_colors)) {
                return array('state' => false, 'msg' => l('У отделений не могут быть одинаковые цвета'));
            }
            $added_services_names[] = trim($service['name']);
            $added_services_colors[] = trim($service['color']);
            $added_services++;
        }
        if (!$added_services) {
            return array('state' => false, 'msg' => l('Добавьте отделения'));
        }
        $users_added = 0;
        $users_logins = array();
        // если не одного юзера нема, то проверку не делаем 
        // и даем возможность зарегаться без сотрудников
        if (count($users) > 1 || $users[0]['fio'] || $users[0]['login'] || $users[0]['password']) {
            foreach ($users as $i => $user) {
                if (!isset($user['login']) || !$user['login']) {
                    return array('state' => false, 'msg' => l('Укажите логин пользователя'));
                }
                if (!isset($user['fio']) || !$user['fio']) {
                    return array('state' => false, 'msg' => l('Укажите ФИО пользователя '));
                }
                if (!isset($user['position']) || !$user['position']) {
                    return array(
                        'state' => false,
                        'msg' => l('Укажите должность пользователя ') . htmlspecialchars($user['fio'])
                    );
                }
                if (!isset($user['service'])) {
                    return array(
                        'state' => false,
                        'msg' => l('Укажите отделение пользователя ') . htmlspecialchars($user['fio'])
                    );
                }
                if ($user['password']) {
                    $users_added++;
                } else {
                    return array(
                        'state' => false,
                        'msg' => l('Укажите пароль для пользователя ') . htmlspecialchars($user['fio'])
                    );
                }
                if (in_array($user['login'], $users_logins)) {
                    return array('state' => false, 'msg' => l('У пользователей не могут быть одинаковые логины'));
                }
                $users_logins[] = trim($user['login']);
            }
        }
        // ------- проверка на ошибки

        // сохраняем страну
        $this->db->query("UPDATE {settings} SET value = ? "
            . "WHERE name = 'country'", array($country));
        // сохраняем бизнес и телефон юзера
        $this->db->query("UPDATE {settings} SET value = ? "
            . "WHERE name = 'account_phone'", array($phone));

        $this->db->query("UPDATE {settings} SET value = ? "
            . "WHERE name = 'account_business'", array($business));

        // вписываем мыло
//        $this->db->query("UPDATE {settings} SET value = ? "
//            . "WHERE name = 'content_email'", array($email));

        // название сервиса
        $this->db->query("UPDATE {settings} SET value = ? "
            . "WHERE name = 'site_name'", array($site_name));

        $this->db->query("UPDATE {settings} SET value = ? "
            . "WHERE name = 'currency_orders'", array($orders_currency));

        // создаем кассу основную
        $cashbox_id = $this->db->query("INSERT IGNORE INTO {cashboxes}(id,cashboxes_type,avail,"
            . "avail_in_balance,avail_in_orders,name) "
            . "VALUES(1,1,1,1,1,'" . lq('Основная') . "')")->id();
        // создаем кассу на которой будет происходить переводы валюты для контрагентов
        $cashbox_c_id = $this->db->query("INSERT IGNORE INTO {cashboxes}(id,cashboxes_type,avail,"
            . "avail_in_balance,avail_in_orders,name) "
            . "VALUES(2,1,1,1,1,'" . lq('Транзитная') . "')")->id();
        // создаем кассу терминал
        $cashbox_t_id = $this->db->query("INSERT IGNORE INTO {cashboxes}(id,cashboxes_type,avail,"
            . "avail_in_balance,avail_in_orders,name) "
            . "VALUES(3,1,1,1,1,'" . lq('Терминал') . "')")->id();

        $this->db->query("UPDATE {settings} SET value = ? "
            . "WHERE name = 'currency_suppliers_orders'", array($contractors_currency));

        // добавляем валюты
//        $currencies[$orders_currency] = $orders_currency;
        $currencies_courses[$orders_currency] = 1;
//        $currencies[$contractors_currency] = $contractors_currency;
        $currencies_courses[$contractors_currency] = $contractors_currency_course;
        foreach ($currencies_courses as $curr => $course) {
            if (isset($this->all_configs['configs']['currencies'][$curr])) {
//                $course = isset($currencies_courses[$curr]) ? (float)$currencies_courses[$curr] : 1;
                $name = $this->all_configs['configs']['currencies'][$curr]['name'];
                $short_name = $this->all_configs['configs']['currencies'][$curr]['shortName'];
                $id = $this->db->query("INSERT IGNORE INTO {cashboxes_courses}(currency,name,short_name,course)"
                    . "VALUES(?i,?,?,?f)", array($curr, $name, $short_name, ($course > 0 ? $course : 1) * 100), 'id');
                // привязываем валюты в основную кассу
                $this->db->query("INSERT IGNORE  INTO {cashboxes_currencies}(cashbox_id,currency,amount) "
                    . "VALUES(?i,?i,0)", array($cashbox_id, $curr));
                // привязываем валюты в транзитную кассу
                $this->db->query("INSERT IGNORE  INTO {cashboxes_currencies}(cashbox_id,currency,amount) "
                    . "VALUES(?i,?i,0)", array($cashbox_c_id, $curr));
                // привязываем валюты в терминал кассу
                $this->db->query("INSERT IGNORE  INTO {cashboxes_currencies}(cashbox_id,currency,amount) "
                    . "VALUES(?i,?i,0)", array($cashbox_t_id, $curr));
            }
        }

        // добавляем сервис центры
        $added_services = array();
        foreach ($services as $i => $service) {
            if (trim($service['name'])) {
                $color = preg_match('/^#[a-f0-9]{6}$/i', trim($service['color'])) ? trim($service['color']) : '#000000';
                $id = $this->db->query(
                    'INSERT IGNORE INTO {warehouses_groups} (name, color, user_id, address) VALUES (?, ?, ?i, ?)',
                    array($service['name'], $color, $_SESSION['id'], $service['address']), 'id');
                if ($id) {
                    // основной
                    $main_wh = $this->create_warehouse($service['name'], $service['address'], $service['phone'], 1, $id,
                        1, 1);
                    // прикрепляем текущего админа к складу
                    $this->db->query("INSERT INTO {warehouses_users}(wh_id,location_id,user_id,main) "
                        . "VALUES(?i,?i,?i,1)",
                        array(
                            $id,
                            $main_wh['loc_id'],
                            $_SESSION['id']
                        ));
                    // брак
                    $this->create_warehouse(lq('Брак') . ' ' . $service['name'], '', '', 1, $id, 1, 0);
                    // клиент
                    $this->create_warehouse(lq('Клиент') . ' ' . $service['name'], '', '', 4, $id, 0, 0);
                    $added_services[$i] = array(
                            'id' => $id
                        ) + $main_wh;
                }
            }
        }
        // склад логистика без группы
        $this->create_warehouse(lq('Логистика'), '', '', 3, 0, 1, 1);
        // недостача без группы
        $this->create_warehouse(lq('Недостача'), '', '', 2, 0, 0, 0);

        // добавляем юзеров
        foreach ($users as $i => $user) {
            if ($user['login'] && $user['password']) {
                $user['position'] = isset($user['position']) ? $user['position'] : 0;
                $user_id = $this->db->query("INSERT INTO {users}(login,pass,email,fio,is_adm,role,state,avail) "
                    . "VALUES(?,?,?,?,1,?i,1,1)", array(
                    $user['login'],
                    $user['password'],
                    $user['login'],
                    $user['fio'],
                    $user['position']
                ))->id();
                if ($user_id && isset($user['service']) && isset($added_services[$user['service']])) {
                    // прикрепляем в складу
                    $this->db->query("INSERT INTO {warehouses_users}(wh_id,location_id,user_id,main) "
                        . "VALUES(?i,?i,?i,1)",
                        array(
                            $added_services[$user['service']]['wh_id'],
                            $added_services[$user['service']]['loc_id'],
                            $user_id
                        ));
                }
            }
        }

        $this->setGoodsManager();

        // ставим отметку что мастер настройки закончен
        $this->db->query("UPDATE {settings} SET value = 1 WHERE name = 'complete-master'", array());

        setcookie('show_intro', 1, time() + 600, $this->all_configs['prefix']);
        return array(
            'state' => true,
            'redirect' => $this->all_configs['prefix'] . 'orders'
        );
    }

    /**
     * @param     $name
     * @param     $address
     * @param     $phone
     * @param     $type
     * @param     $group_id
     * @param int $consider_all
     * @param int $consider_store
     * @return array
     */
    private function create_warehouse($name, $address, $phone, $type, $group_id, $consider_all = 0, $consider_store = 1)
    {
        // создаем склад
        $warehouse_id = $this->db->query('INSERT IGNORE INTO {warehouses}
            (consider_all, consider_store, code_1c, title, print_address,
             print_phone, type, group_id, type_id) VALUES (?i, ?i, null, ?, ?, ?, ?i, ?n, ?n)',
            array($consider_all, $consider_store, $name, $address, $phone, $type, $group_id, 1), 'id');
        // и локацию
        $location_id = $this->db->query("INSERT IGNORE INTO {warehouses_locations} (wh_id,location)"
            . "VALUES(?i,?)", array($warehouse_id, $name), 'id');
        return array(
            'wh_id' => $warehouse_id,
            'loc_id' => $location_id
        );
    }

    /**
     *
     */
    private function ajax()
    {
        $data = array(
            'state' => false,
            'msg' => 'Error'
        );
        $act = isset($_POST['act']) ? $_POST['act'] : '';
        switch ($act) {
            case 'save_data':
                $data = $this->save();
                break;
        }
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    /**
     *
     */
    private function setGoodsManager()
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        if (empty($user_id)) {
            return;
        }
        $goods = $this->db->query('SELECT goods_id FROM {users_goods_manager}', array())->col();
        $query = '';
        if (!empty($goods)) {
            $query = $this->db->makeQuery("AND not id in (?li)", array($goods));
        }
        $this->db->query("INSERT INTO {users_goods_manager} (goods_id, user_id) SELECT id, ?i FROM {goods} WHERE 1=1 ?q",
            array($user_id, $query));
    }
}

