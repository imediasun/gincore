<?php

class Configs {


    protected static $instance = array(

        'host'                      =>  'restore.kiev.ua',
        'canonical-host'            =>  'http://restore.kiev.ua',
        'shop-name'                 =>  'Яблоко',

        /*
         * блок конфига для саас
         */
        
//            'manage-show-phones-btn'    => false, // показать или скрыть кнопку смены аварийных телефонов
//            'manage-active-modules'     => array( // активные модуле в админке
//                                                'accountings','categories','clients',
//                                                'debug','logistics','master','orders',
//                                                'partners','products','settings','statistics',
//                                                'tasks','users','warehouses','wrapper'
//                                           ),
//            'manage-reset-access'       =>  true, // доступен ли сброс в модуле дебаг
//            'settings-master-enabled'   =>  true, // мастер настрйоки при регистрации новой админки
//              'currencies'                => array(
//                      1 => array('name' => 'Гривна', 'shortName' => 'UAH', 'viewName' => 'грн.', 'symbol' => '₴', 'currency-name' => 'grn-cash'),
//                      2 => array('name' => 'ЕВРО', 'shortName' => 'EUR', 'viewName' => '€', 'symbol' => '€', 'currency-name' => ''),
//                      3 => array('name' => 'Доллар США', 'shortName' => 'USD', 'viewName' => '$', 'symbol' => '$', 'currency-name' => 'price'),
//                      4 => array('name' => 'Российский рубль', 'shortName' => 'RUB', 'viewName' => 'руб.', 'symbol' => '<i class="fa fa-rub"></i>', 'currency-name' => ''),
//                      5 => array('name' => 'Белорусский рубль', 'shortName' => 'BYR', 'viewName' => 'бр.', 'symbol' => 'Br', 'currency-name' => ''),
//                      6 => array('name' => 'Тенге', 'shortName' => 'KZT', 'viewName' => 'тнг.', 'symbol' => '₸', 'currency-name' => ''),
//               ),
        
        /*
         * блок конфига для админки рестора
         */
            
            'manage-show-phones-btn'    => true, // показать или скрыть кнопку смены аварийных телефонов
            'manage-active-modules'     => array('*'), // активные модуле в админке
            'manage-reset-access'       =>  false, // доступен ли сброс в модуле дебаг
            'settings-master-enabled'   =>  false, // мастер настрйоки при регистрации новой админки
            'currencies'                => array(
                  1 => array('name' => 'Гривна', 'shortName' => 'UAH', 'viewName' => 'грн.', 'symbol' => '₴', 'currency-name' => 'grn-cash'),
                  2 => array('name' => 'ЕВРО', 'shortName' => 'EUR', 'viewName' => '€', 'symbol' => '€', 'currency-name' => ''),
                  3 => array('name' => 'Доллар США', 'shortName' => 'USD', 'viewName' => '$', 'symbol' => '$', 'currency-name' => 'price'),
                  4 => array('name' => 'Российский рубль', 'shortName' => 'RUB', 'viewName' => 'руб.', 'symbol' => '<i class="fa fa-rub"></i>', 'currency-name' => ''),
            ),
        
        /**
         *  --------------------------------
         */
        
        'manage-redirect-to-https'  =>  false,
        'manage-use-memcached'      =>  true,
        'site-use-memcached'        =>  false,

        'manage-transact-comment'   =>  false, // обязательный комментарий при создании транзакции
        'manage-actngs-in-1-amount' =>  false, // вывод в бухгалтерии денег в одной валюте

        'orders-images-path'        =>  'shop/orders/', // папка с картинками заказов
        'goods-images-path'         =>  'shop/goods/', // папка с картинками товаров
        'product-page'              =>  'p',// страничка товара
        'category-page'             =>  'c',// страничка категории
        'searches-page'             =>  's', // ид странички поиска

        'small-image'               =>  '_small.',// префикс маленькой фотографии
        'medium-image'              =>  '_medium.',// префикс средней фотографии
        'images-sizes'              =>  array(// размеры изображения, писать начиная с большей в сторону уменьшения
            'medium-image' => 300,
            'small-image' => 100,
        ),

        'manage-qty-so-only-debit'  =>  true, // количество не обработанных заказов поставщику (таба) только не оприходованые
        'manage-product-managers'   =>  false, // менеджеры у товара, true много, false только один
        'manage-filters-type'       =>  array(/*0 => 'Не выводить', */1 => 'Выбор', 2 => 'Мультивыбор'/*, 3 => 'Список'*/),
        'cat-img'                   =>  'images/categories/',// папка картинок категорий
        'all-categories-page'       =>  'all-categories',// юрл странички всех категорий
        'service-type-page'         =>  1, // ид для служебных страниц, из таблицы 'page_types', 0 не устанавливать!!!
        'categories-page'           =>  2, // ид странички категории
        'products-page'             =>  3, // ид странички продукта
        'news-page'                 =>  66, // 38 ид странички новости
        'advantages-page'           => 151, // ид странички Преимущества
        'partners-page'             =>  'friends', // галерея партнеры
        'brands-page'               =>  'brands', // галерея бренд
        'search-page'               =>  14, // ид странички поиска
        'goods-count-top'           =>  10, // количиство товаров на главной
        'product-rename-img'        =>  true, // переименовываем название изображений в товаре //@TODO использовать copy_img везде

        'onec-use'                  =>  false, // использование 1с (при использование 1с erp-use должно быть false)
        'onec-code-price'           =>  '88b6179a-d42e-11e2-add2-000c29590540', // код розничной цены для импорта с 1с
        'onec-code-price_purchase'  =>  '63c7d0c2-d431-11e2-add2-000c29590540', // код Закупочная цены для импорта с 1с
        'onec-code-price_wholesale' =>  'нет 2', // код Оптовая цены для импорта с 1с
        'onec-code-hotline'         =>  '2e2c5e79-3574-11e2-8827-000c29590540', // код хотлайна для импорта с 1с
        'onec-tranc'                =>  0,// если 1 переводить цены на гривны, 0 не переводить
        'onec-watermark'            =>  true, // водяной знак при загрузке товаров кроном

        'host'                      =>  '192.168.1.2',

        'users-manage-page'         =>  1, // для таблицы изменений, модуль администраторы
        'categories-manage-page'    =>  2, // для таблицы изменений, модуль категории
        'products-manage-page'      =>  3, // для таблицы изменений, модуль товары
        'clients-manage-page'       =>  4, // для таблицы изменений, модуль клиенты
        'offices-manage-page'       =>  5, // для таблицы изменений, модуль отделения
        'orders-manage-page'        =>  6, // для таблицы изменений, модуль заказы
        'accountings-manage-page'   =>  7, // для таблицы изменений, модуль бухгалтерия
        'imports-manage-page'       =>  8, // для таблицы изменений, модуль импорт
        'warehouses-manage-page'    =>  9, // для таблицы изменений, модуль склады
        'logistics-manage-page'     =>  10, // для таблицы изменений, модуль управление перемещениями
        'tasks-manage-page'         =>  11, // для таблицы изменений, модуль управление перемещениями

        'images-path-sc'            =>  'shop/sc/', // папка фотографий товаров для корзины

        //  cookies
        'cookie-live'               =>  7776000, // время жизни
        'show_goods'                =>  'show-goods',
        'user_id'                   =>  'uid',
        'guest_id'                  =>  'gid',
        'session_id'                =>  'sid',
        'wishlist'                  =>  'wl',
        'currency'                  =>  'currency',
        'region'                    =>  'region',
        'course'                    =>  'course',
        'city'                      =>  'city',
        'salt'                      =>  'salt',
        'count-on-page'             =>  'qty-onp', // количество строк на страничке

        'manage-count-on-page'      => array(10 => 10, 30 => 30, 50 => 50, 100 => 100, 200 => 200), // список сколько строк отображать на странице
        'manage-show-plist-img'     =>  false, // показывать изображение в списке товаров
        'manage-system-clients'     =>  array(1), // клиенты которые используются системой (нельзя редактировать)
        'manage-prefit-commission'  =>  false, // учитывать оплату за доставку и за комиссию в марже
        'manage-show-imports'       =>  true, // импорт в админке
        'manage-show-import-goods'  =>  false, // импорт в админке товаров
        'manage-show-import-price'  =>  false, // импорт в админке обработка товаров
        'import-file-name'          =>  'goods.json', // имя файла для загрузки товаров с импорта
        'rounding-goods'        =>  true,       // окруляет 1=0, 2=0, 3=5, 4=5, 5=5, 6=5, 7=5, 8=10, 9=10, 10=10
        'default-currency'          =>  'grn-cash', // grn, price  - обязательны гривны
        'default-course'            =>  'grn-cash', // grn-cash, grn-vat, grn-noncash  - обязательны гривны
        //'default-currency-corp'     =>  'grn-noncash', // grn, price  - обязательны гривны
        'default-course-corp'       =>  'grn-noncash', // grn-vat, grn-noncash  - обязательны гривны
        'default-city'              =>  13,
        'default-region'            =>  12,
        'tradein'                   =>  2, // false - нет, 1 - цена и максимальный процент в товаре, 2 - минимальная цена из хотлайна а максимальный процент из настроек
        'tradein-ideal'             =>  60, // идеальное состояние (максимальный процент)
        'tradein-good'              =>  10, // хорошее состояние
        'tradein-defects'           =>  30, // есть дефекты
        'tradein-moisture'          =>  20, // попадала влага
        'tradein-sec'               =>  259200, // количество секунд актуальных цен из хотлайна
        'goods-categories-sec-new'  =>  259200, // количество секунд пока категория новая
        'services_in_cart_enabled'  =>  false,
        'count-all-goods-in-sc'     =>  true, // количество товаров в корзине, если true - 5товаров*3штук+3товара*2штуки, если false - 5товаров+3товара
        'waiting-goods-count'       =>  10, // сколько штук можно выбрать при заказе товара со статусом ожидается
        'max-buy-goods-count'       =>  12, // сколько штук можно выбрать при заказе товара
        'default-buy-goods-count'   =>  4, // сколько штук выбирается при заказе товара
        'export-product-hotline'    =>  true, // выгрузка в товара, при изменении цены хотлайном
        'mailme-signin'             =>  false, // Сообщить о поступлении, если true не авторизированный клиент должен ввести пароль
        'select-hotline-cur-shop'   =>  false, // если false - нет возможности выбрать текущий магазин из списка хотланйна
        'one-image-secret_title'    =>  false, // елси true то при загрузке картинок с админки в товар по полю secret_title в товаре, картинка грузится всем товарам с таким полем secret_title, при удалении - удаляется у всех
        'set_watermark'             =>  true, // водяной знак при загрузки картинок
        'save_goods-export_to_1c'   =>  true, // выгрузка товара в 1с при сохранении
        'show-btn-installment'      =>  true, // показывать кнопку купить в рассрочку на страничке товара (для полного отключения рассрочки необходимо закомментировать 'payment-msg')
        'count-days-sale-rate'      =>  7, // количество дней при выгрузке скорости продаж
        'no-warranties'             =>  true, // все товары без гарантии
        'use-mongo'                 =>  false, // использоваение mongodb
        'search-type'               =>  '', // default basic ''
        'parser-comments-limit'     =>  20, // количество отзывов для парсера комментариев
        'show-search-weight'        =>  false, // показывать поисковый вес в товарах при поиске на сайте
        'use-goods-old-price'       =>  false, // использовать старую цену у товаров
        'suppliers-orders-zero'     =>  true, // создание заказа поставщику с ценой закупки 0
        'turbosms'                  =>  false, // turbosms
        'group-goods'               =>  false,
//TODO cron_ конфиг для сериализации товаров (сколько, какие данные селектить ...)
////- история посещений (клиент, гость)
////- самые просматриваемые
////- товары со скидкой
////- хиты продаж
        'gzip_pack'                 =>  true, // для сжатия данных, пока не используется

        'reset-visits-allow' => false, // разрешить сброс счётчика посещений сервисов
        'reset-visits-command' => 'reset', // get комманда для сброса счётчика своих посещений
        'set-visits-command' => 'set', // get комманда для сброса счётчика своих посещений
        // IP с которых возможен сброса счётчика своих посещений в сервисах
        'reset-visits-ip' => array(
            '127.0.0.1'
        ),

        'erp-use'                   =>  true, // использование систему учета (при использование складов, onec-use должно быть false)
        'erp-move-item-logistics'   =>  false, // при перемещение изделия использовать логистику
        'erp-serial-prefix'         =>  'r', // префикс для серийного номер
        'erp-serial-count-num'      =>  7, // количество цифр в серийном номере
        'erp-so-contractor_category_id_from' =>  7, // категория контрагента с которой будет происходить списание средств при оплате заказа поставщику
        'erp-co-contractor_category_id_from_prepay' =>  33, // категория контрагента с которой будет происходить списание средств при оплате заказа поставщику за предоплату
        //'erp-co-contractor_category_id_from_delivery' =>  7, // категория контрагента с которой будет происходить списание средств при оплате заказа поставщику за способ доставки
        //'erp-co-contractor_category_id_from_payment' =>  8, // категория контрагента с которой будет происходить списание средств при оплате заказа поставщику за способ оплаты
        'erp-co-contractor_category_id_from' =>  1, // категория контрагента на которую будет происходить внесение средств за заказ клиента
        'erp-co-contractor_category_id_to' =>  2, // категория контрагента с которой будет происходить списание средств за заказ клиента
        'erp-co-contractor_category_return_id_from' =>  5, // категория контрагента на которую будет проихсодить возврат поставщику
        'erp-co-contractor_category_return_id_to' =>  6, // категория контрагента на которую будет проихсодить возврат возврата поставщику
        'erp-co-contractor_category_off_id_to' =>  3, // категория контрагента с которой будет происходить списание средств за списаный заказ
        'erp-co-contractor_id_from' =>  1, // контрагент которому будет происходить внесение средств за заказ клиента
        'erp-co-contractor_off_id_from' =>  2, // контрагент которому будет происходить внесение средств за списание заказа клиента
        'erp-co-contractor_category_off_id_from' =>  4, // контрагент которому будет происходить внесение средств за списание заказ клиента
        'erp-co-cashbox-write-off'  =>  1, // касса на которую будет происходить транзакция при списании
        'erp-co-category-write-off' =>  8, // категория на которую будет происходить списание
        'erp-co-category-sold'      =>  9, // категория на которую будет происходить продажа
        'erp-co-category-return'    =>  54, // категория на которую будет происходить возврат поставщику
        'erp-so-cashbox-terminal'   =>  3, // касса терминал
        'erp-so-user-terminal'      =>  29, // сотрудник терминал
        'erp-so-client-terminal'    =>  479, // клиент терминал
        'erp-cashbox-transaction'   =>  2, // касса на которой будет происходить переводы валюты для контрагентов
        'erp-so-cashbox'            =>  1, // касса на которой будет происходить оплата за заказы клиентов
        'erp-contractors-types'     =>  array(1 => 'Контрагент', 2 => 'Поставщик', 3 => 'Покупатель', 4 => 'Сотрудник'), // типы контрагентов
        'erp-use-for-accountings-operations'        =>  array(1, 3, 2, 4), // типы контрагентов в бухгалтерии
        //'erp-use-id-for-accountings-operations'     =>  array(79), // id контрагентов используемые в бухгалтерии (транзакции)
        'erp-contractors-use-for-suppliers-orders'  =>  array(2), // типы контрагентов в заказах поставщику
        'erp-contractors-retail-consumers'          =>  array(3), // типы контрагентов не используемые в операциях (транзакции)
        'erp-contractors-staff'     =>  array(4), // типы контрагентов сотрудники
        //'erp-contractor-balance-currency' =>  3, // USD. Валюта счета контрагента, также отображаеется напротив баланса по табличке {cashboxes_courses}
        'erp-write-off-warehouse'   =>  5, // склад куда списываются товары
        'erp-write-off-location'    =>  64, // локация куда списываются товары
        'erp-write-off-user'        =>  1, // клиент которому списываются товары
        'erp-warehouses-types'      =>  array(1 => 'Обычный', 2 => 'Недостача', 3 => 'Логистика', 4 => 'Клиент'), // типы складов
        'erp-warehouse-type-mir'    =>  1, // склад мир куда падает изделие после закрытия цепочки
        'erp-location-type-mir'     =>  1, // локация мир куда падает изделие после закрытия цепочки
        'erp-show-warehouses'       =>  array(/*2, 4*/), // типы складов которые видят только администраторы
        'erp-logistic-warehouses'   =>  array(2, 4), // типы складов логистика в которые товара падают автоматом
        'erp-warehouses-sold'       =>  array(6, 9), // типы складов на которых изделие продано
        'erp-inv-all-items'         =>  true, // считать все изделия в наименовании в инвентаризации
        'erp-warehouses-permiss'    =>  array(), // users_permissions которых можно привязать к складам
        'erp-contractors-founders'  =>  array(86, 87), // контрагенты в расчете долевого участия

        'memcd-navbarphp-categories'=> 7169, //таймаут кеша для переменной в $categories в файле navbar.php
        'memcd-indexphp-settings'   => 3412,
        'memcd-footerphp-news'      => 815,
        'memcd-footerphp-brands'    => 8465,
        'memcd-footerphp-partners'  => 9465,
        'memcd-head_menuphp-tradein'=> 506,
        'memcd-head_menuphp-menu'   => 22486,
        'memcd-head_menuphp-banner' => 4561,
        'memcd-head_menuphp-banner-default'=> 4361,

        'api-context'   => array(
            1 => array('name' => 'Google Adwords‎', 'avail' => 'ga-avail', 'multi' => false),
            2 => array('name' => 'Yandex Direct', 'avail' => 'yd-avail', 'multi' => false),
        ),
        'warranties'    =>  array(// В ЦЕНТАХ
            1   =>  array(// обязательно необходим 1 месяц cart.class.php
                30000   =>  0,
                60000   =>  0,
                100000  =>  0,
                'inf'   =>  0
            ),
            3   =>  array(
                30000   =>  616,
                60000   =>  1231,
                100000  =>  1847,
                'inf'   =>  3079
            ),
            6   =>  array(
                30000   =>  1231,
                60000   =>  1847,
                100000  =>  3079,
                'inf'   =>  4310
            ),
            12  =>  array(
                30000   =>  1847,
                60000   =>  3079,
                100000  =>  4310,
                'inf'   =>  5542
            ),
            24  =>  array(
                30000   =>  3079,
                60000   =>  4310,
                100000  =>  5542,
                'inf'   =>  6773
            ),
        ),
        'reviews-shop-status'           =>  array(// статусы магазина для отзывов
            1   =>  'Отлично',
            2   =>  'Хорошо',
            3   =>  'Плохо',
        ),
        'reviews-shop-become_status'    =>  array(// статусы магазина для отзывов
            1   =>  'Стало лучше',
            2   =>  'Ничего не изменилось',
            3   =>  'Стало хуже',
        ),

        'payment-msg'   => array(// виды оплат
            'cash'          =>  array(// default must be first
                'name'          =>  'Оплата наличными',
                'person'        =>  1,
                'shipping'      =>  array('pickup'=>1),
                'default'       =>  1,
                'pay'           =>  'post',
            ),
            'pay_on_delivery'=> array(
                'name'          =>  'Оплата при получении',
                'person'        =>  1,
                'shipping'      =>  array('express'=>1, 'courier'=>1, 'courier_today'=>1, 'novaposhta'=>1),
                'default'       =>  0,
                'pay'           =>  'post',
            ),
            'transfer'      =>  array(
                'name'          =>  'Банковский перевод или оплата карточкой',
                'person'        =>  1,
                'shipping'      =>  array('courier'=>1, 'courier_today'=>1,'novaposhta_cash'=>1, 'pickup'=>1),
                'default'       =>  0,
                'pay'           =>  'pre',
            ),
            'installment'   =>  array(
                'name'          =>  'Оплата в рассрочку',
                'person'        =>  1,
                'shipping'      =>  array('courier'=>1),
                'default'       =>  0,
                'pay'           =>  'pre',
            ),
            'account'       =>  array(
                'name'          =>  'Оплата по счету',
                'corporation'   =>  1,
                'shipping'      =>  array('pickup'=>1, 'novaposhta_cash'=>1, 'courier_today'=>1, 'courier'=>1),
                'default'       =>  0,
                'pay'           =>  'pre',
            ),
        ),
        'manage-orders-shipping-tab' => array(
            0 => array('name' => 'Самовывоз', 'href' => 'motions_orders-pickup', 'default' => 1, 'city' => 0,
                'open' => 'logistics_motions_orders_pickup', 'region' => 0, 'shippings' => array('pickup'),
                'query' => 'AND (o.shipping="pickup" OR o.shipping="" OR o.shipping IS NULL)',
                'hash' => '#motions_orders-pickup'),
            1 => array('name' => 'Доставка по Киеву', 'href' => 'motions_orders-kiev', 'default' => 0,
                'open' => 'logistics_motions_orders_kiev', 'region' => 12, 'city' => 13,
                'shippings' => array('express', 'courier', 'courier_today'),
                'query' => 'AND (o.shipping="express" OR o.shipping="courier" OR o.shipping="courier_today") AND o.city=13',
                'hash' => '#motions_orders-kiev'),
            2 => array('name' => 'Регионы', 'href' => 'motions_orders-novaposhta', 'default' => 0,
                'open' => 'logistics_motions_orders_novaposhta', 'region' => 0, 'city' => 0,
                'shippings' => array('novaposhta', 'novaposhta_cash'),
                'query' => 'AND (o.shipping="novaposhta" OR o.shipping="novaposhta_cash")',
                'hash' => '#motions_orders-novaposhta'),
        ),
        'shipping-msg'  =>  array( // доставок
            'pickup'        =>  array(// default must be first
                'name'          =>  'Самовывозом',
                'default'       =>  1,
                'person'        =>  1,
                'corporation'   =>  1,
                'np'            => 0,
                //'pay'           =>  'post',
            ),
            'novaposhta_cash'    =>  array(
                'name'          =>  'Новой Почтой по предоплате',
                'default'       =>  0,
                'corporation'   =>  1,
                'person'        =>  1,
                'np'            => 1,
                //'pay'           =>  'pre',
            ),
            'novaposhta'=>  array(
                'name'          =>  'Новой Почтой наложенным платежом',
                'default'       =>  0,
                'person'        =>  1,
                'np'            => 1,
                //'pay'           =>  'post',
            ),
            'courier'       =>  array(
                'name'          =>  'Курьером',
                'default'       =>  0,
                'person'        =>  1,
                'corporation'   =>  1,
                'np'            => 0,
                //'pay'           =>  'post',
            ),
            'courier_today'       =>  array(
                'name'          =>  'Курьером на сегодня',
                'default'       =>  0,
                'person'        =>  1,
                'corporation'   =>  1,
                'time'          =>  16, // время до которого показывать
                'np'            => 0,
                //'pay'           =>  'post',
            ),
            'express'       =>  array(
                'name'          =>  'Экспресс доставкой',
                'default'       =>  0,
                'person'        =>  1,
                'np'            => 0,
                //'pay'           =>  'post',
            ),
        ),

        'changes'                       =>  array( // изменения по сайту
            'create-category'               =>  'Создание новой категории',
            'edit-category'                 =>  'Редактирование категории',
            'edit-category-image'           =>  'Редактирование картинки меню категории',
            'edit-category-thumbs'          =>  'Редактирование превью категории',
            'edit-category-cat-image'       =>  'Редактирование картинки категории',
            'create-filter-group'           =>  'Создание группы фильтров',
            'create-filter-value'           =>  'Создание значения фильтра',
            'edit-filter-group-value'       =>  'Редактирование групп и фильтров',
            'add-similar-goods'             =>  'Добавление аналогичного товара',
            'delete-similar-goods'          =>  'Удаление аналогичного товара',
            'edit-goods'                    =>  'Редактирование товара',
            'delete-filters'                =>  'Удаление фильтров у товара',
            'delete-goods-image'            =>  'Удаление фотографий у товара',
            'create-goods'                  =>  'Создание товара',
            'add-image-goods'               =>  'Добавление фотографии к товару',
            'edit-warranties-add'           =>  'Изменение настроек гарантийных пакетов',
            'update-top-day'                =>  'Обновление товара дня',
            'delete-top-day'                =>  'Удаление товара дня',
            'update-top-index'              =>  'Обноваление товара на главную',
            'update-bestsellers'            =>  'Обновление хита продаж',
            'update-discounts'              =>  'Обновление товара со скидкой',
            'update-goods-title-image'      =>  'Обновление заголовка фотографии товара',
            'update-goods-image-prio'       =>  'Обновление приоритета фотографии товара',
            'update-goods-category'         =>  'Обновление категории у товара',
            'add-goods-to-category'         =>  'Добавление товара в категорию',
            'delete-goods-from-category'    =>  'Удаление товара с категории',
            'add-filter-to-goods'           =>  'Добавление фильтра к товару',
            'delete-filter-from-goods'      =>  'Удаление фильтра у товара',
            'update-filter-goods'           =>  'Обновление фильтра у товара',
            'update-filter-goods-to-multi'  =>  'Обновление фильтра у товара на мульти',
            'delete-filter-goods-multi'     =>  'Удаление мульти фильтра у товара',
            'add-to-role-per'               =>  'Добавление роли новых возможностей',
            'delete-from-role-per'          =>  'Удаление у роли возможностей',
            'update-role'                   =>  'Обновление роли',
            'add-new-role'                  =>  'Добавление новой роли',
            'edit-filter-group'             =>  'Редактирование группы фильтров',
            'delete-link-filter-cat'        =>  'Удаление связки фильтр категория',
            'edit-filter-value'             =>  'Редактирование значения фильтра',
            'delete-filter-value'           =>  'Удаление значение фильтра',
            'update-user'                   =>  'Обновление пользователя',
            'add-user'                      =>  'Добавление нового пользователя',
            'add-manager'                   =>  'Добавление нового менеджера',
            'delete-manager'                =>  'Удаление менеджера',
            'add-section'                   =>  'Добавление тега к сопутствующим товарам',

            'update-goods-reviews'          =>  'Обновление отзыва о магазине',
            'update-shop-reviews'           =>  'Обновление отзыва о товаре',
            'delete-office'                 =>  'Удалено отделение магазина',
            'update-office'                 =>  'Обновление отделения магазина',
            'add-office'                    =>  'Добавление нового отделения магазина',
            'update-np-offices'             =>  'Обновление отделений новой почты',
            'manager-accepted-order'        =>  'Менеджер принял заказ',
            'update-order'                  =>  'Заказ обновлен',
            'new-order'                     =>  'Новый заказ',
            'import-from-price'             =>  'Обновлены товари с прайса',
            'add-review'                    =>  'Добавление нового отзыва',
            'add-comment'                   =>  'Добавление нового комментария к отзыву',
            'edit-comment'                  =>  'Редактирование коментария',
            'export-order'                  =>  'Экспорт заказа',
            'add-market-category'           =>  'Добавление новой категории к прайс агрегатору',
            'add-warehouse-order'           =>  'Новый заказ на поставку товара',
            'edit-warehouse-order'          =>  'Редактирование заказа на поставку товара',
            'remove-supplier-order'         =>  'Удален заказ на поставку товара',
            'debit-supplier-order'          =>  'Оприходован заказ на поставку товара',
            'accept-supplier-order'         =>  'Принят заказ на поставку товара',
            'move-categories'               =>  'Перемещена категория',
            'edit-seo-category'             =>  'Редактирование seo категории',
            'edit-ym_id'                    =>  'Редактирование яндекс маркет ID',

            'add-cashbox'                   =>  'Добавление кассы',
            'edit-cashbox'                  =>  'Редактирование кассы',
            'add-contractor_category'       =>  'Добавление категории контрагентов',
            'edit-contractor_category'      =>  'Редактирование категории контрагентов',
            'edit-contractor'               =>  'Редактирование контрагента',
            'add-contractor'                =>  'Добавление контрагента',
            'add-transaction'               =>  'Добавление транзакции в кассу',
            'edit-product-avail'            =>  'Обновление активности товара',
            'add-warehouse'                 =>  'Добавление склада',
            'edit-warehouse'                =>  'Редактирование склада',

            'remove-contractors-category'   =>  'Удаление категории контрагентов',
            'remove-contractor-from-category'=>  'Удаление контрагента из категории',
            'remove-contractor'             =>  'Удаление контрагента',
            'remove-global-cashbox-course'  =>  'Удаление общего курса у касс',
            'add-contractor-category'       =>  'Добавление контрагента в категорию',
            'add-to-cashbox-currency'       =>  'Добавление курс кассе',
            'create-chain'                  =>  'Создана цепочка',

            'move-item'                     =>  'Перемещение изделия',
            'add-chain-body'                =>  'Добавление цепочка',
            'edit-chain-body'               =>  'Редактирование цепочка',
            'chain-body-update-serial'      =>  'Обновление серийника',
        ),

        'order-status-new'                  =>  0, // новый заказ
        'order-status-work'                 =>  5, // В процессе ремонта
        'order-status-waits'                =>  10, // ожидает запчастей
        'order-status-refused'              =>  15, // Клиент отказался
        'order-status-unrepairable'         =>  20, // Не подлежит ремонту
        'order-status-nowork'               =>  25, // выдан без ремонта
        'order-status-issued'               =>  40, // Выдан
        'order-status-rework'               =>  45, // доработка
        'order-status-ready'                =>  35, // готов
        'order-status-service'              =>  30, // В удаленном сервисе
        'order-status-agreement'            =>  27, // На согласовании
        'order-status-issue-btn'            =>  array(15, 20, 35), // статусы при которых появляется кнопка "выдать"
//        'order-statuses-orders'             =>  array(25, 35, 40),
        'order-statuses-orders'             =>  array(35, 40),
        'order-statuses-closed'             =>  array(25, 40),
        'order-statuses-nocomments'         =>  array(35, 15, 20, 25, 50, 40),
        'order-statuses-manager'            =>  array(0, 5, 10, 27, 30, 45),
        'order-statuses-dis-if-spare-part'  =>  array(15, 20, 25), // нельзя установить этот статус пока к заказу привязаны запчасти (с серийниками или без - неважно)

        /*'order-status-new'              =>  1, //новый заказ
        'order-status-part-pay'         =>  5, //Заказ частично оплачен
        'order-status-work'             =>  10, //В работе
        'order-status-wait-pay'         =>  15, //Ожидаем оплату
        'order-status-pay'              =>  20, //Заказ оплачен
        'order-status-loan-wait'        =>  25, //Кредит ожидаем документы
        'order-status-loan-denied'      =>  30, //Кредит отказан
        'order-status-loan-approved'    =>  35, //Кредит одобрен
        'order-status-return'           =>  37, //Возврат
        'order-status-preorder'         =>  50, //Предзаказ
        'order-status-client-failure'   =>  55, //клиент отказался
        'order-status-completed'        =>  60, //Выполнен
        'order-status-returned'         =>  65, //Выполнен/Возврат
        'order-statuses-closed'         =>  array(60, 65), //Заказ закрыт
        'order-statuses-logistic'       =>  array(10, 25, 30, 35, 37, 40, 45, 55, 60, 65, 70), //статусы заказов при которых видит цепочки логист*/

        //// статусы заказа
        // stockman - редактирование кладовщику (true/false)
        // from - с какого статуса можно изменить менеджеру заказ ((array), (string) from all) 'erp-use'=true
        // role - привилегия, юзер с которой может поменять статус (можно не добавлять)
        // edit - редактирование данных заказа, кроме статуса (true/false)
        'order-status'  =>  array(
            0   => array('name' => 'Принят в ремонт', 'color' => 'B05DBB', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            5   => array('name' => 'В процессе ремонта', 'color' => '414CD2', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            10  => array('name' => 'Ожидает запчастей', 'color' => '90C8EE', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            15  => array('name' => 'Клиент отказался', 'color' => 'F04544', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            20  => array('name' => 'Не подлежит ремонту', 'color' => 'C18BA6', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            25  => array('name' => 'Выдан без ремонта', 'color' => 'FF9C49', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            27  => array('name' => 'На согласовании', 'color' => '7ca319', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            30  => array('name' => 'В удаленном сервисе', 'color' => '0A0E16', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            35  => array('name' => 'Готов', 'color' => '787987', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            40  => array('name' => 'Выдан', 'color' => '76C572', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            45  => array('name' => 'Принят на доработку', 'color' => 'CFAFE7', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            50  => array('name' => 'Переведен в донор', 'color' => 'AC5359', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            /*1               =>  array('name' => 'Новый заказ', 'stockman' => false, 'edit' => true,
                'from' => array(1, 50), 'use-payment' => false),
            5              =>  array('name' => 'Заказ частично оплачен', 'role' => 'accounting', 'stockman' => true, 'edit' => false,
                'from' => array(1, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => false),
            10               =>  array('name' => 'В работе', 'edit' => false, 'stockman' => true,'js-event' => 'confirm_without_prepay(this)',
                'from' => array(1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => true),
            15              =>  array('name' => 'Ожидаем оплату', 'stockman' => false, 'edit' => false,
                'from' => array(1, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => false),
            20              =>  array('name' => 'Заказ оплачен', 'role' => 'accounting', 'stockman' => true, 'edit' => false,
                'from' => array(1, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => false),
            25              =>  array('name' => 'Кредит ожидаем документы', 'stockman' => false, 'edit' => false,
                'from' => array(1, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => false),
            30              =>  array('name' => 'Кредит отказ', 'role' => 'accounting', 'stockman' => false, 'edit' => false,
                'from' => array(1, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => false),
            35              =>  array('name' => 'Кредит одобрен', 'role' => 'accounting', 'stockman' => true, 'edit' => false,
                'from' => array(1, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => false),
            37              =>  array('name' => 'Возврат', 'stockman' => true, 'edit' => false,
                'from' => array(37), 'use-payment' => false),
            40              =>  array('name' => 'Перезвонит сам', 'edit' => false, 'stockman' => false,
                'from' => array(1, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => false),
            45              =>  array('name' => 'Не берет трубку', 'edit' => false, 'stockman' => false,
                'from' => array(1, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => false),
            50              =>  array('name' => 'Предзаказ', 'edit' => true, 'stockman' => false,
                'from' => array(1, 50), 'use-payment' => false),
            55              =>  array('name' => 'Клиент отказался', 'edit' => false, 'stockman' => false,
                'from' => array(1, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55), 'use-payment' => false),
            60              =>  array('name' => 'Выполнен', 'edit' => false, 'stockman' => false,
                'from' => array(60), 'use-payment' => false),
            65              =>  array('name' => 'Выполнен/Возврат', 'stockman' => false,'edit' => false,
                'from' => array(65), 'use-payment' => false),
            //70              =>  array('name' => 'Списан', 'stockman' => false,'edit' => false,
            //    'from' => array(70), 'use-payment' => false),*/
        ),

        'credits_package'   =>  array( // виды кредитов
            2                   =>  10, // %
            3                   =>  12, // %
            6                   =>  25, // %
            9                   =>  43, // %
            12                  =>  50, // %
        ),

        'order-use-regions-and-cities'  =>  false, //использовать регионы и города или
        //брать адрес доставки с поля address в заказе

        'regions'   =>array( // регионы
            11   =>  'АР Крым',
            //'Балаклавы'
            23   =>  'Винницкая область',
            24   =>  'Волынская область',
            4    =>  'Днепропетровская область',
            5    =>  'Донецкая область',
            27   =>  'Житомирская область',
            25   =>  'Закарпатская область',
            26   =>  'Запорожская область',
            6    =>  'Ивано-Франковская область',
            12   =>  'Киевская область',
            //13   =>  'Киев',
            10   =>  'Кировоградская область',
            14   =>  'Луганская область',
            15   =>  'Львовская область',
            16   =>  'Николаевская область',
            17   =>  'Одесская область',
            18   =>  'Полтавская область',
            19   =>  'Ровенская область',
            //20   =>  'Севастополь',
            21   =>  'Сумская область',
            22   =>  'Тернопольская область',
            7    =>  'Харьковская область',
            8    =>  'Херсонская область',
            9    =>  'Хмельницкая область',
            1    =>  'Черкасская область',
            2    =>  'Черниговская область',
            3    =>  'Черновицкая область',
        ),
        'cities'  =>  array( // города
            12  =>  array(
                31  =>  'Барышевка',
                32  =>  'Белая Церковь',
                33  =>  'Белогородка',
                34  =>  'Березань',
                35  =>  'Богуслав',
                36  =>  'Борисполь',
                37  =>  'Боровая',
                38  =>  'Бородянка',
                39  =>  'Боярка',
                40  =>  'Бровары',
                41  =>  'Буча',
                42  =>  'Васильков',
                43  =>  'Вишневое',
                44  =>  'Володарка',
                45  =>  'Вышгород',
                46  =>  'Гребінки',
                47  =>  'Дымер',
                48  =>  'Згуровка',
                49  =>  'Иванков',
                50  =>  'Ирпень',
                51  =>  'Кагарлык',
                52  =>  'Калиновка',
                13  =>  'Киев',
                53  =>  'Клавдиево-Тарасово',
                54  =>  'Коцюбинское',
                55  =>  'Макаров',
                56  =>  'Мироновка',
                57  =>  'Обухов',
                58  =>  'Переяслав-Хмельницкий',
                59  =>  'Ракитное',
                60  =>  'Ржищев',
                61  =>  'Сквира',
                62  =>  'Ставище',
                63  =>  'Стоянка',
                64  =>  'Славутич',
                65  =>  'Тараща',
                66  =>  'Тетиев',
                67  =>  'Узин',
                68  =>  'Українка',
                69  =>  'Фастов',
                70  =>  'Яготин',
            ),
            11  =>  array(
                31  =>  'Алупка',
                32  =>  'Алушта',
                33  =>  'Армянск',
                34  =>  'Бахчисарай',
                35  =>  'Белогорск',
                36  =>  'Джанкой',
                37  =>  'Евпатория',
                38  =>  'Золотое поле',
                39  =>  'Керчь',
                40  =>  'Кировское',
                41  =>  'Красногвардейское',
                42  =>  'Красноперекопск',
                43  =>  'Ленино',
                44  =>  'Нижнегорский',
                45  =>  'Октябрськое',
                46  =>  'Первомайское',
                47  =>  'Раздольное',
                48  =>  'Саки',
                20  =>  'Севастополь',
                30  =>  'Симферополь',
                50  =>  'Советский',
                51  =>  'Старый Крым',
                52  =>  'Судак',
                53  =>  'Феодосия',
                54  =>  'Черноморское',
                55  =>  'Щёлкино',
                56  =>  'Ялта',
            ),
            23  =>  array(
                31  =>  'Бар',
                32  =>  'Бершадь',
                30  =>  'Винница',
                33  =>  'Гайсин',
                34  =>  'Гнивань',
                35  =>  'Жмеринка',
                36  =>  'Ильинцы',
                37  =>  'Казатин',
                38  =>  'Калиновка',
                39  =>  'Крыжополь',
                40  =>  'Ладыжин',
                41  =>  'Липовец',
                42  =>  'Литин',
                43  =>  'Могилев',
                44  =>  'Мурованые Куриловцы',
                45  =>  'Немиров',
                46  =>  'Оратов',
                47  =>  'Песчанка',
                48  =>  'Погребище',
                49  =>  'Теплик',
                50  =>  'Томашполь',
                51  =>  'Тростянец',
                52  =>  'Тульчин',
                53  =>  'Хмельник',
                54  =>  'Шаргород',
                55  =>  'Шпиков',
                56  =>  'Ямполь',
            ),
            24  =>  array(
                31  =>  'Владимир-Волынский',
                32  =>  'Горохов',
                33  =>  'Иваничи',
                34  =>  'Камень-Каширский',
                35  =>  'Киверцы',
                36  =>  'Ковель',
                37  =>  'Колки',
                38  =>  'Локачи',
                30  =>  'Луцк',
                40  =>  'Любешов',
                41  =>  'Любомль',
                42  =>  'Маневичи',
                43  =>  'Нововолынск',
                44  =>  'Ратно',
                45  =>  'Рожище',
                46  =>  'Старая Выжевка',
                47  =>  'Старовойтово',
                48  =>  'Турийск',
                49  =>  'Шацк',
            ),
            4  => array(
                31  =>  'Апостолово',
                32  =>  'Васильковка',
                33  =>  'Верхнеднепровск',
                34  =>  'Верховцево',
                35  =>  'Вольногорск',
                36  =>  'Днепровское',
                37  =>  'Днепродзержинск',
                30  =>  'Днепропетровск',
                39  =>  'Желтые Воды',
                40  =>  'Зеленодольськ',
                41  =>  'Кривой Рог',
                42  =>  'Кринички',
                43  =>  'Магдалиновка',
                44  =>  'Марганец',
                45  =>  'Николаевка',
                46  =>  'Никополь',
                47  =>  'Новомосковск',
                48  =>  'Орджоникидзе',
                49  =>  'Павлоград',
                50  =>  'Перещепине',
                51  =>  'Першотравенск',
                52  =>  'Петриковка',
                53  =>  'Петропавловка',
                54  =>  'Покровское',
                55  =>  'Пятихатки',
                56  =>  'Синельниково',
                57  =>  'Соленое',
                58  =>  'Софиевка',
                59  =>  'Терновка',
                60  =>  'Томаковка',
                61  =>  'Царичанка',
                62  =>  'Широкое',
            ),
            5  => array(
                31  =>  'Авдеевка',
                32  =>  'Амвросиевка',
                33  =>  'Артемовск',
                34  =>  'Белицкое',
                35  =>  'Великая Новоселка',
                36  =>  'Волноваха',
                37  =>  'Володарское',
                38  =>  'Горловка',
                39  =>  'Дебальцево',
                41  =>  'Дзержинск',
                42  =>  'Димитров',
                43  =>  'Доброполье',
                44  =>  'Докучаевск',
                30  =>  'Донецк',
                46  =>  'Дружковка',
                47  =>  'Енакиево',
                48  =>  'Зугрес',
                49  =>  'Иловайск',
                50  =>  'Кировское',
                51  =>  'Константиновка',
                52  =>  'Краматорск',
                53  =>  'Красноармейск',
                54  =>  'Красный Лиман',
                55  =>  'Курахове',
                56  =>  'Макеевка',
                57  =>  'Мангуш',
                58  =>  'Мариуполь',
                59  =>  'Марьинка',
                60  =>  'Моспино',
                61  =>  'Николаевка',
                62  =>  'Новоазовск',
                63  =>  'Новый Свет',
                64  =>  'Светлодарск',
                65  =>  'Святогорск',
                66  =>  'Славянск',
                67  =>  'Снежное',
                68  =>  'Соледар',
                69  =>  'Старобешево',
                70  =>  'Тельманово',
                71  =>  'Торез',
                72  =>  'Угледар',
                73  =>  'Харцызск',
                74  =>  'Шахтерск',
                75  =>  'Ясиноватая',
            ),
            27  => array(
                31  =>  'Андрушевка',
                32  =>  'Барановка',
                33  =>  'Бердичев',
                34  =>  'Брусилов',
                35  =>  'Володарск-Волынский',
                36  =>  'Емильчино',
                30  =>  'Житомир',
                38  =>  'Иршанск',
                39  =>  'Коростень',
                40  =>  'Коростышев',
                41  =>  'Красноармейск',
                42  =>  'Лугины',
                43  =>  'Любар',
                44  =>  'Малин',
                45  =>  'Народичи',
                46  =>  'Новоград-Волынский',
                47  =>  'Овруч',
                48  =>  'Олевск',
                49  =>  'Попельня',
                50  =>  'Радомышль',
                51  =>  'Романов',
                52  =>  'Ружин',
                53  =>  'Черняхов',
                54  =>  'Чуднов',
            ),
            25  =>  array(
                31  =>  'Берегово',
                32  =>  'Великий Березный',
                33  =>  'Виноградов',
                34  =>  'Иршава',
                35  =>  'Межгорье',
                36  =>  'Мукачево',
                37  =>  'Нижние Ворота',
                38  =>  'Перечин',
                39  =>  'Рахов',
                40  =>  'Свалява',
                41  =>  'Тячев',
                30  =>  'Ужгород',
                43  =>  'Хуст',
            ),
            26  =>  array(
                31  =>  'Акимовка',
                32  =>  'Бердянск',
                33  =>  'Васильевка',
                34  =>  'Веселое',
                35  =>  'Вольнянск',
                36  =>  'Гуляйполе',
                37  =>  'Днепрорудное',
                30  =>  'Запорожье',
                39  =>  'Каменка-Днепровская',
                40  =>  'Куйбышево',
                41  =>  'Мелитополь',
                42  =>  'Михайловка',
                43  =>  'Орехов',
                44  =>  'Пологи',
                45  =>  'Приазовское',
                46  =>  'Приморск',
                47  =>  'Токмак',
                48  =>  'Энергодар',
            ),
            6  => array(
                31  =>  'Болехов',
                32  =>  'Бурштын',
                33  =>  'Верховина',
                34  =>  'Галич',
                35  =>  'Городенка',
                36  =>  'Долина',
                37  =>  'Заболотов',
                30  =>  'Ивано-Франковск',
                39  =>  'Калуш',
                40  =>  'Коломыя',
                41  =>  'Косов',
                42  =>  'Надворная',
                43  =>  'Рогатин',
                44  =>  'Рожнятов',
                45  =>  'Снятин',
                46  =>  'Толкователь',
            ),
            10  =>  array(
                31  =>  'Александрия',
                32  =>  'Александровка',
                33  =>  'Бобринец',
                34  =>  'Гайворон',
                35  =>  'Голованевск',
                36  =>  'Добровеличковка',
                37  =>  'Долинская',
                38  =>  'Знаменка',
                30  =>  'Кировоград',
                40  =>  'Компанеевка',
                41  =>  'Малая Виска',
                42  =>  'Новгородка',
                43  =>  'Новоархангельск',
                44  =>  'Новомиргород',
                45  =>  'Новоукраинка',
                46  =>  'Ольшанка',
                47  =>  'Онуфриевка',
                48  =>  'Петрово',
                49  =>  'Помична',
                50  =>  'Ровное',
                51  =>  'Светловодск',
                52  =>  'Смолино',
                53  =>  'Ульяновка',
            ),
            14  =>  array(
                31  =>  'Алчевск',
                32  =>  'Антрацит',
                33  =>  'Беловодск',
                34  =>  'Брянка',
                35  =>  'Кировск',
                36  =>  'Краснодон',
                37  =>  'Красный Луч',
                38  =>  'Кременная',
                39  =>  'Лисичанск',
                30  =>  'Луганск',
                41  =>  'Лутугино',
                42  =>  'Марковка',
                43  =>  'Меловое',
                44  =>  'Новопсков',
                45  =>  'Первомайск',
                46  =>  'Перевальск',
                47  =>  'Попасная',
                48  =>  'Ровеньки',
                49  =>  'Рубежное',
                50  =>  'Сватово',
                51  =>  'Свердловск',
                52  =>  'Северодонецк',
                53  =>  'Славяносербск',
                54  =>  'Станица Луганская',
                55  =>  'Старобельск',
                56  =>  'Стаханов',
                57  =>  'Суходольск',
                58  =>  'Счастье',
            ),
            15  =>  array(
                31  =>  'Броды',
                32  =>  'Буск',
                33  =>  'Городок',
                34  =>  'Дрогобыч',
                35  =>  'Жидачов',
                36  =>  'Жовква',
                37  =>  'Золочев',
                38  =>  'Каменка-Бугская',
                30  =>  'Львов',
                40  =>  'Мостиска',
                41  =>  'Николаев',
                42  =>  'Новояворовск',
                43  =>  'Новый Раздел',
                44  =>  'Перемышляны',
                45  =>  'Радехов',
                46  =>  'Самбор',
                47  =>  'Сколе',
                48  =>  'Старый Самбор',
                49  =>  'Стрый',
                50  =>  'Трускавец',
                51  =>  'Турка',
                52  =>  'Ходоров',
                53  =>  'Червоноград',
            ),
            16  =>array(
                31  =>  'Баштанка',
                32  =>  'Березанка',
                33  =>  'Березнеговатое',
                34  =>  'Братское',
                35  =>  'Веселиново',
                36  =>  'Вознесенск',
                37  =>  'Врадиевка',
                38  =>  'Доманевка',
                39  =>  'Еланец',
                40  =>  'Казанка',
                41  =>  'Кривое Озеро',
                30  =>  'Николаев',
                43  =>  'Новая Одесса',
                44  =>  'Новый Буг',
                45  =>  'Очаков',
                46  =>  'Первомайск',
                47  =>  'Снигиревка',
                48  =>  'Южноукраинск',
            ),
            17  =>array(
                31  =>  'Ананьев',
                32  =>  'Арциз',
                33  =>  'Балта',
                34  =>  'Белгород-Днестровский',
                35  =>  'Беляевка',
                36  =>  'Болград',
                37  =>  'Великая Михайловка',
                38  =>  'Ивановка',
                39  =>  'Измаил',
                40  =>  'Ильичевск',
                41  =>  'Килия',
                42  =>  'Кодыма',
                43  =>  'Коминтерновское',
                44  =>  'Котовск',
                45  =>  'Любашевка',
                46  =>  'Овидиополь',
                30  =>  'Одесса',
                48  =>  'Раздельная',
                49  =>  'Рени',
                50  =>  'Саврань',
                51  =>  'Сарата',
                52  =>  'Татарбунары',
                53  =>  'Теплодар',
                54  =>  'Фрунзовка',
                55  =>  'Южное',
            ),
            18  =>array(
                31  =>  'Белики',
                32  =>  'Великая Багачка',
                33  =>  'Гадяч',
                34  =>  'Глобино',
                35  =>  'Градижск',
                36  =>  'Гребенка',
                37  =>  'Диканька',
                38  =>  'Зеньков',
                39  =>  'Карловка',
                40  =>  'Кобеляки',
                41  =>  'Козельщина',
                42  =>  'Комсомольск',
                43  =>  'Котельва',
                44  =>  'Кременчуг',
                45  =>  'Лохвица',
                46  =>  'Лубны',
                47  =>  'Машевка',
                48  =>  'Миргород',
                49  =>  'Новые Санжары',
                50  =>  'Опошня',
                51  =>  'Оржица',
                52  =>  'Пирятин',
                30  =>  'Полтава',
                54  =>  'Решетиловка',
                55  =>  'Семеновка',
                56  =>  'Хорол',
                57  =>  'Червонозаводское',
                58  =>  'Чернухи',
                59  =>  'Чутово',
                60  =>  'Шишаки',
            ),
            19  =>  array(
                31  =>  'Березно',
                32  =>  'Владимирец',
                33  =>  'Гоща',
                34  =>  'Дубно',
                35  =>  'Дубровица',
                36  =>  'Заречное',
                37  =>  'Здолбунов',
                38  =>  'Клевань',
                39  =>  'Корец',
                40  =>  'Костополь',
                41  =>  'Кузнецовск',
                42  =>  'Млинов',
                43  =>  'Острог',
                44  =>  'Радивилов',
                30  =>  'Ровно',
                46  =>  'Рокитное',
                47  =>  'Сарны',
                48  =>  'Смыга',
            ),
            21  =>  array(
                31  =>  'Ахтырка',
                32  =>  'Белополье',
                33  =>  'Бурынь',
                34  =>  'Великая Писаревка',
                35  =>  'Глухов',
                36  =>  'Дружба',
                37  =>  'Конотоп',
                38  =>  'Краснополье',
                39  =>  'Кролевец',
                40  =>  'Лебедин',
                41  =>  'Липовая Долина',
                42  =>  'Недригайлов',
                43  =>  'Путивль',
                44  =>  'Ромны',
                45  =>  'Свеса',
                46  =>  'Середина-Буда',
                30  =>  'Сумы',
                48  =>  'Тростянец',
                49  =>  'Шостка',
                50  =>  'Ямполь',
            ),
            22  =>  array(
                31  =>  'Бережаны',
                32  =>  'Борщев',
                33  =>  'Бучач',
                34  =>  'Гусятин',
                35  =>  'Залещики',
                36  =>  'Збараж',
                37  =>  'Зборов',
                38  =>  'Козова',
                39  =>  'Копычинцы',
                40  =>  'Кременец',
                41  =>  'Лановцы',
                42  =>  'Монастыриска',
                43  =>  'Подгайцы',
                44  =>  'Почаев',
                45  =>  'Теребовля',
                30  =>  'Тернополь',
                47  =>  'Чертков',
                48  =>  'Шумск',
            ),
            7  =>  array(
                31  =>  'Балаклея',
                32  =>  'Барвенково',
                33  =>  'Безлюдовка',
                34  =>  'Близнецы',
                35  =>  'Богодухов',
                36  =>  'Боровая',
                37  =>  'Валки',
                38  =>  'Великий Бурлук',
                39  =>  'Волчанск',
                40  =>  'Двуречная',
                41  =>  'Зачепиловка',
                42  =>  'Змиев',
                43  =>  'Золочев',
                44  =>  'Изюм',
                45  =>  'Комсомольское',
                46  =>  'Красноград',
                47  =>  'Краснокутск',
                48  =>  'Купянск',
                49  =>  'Лозовая',
                50  =>  'Люботин',
                51  =>  'Мерефа',
                52  =>  'Новая Водолага',
                53  =>  'Первомайский',
                30  =>  'Харьков',
                55  =>  'Чугуев',
                56  =>  'Шевченково',
            ),
            8  =>  array(
                31  =>  'Белозерка',
                32  =>  'Берислав',
                33  =>  'Великая Александровка',
                34  =>  'Великая Лепетиха',
                35  =>  'Верхний Рогачик',
                36  =>  'Высокополье',
                37  =>  'Геническ',
                38  =>  'Голая Пристань',
                39  =>  'Горностаевка',
                40  =>  'Ивановка',
                41  =>  'Каланчак',
                42  =>  'Каховка',
                43  =>  'Новая Каховка',
                44  =>  'Нововоронцовка',
                45  =>  'Новотроицкое',
                46  =>  'Скадовск',
                30  =>  'Херсон',
                48  =>  'Цюрупинск',
                49  =>  'Чаплинка',
            ),
            9 =>array(
                31  =>  'Белогорье',
                32  =>  'Виньковцы',
                33  =>  'Волочиск',
                34  =>  'Городок',
                35  =>  'Деражня',
                36  =>  'Дунаевцы',
                37  =>  'Изяслав',
                38  =>  'Каменец-Подольский',
                39  =>  'Красилов',
                40  =>  'Летичев',
                41  =>  'Нетешин',
                42  =>  'Новая Ушица',
                43  =>  'Полонное',
                44  =>  'Славута',
                45  =>  'Старая Синява',
                46  =>  'Староконстантинов',
                47  =>  'Теофиполь',
                30  =>  'Хмельницкий',
                49  =>  'Чемеровцы',
                50  =>  'Шепетовка',
                51  =>  'Ярмолинцы',
            ),
            1  =>  array(
                31  =>  'Ватутино',
                32  =>  'Городище',
                33  =>  'Драбов',
                34  =>  'Жашков',
                35  =>  'Звенигородка',
                36  =>  'Золотоноша',
                37  =>  'Каменка',
                38  =>  'Канев',
                39  =>  'Катеринополь',
                40  =>  'Корсунь-Шевченковский',
                41  =>  'Лысянка',
                42  =>  'Маньковка',
                43  =>  'Монастырище',
                44  =>  'Смела',
                45  =>  'Тальное',
                46  =>  'Умань',
                47  =>  'Христиновка',
                30  =>  'Черкассы',
                49  =>  'Чернобай',
                50  =>  'Чигирин',
                51  =>  'Шпола',
            ),
            2  =>  array(
                31  =>  'Бахмач',
                32  =>  'Бобровица',
                33  =>  'Борзна',
                34  =>  'Варва',
                35  =>  'Городня',
                36  =>  'Ичня',
                37  =>  'Козелец',
                38  =>  'Короп',
                39  =>  'Корюковка',
                40  =>  'Куликовка',
                41  =>  'Мена',
                42  =>  'Нежин',
                43  =>  'Новгород-Северский',
                44  =>  'Носовка',
                45  =>  'Прилуки',
                46  =>  'Репки',
                47  =>  'Семеновка',
                48  =>  'Серебряное',
                49  =>  'Славутич',
                50  =>  'Сосница',
                51  =>  'Талалаевка',
                30  =>  'Чернигов',
                53  =>  'Щорс',
            ),
            3  =>array(
                31  =>  'Вижница',
                32  =>  'Глыбокая',
                33  =>  'Заставна',
                34  =>  'Кельменцы',
                35  =>  'Кицмань',
                36  =>  'Мамаевцы',
                37  =>  'Новоднестровск',
                38  =>  'Новоселица',
                39  =>  'Сокиряны',
                40  =>  'Сторожинец',
                41  =>  'Хотин',
                30  =>  'Черновцы',
            ),
        ),

        //  время доставок
        /*'delivery-time' =>  array(//сроки доставки
            'person'        =>  array(//физ лицо
            'corporation'   =>array(//юр лицо
                'warehouse'     =>  array(//свой склад
                    'account'       =>  array(//оплата по счету
                        'novaposhta_cash'    =>  array(
                            'all'           =>  array(
                                'time'          =>  'Если оплата будет подтверждена до 12.00 , то забрать можно на следующий день в своем городе после 12.00.',
                            ),
                        ),
                        'pickup'    =>  array(
                            13              =>  array(
                                'time'          =>  'Если оплата будет подтверждена до 12.00, то забрать можно на следующий день в своем городе после 16.00 (ограничения : если заказ оформлен в нерабочее время, то 4 часа отсчитываются с открытия магазина).',
                            ),
                            'all'           =>  array(
                                'time'          =>  'Если оплата будет подтверждена до 12.00, то забрать можно на следующий день в своем городе после 16.00.',
                            ),
                        ),
                        'courier'    =>  array(
                            13              =>  array(
                                'time'          =>  'Мы сможем доставить Ваш заказ в течении 24 ч момента подтверждения оплаты (ограничения : если заказ оформлен в нерабочее время, то 4 часа отсчитываются с открытия магазина)',
                            ),
                            'all'           =>  array(
                                'time'          =>  'Если оплата будет подтверждена до 12.00, то заказ будет доставлен в течении второй половины следующего дня.',
                            ),
                        ),
                    ),
                ),
                'supplier'  =>  array(//склад поставщика
                    'account'       =>  array(//оплата по счету
                        'novaposhta_cash'    =>  array(
                            'all'           =>  array(
                                'time'          =>  'Если оплата будет подтверждена до 10.00, то забрать можно на следующий день в своем городе после 12.00.',
                            ),
                        ),
                        'pickup'    =>  array(
                            13              =>  array(
                                'time'          =>  'Забрать можно будет на следующий день после подтверждения оплаты (ограничения : если заказ оформлен в нерабочее время, то 4 часа отсчитываются с открытия магазина).',
                            ),
                            'all'           =>  array(
                                'time'          =>  'Если оплата произведена до 10.00, то забрать можно на следующий день в своем городе после 16.00.',
                            ),
                        ),
                        'courier'    =>  array(
                            13              =>  array(
                                'time'          =>  'Мы сможем доставить Ваш заказ в течении 1-2 дней с момента подтверждения оплаты (ограничения : если заказ оформлен в нерабочее время, то 4 часа отсчитываются с открытия магазина).',
                            ),
                            'all'           =>  array(
                                'time'          =>  'Если оплата произведена до 10.00, то заказ будет доставлен в течении второй половины следующего дня.',
                            ),
                        ),
                    ),
                ),
            ),
            'person'        =>  array(//физ лицо
                'warehouse'     =>  array(//свой склад
                    'pay_on_delivery'   =>  array(//оплата при получении
                        'novaposhta'        => array(//новая почта наложенный платеж
                            'all'           =>  array(//регионы
                                'time'          =>  'Если заказ оформлен до 12.00, то забрать можно на следующий день в своем городе после 12.00.',
                            ),
                        ),
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'time'          =>  'Если заказ оформлен до 12.00, то забрать можно на следующий день в своем городе после 16.00.',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'если заказ оформлен до 12.00, то заказ будет доставлен в течении второй половины следующего дня.',
                            ),
                        ),
                        'express'           =>  array(//экспресс
                            13                  =>  array(//Киев
                                'time'          =>  'Товар будет доставлен в течении 2 часов.',
                            ),
                        ),
                    ),
                    'cash'          =>  array(//наличка
                        'pickup'        => array(//самовывоз
                            13              =>  array(//Киев
                                'time'          =>  'Если заказ оформлен до 12.00, то забрать можно на следующий день в своем городе после 16.00.',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'Если заказ оформлен до 12.00, то забрать можно на следующий день в своем городе после 16.00.',
                            ),
                        ),
                        'novaposhta_cash'        => array(//новая почта
                            'all'           =>  array(//регионы
                                'time'          =>  'Если заказ оформлен до 12.00, то забрать можно на следующий день в своем городе после 12.00.',
                            ),
                        ),
                    ),
                    'transfer'      =>  array(//перевод
                        'pickup'        => array(//самовывоз
                            13              =>  array(//Киев
                                'time'          =>  'Забрать заказ можно будет через 4 часа с момента подтверждения оплаты (ограничения : если заказ оформлен в нерабочее время, то 4 часа отсчитываются с открытия магазина).',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'Если оплата произведена до 12.00, то забрать можно на следующий день в своем городе после 16.00.',
                            ),
                        ),
                        'novaposhta_cash'        => array(//новая почта
                            'all'           =>  array(//регионы
                                'time'          =>  'Если оплата произведена до 12.00 , то забрать можно на следующий день в своем городе после 12.00.',
                            ),
                        ),
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'time'          =>  'Мы сможем доставить Ваш заказ в течении 24 с момента подтверждения оплаты (ограничения : если заказ оформлен в нерабочее время, то 4 часа отсчитываются с открытия магазина).',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'Если оплата произведена до 12.00, то заказ будет доставлен в течении второй половины следующего дня.',
                            ),
                        ),
                    ),
                    'installment'   =>  array(//рассрочка
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'time'          =>  'Мы сможем доставить Ваш заказ в течении 24 часов с момента получения положительного решения от кредитного отдела(после заполнения анкеты, как правило, решение по выдаче кредита принимается в течении 1-3 дней).',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'В случае, если положительное решение от кредитного отдела будет принято до 12.00 сегодняшнего дня, то заказ будет доставлен в течении второй половины следующего дня.',
                            ),
                        ),
                    ),
                ),
                'supplier'  =>  array(//склад поставщика
                    'pay_on_delivery'   =>  array(//оплата при получении
                        'novaposhta'        => array(//новая почта наложенный платеж
                            'all'           =>  array(//регионы
                                'time'          =>  'Если заказ оформлен до 10.00, то забрать можно на следующий день в своем городе после 12.00.',
                            ),
                        ),
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'time'          =>  'Мы сможем доставить Ваш заказ в течении 1-2 дней с момента подтверждения оплаты (ограничения : если заказ оформлен в нерабочее время, то 4 часа отсчитываются с открытия магазина).',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'Если оплата произведена до 10.00, то забрать можно на следующий день в своем городе после 12.00.',
                            ),
                        ),
                        'express'           =>  array(//экспресс
                            13                  =>  array(//Киев
                                'time'          =>  'Товар будет доставлен в течении 2 часов.',
                            ),
                        ),
                    ),
                    'cash'          =>  array(//наличка
                        'pickup'        => array(//самовывоз
                            13              =>  array(//Киев
                                'time'          =>  'Если заказ оформлен до 10.00, то забрать можно на следующий день в своем городе после 16.00.',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'Если заказ оформлен до 10.00, то забрать можно на следующий день в своем городе после 16.00.',
                            ),
                        ),
                        'novaposhta_cash'        => array(//новая почта
                            'all'           =>  array(//регионы
                                'time'          =>  'Если заказ оформлен до 10.00, то забрать можно на следующий день в своем городе после 12.00.',
                            ),
                        ),
                    ),
                    'transfer'      =>  array(//перевод
                        'pickup'        => array(//самовывоз
                            13              =>  array(//Киев
                                'time'          =>  'Забрать можно будет на следующий день после подтверждения оплаты (ограничения : если заказ оформлен в нерабочее время, то 4 часа отсчитываются с открытия магазина).',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'Если оплата произведена до 10.00, то забрать можно на следующий день в своем городе после 16.00.',
                            ),
                        ),
                        'novaposhta_cash'        => array(//новая почта
                            'all'           =>  array(//регионы
                                'time'          =>  'Если оплата произведена до 10.00, то забрать можно на следующий день в своем городе после 12.00.',
                            ),
                        ),
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'time'          =>  'Мы сможем доставить Ваш заказ в течении 1-2 дней с момента подтверждения оплаты (ограничения : если заказ оформлен в нерабочее время, то 4 часа отсчитываются с открытия магазина).',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'Если оплата произведена до 10.00, то заказ будет доставлен в течении второй половины следующего дня.',
                            ),
                        ),
                    ),
                    'installment'   =>  array(//рассрочка
                        'courier'        => array(//курьер
                            13              =>  array(//Киев
                                'time'          =>  'Мы сможем доставить Ваш заказ в течении 1-2 дней с момента получения положительного решения от кредитного отдела (после заполнения анкеты, как правило, решение по выдаче кредита принимается в течении 1-3 дней).',
                            ),
                            'all'           =>  array(//регионы
                                'time'          =>  'В случае, если положительное решение от кредитного отдела будет принято до 10.00 сегодняшнего дня, то заказ будет доставлен в течении второй половины следующего дня.',
                            ),
                        ),
                    ),
                ),
            ),
        ),*/

    );  // object instance

    private function __construct() { /* ... @return Singleton */ }  // Защищаем от создания через new Singleton

    private function __clone()     { /* ... @return Singleton */ }  // Защищаем от создания через клонирование

    private function __wakeup()    { /* ... @return Singleton */ }  // Защищаем от создания через unserialize

    public static function get()   {    // Возвращает единственный экземпляр класса. @return Singleton

        if ( is_null(self::$instance) ) {
            self::$instance = new Singleton ();
        }
        return self::$instance;
    }
}
