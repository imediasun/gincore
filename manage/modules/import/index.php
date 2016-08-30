<?php

require_once __DIR__ . '/../../Core/Object.php';
require_once __DIR__ . '/../../Core/View.php';

global $all_configs;
$modulename[133] = 'import';
$modulemenu[133] = l('Импорт');
$moduleactive[133] = $all_configs['oRole']->hasPrivilege('edit-users');

class import extends Object
{
    protected $all_configs;
    /** @var View */
    protected $view;
    private $upload_path;
    private $upload_types = array(
        'gincore_items' => array(
            'name' => 'Товары из системы Gincore',
            'handlers' => array(
                'exported' => 'импорт из файла с ранее экспортированными из Gincore товарами',
            )
        ),
        'items' => array(
            'name' => 'Товарная номенклатура',
            'handlers' => array(
                'gincore' => 'из унифицированного формата',
                'vvs' => 'из VVS Склад-офис-магазин',
                'tirika' => 'из базы "Тирика-Магазин"'
            )
        ),
        'orders' => array(
            'name' => 'Заказы', // l() placeholders
            'handlers' => array(
                'gincore' => 'из унифицированного формата',
                'remonline' => 'Remonline'
            )
        ),
        'clients' => array(
            'name' => 'Клиенты',
            'handlers' => array(
                'gincore' => 'из унифицированного формата',
                'remonline' => 'Remonline',
                'onec' => '1C (формат A)'
            )
        ),
        'posting_items' => array(
            'name' => 'Импорт товарных остатков',
            'handlers' => array(
                'xls' => 'из xls файла',
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
        $this->view = new View($all_configs);
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
        return ($this->all_configs['oRole']->hasPrivilege('edit-users'));
    }

    /**
     * @return string
     */
    function gencontent()
    {
        return $this->view->renderFile('import/gencontent');
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
                    mkdir($this->upload_path, 0770);
                }
                $file_info = pathinfo($_FILES['file']['name']);
                $filename = $type . '.' . strtolower($file_info['extension']);
                $target = $this->upload_path . $filename;
                if (file_exists($this->upload_path . $type . '.csv')) {
                    if (!is_dir($this->copy_upload_path)) {
                        mkdir($this->copy_upload_path, 0770);
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
     * @return array
     */
    private function import_import()
    {
        $import = isset($_GET['i']) && isset($this->upload_types[$_GET['i']]) ? $_GET['i'] : '';
        if (isset($_GET['load']) && $_GET['load'] == 'posting_items') {
            return array(
                'html' => $this->view->renderFile('import/import_posting_items', array(
                    'body' => $this->get_import_form('posting_items')
                ))
            );
        }
        return array(
            'html' => $this->view->renderFile('import/import_import', array(
                'selected' => $import,
                'options' => $this->upload_types,
            ))
        );
    }

    /**
     * @param $type
     * @return string
     */
    function get_import_form($type)
    {
        $import = $this->getImportHandler($type, null, null);

        return $this->view->renderFile('import/get_import_form', array(
            'type' => $type,
            'options' => $this->upload_types,
            'handlers' => array_keys($this->upload_types[$type]['handlers']),
            'body' => empty($import) ? '' : $import->getImportForm()
        ));
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
                        $import = $this->getImportHandler($import_type, $source, $handler);
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

        Response::json($data);
    }

    /**
     * @param $import_type
     * @param $source
     * @param $handler
     * @return import_class
     */
    private function getImportHandler($import_type, $source, $handler)
    {
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
            case 'posting_items':
                $import_settings['accepter_as_manager'] = isset($_POST['accepter_as_manager']);
                $import_settings['location'] = isset($_POST['location']) ? $_POST['location'] : 0;
                $import_settings['contractor'] = isset($_POST['contractor']) ? $_POST['contractor'] : 0;
                $import_settings['warehouse'] = isset($_POST['warehouse']) ? $_POST['warehouse'] : 0;
        }
        require $this->all_configs['path'] . 'modules/import/import_helper.php';
        require $this->all_configs['path'] . 'modules/import/import_class.php';
        return new import_class($this->all_configs, $source, $import_type, $handler,
            $import_settings);
    }
}
