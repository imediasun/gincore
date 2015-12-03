<?php

// настройки
$modulename[120] = 'debug';
$modulemenu[120] = l('debug_modulemenu');  //карта сайта

$moduleactive[120] = !$ifauth['is_2'];

class debug{

    protected $all_configs;

    public $debuggers;
    public $debug_title = '';
    
    function __construct(&$all_configs)
    {
        global $input_html, $ifauth;

        $this->all_configs = &$all_configs;

        if (!$this->all_configs['oRole']->hasPrivilege('site-administration')) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">У Вас нет прав</p></div>';
        }

        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }
        
        if($ifauth['is_2']) return false;
        
        $this->debuggers = array(
            array(
                'url' => 'visit-high-price',
                    'title' => 'Повышенные цены для посетителей',
            ),
            array(
                'url' => 'price_parser',
                'title' => 'Парсер цен со страниц оборудования ',
            ),
            array(
                'url' => 'show_price_tables',
                'title' => 'Показать таблички с ценами',
            ),
        );
        // доступ к сбросу
        if($this->all_configs['configs']['manage-reset-access']){
            $this->debuggers[] = array(
                'url' => 'reset',
                'title' => 'Сброс',
            );
        }

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    private function genmenu(){
        $out = '<h4>'.l('debug_list')
//                .' <a style="text-decoration:none" href="'.$this->all_configs['prefix'].'settings/add">+</a>'
                .'</h4>';

        $out .= '<ul>';
        foreach($this->debuggers as $pps){
            
            if(isset($this->all_configs['arrequest'][1]) && $pps['url'] == $this->all_configs['arrequest'][1]) {
                $style = ' style="font-weight: bold"';
                $this->debug_title = $pps['title'];
            } else {
                $style = '';
            }
            $out.='<li><a href="'.$this->all_configs['prefix'].'debug/'.$pps['url'].'"'.$style.'>'.$pps['title'].'</a></li>';
            
        }
        $out .= '</ul>';

        return $out;
    }

    private function gencontent(){
        GLOBAL $ifauth;

        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $value = isset($_POST['value']) ? $_POST['value'] : '';


        if(!isset($this->all_configs['arrequest'][1])){
            $out = l('debug_description');
        }

###############################################################################
        if(isset($this->all_configs['arrequest'][1])){
            $out = '<h3>'.$this->debug_title.'</h3>';

            $out .= $this->gen_debuggers();
            
        }

################################################################################
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'save'){
            $out = l('debug_update_success').' <a href="'.$this->all_configs['prefix'].'debug/'.$this->all_configs['arrequest'][2].'">'.l('continue').'</a>';
        }
