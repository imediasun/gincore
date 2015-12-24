<?php

class Configs {

    private static $instance = null;
    protected $configs = null;
    
    public function set_configs(){
        $this->configs = array(
            'host'                      =>  'restore.kiev.ua',
            'canonical-host'            =>  'http://restore.kiev.ua',
            'shop-name'                 =>  'Яблоко',

            /*
             * блок конфига для саас
             */

            'users-avatars-path'        => 'img/avatars/',
            // выпадающий список цветов устройств при приеме на ремонт
            'devices-colors'            => array(
                l('Черный'), l('Белый'), l('Серый'), l('Золотой'), l('Серебристый'), l('Синий'), 
                l('Красный'), l('Розовый'), l('Желтый'), l('Коричневый'), l('Зеленый'), l('Другой'),
            ),
            // связка типа контрагента и статей (приход/расход) для выбора по умолчанию
            'erp-contractors-type-categories' =>  array( 
                // Контрагент
                1 => array(
                    1 => array(2), // расход
                    2 => array(1), // приход
                ), 
                // Поставщик
                2 => array(
                    1 => array(6,7,8), // расход
                    2 => array(1,5), // приход
                ), 
                // Покупатель
                3 => array(
                    1 => array(2), // расход
                    2 => array(1,33), // приход
                ), 
                // Сотрудник
                4 => array(
                    1 => array(29), // расход
                    2 => array(1), // приход
                )
            ), 
            'countries' => array(
                0 => array(
                    'name' => l('Украина'), 
                    'phone' => array(
                        'code' => 380,  // 380631112233
                        'short_code' => 0, // 0631112233
                        'length' => 9 // длинна без +380
                    )
                ),
                1 => array(
                    'name' => l('Россия'), 
                    'phone' => array(
                        'code' => 7,
                        'short_code' => 8,
                        'length' => 10 
                    )
                ),
                2 => array(
                    'name' => l('Казахстан'), 
                    'phone' => array(
                        'code' => 7,
                        'short_code' => 7,
                        'length' => 10 
                    )
                ),
                3 => array(
                    'name' => l('Беларусь'), 
                    'phone' => array(
                        'code' => 375,
                        'short_code' => 375,
                        'length' => 9 
                    )
                ),
                4 => array(
                    'name' => l('Молдавия'), 
                    'phone' => array(
                        'code' => 373,
                        'short_code' => 373,
                        'length' => 8 
                    )
                ),
                5 => array(
                    'name' => l('USA'),
                    'phone' => array(
                        'code' => 1,
                        'short_code' => 1,
                        'length' => 10
                    )
                ),
            ),
            'manage-langs' => array(
                'current' => 'ru',
                'default' => 'ru',
                'list' => array(
                    'ru' => array(
                        'name' => 'Русский',
                    ),
                    'en' => array(
                        'name' => 'English',
                    )
                )
            ),

                'manage-show-glossary' => true,
                'manage-glossary-url'  => 'https://gincore.net/embedded-faq?iframe=1',
                'manage-print-default-service-restore' => false, // адрес и телефон рестора по умолчанию в печати если не указаны в отделении
                'manage-show-terminal-cashbox'  => false, // показать или скрыть кассу терминал
                'manage-show-phones-btn'    => false, // показать или скрыть кнопку смены аварийных телефонов
                'manage-active-modules'     => array( // активные модуле в админке
                                                    'accountings','categories','clients',
                                                    'debug','logistics','master','orders',
                                                    'partners','products','settings','statistics',
                                                    'tasks','users','warehouses','wrapper','admin_translates'
                                               ),
                'manage-reset-access'       =>  true, // доступен ли сброс в модуле дебаг
                'settings-system-lang-select-enabled' =>  true, // выбор языка системы (+ отрубает переключалку языка в шапке и грузит выбранный язык - из настройки)
                'settings-master-enabled'   =>  true, // мастер настрйоки при регистрации новой админки
                  'currencies'                => array(
                          1 => array('rutils' => array('words' => array(l('гривна'), l('гривны'), l('гривен')), 'gender' => 'female'), 'name' => l('Гривна'), 'shortName' => 'UAH', 'viewName' => l('грн.'), 'symbol' => '₴', 'currency-name' => 'grn-cash'),
                          2 => array('rutils' => array('words' => array(l('евро'), l('евро'), l('евро')), 'gender' => 'male'), 'name' => l('ЕВРО'), 'shortName' => 'EUR', 'viewName' => '€', 'symbol' => '€', 'currency-name' => ''),
                          3 => array('rutils' => array('words' => array(l('доллар'), l('доллара'), l('долларов')), 'gender' => 'male'), 'name' => l('Доллар США'), 'shortName' => 'USD', 'viewName' => '$', 'symbol' => '$', 'currency-name' => 'price'),
                          4 => array('rutils' => array('words' => array(l('рубль'), l('рубля'), l('рублей')), 'gender' => 'male'), 'name' => l('Российский рубль'), 'shortName' => 'RUB', 'viewName' => l('руб.'), 'symbol' => '<i class="fa fa-rub"></i>', 'currency-name' => ''),
                          5 => array('rutils' => array('words' => array(l('рубль'), l('рубля'), l('рублей')), 'gender' => 'male'), 'name' => l('Белорусский рубль'), 'shortName' => 'BYR', 'viewName' => l('бр.'), 'symbol' => 'Br', 'currency-name' => ''),
                          6 => array('rutils' => array('words' => array(l('тунге'), l('тунге'), l('тунге')), 'gender' => 'male'), 'name' => l('Тенге'), 'shortName' => 'KZT', 'viewName' => l('тнг.'), 'symbol' => '₸', 'currency-name' => ''),
                   ),

            /*
             * блок конфига для админки рестора
             */
    //            'manage-print-default-service-restore' => true,
    //            'manage-show-terminal-cashbox'    => true, // показать или скрыть кассу терминал
    //            'manage-show-phones-btn'    => true, // показать или скрыть кнопку смены аварийных телефонов
    //            'manage-active-modules'     => array('*'), // активные модуле в админке
    //            'manage-reset-access'       =>  false, // доступен ли сброс в модуле дебаг
    //            'settings-master-enabled'   =>  false, // мастер настрйоки при регистрации новой админки
    //            'currencies'                => array(
    //                      1 => array('rutils' => array('words' => array(l('гривна'), l('гривны'), l('гривен'), 'gender' => 'female'), 'name' => l('Гривна'), 'shortName' => 'UAH', 'viewName' => l('грн.'), 'symbol' => '₴', 'currency-name' => 'grn-cash'),
    //                      2 => array('rutils' => array('words' => array(l('евро'), l('евро'), l('евро'), 'gender' => 'male'), 'name' => l('ЕВРО'), 'shortName' => 'EUR', 'viewName' => '€', 'symbol' => '€', 'currency-name' => ''),
    //                      3 => array('rutils' => array('words' => array(l('доллар'), l('доллара'), l('долларов'), 'gender' => 'male'), 'name' => l('Доллар США'), 'shortName' => 'USD', 'viewName' => '$', 'symbol' => '$', 'currency-name' => 'price'),
    //                      4 => array('rutils' => array('words' => array(l('рубль'), l('рубля'), l('рублей'), 'gender' => 'male'), 'name' => l('Российский рубль'), 'shortName' => 'RUB', 'viewName' => l('руб.'), 'symbol' => '<i class="fa fa-rub"></i>', 'currency-name' => ''),
    //            ),

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
            'manage-filters-type'       =>  array(/*0 => 'Не выводить', */1 => l('Выбор'), 2 => l('Мультивыбор')/*, 3 => 'Список'*/),
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
            'erp-contractors-types'     =>  array( // типы контрагентов
                1 => 'Контрагент', 
                2 => 'Поставщик', 
                3 => 'Покупатель', 
                4 => 'Сотрудник'
            ), 
            'erp-use-for-accountings-operations'        =>  array(1, 3, 2, 4), // типы контрагентов в бухгалтерии
            //'erp-use-id-for-accountings-operations'     =>  array(79), // id контрагентов используемые в бухгалтерии (транзакции)
            'erp-contractors-use-for-suppliers-orders'  =>  array(2), // типы контрагентов в заказах поставщику
            'erp-contractors-retail-consumers'          =>  array(3), // типы контрагентов не используемые в операциях (транзакции)
            'erp-contractors-staff'     =>  array(4), // типы контрагентов сотрудники
            //'erp-contractor-balance-currency' =>  3, // USD. Валюта счета контрагента, также отображаеется напротив баланса по табличке {cashboxes_courses}
            'erp-write-off-warehouse'   =>  5, // склад куда списываются товары
            'erp-write-off-location'    =>  64, // локация куда списываются товары
            'erp-write-off-user'        =>  1, // клиент которому списываются товары
            'erp-warehouses-types'      =>  array(1 => l('Обычный'), 2 => l('Недостача'), 3 => l('Логистика'), 4 => l('Клиент')), // типы складов
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
                'create-category'               =>  l('Создание новой категории'),
                'edit-category'                 =>  l('Редактирование категории'),
                'edit-category-image'           =>  l('Редактирование картинки меню категории'),
                'edit-category-thumbs'          =>  l('Редактирование превью категории'),
                'edit-category-cat-image'       =>  l('Редактирование картинки категории'),
                'create-filter-group'           =>  l('Создание группы фильтров'),
                'create-filter-value'           =>  l('Создание значения фильтра'),
                'edit-filter-group-value'       =>  l('Редактирование групп и фильтров'),
                'add-similar-goods'             =>  l('Добавление аналогичного товара'),
                'delete-similar-goods'          =>  l('Удаление аналогичного товара'),
                'edit-goods'                    =>  l('Редактирование товара'),
                'delete-filters'                =>  l('Удаление фильтров у товара'),
                'delete-goods-image'            =>  l('Удаление фотографий у товара'),
                'create-goods'                  =>  l('Создание товара'),
                'add-image-goods'               =>  l('Добавление фотографии к товару'),
                'edit-warranties-add'           =>  l('Изменение настроек гарантийных пакетов'),
                'update-top-day'                =>  l('Обновление товара дня'),
                'delete-top-day'                =>  l('Удаление товара дня'),
                'update-top-index'              =>  l('Обноваление товара на главную'),
                'update-bestsellers'            =>  l('Обновление хита продаж'),
                'update-discounts'              =>  l('Обновление товара со скидкой'),
                'update-goods-title-image'      =>  l('Обновление заголовка фотографии товара'),
                'update-goods-image-prio'       =>  l('Обновление приоритета фотографии товара'),
                'update-goods-category'         =>  l('Обновление категории у товара'),
                'add-goods-to-category'         =>  l('Добавление товара в категорию'),
                'delete-goods-from-category'    =>  l('Удаление товара с категории'),
                'add-filter-to-goods'           =>  l('Добавление фильтра к товару'),
                'delete-filter-from-goods'      =>  l('Удаление фильтра у товара'),
                'update-filter-goods'           =>  l('Обновление фильтра у товара'),
                'update-filter-goods-to-multi'  =>  l('Обновление фильтра у товара на мульти'),
                'delete-filter-goods-multi'     =>  l('Удаление мульти фильтра у товара'),
                'add-to-role-per'               =>  l('Добавление роли новых возможностей'),
                'delete-from-role-per'          =>  l('Удаление у роли возможностей'),
                'update-role'                   =>  l('Обновление роли'),
                'add-new-role'                  =>  l('Добавление новой роли'),
                'edit-filter-group'             =>  l('Редактирование группы фильтров'),
                'delete-link-filter-cat'        =>  l('Удаление связки фильтр категория'),
                'edit-filter-value'             =>  l('Редактирование значения фильтра'),
                'delete-filter-value'           =>  l('Удаление значение фильтра'),
                'update-user'                   =>  l('Обновление пользователя'),
                'add-user'                      =>  l('Добавление нового пользователя'),
                'add-manager'                   =>  l('Добавление нового менеджера'),
                'delete-manager'                =>  l('Удаление менеджера'),
                'add-section'                   =>  l('Добавление тега к сопутствующим товарам'),

                'update-goods-reviews'          =>  l('Обновление отзыва о магазине'),
                'update-shop-reviews'           =>  l('Обновление отзыва о товаре'),
                'delete-office'                 =>  l('Удалено отделение магазина'),
                'update-office'                 =>  l('Обновление отделения магазина'),
                'add-office'                    =>  l('Добавление нового отделения магазина'),
                'update-np-offices'             =>  l('Обновление отделений новой почты'),
                'manager-accepted-order'        =>  l('Менеджер принял заказ'),
                'update-order'                  =>  l('Заказ обновлен'),
                'new-order'                     =>  l('Новый заказ'),
                'import-from-price'             =>  l('Обновлены товари с прайса'),
                'add-review'                    =>  l('Добавление нового отзыва'),
                'add-comment'                   =>  l('Добавление нового комментария к отзыву'),
                'edit-comment'                  =>  l('Редактирование коментария'),
                'export-order'                  =>  l('Экспорт заказа'),
                'add-market-category'           =>  l('Добавление новой категории к прайс агрегатору'),
                'add-warehouse-order'           =>  l('Новый заказ на поставку товара'),
                'edit-warehouse-order'          =>  l('Редактирование заказа на поставку товара'),
                'remove-supplier-order'         =>  l('Удален заказ на поставку товара'),
                'debit-supplier-order'          =>  l('Оприходован заказ на поставку товара'),
                'accept-supplier-order'         =>  l('Принят заказ на поставку товара'),
                'move-categories'               =>  l('Перемещена категория'),
                'edit-seo-category'             =>  l('Редактирование seo категории'),
                'edit-ym_id'                    =>  l('Редактирование яндекс маркет ID'),

                'add-cashbox'                   =>  l('Добавление кассы'),
                'edit-cashbox'                  =>  l('Редактирование кассы'),
                'add-contractor_category'       =>  l('Добавление категории контрагентов'),
                'edit-contractor_category'      =>  l('Редактирование категории контрагентов'),
                'edit-contractor'               =>  l('Редактирование контрагента'),
                'add-contractor'                =>  l('Добавление контрагента'),
                'add-transaction'               =>  l('Добавление транзакции в кассу'),
                'edit-product-avail'            =>  l('Обновление активности товара'),
                'add-warehouse'                 =>  l('Добавление склада'),
                'edit-warehouse'                =>  l('Редактирование склада'),

                'remove-contractors-category'   =>  l('Удаление категории контрагентов'),
                'remove-contractor-from-category'=> l('Удаление контрагента из категории'),
                'remove-contractor'             =>  l('Удаление контрагента'),
                'remove-global-cashbox-course'  =>  l('Удаление общего курса у касс'),
                'add-contractor-category'       =>  l('Добавление контрагента в категорию'),
                'add-to-cashbox-currency'       =>  l('Добавление курс кассе'),
                'create-chain'                  =>  l('Создана цепочка'),

                'move-item'                     =>  l('Перемещение изделия'),
                'add-chain-body'                =>  l('Добавление цепочка'),
                'edit-chain-body'               =>  l('Редактирование цепочка'),
                'chain-body-update-serial'      =>  l('Обновление серийника'),
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

            //// статусы заказа
            // stockman - редактирование кладовщику (true/false)
            // from - с какого статуса можно изменить менеджеру заказ ((array), (string) from all) 'erp-use'=true
            // role - привилегия, юзер с которой может поменять статус (можно не добавлять)
            // edit - редактирование данных заказа, кроме статуса (true/false)
            'order-status'  =>  array(
                0   => array('name' => l('Принят в ремонт'), 'color' => 'B05DBB', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                5   => array('name' => l('В процессе ремонта'), 'color' => '414CD2', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                10  => array('name' => l('Ожидает запчастей'), 'color' => '90C8EE', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                15  => array('name' => l('Клиент отказался'), 'color' => 'F04544', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                20  => array('name' => l('Не подлежит ремонту'), 'color' => 'C18BA6', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                25  => array('name' => l('Выдан без ремонта'), 'color' => 'FF9C49', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                27  => array('name' => l('На согласовании'), 'color' => '7ca319', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                30  => array('name' => l('В удаленном сервисе'), 'color' => '0A0E16', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                35  => array('name' => l('Готов'), 'color' => '787987', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                40  => array('name' => l('Выдан'), 'color' => '76C572', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                45  => array('name' => l('Принят на доработку'), 'color' => 'CFAFE7', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
                50  => array('name' => l('Переведен в донор'), 'color' => 'AC5359', 'from' => array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50)),
            ),

            'credits_package'   =>  array( // виды кредитов
                2                   =>  10, // %
                3                   =>  12, // %
                6                   =>  25, // %
                9                   =>  43, // %
                12                  =>  50, // %
            )

        );  // object instance
    }

    public static function getInstance(){
        return self::$instance;
    }
    
    public static function get(){  
        if(is_null(self::$instance)){
            self::$instance = new self();
            self::$instance->set_configs();
        }
        return self::$instance->configs;
    }
    
    private function __construct(){}
    private function __clone(){}
    private function __wakeup(){}
}
