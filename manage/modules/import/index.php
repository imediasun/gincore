<?php

$modulename[133] = 'import';
$modulemenu[133] = l('Импорт');
$moduleactive[133] = !$ifauth['is_2'];

class import
{
    protected $all_configs;
    private $upload_path;
    private $upload_types = array(
        'items' => array(
            'name' => 'Товары из VVS Склад-офис-магазин',
            'handlers' => array(
                'vvs' => 'VVS items'
            )
        ),
        'orders' => array(
            'name' => 'Заказы', // l() placeholders
            'handlers' => array(
                'remonline' => 'Remonline'
            )
        ),
        'clients' => array(
            'name' => 'Клиенты',
            'handlers' => array(
                'gincore' => 'Gincore',
                'remonline' => 'Remonline',
                'onec' => '1C (формат A)'
            )
        )
    );

    /**
     * import constructor.
     * @param $all_configs
     *
     */
    function __construct(&$all_configs)
    {
        global $input_html;
        $this->all_configs = $all_configs;
        $this->upload_path = $this->all_configs['path'] . 'modules/import/files/';
        $this->copy_upload_path = $this->all_configs['path'] . 'modules/import/files/copy/';
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }
        if ($this->can_show_module() == false) {
            $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">' . l('У Вас не достаточно прав') . '</p></div>';
        } else {
            $input_html['mcontent'] = $this->gencontent();
        }
    }

    /**
     * @return mixed
     */
    function can_show_module()
    {
        return ($this->all_configs['oRole']->hasPrivilege('site-administration'));
    }

    /**
     * @return string
     */
    function gencontent()
    {
        return '
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <li><a class="click_tab default" data-open_tab="import_import" 
                           onclick="click_tab(this, event)" data-toggle="tab" href="#import">
                                ' . l('Импортировать данные') . '</a></li>
                </ul>
                <div class="tab-content">
                    <div id="import" class="content_tab tab-pane"></div>
                </div>
            </div>
        ';
    }

    /**
     * @param $type
     * @return bool|string
     */
    private function upload_file($type)
    {
        $file_uploaded = false;
        $filename = '';
        if (isset($_FILES['file']['name']) && trim($_FILES['file']['name'])) {
            if ($_FILES['file']['type'] != 'application/vnd.ms-excel' &&
                $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
                $_FILES['file']['type'] != 'text/csv'
            ) {
                return false;
            } else {
                if (!is_dir($this->upload_path)) {
                    mkdir($this->upload_path, 0777);
                }
                $file_info = pathinfo($_FILES['file']['name']);
                $filename = $type . '.' . $file_info['extension'];
                $target = $this->upload_path . $filename;
                if (file_exists($this->upload_path . $type . '.csv')) {
                    if (!is_dir($this->copy_upload_path)) {
                        mkdir($this->copy_upload_path, 0777);
                    }
                    if (copy($this->upload_path . $type . '.csv',
                        $this->copy_upload_path . date('Y-m-d_H-i-s') . '_' . $type . '.csv')) {
                        unlink($this->upload_path . $type . '.csv');
                    }
                }
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                    if ($_FILES['file']['type'] == 'application/vnd.ms-excel' ||
                        $_FILES['file']['type'] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ) {
                        $this->convert_xls_to_csv($target, $type);
                    }
                    $file_uploaded = true;
                } else {
                    $filename = '';
                }
            }
        } else {
            return false;
        }
        return $filename;
    }

    /**
     * @param $file
     * @param $type
     */
    private function convert_xls_to_csv($file, $type)
    {
        $path_parts = pathinfo($file);
        if ($path_parts['extension'] == 'xlsx' || $path_parts['extension'] == 'xls') {
            require $this->all_configs['path'] . 'classes/PHPExcel/IOFactory.php';
            $reader = 'Excel5';
            if ($path_parts['extension'] == 'xlsx') {
                $reader = 'Excel2007';
            }
            $objReader = PHPExcel_IOFactory::createReader($reader);
//            $objReader->setReadDataOnly(true);
            @$objPHPExcel = $objReader->load($file);
            $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
            $writer->save($this->upload_path . $type . '.csv');
            // удаляем excel
            unlink($file);
        }
    }

    /**
     * @param string $selected
     * @return string
     */
    private function gen_types_select_options($selected = '')
    {
        $types = '';
        foreach ($this->upload_types as $k => $v) {
            $types .= '<option' . ($selected == $k ? ' selected' : '') . ' value="' . $k . '">' . l($v['name']) . '</option>';
        }
        return '<option value="">' . l('Выберите') . '</option>' . $types;
    }

    /**
     * @param        $type
     * @param string $selected
     * @return string
     */
    private function gen_handlers_select_options($type, $selected = '')
    {
        $types = '';
        foreach ($this->upload_types[$type]['handlers'] as $k => $v) {
            $types .= '<option' . ($selected == $k ? ' selected' : '') . ' value="' . $k . '">' . l($v) . '</option>';
        }
        return '<option>' . l('Выберите') . '</option>' . $types;
    }

    /**
     * @todo not used???
     *
     * @return array
     */
    private function import_import()
    {
        $import = isset($_GET['i']) && isset($this->upload_types[$_GET['i']]) ? $_GET['i'] : '';

        $html = '
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <form id="import_form" method="post">
                            <div class="form-group">
                                <label>' . l('Тип импорта') . '</label>
                                <select class="form-control" name="import_type" id="import_type">
                                    ' . $this->gen_types_select_options($import) . '
                                </select>
                            </div>
                            <div id="import_form_part">' . $this->get_import_form($import) . '</div>
                            <div class="form-group">
                                <input type="file" name="file">
                            </div>
                            <div class="form-group">
                                <button class="btn btn-success" type="button" onclick="start_import(this)">' . l('Запустить') . '</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row row-15" id="upload_messages"></div>
            </div>
        ';

        return array(
            'html' => $html
        );
    }

    /**
     * @param $type
     * @return string
     */
    function get_import_form($type)
    {
        $form = '';
        if ($type) {
            $form = '
                <div class="form-group">
                    <label>' . l('Провайдер') . '</label>
                    <select class="form-control" name="handler">
                        ' . $this->gen_handlers_select_options($type) . '
                    </select>
                </div>
            ';
        }
        switch ($type) {
            case 'items':
                $form .= '
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="accepter_as_manager" value="1">
                                ' . l('назначить приемщика менеджером, если последний не указан') . '
                            </label>
                        </div>
                    </div>
                ';
                break;
            case 'orders':
                $form .= '
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input' . ($this->has_orders() ? ' disabled' : '') . ' type="checkbox" name="clear_categories" value="1">
                                ' . l('очистить категории (и товары) и заменить категориями с импорта') . '
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="accepter_as_manager" value="1">
                                ' . l('назначить приемщика менеджером, если последний не указан') . '
                            </label>
                        </div>
                    </div>
                ';
                break;
        }
        return $form;
    }

    /**
     * @return mixed
     */
    private function has_orders()
    {
        return db()->query("SELECT count(*) FROM {orders}")->el();
    }

    /**
     * @return array
     */
    private function import()
    {
        $import_type = isset($_POST['import_type']) ? trim($_POST['import_type']) : '';
        $handler = isset($_POST['handler']) ? trim($_POST['handler']) : '';

        if (isset($this->upload_types[$import_type])) {
            if (isset($this->upload_types[$import_type]['handlers'][$handler])) {
                $upload_file = $this->upload_file($import_type);
                if ($upload_file === false) {
                    $data['state'] = false;
                    $data['message'] = l('Не выбран файл импорта или он имеет неверный формат. Только Excel или CSV файлы');
                } else {
                    $source = $this->upload_path . $import_type . '.csv';
                    if (file_exists($source)) {
                        $import_settings = array();
                        switch ($import_type) {
                            case 'items':
                                $import_settings['accepter_as_manager'] = isset($_POST['accepter_as_manager']);
                                break;
                            case 'orders':
                                if (!$this->has_orders()) { // если есть заказы в системе то низя
                                    $import_settings['clear_categories'] = isset($_POST['clear_categories']);
                                }
                                $import_settings['accepter_as_manager'] = isset($_POST['accepter_as_manager']);
                                break;
                        }
                        require $this->all_configs['path'] . 'modules/import/import_helper.php';
                        require $this->all_configs['path'] . 'modules/import/import_class.php';
                        $import = new import_class($this->all_configs, $source, $import_type, $handler,
                            $import_settings);
                        $data = $import->run();
                    } else {
                        $data['state'] = false;
                        $data['message'] = l('Не найден файл импорта. Сначала загрузите файл.');
                    }
                }
            } else {
                $data['state'] = false;
                $data['message'] = l('Не указан или не найден провайдер');
            }
        } else {
            $data['state'] = false;
            $data['message'] = l('Не указан или не найден тип импорта');
        }
        if (!$data['state']) {
            $data['message'] = '
                <div class="alert alert-danger alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  ' . $data['message'] . '
                </div>
            ';
        }
        return $data;
    }

    /**
     *
     */
    function ajax()
    {
        $data = array(
            'state' => false
        );
        // проверка доступа
        if ($this->can_show_module() == false) {
            $data['message'] = 'Нет прав';
        }

        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $act = isset($_GET['act']) ? $_GET['act'] : '';

        switch ($act) {
            case 'get_form':
                $type = isset($_POST['type']) ? $_POST['type'] : '';
                $data['state'] = true;
                $data['form'] = $this->get_import_form($type);
                break;
            case 'upload':
                $data = $this->upload();
                break;
            case 'import':
                $data = $this->import();
                break;
            case 'tab-load':
                // грузим табу
                if (!empty($_POST['tab'])) {
                    if (method_exists($this, $_POST['tab'])) {
                        $function = call_user_func_array(
                            array($this, $_POST['tab']),
                            array(
                                (isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'], 'UTF-8')) > 0)
                                    ? trim($_POST['hashs']) : null
                            )
                        );
                        $data['state'] = true;
                        $data['html'] = $function['html'];
                        if (isset($function['functions'])) {
                            $data['functions'] = $function['functions'];
                        }
                    } else {
                        $data['message'] = l('Не найдено');
                    }
                }
                break;
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }
}
