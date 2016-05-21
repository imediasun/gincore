<?php

// настройки
$modulename[160] = 'langs';
$modulemenu[160] = l('langs_modulemenu');  //карта сайта

$moduleactive[160] = !$ifauth['is_2'];

class langs
{

    protected $all_configs;
    private $lang;
    private $def_lang;
    private $langs;

    function __construct($all_configs, $lang, $def_lang, $langs)
    {
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->all_configs = $all_configs;

        global $input_html, $ifauth;

        if ($ifauth['is_1']) {
            return false;
        }

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }


        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    private function genmenu()
    {
        return '';
    }

    private function gencontent()
    {

        $out = '';

        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $value = isset($_POST['value']) ? $_POST['value'] : '';
        $text_direction = isset($_POST['text_direction']) ? $_POST['text_direction'] : '';

        if (isset($_POST['lang'])) {
            foreach ($_POST['lang'] as $id => $values) {
                $default = $_POST['default'] == $id ? 1 : 0;
                $state = isset($values['state']) ? 1 : 0;
                $this->all_configs['db']->query(
                    "UPDATE {langs} "
                    . "SET state = ?i, `default` = ?i, name = ?, url = ?, prio = ?i "
                    . "WHERE id = ?i", array($state, $default, $values['name'], $values['url'], $values['prio'], $id));
            }
            header('Location: ' . $this->all_configs['prefix'] . 'langs');
            exit;
        }

        // добавить новый
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'add_new') {
            if ($name) {
                $this->all_configs['db']->query("INSERT INTO {langs}(name, url, state, `default`, text_direction) 
                            VALUES(?, ?, 0, 0, ?)", array($name, $url, $text_direction));
            }
            header('Location: ' . $this->all_configs['prefix'] . 'langs');
            exit;
        }

        $langs = $this->all_configs['db']->query("SELECT * FROM {langs}")->assoc();

        $html = '';

        foreach ($langs as $lang) {
            $html .= '
                <tr>
                    <td><input class="form-control" type="text" name="lang[' . $lang['id'] . '][name]" value="' . $lang['name'] . '"></td>
                    <td><input class="form-control" type="text" name="lang[' . $lang['id'] . '][url]" value="' . $lang['url'] . '"></td>
                    <td><input class="form-control" type="text" name="lang[' . $lang['id'] . '][prio]" value="' . $lang['prio'] . '"></td>
                    <td align="center"><input type="checkbox" name="lang[' . $lang['id'] . '][state]" ' . ($lang['state'] ? 'checked="checked"' : '') . '></td>
                    <td align="center"><input type="radio" name="default" value="' . $lang['id'] . '" 
                            ' . ($lang['default'] ? 'checked="checked"' : '') . '
                                ' . (!$lang['state'] ? 'disabled="disabled"' : '') . '>
                                </td>
                </tr>
            ';
        }

        $out .= '
            <h3>' . l('Управелние городами') . '</h3>
            <form action="' . $this->all_configs['prefix'] . 'langs/save" method="post">
                <table class="table table-hover table-bordered" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th>' . l('Язык') . '</th>
                            <th>url</th>
                            <th>' . l('приоритет') . '</th>
                            <th>' . l('Включен') . '</th>
                            <th>' . l('По умолчанию') . '</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $html . '
                    </tbody>
                </table>
                <input type="submit" value="' . l('Сохранить') . '" class="btn btn-primary">
            </form>
            
            <br>
            <h3>' . l('Добавить новый') . ':</h3>
            <br>
            <form style="max-width:300px" action="' . $this->all_configs['prefix'] . 'langs/add_new" method="post">
                <div class="form-group">
                    <label>' . l('Название города') . ':</label>
                    <input type="text" name="name" class="form-control">
                </div>
                <div class="form-group">
                    <label>url:</label>
                    <input type="text" class="form-control" name="url">
                </div>
                <input type="submit" value="' . l('Добавить') . '" class="btn btn-primary">
            </form>
        ';


        return $out;
    }

    private function ajax()
    {

        $data = array(
            'state' => false
        );

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

}

