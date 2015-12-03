<?php


$modulename[] = 'logisticsold';
$modulemenu[] = 'Логистика';
$moduleactive[] = !$ifauth['is_2'];


class logisticsold
{
    protected $all_configs;

    public $count_on_page;

    function __construct(&$all_configs)
    {
        $this->all_configs = $all_configs;
        $this->count_on_page = count_on_page();

        global $input_html;

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        // проверяем вкл склады
        if ($this->can_show_module() == false) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">У Вас не достаточно прав</p></div>';
        }

        // если отправлена форма
        if (!empty($_POST))
            $this->check_post($_POST);

        //if ($ifauth['is_2']) return false;

        $input_html['mcontent'] = $this->gencontent();

    }

    function can_show_module()
    {
        if ($this->all_configs['configs']['erp-use'] && ($this->all_configs['oRole']->hasPrivilege('logistics')
                /*|| $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')*/)) {
            return true;
        } else {
            return false;
        }
    }

    function check_post($post)
    {
        $mod_id = $this->all_configs['configs']['logistics-manage-page'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';


        header("Location:" . $_SERVER['REQUEST_URI']);
        exit;
    }

    function gencontent()
    {
        $out = '<div class="tabbable"><ul class="nav nav-tabs">';
        $out .= '<li><a class="click_tab default" data-open_tab="logistics_motions" onclick="click_tab(this, event)" ';
        $out .= 'data-toggle="tab" href="#motions">Логистика</a></li>';
        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $out .= '<li><a class="click_tab" data-open_tab="logistics_settings" onclick="click_tab(this, event)" ';
            $out .= 'data-toggle="tab" href="#settings">Настройки</a></li>';
        }
        $out .= '</ul><div class="tab-content">';

        // управление перемещениями
        $out .= '<div id="motions" class="tab-pane">';
        $out .= '</div><!--#motions-->';

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $out .= '<div id="settings" class="tab-pane">';
            $out .= '</div><!--#settings-->';
        }

        $out .= '</div><!--.tab-content-->';
        $out .= '</div><!--.tabbable-->';

        $out .= $this->all_configs['chains']->append_js();

