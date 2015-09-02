<?php

include $all_configs['path'].'modules/langs/langs.php';

$lang_arr = array_merge($lang_arr, $langs_lang);

// нужные переводы для шаблона


// настройки
$modulename[] = 'langs';
$modulemenu[] = l('langs_modulemenu');  //карта сайта

$moduleactive[] = !$ifauth['is_2'];

class langs{

    protected $all_configs;
    private $lang;
    private $def_lang;
    private $langs;
    function __construct($all_configs, $lang, $def_lang, $langs){
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->all_configs = $all_configs;
        
        global $input_html, $ifauth;

        if($ifauth['is_1']) return false;
        
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }
        

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    private function genmenu(){
        $out = '';

        return $out;
    }

    private function gencontent(){

        $out = '';
        
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $value = isset($_POST['value']) ? $_POST['value'] : '';
        $text_direction = isset($_POST['text_direction']) ? $_POST['text_direction'] : '';

        if(isset($_POST['lang'])){
            foreach($_POST['lang'] as $id => $values){
                $default = $_POST['default'] == $id ? 1 : 0;
                $state = isset($values['state']) ? 1 : 0;
                $this->all_configs['db']->query(
                            "UPDATE {langs} "
                           ."SET state = ?i, `default` = ?i, name = ?, url = ?, prio = ?i "
                           ."WHERE id = ?i", array($state, $default, $values['name'], $values['url'], $values['prio'], $id));
            }
            header('Location: '.$this->all_configs['prefix'].'langs');
            exit;
        }
        
        // добавить новый
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'add_new'){
            if($name){
                $this->all_configs['db']->query("INSERT INTO {langs}(name, url, state, `default`, text_direction) 
                            VALUES(?, ?, 0, 0, ?)", array($name, $url, $text_direction));
            }
            header('Location: '.$this->all_configs['prefix'].'langs');
            exit;
        }
        
        $langs = $this->all_configs['db']->query("SELECT * FROM {langs}")->assoc();
            
        $html = '';
        
        foreach($langs as $lang){
            $html .= '
                <tr>
                    <td><input type="text" name="lang['.$lang['id'].'][name]" value="'.$lang['name'].'"></td>
                    <td><input type="text" name="lang['.$lang['id'].'][url]" value="'.$lang['url'].'"></td>
                    <td><input type="text" name="lang['.$lang['id'].'][prio]" value="'.$lang['prio'].'"></td>
                    <td align="center"><input type="checkbox" name="lang['.$lang['id'].'][state]" '.($lang['state'] ? 'checked="checked"' : '').'></td>
                    <td align="center"><input type="radio" name="default" value="'.$lang['id'].'" 
                            '.($lang['default'] ? 'checked="checked"' : '').'
                                '.(!$lang['state'] ? 'disabled="disabled"' : '').'>
                                </td>
                </tr>
            ';
        }
        
        $out.='
            <h3>Управелние языками</h3>
            <br>
            
            <form action="'.$this->all_configs['prefix'].'langs/save" method="post">
                <table class="table table-hover table-bordered" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th>Язык</th>
                            <th>url</th>
                            <th>приоритет</th>
                            <th>Включен</th>
                            <th>По умолчанию</th>
                        </tr>
                    </thead>
                    <tbody>
                        '.$html.'
                    </tbody>
                </table>
                <input type="submit" value="Сохранить" class="btn btn-primary">
            </form>
            
            <br>
            <br>
            <strong>Добавить новый:</strong>
            <br>
            <br>
            <form action="'.$this->all_configs['prefix'].'langs/add_new" method="post">
                Язык:<br>
                <input type="text" name="name"> <br>
                url:<br>
                <input type="text" name="url"> <br>
                <input type="submit" value="Добавить" class="btn btn-primary">
            </form>
        ';


        return $out;
    }

    private function ajax(){

        $data = array(
            'state' => false
        );

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

}

?>
