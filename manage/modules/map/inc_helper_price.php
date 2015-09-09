<?php

class Inc_helper_map_price
{

    
    public static function show_tables_by_type($map_id, $type, $add_row = true)
    {
        GLOBAL $all_configs, $langs;
        $out = $rows = '';
        
        $sql = "SELECT * from {map_prices} WHERE map_id=?i and table_type=?i ORDER BY prio,id ASC";
        $price_table = $all_configs['db']->query($sql, array($map_id, $type))->assoc('id');
        if ($type == 1) {
            $tbl = '';
            $max_prio = -1;
            if ($price_table){
                $translates0 = get_few_translates(
                    'map_prices', 'row_id', $all_configs['db']->makeQuery("row_id IN(?q)", array(implode(',', array_keys($price_table))))
                );
                foreach ($price_table as $row) {
                    $row = translates_for_page($langs['lang'], $langs['def_lang'], $translates0[$row['id']], $row, true);
                    $rows .= '<tr'.($row['hidden'] ? ' style="background:#F0F0F0"' : '').'>'
                            . '<td>'.$row['id'].'</td>'
                            . '<td class="editable" data-name="name" data-pk="'.$row['id'].'" data-type="textarea">' . $row['name'] . '</td>'
                            . '<td class="text-center editable" data-name="price_mark" data-pk="'.$row['id'].'">' . $row['price_mark'] . '</td>'
                            . '<td class="text-center editable" data-name="price" data-pk="'.$row['id'].'">' . $row['price'] . '</td>'
                            . '<td class="text-center editable" data-name="time_required" data-pk="'.$row['id'].'">' . htmlspecialchars($row['time_required']) . '</td>'
                            . '<td class="text-center editable" data-name="prio" data-pk="'.$row['id'].'">' . $row['prio'] . '</td>'
                            . '<td class="text-center editable" data-name="hidden" data-pk="'.$row['id'].'">' . $row['hidden'] . '</td>'
                            . '<td><a onclick="return confirm(\'Удалить?\');" class="icon-remove" href="del-from-prices-table/' . $row['id'] . '/'.$map_id.'"></a></td>'
                            . '</tr>';
                    if($max_prio < $row['prio']){
                        $max_prio = $row['prio'];
                    }
                }
            }else{
                $tbl = '
                    Скопировать с другой страницы, id: <br>
                    <div class="input-append">
                        <input type="text" name="pricing_copy" class="input-small"> 
                        <button class="btn copy_pricing_table" type="button" data-type="1" data-current_id="'.$map_id.'">Скопировать</button>
                    </div>
                ';
            }
            if($add_row){
                $rows .= '<tr>'
                    . '<td>Нов.</td>'
                    . '<td><input class="form-control" type="text" id="pr1-name"></td>'
                    . '<td><input class="form-control" type="text"  id="pr1-pricemark"></td>'
                    . '<td><input class="form-control" type="text"  id="pr1-price"></td>'
                    . '<td><input class="form-control" type="text"  id="pr1-timerequired"></td>'
                    . '<td><input class="form-control" type="text"  id="pr1-prio" value="'.($max_prio+1).'"></td>'
                    . '<td><button class="btn btn-default btn-sm" type="button" id="pr1-btn" data-map="'.$map_id.'">Доб.</button></td>'
                    . '</tr>';
            }
                $tbl .= '<div class="table-responsive"><table class="table table-hover tbl-prices table-condensed" id="tbl-price1">'
                    . '<thead>'
                    . '<tr>'
                    . '<td>id</td>'
                    . '<td>Вид предоставляемых работ</td>'
                    . '<td class="text-center">От</td>'
                    . '<td class="text-center">Цена, грн.</td>'
                    . '<td class="text-center">Время, мин</td>'
                    . '<td class="text-center">Прио.</td>'
                    . '<td class="text-center">Скрыть</td>'
                    . '<td></td>'
                    . '</tr></thead><tbody>'
                    . $rows
                    . '</tbody></table></div>';
            $out = $tbl;
        }
        if ($type == 2) {
            $tbl = '';
            $max_prio = -1;
            if ($price_table){
                $translates0 = get_few_translates(
                    'map_prices', 'row_id', $all_configs['db']->makeQuery("row_id IN(?q)", array(implode(',', array_keys($price_table))))
                );
                foreach ($price_table as $row) {
                    $row = translates_for_page($langs['lang'], $langs['def_lang'], $translates0[$row['id']], $row, true);
                    $rows .= '<tr'.($row['hidden'] ? ' style="background:#F0F0F0"' : '').'>'
                        . '<td>' . $row['id'] . '</td>'
                        . '<td class="editable" data-name="name" data-pk="'.$row['id'].'" data-type="textarea">' . $row['name'] . '</td>'
                        . '<td class="text-center editable" data-name="price_copy_mark" data-pk="'.$row['id'].'">' . $row['price_copy_mark'] . '</td>'
                        . '<td class="text-center editable" data-name="price_copy" data-pk="'.$row['id'].'">' . $row['price_copy'] . '</td>'
                        . '<td class="text-center editable" data-name="price_mark" data-pk="'.$row['id'].'">' . $row['price_mark'] . '</td>'
                        . '<td class="text-center editable" data-name="price" data-pk="'.$row['id'].'">' . $row['price'] . '</td>'
                        . '<td class="text-center editable" data-name="time_required" data-pk="'.$row['id'].'">' . htmlspecialchars($row['time_required']) . '</td>'
                        . '<td class="text-center editable" data-name="prio" data-pk="'.$row['id'].'">' . $row['prio'] . '</td>'
                        . '<td class="text-center editable" data-name="hidden" data-pk="'.$row['id'].'">' . $row['hidden'] . '</td>'
                        . '<td><a onclick="return confirm(\'Удалить?\');" class="icon-remove" href="del-from-prices-table/' . $row['id'] . '/'.$map_id.'"></a></td>'
                        . '</tr>';
                    if($max_prio < $row['prio']){
                        $max_prio = $row['prio'];
                    }
                }
            }else{
                $tbl = '
                    Скопировать с другой страницы, id: <br>
                    <div class="input-append">
                        <input type="text" name="pricing_copy" class="input-small"> 
                        <button class="btn copy_pricing_table" type="button" data-type="2" data-current_id="'.$map_id.'">Скопировать</button>
                    </div>
                ';
            }
            if($add_row){
                $rows .= '<tr>'
                        . '<td>Нов.</td>'
                        . '<td><input class="form-control" type="text" id="pr2-name"></td>'
                        . '<td><input class="form-control" type="text" id="pr2-pricecopymark"></td>'
                        . '<td><input class="form-control" type="text"  id="pr2-pricecopy"></td>'
                        . '<td><input class="form-control" type="text"  id="pr2-pricemark"></td>'
                        . '<td><input class="form-control" type="text"  id="pr2-price"></td>'
                        . '<td><input class="form-control" type="text"  id="pr2-timerequired"></td>'
                        . '<td><input class="form-control" type="text"  id="pr2-prio" value="'.($max_prio+1).'"></td>'
                        . '<td><button class="btn btn-default dtn-sm" type="button" id="pr2-btn" data-map="'.$map_id.'">Доб.</button></td>'
                        . '</tr>';
            }    
                $tbl .= '<div class="table-responsive"><table class="table-condensed table table-hover tbl-prices" id="tbl-price2"><thead><tr>'
                    . '<td>id</td>'
                    . '<td>Вид предоставляемых работ</td>'
                    . '<td class="text-center">От</td>'
                    . '<td class="text-center">Копия, грн.</td>'
                    . '<td class="text-center">От</td>'
                    . '<td class="text-center">Оригинал, грн.</td>'
                    . '<td class="text-center">Время, часов</td>'
                    . '<td class="text-center">Прио.</td>'
                    . '<td></td>'
                    . '</tr></thead><tbody>'
                     . $rows .
                    '</tbody></table></div>';
                $out = $tbl;
            }
//        if (!$price_table) {
//            $out = 'нет записей';
//        }
        
        return $out;
    }            
            
            
    public static function download_send_headers($filename)
    {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
    }

    public static function array2csv(array $prices)
    {
        if (count($prices) == 0) {
            return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        //fprintf($df, chr(0xEF) . chr(0xBB) . chr(0xBF)); //correct UTF-8 encoding in CSV
        fputcsv($df, array_keys(reset($prices)), ';', '"');
        foreach ($prices as $row) {
            fputcsv($df, $row, ';', '"');
        }
        fclose($df);
        return ob_get_clean();
    }

}
