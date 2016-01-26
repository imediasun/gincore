<?php

/**
 * Класс для вывода 
 * данных в ексель-формате
 * 
 * принимает массив данных
 * формирует из них xls-документ
 * предоставляет для скачивания либо сохраняет файл на сервере
 * 
 * используется в модуле orders Заказы -- Заказы поставщика
 * для формирования заказов постащика для скачивания
 * 
 */

/**
 * TODO
 * 
 * 1. выбор шаблона документа для формирования данных
 * 2. Шаблоны документов беря за основу generateXLS();
 * 3. Формирование шапки: определиться с переменными
 * 
 */

class ExcelTemplater {
    
    public $page; // PHPExcel object
    public $header_rows; // number of header rows before self table

    // configs sent by module that calls this class
    function __construct($configs)
    {
        global $sitepath;
        require_once $sitepath . 'manage/classes/PHPExcel.php'; // Подключаем библиотеку PHPExcel
        $this->configs = $configs;
        
        $this->setCache('1');
        $this->header_rows = 0;
    }

    /**
     * Setup cache size for PHPExcel
     * 
     * @param type $size
     */
    function setCache($size) {
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( 'memoryCacheSize ' => $size.'MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
    }
    
    function setDocument() {
        $this->phpexcel = new PHPExcel(); // Создаём объект PHPExcel
        /* Каждый раз делаем активной 1-ю страницу и получаем её, потом записываем в неё данные */
        $this->page = $this->phpexcel->setActiveSheetIndex(0); // Делаем активной первую страницу и получаем её
        $page = $this->page;
        //Ориентация страницы и  размер листа
        $page->getPageSetup()
                        ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $page->getPageSetup()
                        ->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        //Поля документа
        $page->getPageMargins()->setTop(0.5);
        $page->getPageMargins()->setRight(0.5);
        $page->getPageMargins()->setLeft(0.5);
        $page->getPageMargins()->setBottom(0.5);
    }
    
    function setDocumentStyle() {
        //Настройки шрифта
        $this->phpexcel->getDefaultStyle()->getFont()->setName('Arial');
        $this->phpexcel->getDefaultStyle()->getFont()->setSize(10);
        //$phpexcel->getDefaultStyle()->getAlignment()->setWrapText(true);
    }

    function setTitle($title='Лист 1') {
        //Название листа
        $this->page->setTitle($title);
    }
    
    function setColumnsWidth () {
        $page = $this->page;
//        total width 130
        $page->getColumnDimension('A')->setWidth(8);
        $page->getColumnDimension('B')->setWidth(20);
        $page->getColumnDimension('C')->setWidth(40);
        $page->getColumnDimension('D')->setWidth(8);
        $page->getColumnDimension('E')->setWidth(8);
        $page->getColumnDimension('F')->setWidth(12);
        $page->getColumnDimension('G')->setWidth(20);
        $page->getColumnDimension('H')->setWidth(20);
        $page->getColumnDimension('I')->setWidth(8);
        $page->getColumnDimension('J')->setWidth(60);
    }
    
    function genHeader($rows = 6) {
        $this->header_rows = $rows;
        $this->styleHeader();
        $this->contentHeader();
    }
    
    function styleHeader() {
        $page = $this->page;
         
//        $page->mergeCells('A1:D1');
        $page->getRowDimension('1')->setRowHeight(25);
        //Устанавливает формат данных в ячейке - дата
        $page->getStyle('D3')->getNumberFormat()
        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14);

        //Стили для верхней надписи строка 1
        $style_header = array(
                //Шрифт
                'font'=>array(
                        'bold' => true,
                        'italic' => true,
                        'name' => 'Arial',
                        'size' => 14,
                        'color'=>array(
                                'rgb' => '5555AA'
                        )
                ),
        //Выравнивание
                'alignment' => array(
                        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                )
        );
        $page->getStyle('B3')->applyFromArray($style_header);

        $style_name = array(
                'font'=>array(
                        'italic' => false,
                        'size' => 16,
                        'color'=>array(
                                'rgb' => '85B972'
                        )
                ),
            //Выравнивание
                'alignment' => array(
                        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                )
        );
        $page->getStyle('B1')->applyFromArray($style_name);

        //date
        $style_date = array(
                //Шрифт
                'font'=>array(
                        'bold' => true,
                ),
        //Выравнивание
                'alignment' => array(
                        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                )
        );

        $page->getStyle('B5')->applyFromArray($style_date);


        $style_contacts = array(
                //Шрифт
                'font'=>array(
                        'bold' => true,
                        'italic'=>true,
                ),
        //Выравнивание
                'alignment' => array(
                        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                )
        );
        $page->getStyle('D1:D6')->applyFromArray($style_contacts);

    }
    
