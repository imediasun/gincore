<?php

/**
 * Че вообще происходит:
 *
 * шаблоны печатных документов берутся из таблицы core_template_vars
 *   - для рестора в ней шаблоны разделены по городам (есть переключалка городов)
 *   - для жинкора шаблоны тянутся с города kiev (без переключалки по городам)
 *
 * при распаковке новой системы жинкор в core_template_vars город kiev прописываются шаблоны
 * согласно выбранного языка с таблицы core_admin_translates
 *
 * итого: если менять чето в шаблонах, нужно менять в core_template_vars для всех городов - это для рестора
 *                                      плюс менять в core_admin_translates - для распаковщика жинкора
 */

require_once __DIR__ . '/../../Core/Controller.php';

class prints extends Controller
{
    public $editor = false;
    protected $langs;
    protected $cur_lang;
    protected $templateTable;
    protected $act;
    protected $template;

    public function __construct(&$all_configs)
    {
        parent::__construct($all_configs);

        $this->langs = get_langs();

        $this->cur_lang = $this->getRestoreLang();
        $this->templateTable = $this->getRestoreTable();

        $this->act = isset($_GET['act']) ? trim($_GET['act']) : '';
        $this->template = $this->getTemplate($this->act);
    }

    /**
     * @param $act
     * @return AbstractTemplate|null
     */
    public function getTemplate($act)
    {
        if (file_exists(__DIR__ . '/templates/' . $act . '.php')) {
            require_once(__DIR__ . '/templates/' . $act . '.php');
            return new $act($this->all_configs, $this->templateTable, $this->cur_lang);
        }
        return null;
    }

    /**
     * @param array $arrequest
     * @return string
     */
    public function routing(Array $arrequest)
    {
        if (isset($_GET['ajax']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            return $this->ajax();
        }

        // если отправлена форма
        if (count($_POST) > 0) {
            return $this->check_post($_POST);
        }

        return $this->check_get($_GET);
    }

    /**
     * @inheritdoc
     */
    public function ajax()
    {
        $return = array('state' => false, 'msg' => 'Произошла ошибка');

        if ($_GET['ajax'] == 'editor' && isset($_GET['act'])) {
            $save_act = trim($_GET['act']);
            if (in_array($save_act, array(
                    'check',
                    'warranty',
                    'invoice',
                    'act',
                    'invoicing',
                    'waybill',
                    'sale_warranty',
                    'price_list'
                )) && isset($_POST['html'])
            ) {
                // remove empty tags
                $value = preg_replace("/<[^\/>]*>([\s]?)*<\/[^>]*>/", '', trim($_POST['html']));
                $return['state'] = true;
                $var_id = $this->all_configs['db']->query("SELECT id FROM {?q} WHERE var = 'print_template_" . $save_act . "'",
                    array($this->templateTable))->el();
                if (empty($var_id)) {
                    $var_id = $this->all_configs['db']->query("INSERT INTO {?q} (var) VALUES (?)",
                        array($this->templateTable, 'print_template_' . $save_act), 'id');
                }
                $this->all_configs['db']->query("INSERT INTO {?q_strings}(var_id,text,lang) "
                    . "VALUES(?i,?,?) ON DUPLICATE KEY UPDATE text = VALUES(text)",
                    array($this->templateTable, $var_id, $value, $this->cur_lang));
            }
        }
        // загрузка картинки
        if ($_GET['ajax'] == 'upload') {
            $return = array(
                'file' => upload()
            );

        }
        Response::json($return);
    }

    /**
     * @inheritdoc
     */
    public function check_post(Array $post)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function can_show_module()
    {
        return $this->all_configs['oRole']->is_active();
    }

    /**
     * @inheritdoc
     */
    public function gencontent()
    {
        if (empty($this->template)) {
            FlashMessage::set(l('Шаблон документа не найден'), FlashMessage::DANGER);
            Response::redirect($this->all_configs['prefix']);
        }
        try {
            $print_html = $this->template->draw();
            if (empty($print_html)) {
                FlashMessage::set(l('Сгенерирован пустой документ'), FlashMessage::DANGER);
                Response::redirect($this->all_configs['prefix']);
            }
        } catch (ExceptionWithMsg $e) {
            FlashMessage::set($e->getMessage(), FlashMessage::DANGER);
            Response::redirect($this->all_configs['prefix']);
        }
        $print_html = $this->template->add_edit_form($print_html);
        echo $this->view->renderFile('prints/index', array(
            'print_html' => $print_html,
        ));
        exit;
    }

    public function barcode_generate($barcode, $type)
    {
        require_once(__DIR__ . '/../../classes/BCG/BCGFontFile.php');
        require_once(__DIR__ . '/../../classes/BCG/BCGColor.php');
        require_once(__DIR__ . '/../../classes/BCG/BCGDrawing.php');

        $font = new BCGFontFile(__DIR__ . '/../../classes/BCG/font/Arial.ttf', 10);
        $color_black = new BCGColor(0, 0, 0);
        $color_white = new BCGColor(255, 255, 255);


        // Barcode Part
        if ($type == 'sn') {
            require_once(__DIR__ . '/../../classes/BCG/BCGcode128.barcode.php');
            $code = new BCGcode128();

            $code->setScale(1);
            $code->setThickness(35);

        } elseif ($type == 'ean') {
            require_once(__DIR__ . '/../../classes/BCG/BCGean13.barcode.php');
            $code = new BCGean13();

            $code->setScale(1.5);
            $code->setThickness(35);

        } else {
            require_once(__DIR__ . '/../../classes/BCG/BCGcodabar.barcode.php');
            $code = new BCGcodabar();
        }

        $code->setForegroundColor($color_black);
        $code->setBackgroundColor($color_white);
        $code->setFont($font);

        header('Content-Type: image/png');

        try {
            $code->parse($barcode);
            // Drawing Part
            $drawing = new BCGDrawing('', $color_white);
            $drawing->setBarcode($code);
            $drawing->draw();
            $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
        } catch (Exception $e) {
            $im = imagecreate(1, 1);
            $background_color = imagecolorallocate($im, 255, 255, 255);
            imagepng($im);
            imagedestroy($im);
        }
        exit;
    }

    public function upload()
    {
        $filename = '';
        if (isset($_FILES['file']) && trim($_FILES['file']['name'])) {
            $filename = pathinfo($_FILES['file']['name']);
            $ext = $filename['extension'];
            if (in_array($filename['extension'], array('JPG', 'jpg', 'GIF', 'gif', 'PNG', 'png', 'JPEG', 'jpeg'))) {
                $file_hash = substr(md5(microtime()), 0, 15);
                $filename = $file_hash . '.' . $ext;
                $path_to_directory = __DIR__ . "/../../img/upload/";
                $source = $_FILES['file']['tmp_name'];
                $destination = $path_to_directory . $filename;
                if (move_uploaded_file($source, $destination)) {
                    chmod($destination, 0777);
                    $filename = '/img/upload/' . $filename;
                }
            }
        }
        return $filename;
    }

    /**
     * @return string
     */
    public function getRestoreLang()
    {
        return isset($_GET['lang']) ? trim($_GET['lang']) : $this->langs['def_lang'];
    }

    /**
     * @return string
     */
    public function getRestoreTable()
    {
        return 'template_vars';
    }

    /**
     * @param $get
     * @return string|void
     */
    public function check_get($get)
    {
        if (isset($get['barcode']) && isset($get['bartype'])) {
            return $this->barcode_generate($get['barcode'], $get['bartype']);
        }
        return parent::check_get($get);
    }
}
