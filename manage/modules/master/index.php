<?php

require_once __DIR__ . '/../../Core/Object.php';
require_once __DIR__ . '/../../Core/Response.php';
require_once __DIR__ . '/../../Core/Exceptions.php';
require_once __DIR__ . '/../../Core/View.php';

$modulename[] = 'master';
$modulemenu[] = 'Мастер';
$moduleactive[] = true;

/**
 * @property  db
 * @property MSettings Settings
 */
class master extends Object
{
    protected $all_configs;
    protected $db;
    private $lang;
    private $def_lang;
    private $langs;
    public $uses = array(
        'Settings'
    );

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

        $view = new View($this->all_configs);
        // должности юзеров
        $input_html['roles'] = $view->renderFile('master/_roles', array(
            'roles' => $this->db->query("SELECT id, name FROM {users_roles} WHERE avail = 1 ORDER BY name")->vars()
        ));

        // валюты
        $input_html['currencies_select'] = $view->renderFile('master/_currencies_select', array(
            'currencies' => $this->all_configs['configs']['currencies']
        ));
        $input_html['currencies'] = $view->renderFile('master/_currencies', array(
            'currencies' => $this->all_configs['configs']['currencies']
        ));

        // страны
        $input_html['country_select'] = '';
        $countryIds = array();
        foreach ($this->all_configs['configs']['countries'] as $id => $country) {
            $countryIds[$country['name']] = $id;
        }
        ksort($countryIds, SORT_LOCALE_STRING);
        $input_html['country_select'] = $view->renderFile('master/_currencies', array(
            'countryIds' => $countryIds
        ));
    }

    /**
     * @return array
     */
    private function save()
    {
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

        try {
            // проверка на ошибки
            if (!isset($this->all_configs['configs']['countries'][$country])) {
                throw new ExceptionWithMsg(l('Выберите страну'));
            }
            if (empty($site_name)) {
                throw new ExceptionWithMsg(l('Укажите название сервисного центра'));
            }

            if (!$orders_currency || !isset($this->all_configs['configs']['currencies'][$orders_currency])) {
                throw new ExceptionWithMsg(l('Выберите валюту расчетов за заказы'));
            }

            if (!$contractors_currency || !isset($this->all_configs['configs']['currencies'][$contractors_currency])) {
                throw new ExceptionWithMsg(l('Выберите валюту расчетов за заказы поставщикам'));
            }
            // ------- проверка на ошибки
            $this->validateServicesData($services);
            $this->validateUsersData($users);

            $this->saveMainSettings($country, $phone, $business, $site_name, $orders_currency);
            $this->addCurrencies($contractors_currency, $orders_currency, $contractors_currency_course);
            $added_services = $this->addServices($services);
            $this->createWarehouses();
            $this->addUsers($users, $added_services);
            $this->setGoodsManager();
            $this->endSetup();
        } catch (ExceptionWithMsg $e) {
            return array(
                'state' => false,
                'msg' => $e->getMessage()
            );
        }
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
             print_phone, type, group_id, type_id, is_system) VALUES (?i, ?i, null, ?, ?, ?, ?i, ?n, ?n, 1)',
            array($consider_all, $consider_store, $name, $address, $phone, $type, $group_id, 1), 'id');
        // и локацию
        $location_id = $this->db->query("INSERT IGNORE INTO {warehouses_locations} (wh_id,location) VALUES(?i,?)",
            array($warehouse_id, $name), 'id');
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
        if ($act === 'save_data') {
            $data = $this->save();
        }
        Response::json($data);
    }

    /**
     *
     */
    private function setGoodsManager()
    {
        $user_id = $this->db->query('SELECT id FROM {users} ORDER BY id ASC LIMIT 1')->el();
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

    /**
     *
     */
    private function endSetup()
    {
// ставим отметку что мастер настройки закончен
        $this->Settings->deleteAll(array(
            'name' => array(
                'order-fields-hide',
                'site-for-add-rating',
                'order-send-sms-with-client-code'
            )
        ));
        $this->Settings->update(array('value' => 1), array('name' => 'complete-master'));

        setcookie('show_intro', 1, time() + 600, $this->all_configs['prefix']);
    }

    /**
     * @param $users
     * @param $added_services
     */
    private function addUsers($users, $added_services)
    {
// добавляем юзеров
        foreach ($users as $i => $user) {
            if ($user['login'] && $user['password']) {
                $user['position'] = isset($user['position']) ? $user['position'] : 0;
                $user_id = $this->db->query("INSERT INTO {users}(login,pass,email,fio,is_adm,role,state,avail) VALUES(?,?,?,?,1,?i,1,1)",
                    array(
                        $user['login'],
                        $user['password'],
                        $user['login'],
                        $user['fio'],
                        $user['position']
                    ))->id();
                if ($user_id && isset($user['service']) && isset($added_services[$user['service']])) {
                    // прикрепляем в складу
                    $this->db->query("INSERT INTO {warehouses_users}(wh_id,location_id,user_id,main) VALUES(?i,?i,?i,1)",
                        array(
                            $added_services[$user['service']]['wh_id'],
                            $added_services[$user['service']]['loc_id'],
                            $user_id
                        ));
                }
            }
        }
    }

    /**
     *
     */
    private function createWarehouses()
    {
// склады брак и клиент присутствуют в системе в единственном числе без групп
        // брак
        $this->create_warehouse(lq('Брак'), '', '', 1, 0, 1, 0);
        // клиент
        $this->create_warehouse(lq('Клиент'), '', '', 4, 0, 0, 0);
        // склад логистика без группы
        $this->create_warehouse(lq('Логистика'), '', '', 3, 0, 1, 1);
        // недостача без группы
        $this->create_warehouse(lq('Недостача'), '', '', 2, 0, 0, 0);
    }

    /**
     * @param $services
     * @return array
     */
    private function addServices($services)
    {
// добавляем сервис центры
        $added_services = array();
        foreach ($services as $i => $service) {
            if (trim($service['name'])) {
                $color = preg_match('/^#[a-f0-9]{6}$/i',
                    trim($service['color'])) ? trim($service['color']) : '#000000';
                $id = $this->db->query(
                    'INSERT IGNORE INTO {warehouses_groups} (name, color, user_id, address) VALUES (?, ?, ?i, ?)',
                    array($service['name'], $color, $_SESSION['id'], $service['address']), 'id');
                if ($id) {
                    // основной
                    $main_wh = $this->create_warehouse($service['name'], $service['address'], $service['phone'], 1, $id,
                        1, 1);
                    // прикрепляем текущего админа к складу
                    $this->db->query("INSERT INTO {warehouses_users}(wh_id,location_id,user_id,main) VALUES(?i,?i,?i,1)",
                        array(
                            $id,
                            $main_wh['loc_id'],
                            $_SESSION['id']
                        ));
                    $added_services[$i] = array(
                            'id' => $id
                        ) + $main_wh;
                }
            }
        }
        return $added_services;
    }

    /**
     * @param $contractors_currency
     * @param $orders_currency
     * @param $contractors_currency_course
     */
    private function addCurrencies(
        $contractors_currency,
        $orders_currency,
        $contractors_currency_course
    ) {
        $cashbox_id = $this->db->query("
            INSERT IGNORE INTO {cashboxes}(id,cashboxes_type,avail,avail_in_balance,avail_in_orders,name) VALUES(?i,1,1,1,1,?)",
            array(
                SYSTEM_CASHBOX_MAIN_ID,
                lq('Основная')
            ))->id();
        // создаем кассу на которой будет происходить переводы валюты для контрагентов
        $cashbox_c_id = $this->db->query("
            INSERT IGNORE INTO {cashboxes}(id,cashboxes_type,avail,avail_in_balance,avail_in_orders,name) VALUES(?i,1,1,1,1,?)",
            array(SYSTEM_CASHBOX_TRANSIT_ID, lq('Транзитная')))->id();
        // создаем кассу терминал
        $cashbox_t_id = $this->db->query("
            INSERT IGNORE INTO {cashboxes}(id,cashboxes_type,avail,avail_in_balance,avail_in_orders,name) VALUES(?i,1,1,1,1,?)",
            array(SYSTEM_CASHBOX_TERMINAL_ID, lq('Терминал')))->id();

        $this->Settings->update(array('value' => $contractors_currency), array('name' => 'currency_suppliers_orders'));

        $currencies_courses = array();
        // добавляем валюты
        $currencies_courses[$orders_currency] = 1;
        $currencies_courses[$contractors_currency] = $contractors_currency_course;
        foreach ($currencies_courses as $curr => $course) {
            if (isset($this->all_configs['configs']['currencies'][$curr])) {
                $name = $this->all_configs['configs']['currencies'][$curr]['name'];
                $short_name = $this->all_configs['configs']['currencies'][$curr]['shortName'];
                $id = $this->db->query("
                    INSERT IGNORE INTO {cashboxes_courses}(currency,name,short_name,course) VALUES(?i,?,?,?f)",
                    array($curr, $name, $short_name, ($course > 0 ? $course : 1) * 100),
                    'id');
                // привязываем валюты в основную кассу
                $this->db->query("INSERT IGNORE  INTO {cashboxes_currencies}(cashbox_id,currency,amount) VALUES(?i,?i,0)",
                    array($cashbox_id, $curr));
                // привязываем валюты в транзитную кассу
                $this->db->query("INSERT IGNORE  INTO {cashboxes_currencies}(cashbox_id,currency,amount) VALUES(?i,?i,0)",
                    array($cashbox_c_id, $curr));
                // привязываем валюты в терминал кассу
                $this->db->query("INSERT IGNORE  INTO {cashboxes_currencies}(cashbox_id,currency,amount) VALUES(?i,?i,0)",
                    array($cashbox_t_id, $curr));
            }
        }
    }

    /**
     * @param $country
     * @param $phone
     * @param $business
     * @param $site_name
     * @param $orders_currency
     */
    private function saveMainSettings($country, $phone, $business, $site_name, $orders_currency)
    {
// сохраняем страну
        $this->Settings->update(array('value' => $country), array('name' => 'country'));

        // сохраняем бизнес и телефон юзера
        $this->Settings->update(array('value' => $phone), array('name' => 'account_phone'));

        $this->Settings->update(array('value' => $business), array('name' => 'account_business'));

        // название сервиса
        $this->Settings->update(array('value' => $site_name), array('name' => 'site_name'));

        $this->Settings->update(array('value' => $orders_currency), array('name' => 'currency_orders'));
    }

    /**
     * @param $services
     * @return bool
     * @throws ExceptionWithMsg
     */
    private function validateServicesData($services)
    {
        $added_services_names = array();
        $added_services_colors = array();
        foreach ($services as $i => $service) {
            if (!trim($service['name'])) {
                throw new ExceptionWithMsg(l('Не указано название отделения'));
            }
            if (!$service['color']) {
                throw new ExceptionWithMsg(l('Не выбран цвет отделения') . ' ' . h($service['name']));
            }
            if (!$service['address']) {
                throw new ExceptionWithMsg(l('Не выбран адрес отделения') . ' ' . h($service['name']));
            }
            if (!$service['phone']) {
                throw new ExceptionWithMsg(l('Не выбран телефон отделения') . ' ' . h($service['name']));
            }
            if (in_array($service['name'], $added_services_names)) {
                throw new ExceptionWithMsg(l('У отделений не могут быть одинаковые названия'));
            }
            if (in_array($service['color'], $added_services_colors)) {
                throw new ExceptionWithMsg(l('У отделений не могут быть одинаковые цвета'));
            }
            $added_services_names[] = trim($service['name']);
            $added_services_colors[] = trim($service['color']);
        }
        if (empty($added_services_names)) {
            throw new ExceptionWithMsg(l('Добавьте отделения'));
        }
        return true;
    }

    /**
     * @param $users
     * @throws ExceptionWithMsg
     */
    private function validateUsersData($users)
    {
        $users_logins = array();
        // если не одного юзера нема, то проверку не делаем
        // и даем возможность зарегаться без сотрудников
        if (count($users) > 1 || $users[0]['fio'] || $users[0]['login'] || $users[0]['password']) {
            foreach ($users as $i => $user) {
                if (!isset($user['login']) || !$user['login']) {
                    throw new ExceptionWithMsg(l('Укажите логин пользователя'));
                }
                if (!isset($user['fio']) || !$user['fio']) {
                    throw new ExceptionWithMsg(l('Укажите ФИО пользователя '));
                }
                if (!isset($user['position']) || !$user['position']) {
                    throw new ExceptionWithMsg(l('Укажите должность пользователя ') . h($user['fio']));
                }
                if (!isset($user['service'])) {
                    throw new ExceptionWithMsg(l('Укажите отделение пользователя ') . h($user['fio']));
                }
                if (empty($user['password'])) {
                    throw new ExceptionWithMsg(l('Укажите пароль для пользователя ') . h($user['fio']));
                }
                if (in_array($user['login'], $users_logins)) {
                    throw new ExceptionWithMsg(l('У пользователей не могут быть одинаковые логины'));
                }
                $users_logins[] = trim($user['login']);
            }
        }
    }
}