    function contentHeader($document_title = '', $company_name = '',
           $city = '', $address = '', $phone_1= '', $phone_2 = '', 
           $site_name = '', $adds = '' ) {
        
        //Записываем данные в ячейки
        if(!$company_name)
            $company_name = $this->configs['shop-name'];
        if(!$site_name)
            $site_name = $_SERVER['SERVER_NAME'];
        $site_name = 'http//:'.$site_name;

        $page = $this->page;
        $page->setCellValue('B1',$company_name);
        $page->setCellValue('B3',$document_title );
        $date = date('d/m/Y');
        $page->setCellValue('B5',$date);
        $page->setCellValue('D1',$city);
        $page->setCellValue('D2',$address);
        $page->setCellValue('D3',$phone_1);
        $page->setCellValue('D4',$phone_2);
        $page->setCellValue('D5',$site_name);
        $page->setCellValue('D6',$adds);
    }
    
    function genColumnsTilesStyles() {
        $page = $this->page;

        //Стили для шапочки прайс-листа
        $style_hprice = array(
                //выравнивание
                'alignment' => array(
                        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
                ),
        //заполнение цветом
                'fill' => array(
                        'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                        'color'=>array(
                                'rgb' => '8F8F8F'
                        )
                ),
        //Шрифт
                'font'=>array(
                        'bold' => true,
                ),
        //рамки
                'borders' => array(
                        'allborders' => array(
                                    'style'=>PHPExcel_Style_Border::BORDER_MEDIUM
                                    )
                )

        );
        
