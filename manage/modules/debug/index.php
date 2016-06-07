<?php

// настройки
$modulename[120] = 'debug';
$modulemenu[120] = l('debug_modulemenu');  //карта сайта

$moduleactive[120] = !$ifauth['is_2'];

class debug{

    protected $all_configs;

    public $debuggers;
    public $debug_title = '';

	/**
	 * debug constructor.
	 * @param $all_configs
     */
	function __construct(&$all_configs)
    {
        global $input_html, $ifauth;

        $this->all_configs = &$all_configs;

        if (!$this->all_configs['oRole']->hasPrivilege('site-administration')) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">' . l('У Вас нет прав') .'</p></div>';
        }

        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }
        
        if($ifauth['is_2']) return false;
        
        $this->debuggers = array(
            array(
                'url' => 'visit-high-price',
                    'title' => l('Повышенные цены для посетителей'),
            ),
            array(
                'url' => 'price_parser',
                'title' => l('Парсер цен со страниц оборудования '),
            ),
            array(
                'url' => 'show_price_tables',
                'title' => l('Показать таблички с ценами'),
            ),
        );
        // доступ к сбросу
        if($this->all_configs['configs']['manage-reset-access']){
            $this->debuggers[] = array(
                'url' => 'reset',
                'title' => l('Сброс'),
            );
        }

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

	/**
	 * @return string
     */
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

	/**
	 * @return mixed|string
     */
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

	/**
	 *
     */
	private function ajax(){

        $data = array(
            'state' => false
        );

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

	/**
	 * @return string
	 * @throws Exception
     */
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
                $out = '<div class="alert alert-success">' .
                    l('Вы успешно обновили информацию') .'<br>'
                    .'Ваших визитов: '.$visitors->get_visit().'<br>'
                    .'<a class="alert-link" href="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/'.$this->all_configs['arrequest'][1].'">' . l('Вернуться к отладчику') .'</a>'
                    .'</div>';
            }
            
            $out .= 
                '<a class="btn btn-success" href="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/'.$this->all_configs['arrequest'][1].'?reset">' . l('Отметить мое посещение как новое') .'</a><br><br>'
                .'<a class="btn btn-success" href="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/'.$this->all_configs['arrequest'][1].'?set">' . l('Отметить мое посещение как повторное') .'</a><br><br>'
                .'<a class="btn btn-success" href="'.$this->all_configs['prefix'].$this->all_configs['arrequest'][0].'/'.$this->all_configs['arrequest'][1].'?reset">' . l('Обычный режим') .'</a><br><br>'
                ;
            

        }

        if ($this->all_configs['arrequest'][1] == 'reset' /*&& $this->all_configs['configs']['manage-reset-access']*/) {

            if(!empty($_GET)) {
                $this->all_configs['db']->query('SET FOREIGN_KEY_CHECKS = 0');
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
				$this->all_configs['db']->query('TRUNCATE TABLE {users_ratings}');
				$this->all_configs['db']->query('TRUNCATE TABLE {cashboxes_users}');
                $this->all_configs['db']->query('DELETE FROM {users_goods_manager} WHERE id <> ?i', array($_SESSION['id']));
                $this->all_configs['db']->query('DELETE FROM {users} WHERE id <> ?i', array($_SESSION['id']));
                
                $this->all_configs['db']->query("UPDATE {settings} SET value = 0 "
                                               ."WHERE name = 'complete-master'");
                $this->all_configs['db']->query("UPDATE {settings} SET value = '' "
                                               ."WHERE name = 'lang'");
                $this->all_configs['db']->query("DELETE FROM {settings} WHERE name='order-fields-hide'");
				$this->all_configs['db']->query("DELETE FROM {settings} WHERE name='site-for-add-rating'");
				$this->all_configs['db']->query("DELETE FROM {settings} WHERE name='order-send-sms-with-client-code'");

                
                $this->all_configs['db']->query('SET FOREIGN_KEY_CHECKS = 1');
                
                // чистим кеш складов
                get_service('wh_helper')->clear_cache();
                
                $out = '<div class="alert alert-success">' .
                    l('Вы успешно обновили информацию') .'<br>'
                    .'<a class="alert-link" href="' . $href . '">' . l('Вернуться к отладчику') .'</a>'
                    .'</div>';
            }
            $out .= '<a class="btn btn-success" href="' . $href . '?reset">' . l('Сброс') .'</a>';
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


	/**
	 * @param      $price_table
	 * @param bool $competitor
	 * @return string
     */
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
			<td>" . l('Вид предоставляемых работ') ."</td>
			<td>" . l('Стоимость') . "</td>
			<td>" . l('Время') ."</td>
		</tr>
	" .$rows.
	"</tbody></table>"; 
	
	return $tbl;
	
	}


	/**
	 * @param      $price_table
	 * @param bool $competitor
	 * @return string
     */
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
<td>" . l('Вид предоставляемых работ') ."</td>
<td>" . l('Копия') . "</td>
<td>" . l('Оригинал') . "</td>
<td>" . l('Время') . "</td>
</tr>
	" .$rows.
	"</tbody></table>"; 
	
	return $tbl;
	
	}


	/**
	 * @param $price
	 * @return string
     */
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

	/**
	 * @param $content
	 * @return array
     */
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