//        print_r($this->get_chains());
//        exit;
        
        return $out;
    }

    function get_chains() //ok
    {
        $data = $this->all_configs['db']->query('SELECT h.id, h.avail, w.title, l.location, b.location_id, b.wh_id,
                b.number, b.id as b_id, b.type
            FROM {chains_headers} as h
            LEFT JOIN {chains_bodies} as b ON b.chain_id=h.id
            LEFT JOIN {warehouses} as w ON w.id=b.wh_id
            LEFT JOIN {warehouses_locations} as l ON l.id=b.location_id
            ORDER BY h.avail DESC, h.id, b.number', array())->assoc();

        $chains = array();
        if ($data) {
            foreach ($data as $chain) {
                if (!isset($chains[$chain['id']]['id'])) {
                    $chains[$chain['id']]['id'] = $chain['id'];
                    $chains[$chain['id']]['avail'] = $chain['avail'];
                    $chains[$chain['id']]['bodies'] = array();
                }
                if ($chain['b_id'] > 0) {
                    $chains[$chain['id']]['bodies'][$chain['b_id']]['id'] = $chain['b_id'];
                    $chains[$chain['id']]['bodies'][$chain['b_id']]['title'] = $chain['title'];
                    $chains[$chain['id']]['bodies'][$chain['b_id']]['location'] = $chain['location'];
                    $chains[$chain['id']]['bodies'][$chain['b_id']]['location_id'] = $chain['location_id'];
                    $chains[$chain['id']]['bodies'][$chain['b_id']]['wh_id'] = $chain['wh_id'];
                    $chains[$chain['id']]['bodies'][$chain['b_id']]['type'] = $chain['type'];
                    $chains[$chain['id']]['bodies'][$chain['b_id']]['number'] = $chain['number'];
                    $chains[$chain['id']]['bodies'][$chain['b_id']]['chain_id'] = $chain['id'];
                }
            }
        }

        return $chains;
    }

    function logistics_motions()
    {
        $date = isset($_GET['date']) ? $_GET['date'] : '';

        $out = '<div class="row-fluid"><div class="span2"><form>';
        // filters
        $out .= '<input type="text" placeholder="'.l('Дата').'" name="date" class="daterangepicker input-medium" value="' . $date . '" />';
        $out .= '<input name="o_id" value="';
        $out .= isset($_GET['o_id']) && intval($_GET['o_id']) > 0 ? intval($_GET['o_id']) : '';
        $out .= '" type="text" class="input-medium" placeholder="№ заказа">';
        $out .= '<input name="i_id" value="';
        $out .= isset($_GET['i_id']) && mb_strlen($_GET['i_id'], 'UTF-8') > 0 ? trim($_GET['i_id']) : '';
        $out .= '" type="text" class="input-medium" placeholder="№ изделия">';
        $out .= '<label>Курьер: <br>';
        $out .= '<select disabled class="input-medium" name="crr"><option value="">Любой</option>';
        $locations = $this->all_configs['db']->query('SELECT l.id, l.location
            FROM {warehouses} as w, {warehouses_locations} as l WHERE w.type=?i AND w.id=l.wh_id', array(3))->vars();
        if ($locations) { //@todo тут ошибка, нужно фильтровать по курьеру. Сейчас задизейбленно
            foreach ($locations as $location_id=>$location) {
                $selected = isset($_GET['crr']) && $location_id == $_GET['crr'] ? 'selected': '';
                $out .= '<option ' . $selected . ' value="' . $location_id . '">' . htmlspecialchars($location) . '</option>';
            }
        }
        $out .= '</select></label>';
   
        if (isset($_GET['whfrom'])){
            $whfrom = is_array($_GET['whfrom']) ? implode(',', $_GET['whfrom']) : $_GET['whfrom'];
            $whfrom_ar = explode(',', $whfrom);
        }
        if (isset($_GET['whto'])){
            $whto = is_array($_GET['whto']) ? implode(',', $_GET['whto']) : $_GET['whto'];
            $whto_ar = explode(',', $whto);
        }
        $wharehouses = $this->all_configs['db']->query('SELECT id, title
            FROM {warehouses} WHERE type=1 ORDER BY id', array())->assoc();
        $out .= '<label>Откуда: <br>';
        $out .= '<select class="multiselect input-small" name="whfrom[]" multiple="multiple">';
        $out .= build_array_tree($wharehouses, ((isset($whfrom_ar)) ? $whfrom_ar : array()));
        $out .= '</select></label>';
        $out .= '<label>Куда: <br>';
        $out .= '<select class="multiselect input-small" name="whto[]" multiple="multiple">';
        $out .= build_array_tree($wharehouses, ((isset($whto_ar)) ? $whto_ar : array()));
        $out .= '</select></label>';
        
        $out .= '<input type="button" onclick="send_get_form(this)" value="Фильтровать" class="btn" />';
        $out .= '</form></div>'
                . '<div class="span10">';
        // content begin
         
        //get all chains
        $chains = $this->get_chains();
        
        $chains_filter = array();
        
        // вместо выключенных кнопок см. ниже, опрелелем какие айди цепочек 
        // подходит под выбранные фальтрый "куда" и "откуда" и определяем айди
        $chains_filter_from_to = array();
        
        if ($chains) {
            $avail_chains = array();
            foreach ($chains as $chain) {
                if (isset($chain['bodies']) && count($chain['bodies']) > 0) {
                    $class = $chain['avail'] == 1 ? '' : ' btn-danger';
                    $href = $this->all_configs['prefix'] . 'logistics';
                    // first body
                    reset($chain['bodies']);
                    $current = current($chain['bodies']);
                    $location_id = $current['location_id'];
                    // chain filter
                    if (isset($_GET['chn']) && intval($_GET['chn']) > 0) {
                        if (intval($_GET['chn']) == $chain['id']) {
                            $class .= ' active';
                            if ($chain['avail'] == 1) {
                                $avail_chains[$location_id]['bodies'] = $chain['bodies'];
                            }
                        } else {
                            $href .= '?chn=' . $chain['id'];
                        }
                    }  
                    
                    $from = htmlspecialchars($current['title'])/* . ' ' . htmlspecialchars($current['location'])*/;
                    $fromid = htmlspecialchars($current['wh_id']);
                    // last body
                    end($chain['bodies']);
                    $current = current($chain['bodies']);
                    $to = htmlspecialchars($current['title'])/* . ' ' . htmlspecialchars($current['location'])*/;
                    $toid = htmlspecialchars($current['wh_id']);
                    // filter chain links
                    //$out .= '<a href="' . $href . '#motions" class="btn btn-mini' . $class . '">' . $from . '<br />' . $to . '</a>';
                    $chains_filter[$location_id][$chain['id']] = array(
                        'from' => $from,
                        'fromid' => $fromid,
                        'to' => $to,
                        'toid' => $toid,
                        'href' => $href,
                        'class' => $class,
                        'avail' => 1//$chain['avail']
                    );
                    
                    if (!isset($_GET['chn'])){
                        $href .= '?chn=' . $chain['id'];
                        
                        if ($chain['avail'] == 1 && isset($whfrom_ar) && !isset($whto_ar) && in_array($fromid, $whfrom_ar)){
                            $chains_filter_from_to[]=$chain['id'];
                            $avail_chains[$location_id]['bodies'] = $chain['bodies'];
                        }
                        
                        if ($chain['avail'] == 1 && !isset($whfrom_ar) && isset($whto_ar) && in_array($toid, $whto_ar)){
                            $chains_filter_from_to[]=$chain['id'];
                            $avail_chains[$location_id]['bodies'] = $chain['bodies'];
                        }
                        
                        if ($chain['avail'] == 1 && isset($whfrom_ar) && isset($whto_ar) && in_array($fromid, $whfrom_ar) && in_array($toid, $whto_ar)){
                            $chains_filter_from_to[]=$chain['id'];
                            $avail_chains[$location_id]['bodies'] = $chain['bodies'];
                        }

                        
                    } //!isset($_GET['chn']
                    
                }
            }
            

            
//            return array(
//                'html' => print_r($chains_filter_from_to, true),
//                'functions' => array('reset_multiselect()'),
//            );
            
            // set filters
            $query = '';
            // order
            if (isset($_GET['o_id']) && intval($_GET['o_id']) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',
                    array($query, intval($_GET['o_id'])));
            }
            // item
            if (isset($_GET['i_id']) && mb_strlen($_GET['i_id'], 'UTF-8') > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND i.id=?i',
                    array($query, suppliers_order_generate_serial(array('serial' => $_GET['i_id']), false)));
            }
            // date
            if (isset($_GET['date']) && mb_strlen(trim($_GET['date']), 'UTF-8') > 0) {
                $date = explode('-', trim($_GET['date']));
                if (isset($date[0]) && strtotime($date[0]) > 0) {
                    $query = $this->all_configs['db']->makeQuery('?query AND m.date_move>=?',
                        array($query, date("Y-m-d 00:00:00", strtotime($date[0]))));
                }
                if (isset($date[1]) && strtotime($date[1]) > 0) {
                    $query = $this->all_configs['db']->makeQuery('?query AND m.date_move<=?',
                        array($query, date("Y-m-d 23:59:59", strtotime($date[1]))));
                }
            }
            
//            //wharehouse to
//            if (isset($whto)){
//                $query = $this->all_configs['db']->makeQuery('?query AND o.location_id IN (?)',
//                    array($query, $whto));
//            }
            
            /*return array(
                'html' => $whto,
                'functions' => array('reset_multiselect()'),
            );
             */
            //echo $whto; //= 2,3,6
            //exit;  

            // count items and orders
            $iter = 0;
            $count = 0;
            $skip = isset($_GET['p']) && $_GET['p'] > 1 ? ($_GET['p'] - 1) * $this->count_on_page : 0;

            $display = array();
            $out_body = '<br /><br /><table class="table chains table-compact"><tbody>';
            $iter += $skip;
            
            //сколько перемещений (активных?) в каждой из цепочек.
            $counts = $this->all_configs['db']->query(
                    'SELECT m.location_id, COUNT(DISTINCT IF (m.item_id IS NULL , m.order_id, null)) + COUNT(DISTINCT m.item_id)
                    FROM {warehouses_stock_moves} as m
                    LEFT JOIN {warehouses_goods_items} as i ON m.item_id=i.id AND m.location_id=i.location_id AND i.order_id IS NULL
                    LEFT JOIN {orders} as o ON m.order_id=o.id AND m.location_id=o.location_id AND m.item_id IS NULL
                    WHERE (i.id IS NOT NULL OR o.id IS NOT NULL) AND m.location_id IN (?li) GROUP BY m.location_id',
                array(array_keys($chains_filter)))->vars(); //Array ( [2588] => 16 [2606] => 3 [2607] => 8 ) 

//  выключены кнопки-фильтры
//            foreach($chains_filter as $chain_id=>$chain_filter) {
//                foreach ($chain_filter as $cf) {
//                    if ($cf['avail'] == 0) {
//                        continue;
//                    }
//                    $qty = $counts && isset($counts[$chain_id]) ? $counts[$chain_id] : 0;
//                    $out .= '<a href="' . $cf['href'] . '#motions" class="btn btn-mini' . $cf['class'] . '">';
//                    $out .= '<span class="qty-logistics">' . $qty . '</span>' . $cf['from'] . '<br />' . $cf['to'] . '</a>';
//                }
//            }

//            return array(
//                'html' => '<pre>'.  htmlspecialchars(print_r(array_keys($avail_chains), true)).'</pre>',
//                'functions' => array('reset_multiselect()'),
//            );
//            exit; 
            
            if (count($avail_chains) > 0 && !isset($_GET['crr'])) {
                $count += $this->all_configs['db']->query(
                    'SELECT COUNT(DISTINCT IF (m.item_id IS NULL , m.order_id, null)) + COUNT(DISTINCT m.item_id)
                    FROM {warehouses_stock_moves} as m
                    LEFT JOIN {warehouses_goods_items} as i ON m.item_id=i.id AND m.location_id=i.location_id AND i.order_id IS NULL
                    LEFT JOIN {orders} as o ON m.order_id=o.id AND m.location_id=o.location_id AND m.item_id IS NULL
                    WHERE (i.id IS NOT NULL OR o.id IS NOT NULL) AND m.location_id IN (?li) ?query',
                    array(array_keys($avail_chains), $query))->el();

                if ($count > 0) {
                    if (!isset($_GET['crr'])) {
                        $display = $this->all_configs['db']->query('SELECT m.item_id, m.order_id, i.serial, m.date_move,
                                  m.location_id FROM {warehouses_stock_moves} as m
                                LEFT JOIN {warehouses_goods_items} as i ON m.item_id=i.id AND m.location_id=i.location_id AND i.order_id IS NULL
                                LEFT JOIN {orders} as o ON m.order_id=o.id AND m.location_id=o.location_id AND m.item_id IS NULL
                                WHERE (i.id IS NOT NULL OR o.id IS NOT NULL) AND m.location_id IN (?li) ?query
                                GROUP BY i.id, o.id ORDER BY m.date_move DESC LIMIT ?i, ?i',
                            array(array_keys($avail_chains), $query, $skip, $this->count_on_page))->assoc();

                        if ($display) {

                            foreach ($display as $move) {
                                $iter++;
                                $obj = $move['item_id'] > 0 ? suppliers_order_generate_serial($move, true, true) :
                                    '<a href="' . $this->all_configs['prefix'] . 'orders/create/' . $move['order_id'] . '">Заказ №' . $move['order_id'] . '</a>';
                                $out_body .= '<tr><td>' . $iter . '</td><td class="well">' . $obj . '</td>';
                                foreach ($avail_chains[$move['location_id']]['bodies'] as $chain) {
                                    $title = htmlspecialchars($chain['title']);
                                    $title .= $chain['location_id'] > 0 ? ' (' . htmlspecialchars($chain['location']) . ')' : '';
                                    $class = $move['location_id'] == $chain['location_id'] ? 'success' : '';
                                    $date = $move['location_id'] == $chain['location_id'] ? ' <span title="' . do_nice_date($move['date_move'], false) . '">' . do_nice_date($move['date_move']) . '</span>' : '';
                                    $out_body .= '<td class="' . $class . '">' . $title . $date . '</td><td class="chain-body-arrow"></td>';
                                }
                                $out_body .= '</tr><tr><td colspan="7"></td></tr>';
                            }
                        }
                    }
                }
            }

            $out .= $out_body;
            // chain filter
            if (isset($_GET['chn']) && intval($_GET['chn']) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND m.chain_id=?i',
                    array($query, 1));
            }

            //должно работать или $_GET['chn'] или $chains_filter_from_to
            //фильтр по цепочкам
            if (!isset($_GET['chn']) && count($chains_filter_from_to)>0){
                $query = $this->all_configs['db']->makeQuery('?query AND m.chain_id IN (?l)',
                    array($query, $chains_filter_from_to)); 
            }
            
//            return array(
//                'html' => print_r($chains_filter_from_to, true),
//                'functions' => array('reset_multiselect()'),
//            );
//            exit;
            
            // courier filter
            //@TODO тут ошибка, не потому фильтр :(
            if (isset($_GET['crr']) && intval($_GET['crr']) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND IF (w.type=?i, m.location_id=?i, true)',
                    array($query, $this->all_configs['chains']->chain_logistic, intval($_GET['crr'])));
            }

            // get order and items move on chains
            $query = $this->all_configs['db']->makeQuery('SELECT m.item_id, m.order_id, i.serial, m.date_move,
                  m.location_id, m.chain_id, m.wh_id, m.user_id, l.location, m.chain_body_id,
                  (SELECT date_move FROM {warehouses_stock_moves} WHERE id<m.id AND (i.id=item_id OR o.id=order_id) ORDER BY id DESC LIMIT 1) as begin_date_move
                FROM {warehouses_stock_moves} as m
                LEFT JOIN {warehouses_goods_items} as i ON m.item_id=i.id AND m.order_id IS NULL
                LEFT JOIN {orders} as o ON m.order_id=o.id AND m.item_id IS NULL
                LEFT JOIN {warehouses_locations} as l ON l.id=m.location_id
                WHERE (i.id IS NOT NULL OR o.id IS NOT NULL) AND m.chain_id IS NOT NULL ?query '
                .' ORDER BY IF(i.id, i.id, null), IF(o.id, o.id, null), m.id',
                //.' GROUP BY m.chain_id',
                array($query));

            $objects = $this->all_configs['db']->query($query, array())->assoc();
            
//            return array(
//                'html' => '<pre>'.  htmlspecialchars(print_r($chains, true)).'</pre>',
//                'functions' => array('reset_multiselect()'),
//            );
//            exit;
            
            
            $i = 0;
            if ($objects) {

                $moves = array();
                reset($objects);
                while ($object = current($objects)) {
                    if (isset($chains[$object['chain_id']]['bodies'])) {
                        $key = strtotime($object['date_move']);
                        //$iter++;
                        $i++;
                        $obj = $object['item_id'] > 0 ? suppliers_order_generate_serial($object, true, true) :
                            '<a href="' . $this->all_configs['prefix'] . 'orders/create/' . $object['order_id'] . '">Заказ №' . $object['order_id'] . '</a>';
                        $moves[$key][$i] = /*'<tr><td>' . $iter . '</td>*/'<td class="well">' . $obj . '</td>';
                        $j = 0;

                        $prev_b_id = null;
                        foreach ($chains[$object['chain_id']]['bodies'] as $b_id=>$chain) {
                            if ($prev_b_id && $prev_b_id == $object['chain_body_id']) {
                                next($objects);
                                $object = current($objects);
                            }
                            $prev_b_id = $b_id;
                            $j++;
                            $title = htmlspecialchars($chain['title']);
                            $title .= $chain['location_id'] == 0 ? ($object['location_id'] > 0 ? ' (' . htmlspecialchars($object['location']) . ')' : '') : ' (' . htmlspecialchars($chain['location']) . ')';
                            $class = ($object['location_id'] != $chain['location_id'] && $j == 1) || ($object['wh_id'] == $chain['wh_id'] && ($chain['location_id'] == 0 || $object['location_id'] == $chain['location_id'])) ? 'success' : '';
                            $object['date_move'] = $j == 1 ? $object['begin_date_move'] : $object['date_move'];
                            $date = ($object['location_id'] != $chain['location_id'] && $j == 1) || ($object['wh_id'] == $chain['wh_id'] && ($chain['location_id'] == 0 || $object['location_id'] == $chain['location_id'])) ? ' <span title="' . do_nice_date($object['date_move'], false) . '">' . do_nice_date($object['date_move']) . '</span>' : '';
                            $moves[$key][$i] .= '<td class="' . $class . '">' . $title . $date . '</td><td class="chain-body-arrow"></td>';

                            if ($b_id == $object['chain_body_id'] && count($chains[$object['chain_id']]['bodies']) > $j) {
                                $prev = $object;
                                next($objects);
                                $object = current($objects);
                                if ($prev['order_id'] != $object['order_id'] || $prev['item_id'] != $object['item_id']) {
                                    prev($objects);
                                    $object = current($objects);
                                }
                            }
                        }
                        $prev_b_id = null;
                        $moves[$key][$i] .= '</tr><tr><td colspan="7"></td></tr>';
                    }
                    next($objects);
                }

                if (count($display) < $this->count_on_page && count($moves) > 0) {
                    // sort by key date desc
                    krsort($moves);

                    $j = 0;
                    foreach ($moves as $move) {
                        foreach ($move as $item) {
                            //echo $skip .'-'.$iter.' ';
                            $j++;
                            if ($j < $skip) {
                                continue;
                            }
                            if ($j >= $this->count_on_page + $skip) {
                                break 2;
                            }
                            $iter++;
                            $out .= '<tr><td>' . $iter . '</td>' . $item;
                        }
                    }
                }
                $count += $i;
            }
            $out .= '</tbody></table>';

            // строим блок страниц
            $count_page = ceil($count / $this->count_on_page);
            $out .= page_block($count_page, '#motions');
        } else {
            $out .= 'Цепочек не найдено';
        }
        $out .= '</div></div>';

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function logistics_settings()
    {
        $out = '';

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $chains = $this->all_configs['db']->query('SELECT h.id, h.avail, w.title, l.location, b.location_id, b.wh_id,
                    b.number, b.id as b_id, b.type
                FROM {chains_headers} as h
                LEFT JOIN {chains_bodies} as b ON b.chain_id=h.id
                LEFT JOIN {warehouses} as w ON w.id=b.wh_id
                LEFT JOIN {warehouses_locations} as l ON l.id=b.location_id
                ORDER BY h.avail DESC, h.id, b.number', array())->assoc();

            if ($chains) {
                $out .= '<table class="table chains table-compact"><tbody>';
                $last_chain = null;
                $remove_btn = function($chain) {
                    if ($chain && $chain['avail'] == 1) {
                        return '<i class="icon-remove cursor-pointer" title="Удалить цепочку ' . $chain['id'] . '" onclick="remove_chain(this, ' . $chain['id'] . ')"></i>';
                    }
                };
                foreach ($chains as $chain) {
                    if (!$last_chain || $last_chain['id'] != $chain['id']) {
                        $out .= $last_chain === null ? '' : '<td>' . $remove_btn($last_chain) . '</td></tr><tr><td colspan="7"></td></tr>';
                        $out .= '<tr class="' . ($chain['avail'] == 1 ? '' : 'error') . '">';
                    }
                    $title = htmlspecialchars($chain['title']);
                    $title .= $chain['location_id'] > 0 ? ' (' . htmlspecialchars($chain['location']) . ')' : '';
                    $out .= '<td>' . $title . '</td><td class="chain-body-arrow"></td>';

                    $last_chain = $chain;
                }
                end($chains);
                $chain = current($chains);
                $out .= '<td>' . $remove_btn($chain) . '</td></tr></tbody></table>';
            }

            $out .= '<div class="accordion" id="accordion-logistics">';
            $chain = array(
                'id' => 0,
                'avail' => 1,
                'title' => 'Добавить логистическую цепочку',
                'title_end' => '',
                'bodies' => array(
                    array('wh_id' => 0, 'location_id' => 0, 'type' => $this->all_configs['chains']->chain_warehouse),
                    array('wh_id' => 0, 'location_id' => 0, 'type' => $this->all_configs['chains']->chain_logistic),
                    array('wh_id' => 0, 'location_id' => 0, 'type' => $this->all_configs['chains']->chain_warehouse),
                )
            );
            $out .= $this->display_logistic_settings($chain);
            $out .= '</div>';
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function display_logistic_settings($chain)
    {
        $out = '<div class="accordion-group"><div class="accordion-heading">';
        $out .= '<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-logistics" href="#collapseLogistics-' . $chain['id'] . '">';
        $out .= ($chain['avail'] == 1 ? $chain['title'] . $chain['title_end'] : '<strike>' . $chain['title'] . $chain['title_end'] . '</strike>') . '</a></div>';
        $out .= '<div id="collapseLogistics-' . $chain['id'] . '" class="accordion-body collapse"><div class="accordion-inner">';
        $out .= '<form class="form-horizontal"><input type="hidden" name="chain_id" value="' . $chain['id'] . '" />';
        $out .= '<table class="table"><tbody>';

        $row_inform = '<tr>';
        $row_settings = '<tr>';
        $row_bodies = '<tr>';
        $informs = array(
            0 => 'Укажите отправную точку (локацию), при перемещении на которую будет автоматически формироватся логистическая цепочка',
            1 => '',
            2 => 'Укажите точку назначения (локацию), при перемещении на которую будет закрываться логистическая цепочка',
        );
        $i = 0;
        foreach ($chain['bodies'] as $body) {
            $row_inform .= '<td>' . (isset($informs[$i]) ? htmlspecialchars($informs[$i]) : '') . '</td>';

            $row_settings .= '<td><div class="control-group"><label class="control-label">Склад:</label><div class="controls">';
            if ($chain['id'] > 0) {
                $row_settings .= $body['title'];
            } else {
                $row_settings .= '<select data-multi="' . $i . '" onchange="change_warehouse(this)" class="input-medium select-warehouses-item-move" name="wh_id_destination[' . $i . ']">';
                $row_settings .= $this->all_configs['chains']->get_options_for_move_item_form(false, $body['wh_id'], null, false, null, null, $body['type'] == $this->all_configs['chains']->chain_logistic ? true : false);
                $row_settings .= '</select>';
            }
            $row_settings .= '</div></div>';
            if ($body['type'] == $this->all_configs['chains']->chain_warehouse) {
                $row_settings .= '<div class="control-group"><label class="control-label">Локация:</label><div class="controls">';
                if ($chain['id'] > 0) {
                    $row_settings .= $body['location'];
                } else {
                    $row_settings .= '<select class="multiselect input-medium select-location' . $i . '" name="location[' . $i . ']">';
                    $row_settings .= $this->all_configs['suppliers_orders']->gen_locations($body['wh_id'], $body['location_id']);
                    $row_settings .= '</select>';
                }
                $row_settings .= '</div></div>';
            }
            $row_settings .= '</td>';
            $i++;
        }
        $row_inform .= '</tr>';
        $row_settings .= '</tr>';
        $row_bodies .= '</tr>';

        $out .= $row_inform .  $row_settings . $row_bodies . '</tbody></table>';
        if ($chain['avail'] == 1) {
            $out .= '<div class="control-group"><div class="controls">';
            if ($chain['id'] > 0) {
                $out .= '<label class="checkbox"><input type="checkbox" name="remove" value="1" /> Удалить</label>';
            }
            $out .= '<input class="btn btn-primary" type="button" value="'.l('Сохранить').'" onclick="create_chain(this)" /></div></div>';
        }
        $out .= '</form>';
        $out .= '</div></div></div>';

        return $out;
    }

    function preload()
    {

    }

    function ajax()
    {
        $data = array(
            'state' => false
        );

        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['logistics-manage-page'];

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: applicpreloadation/json; charset=UTF-8");
            echo json_encode(array('message' => 'Нет прав', 'state' => false));
            exit;
        }

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
                header("Content-Type: application/json; charset=UTF-8");
                $this->preload();
                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array((isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'], 'UTF-8')) > 0) ? trim($_POST['hashs']) : null)
                    );
                    echo json_encode(array('html' =>  $function['html'], 'state' => true, 'functions' => $function['functions']));
                } else {
                    echo json_encode(array('message' => 'Не найдено', 'state' => false));
                }
                exit;
            }
        }

        // удаляем цепочку
        if ($act == 'remove-chain') {
            if (isset($_POST['chain_id']) && intval($_POST['chain_id']) > 0) {
                $this->all_configs['db']->query('UPDATE {chains_headers} SET avail=?i WHERE id=?i',
                    array(0, intval($_POST['chain_id'])));
                $data['state'] = true;
            } else {
                $data['msg'] = 'Цепочка не найдена';
            }
        }

        // создаем цепочку
        if ($act == 'create-chain') {

            $whs = isset($_POST['wh_id_destination']) ? (array)$_POST['wh_id_destination'] : array();
            $locs = isset($_POST['location']) ? (array)$_POST['location'] : array();

            $wh_from_id = isset($whs[0]) ? intval($whs[0]) : null;
            $wh_to_id = isset($whs[2]) ? intval($whs[2]) : null;
            $location_from_id = isset($locs[0]) ? intval($locs[0]) : null;
            $location_to_id = isset($locs[2]) ? intval($locs[2]) : null;
            $logistic = isset($whs[1]) ? intval($whs[1]) : null;

            $data['state'] = true;

            if ($data['state'] == true && $wh_from_id == 0) {
                $data['state'] = false;
                $data['msg'] = 'Укажите склад откуда';
            }
            if ($data['state'] == true && $location_from_id == 0) {
                $data['state'] = false;
                $data['msg'] = 'Укажите локацию откуда';
            }
            if ($data['state'] == true && $wh_to_id == 0) {
                $data['state'] = false;
                $data['msg'] = 'Укажите склад куда';
            }
            if ($data['state'] == true && $location_to_id == 0) {
                $data['state'] = false;
                $data['msg'] = 'Укажите локацию куда';
            }
            if ($data['state'] == true && $logistic == 0) {
                $data['state'] = false;
                $data['msg'] = 'Укажите логистику';
            }
            if ($data['state'] == true && $location_to_id == $location_from_id) {
                $data['state'] = false;
                $data['msg'] = 'Локация откуда не может совпадать с локацией куда';
            }
            if ($data['state'] == true) {
                $isset = $this->all_configs['db']->query('SELECT b.id FROM {chains_bodies} as b, {chains_headers} as h
                    WHERE b.chain_id=h.id AND b.number=?i AND b.wh_id=?i AND b.location_id=?i AND b.type=?i AND h.avail=?i',
                    array(1, $wh_from_id, $location_from_id, $this->all_configs['chains']->chain_warehouse, 1))->el();
                if ($isset) {
                    $data['state'] = false;
                    $data['msg'] = 'Такая локация уже существует';
                }
            }
            if ($data['state'] == true) {
                $chain_id = $this->all_configs['db']->query('INSERT INTO {chains_headers} (user_id, avail) VALUES (?i, ?i)',
                    array($user_id, 1), 'id');

                if ($chain_id) {
                    $this->all_configs['db']->query(
                        'INSERT INTO {chains_bodies} (chain_id, `number`, wh_id, location_id, `type`) VALUES (?i, ?i, ?i, ?i, ?i)',
                        array($chain_id, 1, $wh_from_id, $location_from_id, $this->all_configs['chains']->chain_warehouse));
                    $this->all_configs['db']->query(
                        'INSERT INTO {chains_bodies} (chain_id, `number`, wh_id, location_id, `type`) VALUES (?i, ?i, ?i, ?n, ?i)',
                        array($chain_id, 2, $logistic, null, $this->all_configs['chains']->chain_logistic));
                    $this->all_configs['db']->query(
                        'INSERT INTO {chains_bodies} (chain_id, `number`, wh_id, location_id, `type`) VALUES (?i, ?i, ?i, ?i, ?i)',
                        array($chain_id, 3, $wh_to_id, $location_to_id, $this->all_configs['chains']->chain_warehouse));
                }
            }
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }
}