        $start_row = $this->header_rows + 1;
        $page->getStyle('A'.$start_row.':J'.$start_row)->applyFromArray($style_hprice);
        $page->getStyle('A'.$start_row.':J'.$start_row)->getAlignment()->setWrapText(true);
        $page->getRowDimension($start_row)->setRowHeight(40);
    }
    
    function genColumnsTiles($array = array()) {
        $this->page->fromArray( $array, NULL, 'A'.($this->header_rows + 1) );
    }
    
    function setColumnsListStyles() {
        $page = $this->page;
        //стили для данных в таблице прайс-листа
        // for price format 1,234.00
        $page->getStyle('E:F')->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//        $page->getStyle('J')->getAlignment()->setWrapText(true);
        $page->getStyle('A')->
                applyFromArray(array('alignment' => array(
                                    'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_LEFT,
                                    ),
                               )
                        );
    }
    
    function setColumnsList($array = array(), $row_process = '') {
        if (empty($array))
            return false;
        if (!$row_process){
            $this->page->fromArray( $array, NULL, 'A'.($this->header_rows + 2) );
            return true;
        } else {
            $method = $row_process;
            if( !$this->check_allowed_methods($method))
                return false;
//                return 'Метод запрещён';
            // check if there is method in class
            if (!method_exists($this,$method))
                return false;
//                return 'Метод в классе отсутствует.';
            // call method
            $this->$method($array);
            return true;
        }
    }
    
    private function check_allowed_methods($method){
        $allowed_methods = array('processSuppliersOrders');
        if(!in_array($method, $allowed_methods))
            return false;
        return true;               
    }
    
    private function processSuppliersOrders($array){
        $i = $this->header_rows + 1;
        $style = $this->getFillColorStyles();
        $date = time();
        foreach($array as $el) {
            $i++;
            $color = 'default';
            if(strtotime($el['date_wait'])<$date)
                    $color = 'red';
            if($el['date_come'])
                    $color = 'green';
//            set styles
//            write data
            
            $this->page->getStyle('A'.$i.':J'.$i)->applyFromArray($style[$color]+$style['border']);
//          стиль строки переписывает все стили колонок
//            поєтому перед загрузкой данніх необходимо задать форматы данных в ячейкахж
            $this->page->getStyle('E'.$i.':F'.$i)->getNumberFormat()->
                    setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $this->page->getStyle('A'.$i)->
                applyFromArray(array('alignment' => array(
                                    'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_LEFT,
                                    ),
                               )
                        );
            $this->page->fromArray( $el, NULL, 'A'.$i );
        }
    }
    
    private function getFillColorStyles(){
        $style['default'] = array(
        //заполнение цветом
                'fill' => array(
                        'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                        'color'=>array(
                            'rgb' => 'FFFFFF'
                            )
                    )
        );
        $style['red'] = array(
        //заполнение цветом
                'fill' => array(
                        'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                        'color'=>array(
                                'rgb' => 'F8C5C5')
                    )
        );
        $style['green'] = array(
        //заполнение цветом
                'fill' => array(
                        'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
                        'color'=>array(
                                'rgb' => 'CCFFCC')
                    )
        );
        $style['border'] = array(
        //рамки
            'borders' => array(
                'allborders' => array(
                    'style'=>PHPExcel_Style_Border::BORDER_THIN
                    )
            )
        );
        
        return $style;
    }

    /**
     * Сборщик документа
     * 
     * @param array $database - данные для таблицы
     * @param array $columns_titles - заголовки колонок (для данных)
     * @param string $title - название листа
     * @param string $row_process - метод класса для обработки данных
     */
    function generateXLS($database = array(), $columns_titles = array(), $title = '', $row_process = ''){
/*
        $title = 'Название страницы';
        $columns_titles = array('id', 'Цена', 'Количество', 'Дата создания', 'Дата приходования', 'Товар', 'Поставщик', 'Автор', 'Оприходован', 'Примечание');
        $database = array( array( 'Tree',  'Height', 'Age', 'Yield', 'Profit' ),
                           array( 'Apple',  18,       20,    14,      105.00  ),
                           array( 'Pear',   12,       12,    10,       96.00  ),
                           array( 'Cherry', 13,       14,     9,      105.00  ),
                           array( 'Apple',  14,       15,    10,       75.00  ),
                           array( 'Pear',    9,        8,     8,       76.80  ),
                           array( 'Apple',   8,        9,     6,       45.00  ),
                         );
*/
//         first get styles
//         second get content
         $this->setDocument();
         $this->setDocumentStyle();
         $this->setTitle($title);
         $this->setColumnsWidth();
//         $this->genHeader();
         $this->genColumnsTilesStyles();
         $this->genColumnsTiles($columns_titles);
         $this->setColumnsListStyles();
         $this->setColumnsList($database, $row_process);
         
    }
    
    /**
     * Choose output for Excel content
     * 
     * @param type $file - path to save OR filename to download
     * @param type $save - true = save, false = download
     */
    function output($file, $save = false){
        
        if ($save) {
            $objWriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
            /* Записываем в файл */
            $objWriter->save($file);
        } else {
            /* выдать на скачивание */
            // Redirect output to a client’s web browser (Excel5)
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$file.'"');
            header('Cache-Control: max-age=0');
            $this->objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
            //$objWriter->save('../files/user_price/test.xls');
            $this->objWriter->save('php://output');
        }
    }


}