###############################################################################


        return $out;
    }

    private function ajax(){

        $data = array(
            'state' => false
        );

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }
    
    private function gen_debuggers(){
        $out = '';
        $href = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1];

        // отладка количества визитов
        if($this->all_configs['arrequest'][1] == 'visit-high-price'){
            require_once $this->all_configs['path'].'../class_visitors.php';
            /*
            $visit = $visitors->init_visitors();
            $out = 'Визитов: '.$visit;
            */
            if(!empty($_GET)) {
                $visitors = Visitors::getInstance();
                $visitors->allow_reset();
                $visitors->init_visitors();
                $out = '<div class="alert alert-success">'
                    .'Вы успешно обновили информацию<br>'
                    .'Ваших визитов: '.$visitors->get_visit().'<br>'
                    .'<a class="alert-link" href="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/'.$this->all_configs['arrequest'][1].'">Вернуться к отладчику</a>'
                    .'</div>';
            }
            
            $out .= 
                '<a class="btn btn-success" href="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/'.$this->all_configs['arrequest'][1].'?reset">Отметить мое посещение как новое</a><br><br>'
                .'<a class="btn btn-success" href="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/'.$this->all_configs['arrequest'][1].'?set">Отметить мое посещение как повторное</a><br><br>'
                .'<a class="btn btn-success" href="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/'.$this->all_configs['arrequest'][1].'?reset">Обычный режим</a><br><br>'
                ;
            

        }

        if ($this->all_configs['arrequest'][1] == 'reset' /*&& $this->all_configs['configs']['manage-reset-access']*/) {

            if(!empty($_GET)) {
                $this->all_configs['db']->query('SET FOREIGN_KEY_CHECKS=0');
                $this->all_configs['db']->query('TRUNCATE TABLE {orders_images}');
                $this->all_configs['db']->query('TRUNCATE TABLE {cashboxes_amount_by_day}');
                $this->all_configs['db']->query('UPDATE {cashboxes_currencies} SET `amount` = 0');
                $this->all_configs['db']->query('UPDATE {contractors} SET `amount` = 0');
                $this->all_configs['db']->query('TRUNCATE TABLE {cashboxes_transactions}');
                $this->all_configs['db']->query('TRUNCATE TABLE {changes}');
                $this->all_configs['db']->query('DELETE FROM {contractors_suppliers_orders}');
                $this->all_configs['db']->query('ALTER TABLE {contractors_suppliers_orders} auto_increment = 1');
                $this->all_configs['db']->query('TRUNCATE TABLE {contractors_transactions}');
                $this->all_configs['db']->query('TRUNCATE TABLE {messages}');
                $this->all_configs['db']->query('TRUNCATE TABLE {orders_comments}');
                $this->all_configs['db']->query('DELETE FROM {orders_goods}');
                $this->all_configs['db']->query('ALTER TABLE {orders_goods} auto_increment = 1');
                $this->all_configs['db']->query('TRUNCATE TABLE {orders_suppliers_clients}');
                $this->all_configs['db']->query('TRUNCATE TABLE {order_status}');
                $this->all_configs['db']->query('TRUNCATE TABLE {goods_amount}');
                $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_goods_amount}');
                $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_stock_moves}');
                $this->all_configs['db']->query('DELETE FROM {warehouses_goods_items}');
                $this->all_configs['db']->query('ALTER TABLE {warehouses_goods_items} auto_increment = 1');
                $this->all_configs['db']->query('DELETE FROM {orders}');
                $this->all_configs['db']->query('ALTER TABLE {orders} auto_increment = 1');
                $this->all_configs['db']->query('UPDATE {goods} SET qty_store = 0, qty_wh = 0');
                $this->all_configs['db']->query('TRUNCATE TABLE {alarm_clock}');
                $this->all_configs['db']->query('TRUNCATE TABLE {users_marked}');
                $this->all_configs['db']->query('TRUNCATE TABLE {goods_demand}');
                $this->all_configs['db']->query('TRUNCATE TABLE {clients}');
                $this->all_configs['db']->query('TRUNCATE TABLE {clients_phones}');
                $this->all_configs['db']->query('TRUNCATE TABLE {chains}');
                $this->all_configs['db']->query('TRUNCATE TABLE {chains_bodies}');
                $this->all_configs['db']->query('TRUNCATE TABLE {chains_headers}');
                $this->all_configs['db']->query('TRUNCATE TABLE {chains_moves}');
                $this->all_configs['db']->query('TRUNCATE TABLE {cashboxes_currencies}');
                $this->all_configs['db']->query('TRUNCATE TABLE {cashboxes_courses}');
                $this->all_configs['db']->query('TRUNCATE TABLE {cashboxes}');
                $this->all_configs['db']->query('TRUNCATE TABLE {contractors}');
                $this->all_configs['db']->query('TRUNCATE TABLE {contractors_categories_links}');
                $this->all_configs['db']->query('TRUNCATE TABLE {goods_suppliers}');
                $this->all_configs['db']->query('TRUNCATE TABLE {orders_manager_history}');
                $this->all_configs['db']->query('TRUNCATE TABLE {users_notices}');
                $this->all_configs['db']->query('TRUNCATE TABLE {warehouses}');
                $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_goods_amount}');
                $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_goods_items}');
                $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_groups}');
                $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_locations}');
                $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_stock_moves}');
                $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_users}');
                $this->all_configs['db']->query('TRUNCATE TABLE {sms_senders}');
                $this->all_configs['db']->query('TRUNCATE TABLE {sms_templates}');
                $this->all_configs['db']->query('TRUNCATE TABLE {sms_templates_strings}');
                $this->all_configs['db']->query('TRUNCATE TABLE {sms_log}');
                $this->all_configs['db']->query('TRUNCATE TABLE {tasks}');
                $this->all_configs['db']->query('TRUNCATE TABLE {crm_analytics}');
                $this->all_configs['db']->query('TRUNCATE TABLE {crm_calls}');
                $this->all_configs['db']->query('TRUNCATE TABLE {crm_expenses}');
                $this->all_configs['db']->query('TRUNCATE TABLE {crm_requests}');
                $this->all_configs['db']->query('TRUNCATE TABLE {cron_history}');
                $this->all_configs['db']->query('TRUNCATE TABLE {forms}');
                $this->all_configs['db']->query('TRUNCATE TABLE {forms_data}');
                $this->all_configs['db']->query('TRUNCATE TABLE {forms_fields}');
                $this->all_configs['db']->query('TRUNCATE TABLE {forms_fields_strings}');
                $this->all_configs['db']->query('TRUNCATE TABLE {merchant_logger}');
                $this->all_configs['db']->query('TRUNCATE TABLE {forms_strings}');
                $this->all_configs['db']->query('TRUNCATE TABLE {forms_users}');
                $this->all_configs['db']->query('TRUNCATE TABLE {image_titles}');
                $this->all_configs['db']->query('TRUNCATE TABLE {visitors}');
                $this->all_configs['db']->query('TRUNCATE TABLE {visitors_code}');
                $this->all_configs['db']->query('TRUNCATE TABLE {visitors_system_codes}');
                $this->all_configs['db']->query('DELETE FROM {users_goods_manager} WHERE id <> ?i', array($_SESSION['id']));
                $this->all_configs['db']->query('DELETE FROM {users} WHERE id <> ?i', array($_SESSION['id']));
                // статьи расход-приход
                $this->all_configs['db']->query('TRUNCATE TABLE {contractors_categories}');
                $this->all_configs['db']->query("
                    INSERT INTO `restore4_contractors_categories` 
                    (`id`, `parent_id`, `avail`, `transaction_type`, `name`, `code_1c`, `date_add`, `comment`) VALUES
                    (1, 0, b'1', 2, 'Оплаты за заказы с сайта', '', NOW(), ''),
                    (2, 0, b'1', 1, 'Возврат денег за заказ с сайта', '', NOW(), ''),
                    (3, 0, b'1', 1, 'Контрагент', '', NOW(), ''),
                    (4, 0, b'1', 1, 'Списание', '', NOW(), ''),
                    (5, 0, b'1', 2, 'Списание с баланса за возврат поставщику', '', NOW(), ''),
                    (6, 0, b'1', 1, 'Списание денег с баланса за возврат поставщику', '', NOW(), ''),
                    (7, 0, b'1', 1, 'Оплата заказа поставщику', '', NOW(), ''),
                    (8, 0, b'1', 1, 'Оплата заказа поставщику (на баланс, без привязки к заказу)', '', NOW(), ''),
                    (9, 0, b'1', 1, 'Комиссионные взыскания банков', '', NOW(), ''),
                    (12, 0, b'1', 1, 'Оплата за аренду (недвижимости)', '', NOW(), ''),
                    (14, 0, b'1', 1, 'Оплата коммунальных услуг', '', NOW(), ''),
                    (15, 0, b'1', 1, 'Почтовые расходы', '', NOW(), ''),
                    (16, 0, b'1', 1, 'Расходы на телефонию и связь', '', NOW(), ''),
                    (17, 16, b'1', 1, 'Интернет', '', NOW(), ''),
                    (18, 16, b'1', 1, 'СМС отправки', '', NOW(), ''),
                    (20, 0, b'1', 1, 'Расходы на рекламу', '', NOW(), ''),
                    (21, 20, b'1', 1, 'Google Adwords', '', NOW(), ''),
                    (22, 20, b'1', 1, 'Яндекс Direct', '', NOW(), ''),
                    (23, 20, b'1', 1, 'SEO ( органическое продвижение)', '', NOW(), ''),
                    (24, 20, b'1', 1, 'Расходы на наружную рекламу', '', NOW(), ''),
                    (25, 0, b'1', 1, 'Обслуживание офиса', '', NOW(), ''),
                    (26, 25, b'1', 1, 'Расходы на хозтовары', '', NOW(), ''),
                    (27, 25, b'1', 1, 'Расходы канцтовары', '', NOW(), ''),
                    (28, 25, b'1', 1, 'Услуги пультовой охраны', '', NOW(), ''),
                    (29, 0, b'1', 1, 'Зарплаты', '', NOW(), ''),
                    (30, 34, b'1', 1, 'Инвентарь', '', NOW(), ''),
                    (31, 0, b'1', 2, 'Инвестиции', '', NOW(), ''),
                    (32, 0, b'1', 2, 'Ввод денежных остатков', '', NOW(), ''),
                    (33, 0, b'1', 2, 'Предоплата заказов на ремонт', '', NOW(), ''),
                    (34, 0, b'1', 1, 'Необоротные активы', '', NOW(), ''),
                    (35, 34, b'1', 1, 'Мебель', '', NOW(), ''),
                    (36, 34, b'1', 1, 'Оргтехника', '', NOW(), '')
                ");
                // настройки
                $this->all_configs['db']->query('TRUNCATE TABLE {settings}');
                $this->all_configs['db']->query('
                    INSERT INTO `restore4_settings` (`id`, `section`, `description`, `name`, `value`, `title`, `ro`) VALUES
                    (1,  1, "", \'content_email\', \'\', \'Эл. адрес\', 0),
                    (2,  1, "", \'site_name\', \'Сервисный центр\', \'Название сайта\', 0),
                    (34, 1, "", \'turbosms-from\', \'\', \'Турбосмс от кого\', 0),
                    (35, 1, "", \'turbosms-login\', \'\', \'Турбосмс логин\', 0),
                    (36, 1, "", \'turbosms-password\', \'\', \'Турбосмс пароль\', 0),
                    (37, 1, "", \'orders_comments_days\', \'3\', \'Количество дней для уведомления менеджера об отсутствии новых записей в статусе заказа\', 0),
                    (38, 1, "", \'warranties_left_days\', \'1,3,7\', \'Дни, для уведомлений менеджеру до конца 14ти дневного срока гарантийного обслуживания\', 0),
                    (39, 1, "", \'unsold_items_days\', \'10\', \'Количество дней для уведомления менеджера о нарушении оборачиваемости\', 0),
                    (42, 1, "", \'cat-non-all-ext\', \'2, 7, 8, 6, 5\', \'Статьи не используемые в выдачах\', 0),
                    (43, 1, "Укажите номер статьи", \'cat-non-current-assets\', \'34\', \'Статьи используемые для вычисления необоротных активов\', 0),
                    (48, 1, "Укажите доступные сроки гарантии через запятую", \'order_warranties\', \'1,3,6,12\', \'Гарантии в заказ на ремонт\', 0),
                    (52, 1, "", \'demand-factor\', \'0.33\', \'Коэффициент спроса\', 0),
                    (70, 1, "", \'currency_suppliers_orders\', \'3\', \'Валюта заказов поставщикам\', 0),
                    (71, 1, "", \'currency_orders\', \'1\', \'Валюта заказов\', 0),
                    (82, 1, "", \'complete-master\', \'0\', \'Пройден мастер настройки\', 1)
                ');
//                    (22, 1, "", \'grn-cash\', \'2600\', \'Курс валют доллар\', 0),
//                    (51, 1, "", \'print_template_warranty\', \'<h4><span style="font-family: Arial;">Сервисный центр “Restore”</span></h4><span style="font-family: Arial;">Адрес фирмы: {{wh_address}}<br>{{wh_phone}}<br>Точка: {{warehouse_accept}}<br><div style="text-align: center;"><h4><span style="font-family: Arial;">Гарантийный талон №{{id}} от {{now}}</span></h4></div><span style="font-family: Arial;">Заказчик: {{fio}} тел.: {{phone}}<br>Заявленная неисправность: {{defect}}<br>{{product}}, IMEI: {{serial}}<br><br><table class="table table-bordered"><tbody><tr><td>Устраненная неисправность</td><td>Стоимость работы</td></tr><tr><td>{{services}}</td><td>{{services_cost}} грн.</td></tr><tr><td>Установленные запчасти</td><td>Стоимость комплектующих</td></tr><tr><td>{{products}}</td><td>{{products_cost}}</td></tr></tbody></table>Срок гарантии: {{warranty}}<br>Инженер: {{engineer}}<br>Примечание к ремонту: {{comment}}<br><br>ИТОГО ЗА РЕМОНТ: {{sum}} грн.<br>ОПЛАЧЕНО: {{sum_paid}} грн.<br><br>Гарантийный ремонт осуществляется&nbsp; при условии правильного и четкого заполнения гарантийного талона,распространяется только на неисправности ,устраненные в результате произведенного ремонта.Гарантийный ремонт не распространяется на акссесуары поставляемые с оборудованием.<br>Сервисный центр может отказать в гарантийном ремонте в следующих случаях:<ul><li><span style="font-family: Arial;">Неправильно заполненных документов.</span></li><li><span style="font-family: Arial;">Нарушения сохранности гарантийных пломб.</span></li><li><span style="font-family: Arial;">Использования оборудования вместе с акссесуарами ,не одобреных предприятием изготовителем.</span></li><li><span style="font-family: Arial;">Повреждений вызванных нарушением условий эксплуатации&nbsp; и хранения;несанкционированным вмешательством;механическим,химическим или тепловым воздействием;стихийными бедствиями,водой.</span></li><li><span style="font-family: Arial;">Данные услуги по желанию клиента могут быть выполнены за отдельную плату.</span></li></ul><span style="font-family: Arial;"><br>От Исполнителя______________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; От Заказчика_____________<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; СЦ «Restore»&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{fio}}<br><br>М.П</span></span></span>\', \'Шаблон печати гарантии\', 0),
//                    (65, 1, "", \'print_template_check\', \'<div style="text-align: right;">\n    <h4 style="text-align: right;"><span style="color: inherit; font-family: Arial; font-size: 18px;">Сервисный центр “Restore”</span><span style="font-family: Arial; font-size: 11px; font-weight: normal; line-height: 12px; color: inherit;">&nbsp;</span><span style="color: inherit; font-size: 18px; font-family: Arial;">&nbsp;&nbsp;</span></h4><h4 style="text-align: right;"><span style="color: inherit; font-size: 18px; font-family: Arial;">&nbsp; &nbsp;</span><span style="font-family: Tahoma;"><span style="color: inherit; font-size: 11px; line-height: 12px;">{{wh_address}}&nbsp;</span><span style="color: inherit; font-size: 11px; line-height: 12px;">{{wh_phone}}</span></span></h4><h4 style="text-align: center;"><span style="font-family: Arial; color: inherit; font-size: 18px;">Квитанция №</span><span style="font-family: Arial; color: inherit; font-size: 18px;">{{id}}</span><span style="font-family: Arial; color: inherit; font-size: 18px;"> от&nbsp;</span><span style="color: inherit; font-family: inherit; font-size: 18px; text-align: left;">{{date}}</span></h4><h4 style="text-align: center;"><span style="font-family: Arial; font-size: 11px; line-height: 12px;">Статус ремонта Вы можете отслеживать на сайте </span><a target="_blank" href="http://restore.com.ua" style="font-family: Arial; font-size: 11px; line-height: 12px;">restore.com.ua</a><span style="font-family: Arial; font-size: 11px; line-height: 12px;">&nbsp;</span></h4></div><div style="text-align: right;"><table class="table table-bordered"><tbody><tr><td><span style="line-height: 11.999999046325684px; font-family: Arial; background-color: rgb(255, 255, 255);">Вид ремонта:</span></td><td>&nbsp;<span style="font-family: Arial; line-height: 11.999999046325684px; font-weight: bold;">{{repair}}</span></td><td><span style="line-height: 11.999999046325684px;">Устройство:&nbsp;</span></td><td><span style="font-family: Arial; line-height: 11.999999046325684px; font-weight: bold;">{{product}}</span></td></tr><tr><td><span style="font-family: Arial; line-height: 11.999999046325684px;">Заказчик:</span></td><td><span style="font-family: Arial; line-height: 11.999999046325684px;">&nbsp;</span><span style="font-family: Arial; line-height: 11.999999046325684px; font-weight: bold;">{{fio}}</span></td><td><span style="line-height: 11.999999046325684px;">Серийный номер</span><span style="line-height: 11.999999046325684px; font-family: Arial;">(IMEI)</span><span style="line-height: 11.999999046325684px;">:</span></td><td><span style="font-family: Arial; line-height: 11.999999046325684px; font-weight: bold;">{{serial}}</span></td></tr><tr><td><span style="font-family: Arial;">Контактные данные</span></td><td><span style="font-family: Arial; font-weight: bold;">{{phone}}</span></td><td><span style="font-family: Arial;">Комплектация:</span></td><td><span style="font-family: Arial; font-weight: bold;">{{complect}}</span></td></tr><tr><td>Заявленная неисправность</td><td><span style="font-family: Arial; line-height: 11.999999046325684px; font-weight: bold;">{{defect}}</span><span style="font-family: Arial; line-height: 11.999999046325684px;">&nbsp;</span></td><td>Внешний вид</td><td><span style="font-family: Arial; font-weight: bold;">{{comment}}</span></td></tr><tr><td>Ориентировочная стоимость</td><td><span style="font-weight: bold;"><span style="font-family: Arial;">{{sum}}</span><span style="font-family: Arial;">&nbsp;грн.</span></span></td><td>Предоплата</td><td><span style="font-weight: bold;"><span style="font-family: Arial;">{{prepay}}</span><span style="font-family: Arial;">&nbsp;грн.</span></span></td></tr></tbody></table>\n<ol>\n    <li style="text-align: left; "><span style="font-family: \'\'Times New Roman\'\';">Сервисный Центр не несет ответственности за возможную потерю данных в индивидуальной памяти устройства,\n        связанную с заменой плат, установкой программного обеспечения, заменой носителя информации.\n    </span></li><li style="text-align: left; "><span style="font-family: \'\'Times New Roman\'\';">Диагностика бесплатная.</span></li>\n    <li style="text-align: left;"><span style="font-family: \'\'Times New Roman\'\';">Срок проведения диагностики от 3 до 7 дней. Ремонт проводится в течение 14 рабочих дней со дня приема\n        устройства в Сервисном Центр. При отсутствии запчастей срок ремонта продлевается по согласованию сторон.</span></li>\n    <li style="text-align: left;"><span style="font-family: \'\'Times New Roman\'\';">СЦ «Restore» оставляет за собой право отказать в гарантийном обслуживании в случаях когда: товар\n        использовался или хранился с нарушениями правил эксплуатации; выявлено нарушения заводских или сервисных\n        пломб; неисправность была вызвана механическим влиянием; в случаях обнаружения&nbsp; внутри товара сторонних\n        предметов, жидкости, насекомых и т. п.\n    </span></li>\n    <li style="text-align: left; "><span style="font-family: \'\'Times New Roman\'\';">Заказчик принимает на себя риск возможной полной или частичной утраты работоспособности аппарата в процессе\n        ремонта , в случае грубых нарушений пользователем условий эксплуатации ( наличия следов\n        попадания токопроводящей жидкости (коррозии), механических повреждений. В случае замены сенсорного стекла отдельно от экрана, заказчик принимает на себя риски возможной частичной или полной потери работоспособности экрана, так как данная работа требует&nbsp;</span><span style="font-family: \'\'Times New Roman\'\';">тепловой обработки дисплейного модуля.</span></li>\n    <li style="text-align: left;"><span style="font-family: \'\'Times New Roman\'\';">Гарантия не распространяется на аппараты после падения (наличие вмятин и сколов на корпусе), а также аппараты со следам попадания влаги (красные индикаторы, окиси на плате).</span></li>\n    <li style="text-align: left;"><span style="font-family: \'\'Times New Roman\'\';">Срок хранения аппарата 6 месяцев в независимости от состояния готовности.&nbsp;После данного срока аппарат&nbsp; утилизируется&nbsp; и претензии по нему не принимаются.&nbsp;Срок может быть продлен по письменному заявлению клиента.</span></li><li style="text-align: left;"><span style="font-family: \'\'Times New Roman\'\';">В случае утери квитанции, товар выдается только при наличии паспорта и письменного заявления\n        владельца товара.&nbsp;</span><span style="font-size: 11px; line-height: 12px;">&nbsp;</span></li></ol><span style="font-family: Arial;"><div style="text-align: left;"><span style="font-size: 11px;">&nbsp; &nbsp; &nbsp; &nbsp;</span><span style="font-size: 11px;">Приемщик:&nbsp;</span><span style="font-size: 11px;">{{accepter}}</span><span style="font-size: 11px;">&nbsp; &nbsp; &nbsp; _______________________ &nbsp; &nbsp; &nbsp; &nbsp;От Заказчика._______________________</span></div></span><div style="font-family: Arial; text-align: left;"><span style="font-size: 11px;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; СЦ "Restore" &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span><span style="font-size: 11px;">{{fio}}</span><span style="font-family: \'\'Helvetica Neue\'\', Helvetica, Arial, sans-serif; font-size: 11px;">&nbsp;</span></div><div style="text-align: left;"><div style="text-align: left;"><div style="font-family: Arial; text-align: left;"><span style="font-family: \'\'Helvetica Neue\'\', Helvetica, Arial, sans-serif; font-size: 11px; background-color: rgb(255, 255, 255); color: rgb(156, 156, 148);">___________________________________________________________________________________________________________________________________________</span></div><div style="font-family: Arial; text-align: left;"><span style="font-family: \'\'Helvetica Neue\'\', Helvetica, Arial, sans-serif; font-size: 11px; background-color: rgb(255, 255, 255); color: rgb(156, 156, 148);">&nbsp;&nbsp;</span></div><div style="font-family: Arial; text-align: left;"><span style="font-family: \'\'Helvetica Neue\'\', Helvetica, Arial, sans-serif; font-size: 11px; background-color: rgb(255, 255, 255); color: rgb(156, 156, 148);">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;.</span></div><div><div style="text-align: left;"><h4 style="text-align: left;"><span style="font-family: Arial; font-size: 11px;">&nbsp;&nbsp;</span><span style="font-size: 11px; text-align: center;"><span style="font-family: Arial;">{{barcode}} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span style="font-family: \'\'Arial Black\'\'; font-weight: bold;">{{warehouse_accept}}</span><span style="font-family: Arial;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</span></span><span style="font-family: Arial; font-size: 11px; text-align: center;">{{barcode}}</span></h4><div style="text-align: center;">\n    <h4 style="font-family: Arial; text-align: left;"><span style="font-family: Arial;">&nbsp; &nbsp;№{{id}}&nbsp;</span></h4><h5 style="font-family: Arial; text-align: left;"><span style="font-family: Arial;">от {{date}}</span></h5><p style="text-align: left; line-height: 2;"><span style="font-family: \'\'Arial Black\'\';"><span style="line-height: 11.999999046325684px; font-size: 11px;">{{product}}</span><span style="font-size: 10.666666984558105px; line-height: 11.999999046325684px;">&nbsp;</span></span></p><p style="text-align: left; line-height: 2;"><span style="font-family: \'\'Arial Black\'\';"><span style="font-family: \'\'Arial Black\'\';"><span style="font-family: \'\'Arial Black\'\';"><span style="font-size: 10.666666984558105px; line-height: 11.999999046325684px; font-family: \'\'Arial Black\'\';">&nbsp;</span><span style="font-family: \'\'Arial Black\'\'; font-size: 11px; line-height: 12px;">{{fio}}</span></span></span></span></p><div style="font-family: Arial; text-align: left;"><span style="font-weight: bold;"><span style="font-size: 11px; line-height: 12px; font-family: \'\'Arial Black\'\';">{{repair}}&nbsp;</span></span></div><div style="font-family: Arial; text-align: left;"><span style="line-height: 20px;">Неисправность:&nbsp;</span><span style="font-size: 11px; font-family: \'\'Arial Black\'\';">{{defect}}</span></div><div style="font-family: Arial; text-align: left;"><span style="line-height: 20px; font-size: 11px;"><span style="font-family: Arial;">тел:</span><span style="font-weight: bold; font-family: \'\'Arial Black\'\';">&nbsp;</span></span><span style="line-height: 20px; font-size: 11px; font-weight: bold; font-family: \'\'Arial Black\'\';">{{phone}}</span></div><div style="text-align: left;"><span style="font-family: Arial;">Серийный (IMEI):&nbsp;</span><span style="font-family: \'\'Arial Black\'\'; font-size: 11px; line-height: 12px;">{{serial}}</span><div style="text-align: left;"><br><div style="text-align: left;"><div style="font-family: Arial; text-align: left;"><span style="font-family: Arial; line-height: 20px;">Примечание:&nbsp;</span><span style="line-height: 20px; font-size: 11px; font-weight: bold; font-family: \'\'Arial Black\'\';">{{comment}}</span></div><div style="font-family: Arial; text-align: left;"><span style="font-family: Arial; line-height: 20px;">Ор-я стоимость:&nbsp;</span><span style="font-weight: bold; font-family: \'\'Arial Black\'\';"><span style="font-size: 11px; line-height: 20px;">{{sum}}</span><span style="font-size: 11px; line-height: 20px;">&nbsp;грн.</span></span></div><div style="font-family: Arial; text-align: left;"><span style="font-size: 11px; line-height: 20px;">Предоплата:&nbsp;</span><span style="font-size: 11px; line-height: 20px; font-weight: bold; font-family: \'\'Arial Black\'\';">{{prepay}}</span><span style="font-size: 11px; line-height: 20px;"><span style="font-weight: bold; font-family: \'\'Arial Black\'\';">&nbsp;грн</span>.</span></div><div style="text-align: left;"><span style="font-family: Arial; line-height: 20px;">Комплектация::&nbsp;</span><span style="line-height: 20px; font-size: 11px; font-weight: bold; font-family: \'\'Arial Black\'\';">{{complect}}</span></div></div><span style="font-family: Arial;"><span style="font-family: Arial;"><span style="font-family: Arial;"><span style="font-family: Arial;"><span style="font-family: Arial;">Приемщик: {{accepter}}</span></span></span></span></span></div><div style="font-family: Arial; text-align: left;"><span style="font-family: Arial;"><span style="font-family: Arial;"><span style="font-family: Arial;"><span style="font-family: Arial;"><span style="font-family: Arial;"><br>Исполнитель.______________ &nbsp; Заказчик.______________<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; СЦ "Restore" &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; {{fio}}</span></span></span></span></span></div><div style="font-family: Arial; text-align: left;"><span style="text-align: center;">{{barcode}} &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</span><span style="text-align: center;">{{barcode}}</span></div>\n    \n    </div></div></div></div></div></div>\n    \n    </div>\', \'Шаблон печати квитанции\', 0),
                
                $this->all_configs['db']->query("UPDATE {goods} SET date_add = NOW()");
                $this->all_configs['db']->query("INSERT INTO {clients}(phone,pass,fio,date_add,person) "
                                               ."VALUES('000000000000','-','Списание товара',NOW(),1)");
                // права доступа
                $this->all_configs['db']->query('TRUNCATE TABLE {users_permissions_groups}');
                $this->all_configs['db']->query("
                    INSERT INTO {users_permissions_groups} (`id`, `name`, `prio`) VALUES 
                    (1, 'Администрирование', '0'), 
                    (2, 'Управление контентом', '1'), 
                    (3, 'Просмотр контента', '2'), 
                    (4, 'Заказы клиентов', '3'), 
                    (5, 'Заказы поставщикам', '4'), 
                    (6, 'Бухгалтерия', '5'), 
                    (7, 'Логистика', '6'), 
                    (8, 'Доступ для инженера', '7'), 
                    (9, 'Постановка задач сотрудникам', '8'),
                    (10, 'Доступ для партнеров компании', '9'),
                    (11, 'Доступ для оборудования', '10')
                ");
                $this->all_configs['db']->query('TRUNCATE TABLE {users_permissions}');
                $this->all_configs['db']->query("
                    INSERT INTO {users_permissions} (`id`, `name`, `link`, `child`, `group_id`) VALUES
                    (1, 'Распределение прав доступа', 'edit-users', 0, 1),
                    (2, 'Создание товара/детали ', 'create-goods', 6, 1),
                    (3, 'Редактирование товарных позиций', 'edit-goods', 6, 2),
                    (4, 'Создание фильтров и категорий', 'create-filters-categories', 7, 2),
                    (5, 'Редактирование фильтров и категорий', 'edit-filters-categories', 7, 2),
                    (6, 'Просмотр товарной позиции', 'show-goods', 0, 3),
                    (7, 'Просмотр категорий и фильтров', 'show-categories-filters', 0, 3),
                    (9, 'Супер роль (доступ ко всему)', 'site-administration', 0, 1),
                    (14, 'Новый заказ (сообщение)', 'mess-new-order', 0, 4),
                    (18, 'Управление заказами клиентов', 'edit-clients-orders', 0, 4),
                    (19, 'Редактирование заказов поставщику', 'edit-suppliers-orders', 0, 5),
                    (25, 'Бухгалтерия', 'accounting', 0, 6),
                    (26, 'Приходование заказов поставщику', 'debit-suppliers-orders', 0, 5),
                    (27, 'Логистика', 'logistics', 0, 7),
                    (28, 'Оплата заказов поставщику (сообщение)', 'mess-accountings-suppliers-orders', 25, 5),
                    (29, 'Оприходование заказов поставщику (сообщение)', 'mess-warehouses-suppliers-orders', 26, 5),
                    (30, 'Принять/выдать/привязать серийник (сообщение)', 'mess-debit-clients-orders', 26, 5),
                    (34, 'Возврат поставщику товара/детали', 'return-items-suppliers', 0, 1),
                    (39, 'Доступ к разделу \"Контрагенты\" в Бухгалтерии', 'accounting-contractors', 0, 6),
                    (40, 'Доступ к разделу \"Оборот\" в Бухгалтерии', 'accounting-reports-turnover', 0, 6),
                    (41, 'Доступ к разделу \"Транзакции контрагентов\" в Бухгалтерии', 'accounting-transactions-contractors', 0, 6),
                    (43, 'Создание заказов клиента', 'create-clients-orders', 0, 4),
                    (44, 'Добавление комментарий к заказу клиента', 'add-comment-to-clients-orders', 0, 4),
                    (45, 'Просмотр заказов клиентов', 'show-clients-orders', 0, 4),
                    (46, 'Редактирование фотографий с вебкамеры', 'client-order-photo', 0, 4),
                    (47, 'Инженер', 'engineer', 0, 8),
                    (50, 'Сканер штрихкодов', 'scanner-moves', 0, 11),
                    (51, 'Логистика (уведомления)', 'logistics-mess', 0, 7),
                    (52, 'Партнер', 'partner', 0, 10),
                    (53, 'Доступ к приложению \"Менеджер заказов\"', 'orders-manager', 0, 4),
                    (54, 'Мониторинг конкурентов', 'monitoring', 0, 1),
                    (55, 'Создать задачу', 'create-task', 0, 9),
                    (56, 'Доступ к статистике на главной странице', 'dashboard', 0, 1),
                    (57, 'Внешний маркетинг', 'external-marketing', 0, 2)
                ");
                $this->all_configs['db']->query('TRUNCATE TABLE {users_role_permission}');
                $this->all_configs['db']->query("
                    INSERT INTO {users_role_permission} (`id`, `role_id`, `permission_id`) VALUES
                    (2, 3, 18),(3, 4, 25),(4, 4, 28),(7, 6, 43),(8, 2, 26),(9, 2, 29),(10, 2, 30),(23, 1, 14),(27, 1, 18),(52, 1, 43),(53, 1, 44),(54, 7, 45),(56, 6, 2),(57, 6, 6),(58, 10, 1),(59, 10, 2),(60, 10, 3),(61, 10, 4),(62, 10, 5),(63, 10, 6),(64, 10, 7),(66, 10, 9),(70, 10, 14),(74, 10, 18),(75, 10, 19),(81, 10, 25),(82, 10, 26),(83, 10, 27),(90, 10, 34),(95, 10, 39),(96, 10, 40),(97, 10, 41),(99, 10, 43),(100, 10, 44),(101, 10, 45),(103, 11, 2),(104, 11, 3),(105, 11, 4),(106, 11, 5),(107, 11, 6),(108, 11, 7),(114, 11, 14),(118, 11, 18),(119, 11, 19),(126, 11, 26),(127, 11, 27),(129, 11, 29),(130, 11, 30),(139, 11, 39),(141, 11, 41),(143, 11, 43),(144, 11, 44),(147, 12, 2),(148, 12, 3),(149, 12, 4),(150, 12, 5),(151, 12, 6),(152, 12, 7),(187, 12, 43),(188, 12, 44),(193, 7, 44),(194, 6, 3),(197, 6, 44),(198, 12, 18),(199, 7, 47),(200, 10, 46),(202, 6, 18),(203, 6, 45),(204, 6, 4),(205, 6, 7),(218, 13, 50),(219, 3, 2),(220, 3, 3),(221, 3, 6),(222, 3, 7),(224, 5, 2),(225, 5, 3),(226, 5, 4),(227, 5, 5),(228, 5, 6),(229, 5, 7),(231, 8, 27),(232, 5, 44),(234, 5, 18),(235, 9, 43),(236, 9, 44),(237, 9, 45),(238, 9, 52),(239, 8, 51),(240, 9, 18),(242, 1, 45),(243, 5, 45),(244, 11, 45),(245, 12, 45),(246, 6, 53),(247, 1, 53),(248, 11, 53),(249, 10, 54),(250, 8, 45),(252, 2, 19),(253, 2, 2),(254, 2, 3),(255, 2, 5),(256, 2, 6),(257, 2, 7),(259, 2, 18),(260, 2, 43),(261, 2, 44),(262, 2, 45),(263, 8, 44),(265, 8, 2),(266, 8, 3),(267, 8, 6),(268, 8, 43),(269, 5, 43),(270, 5, 53),(271, 5, 54),(273, 5, 27),(275, 1, 6),(276, 10, 55),(278, 11, 55),(279, 3, 19),(280, 3, 43),(281, 3, 44),(282, 3, 45),(283, 6, 27),(284, 1, 3),(285, 1, 2),(286, 1, 4),(287, 1, 5),(288, 1, 7),(289, 10, 57),(290, 3, 57),(291, 11, 57)
                ");
                $this->all_configs['db']->query('TRUNCATE TABLE {users_roles}');
                $this->all_configs['db']->query("
                    INSERT INTO {users_roles} (`id`, `name`, `avail`, `date_end`) VALUES
                    (1, 'Руководитель', 1, '0000-00-00 00:00:00'),(2, 'Кладовщик', 1, '0000-00-00 00:00:00'),(3, 'Менеджер по закупкам', 1, '0000-00-00 00:00:00'),(4, 'Бухгалтер', 0, '0000-00-00 00:00:00'),(5, 'Менеджер по продажам', 1, '0000-00-00 00:00:00'),(6, 'Приемщик', 1, '0000-00-00 00:00:00'),(7, 'Инженер', 1, '0000-00-00 00:00:00'),(8, 'Курьер', 1, '0000-00-00 00:00:00'),(9, 'Партнер', 1, '0000-00-00 00:00:00'),(10, 'Учредитель', 1, '0000-00-00 00:00:00'),(11, 'Директор', 1, '0000-00-00 00:00:00'),(12, 'Оператор- кладовщик', 1, '0000-00-00 00:00:00'),(13, 'Сканер', 1, '0000-00-00 00:00:00')
                ");
                
                
                $this->all_configs['db']->query('SET FOREIGN_KEY_CHECKS=1');
                
                // чистим кеш складов
                get_service('wh_helper')->clear_cache();
                
                $out = '<div class="alert alert-success">'
                    .'Вы успешно обновили информацию<br>'
                    .'<a class="alert-link" href="' . $href . '">Вернуться к отладчику</a>'
                    .'</div>';
            }
            $out .= '<a class="btn btn-success" href="' . $href . '?reset">Сброс</a>';
        }
		
	
		if ($this->all_configs['arrequest'][1] == 'price_parser') {
			
//            $str = '48<br /><br ';
//            //$str = trim($str, '&nbsp; ');
//            $str = trim($str, 'r');
//            
//            var_dump($str);
//            exit;
            
			// connect html dom parser
			require_once('simple_html_dom.php');
			
			// first step - select pages in repair categories by device types
			$sql = ' SELECT * from {map} WHERE parent IN ( 141 ,142 ,143 ,144, 145, 146, 147, 148 ); ';
			
			$brands_maps = $this->all_configs['db']->query($sql, array())->assoc();
		    foreach ($brands_maps as $brands_map) {
				
				// second step - select devices in each brand
				$out .= "<h1>" .$brands_map['name']. "</h1>";
				$sql = 'SELECT * from {map} WHERE parent = ?;';
				$equipment_maps = $this->all_configs['db']->query( $sql,array( $brands_map['id']) )->assoc();
				
                $count = 1;

				foreach ($equipment_maps as $eq_map){
					
					// here map for each equipment
					$out .= $eq_map['name']."<br>";
					
					
					// parse prices for main equipment page
					$prices = $this->parse_price($eq_map['content']);

					//print_r($prices);
					//echo "\n\n\n";
					
					$tbl_1 = null; 
					$tbl_2 = null;
					
					$tbl_1_first_row = false;
					$tbl_2_first_row = false;
					
					$c1 = 0; // row counter for first table
					$c2 = 0; // row counter for second table
					
					foreach ($prices as $price){
						// первая таблица - с тремя колонками, четвёртая [3] - пустая 
						if ( !isset($price[3]) ){
							if ($tbl_1_first_row){
								//$tbl_1[$c1]['name'] = trim($price[0], '&nbsp;');
                                $tbl_1[$c1]['name'] = strtr($price[0], array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
								$tbl_1[$c1]['name'] = trim($tbl_1[$c1]['name'], chr(0xC2).chr(0xA0));
                                $tbl_1[$c1]['name'] = strip_tags($tbl_1[$c1]['name']);
								
								$tbl_1[$c1]['price_mark']=$this->get_price_mark($price[1]);
								$tbl_1[$c1]['price'] = preg_replace('~\D+~','',$price[1]); 
								
								$time = trim($price[2], '&nbsp;');
								$tbl_1[$c1]['time'] = str_replace('<br />', '', $time);
								$tbl_1[$c1]['prio'] = $c1;
								$c1 ++ ;
							}
							$tbl_1_first_row = true ;
						}
						// вторая таблица больше - там есть третья колонка 
						else{
							if ($tbl_2_first_row){
								
                                //$tbl_2[$c2]['name'] = trim($price[0],'&nbsp;');
                                $tbl_2[$c2]['name'] = strtr($price[0], array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
								$tbl_2[$c2]['name'] = trim($tbl_2[$c2]['name'], chr(0xC2).chr(0xA0));
                                $tbl_2[$c2]['name'] = strip_tags($tbl_2[$c2]['name']);
                                
								$tbl_2[$c2]['price_copy_mark'] = $this->get_price_mark($price[1]);
								$tbl_2[$c2]['price_copy'] = preg_replace('~\D+~','',$price[1]); 
								
								$tbl_2[$c2]['price_mark'] = $this->get_price_mark($price[2]);
								$tbl_2[$c2]['price'] = preg_replace('~\D+~','',$price[2]);
								
								$time = trim($price[3], '&nbsp;');
								$tbl_2[$c2]['time'] = str_replace('<br />', '', $time);
								$tbl_2[$c2]['prio'] = $c2;
								$c2 ++ ;
							}
							$tbl_2_first_row = true;
						}
						
					}
					
					// third step - look for the concurent page with different prices
					$sql = 'SELECT * from {map} WHERE parent = ? limit 1;';
					$concurent_maps = $this->all_configs['db']->query( $sql,array( $eq_map['id']) )->assoc();
					$conc_map = $concurent_maps[0];
					
					// parse page with concurent prices 
					$c_prices = $this->parse_price($conc_map['content']);
					
					$c1_c = 0; // row counter for first table
					$c2_c = 0; // row counter for second table
					
					$tbl_1_first_row =false;
					$tbl_2_first_row =false;
					// add concurent prices to existing tables ( arrays  tbl_1 and tbl_2 )
					
					foreach ($c_prices as $c_price){
						// первая таблица - с тремя колонками, четвёртая [3] - пустая 
						if ( !isset($c_price[3]) ){
							if ($tbl_1_first_row){
								
								$tbl_1[$c1_c]['c_price_mark'] = $this->get_price_mark($c_price[1]);
								$tbl_1[$c1_c]['c_price'] = preg_replace('~\D+~','',$c_price[1]);
								
								$c1_c ++ ;
							}
							$tbl_1_first_row = true;
						}
						// вторая таблица больше - там есть третья колонка 
						else{
							if ($tbl_2_first_row){
								$tbl_2[$c2_c]['c_price_copy_mark'] = $this->get_price_mark($c_price[1]);
								$tbl_2[$c2_c]['c_price_copy'] = preg_replace('~\D+~','',$c_price[1]);
								
								$tbl_2[$c2_c]['c_price_mark'] = $this->get_price_mark($c_price[2]);
								$tbl_2[$c2_c]['c_price'] = preg_replace('~\D+~','',$c_price[2]);
								
								//$tbl_2[$c2_c]['c_time'] = $c_price[3];
								$c2_c ++ ;
							}
							$tbl_2_first_row = true ;
						}
						
					}
					
					/*
						$row .= "
						<tr><td>".$tbl_row['name']."</td>
						<td>".$tbl_row['price']."</td>
						<td>".$tbl_row['price_mark']."</td>
						<td>".$tbl_row['c_price']."</td>
						<td>".$tbl_row['c_price_mark']."</td>
						<td>".$tbl_row['price_copy']."</td>
						<td>".$tbl_row['price_copy_mark']."</td>
						<td>".$tbl_row['c_price_copy']."</td>
						<td>".$tbl_row['c_price_copy_mark']."</td>
						<td>".$tbl_row['time']."</td>
						<td>".$tbl_row['c_time']."</td>
						</tr>";
						*/
					
					//extract array tables 
					if (is_array($tbl_1)){
						$sql = 'INSERT INTO {map_prices}
						(`id`,
						`map_id`,
						`table_type`,
						`name`,
						`price`,
						`price_mark`,
						`time_required`,
						`prio`)
						VALUES ' ;
						$comma='';
						
						foreach ($tbl_1 as $tbl_row){
						$sql .= $comma."
						('0',
						'".$eq_map['id']."',
						'1',
						'".$tbl_row['name']."',
						'".($tbl_row['price']+50)."',
						'".$tbl_row['price_mark']."',
						'".$tbl_row['time']."',
						'".$tbl_row['prio']."')
						";
						$comma = ',';
					}
					$insertion = $this->all_configs['db']->query( $sql.";",array() );
					}
					
					if (is_array($tbl_2)){
						$sql = "INSERT INTO {map_prices}
						(`id`,
						`map_id`,
						`table_type`,
						`name`,
						`price`,
						`price_mark`,
						`price_copy`,
						`price_copy_mark`,
						`time_required`,
						`prio`)
						VALUES ";
						
						$comma="";
					foreach ($tbl_2 as $tbl_row){
						$sql .= $comma."
						('0',
						'".$eq_map['id']."',
						'2',
						'".$tbl_row['name']."',
						'".($tbl_row['price']+50)."',
						'".$tbl_row['price_mark']."',
						'".($tbl_row['price_copy']+50)."',
						'".$tbl_row['price_copy_mark']."',
						'".$tbl_row['time']."',
						'".$tbl_row['prio']."')
						";
						$comma = ", ";
					}
					$insertion = $this->all_configs['db']->query( $sql.";",array() );
					}

					
					echo "<h1>".$count."-".$eq_map['id']."</h1><br>";
					$count ++;
	
				
				}
				
			}
			exit;
		}
			
		
		if ($this->all_configs['arrequest'][1] == 'show_price_tables') {
			
			$map_id = 510 ;
			$competitor = 0 ;
			
			$table_type = 1 ;
			$sql = "SELECT * FROM {map_prices} WHERE map_id=? AND table_type=?";
			$price_table = $this->all_configs['db']->query( $sql, array( $map_id , $table_type ) )->assoc();
			if ( $price_table ) {
				$table_1 = $this->get_price_table_1($price_table , $competitor );
			}
			
			$table_type = 2 ;
			$sql = "SELECT * FROM {map_prices} WHERE map_id=? AND table_type=?";
			$price_table = $this->all_configs['db']->query( $sql,array( $map_id , $table_type ) )->assoc();
			if ( $price_table ) {
				$table_2 = $this->get_price_table_2($price_table , $competitor );
			}
			
			$out = "".$table_1."<hr>".$table_2;
		}
		
        return $out;
    }

	
	function get_price_table_1($price_table, $competitor = false){
	
		$rows = null;
		if( !$competitor ){
			$price_row = 'price';
			$mark_row = 'price_mark';
		}
		else{
			$price_row = 'price_competitor';
			$mark_row = 'price_competitor_mark';
		}
		
		foreach ( $price_table as $row ){
			$rows .= '<tr><td>' .$row['name']. '</td><td> ' .$row[$mark_row]. ' ' .$row[$price_row]. '</td><td>' .$row['time_required']. '</td></tr>';
		}
		
		$tbl = "
	<table>
	<tbody>
		<tr>
			<td>Вид предоставляемых работ</td>
			<td>Стоимость</td>
			<td>Время</td>
		</tr>
	" .$rows.
	"</tbody></table>"; 
	
	return $tbl;
	
	}
	
	
	function get_price_table_2($price_table, $competitor = false ){
	
		$rows = null;
		if( !$competitor ){
			$price_row = 'price';
			$mark_row = 'price_mark';
			$price_copy_row = 'price_copy';
			$price_copy_mark_row = 'price_copy_mark';
			
		}
		else{
			$price_row = 'price_competitor';
			$mark_row = 'price_competitor_mark';
			$price_copy_row = 'price_copy_competitor';
			$price_copy_mark_row = 'price_copy_competitor_mark';
		}
		
		
		foreach ( $price_table as $row ){
			$rows .= '<tr>
			<td>' .$row['name']. '</td>
			<td> ' .$row[$mark_row]. ' ' .$row[$price_row]. '</td>
			<td> ' .$row[$price_copy_mark_row]. ' ' .$row[$price_copy_row]. '</td>
			<td>' .$row['time_required']. '</td>
			</tr>';
		}
		
		$tbl = "
	<table>
	<tbody>
		<tr>
<td>Вид предоставляемых работ</td>
<td>Копия</td>
<td>Оригинал</td>
<td>Время</td>
</tr>
	" .$rows.
	"</tbody></table>"; 
	
	return $tbl;
	
	}
	
	
	function get_price_mark($price){
		if (preg_match("/от/i",$price)){
			$price_mark = "от";
		}
		elseif(preg_match("/до/i",$price)){
			$price_mark = "до";
		}
		else{
				$price_mark = "";
		}
		return $price_mark ;
	}
	
	function parse_price($content){
		$prices = array();
		$html = str_get_html($content); 
		
		if ( is_object( $html ) ){
				foreach($html->find('table') as $onetable){
				if (is_object($onetable)){
					foreach($onetable->find('tr') as $row) {
						$rowData = array();
							if(is_object($row)){
								foreach($row->find('td') as $cell) {
									$rowData[] = $cell->innertext;
								}
							}
						$prices[] = $rowData;
					}
				}
			}
		}
		return $prices;
	}
	
}