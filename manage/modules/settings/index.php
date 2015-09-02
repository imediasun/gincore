<?php

include $all_configs['path'].'modules/settings/langs.php';

$lang_arr = array_merge($lang_arr, $settings_lang);

// нужные переводы для шаблона


// настройки
$modulename[] = 'settings';
$modulemenu[] = l('sets_modulemenu');  //карта сайта

$moduleactive[] = !$ifauth['is_2'];

class settings{

    protected $all_configs;

    function __construct($all_configs){
        global $input_html, $ifauth;

        $this->all_configs = &$all_configs;

        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }
        
        if($ifauth['is_2']) return false;

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    private function genmenu(){
        $out = '<h4>'.l('sets_list').' <a style="text-decoration:none" href="'.$this->all_configs['prefix'].'settings/add">+</a></h4>';

        $sqls = $this->all_configs['db']->query("SELECT * FROM {settings} ORDER BY `title`")->assoc();

        $out .= '<ul>';
        foreach($sqls as $pps){
            $out.='<li><a href="'.$this->all_configs['prefix'].'settings/'.$pps['id'].'"'.(isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '').'>'.$pps['title'].'</a></li>';
        }

        $out .= '</ul>';



        return $out;
    }

    private function gencontent(){

        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $value = isset($_POST['value']) ? $_POST['value'] : '';


        if(!isset($this->all_configs['arrequest'][1])){

            $out = l('sets_description');
        }

###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && is_numeric($this->all_configs['arrequest'][1])){
            $pp = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE id = ?i", array($this->all_configs['arrequest'][1]), 'row');
            $out = '<ul>';

            $out = '<h3>«'.$pp['title'].'»</h3><br>';


            if(!isset($this->all_configs['arrequest'][2])){

                $out.='

                <form action="'.$this->all_configs['prefix'].'settings/'.$pp['id'].'/update" method="POST">
                    
                    '.l('sets_param').': <b>'.$pp['name'].'</b><br><br>
                    <form class="form-horizontal">
                      <div class="control-group">
                        <label class="control-label" for="inputParam">'.l('sets_value').':</label>
                        <div class="controls">
                            <textarea id="inputParam" '.($pp['ro'] == '1' ? 'disabled="disabled"' : '').' name="value" rows="5" cols="60">'.$pp['value'].'</textarea>
                        </div>
                      </div>
                      <div class="control-group">
                        <div class="controls">
                          <input type="submit" value="'.l('save').'" class="btn btn-primary">
                        </div>
                      </div>
                    </form>

       


                    ';

            }

            if(isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'update'){

                $sql = $this->all_configs['db']->query("UPDATE {settings} SET value=?
                             WHERE id=?i LIMIT 1", array($value, $this->all_configs['arrequest'][1]), 'ar');

                header('Location: '.$this->all_configs['prefix'].'settings/save/'.$this->all_configs['arrequest'][1]);
                exit;
            }//update
        }
################################################################################

        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'add'){
            if(isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'ok' && isset($_POST['name']) && isset($_POST['value']) && isset($_POST['title'])){
                $sql = $this->all_configs['db']->query("INSERT INTO {settings}(name, value, title, ro) 
                            VALUES(?, ?, ?, ?i)", array($_POST['name'], $_POST['value'], $_POST['title'], isset($_POST['ro']) ? 1 : 0));
                header('Location: '.$this->all_configs['prefix'].'settings');
                exit;
            }else{
                $out = '
                <h3>Добавление нового параметра</h3>
                <form action="'.$this->all_configs['prefix'].'settings/add/ok" method="post">
                    '.l('sets_param').': <input type="text" name="name" value=""><br><br>
                    '.l('sets_value').': <textarea name="value"></textarea><br><br>
                    '.l('name').': <textarea  name="title"></textarea><br><br>
                    '.l('sets_read_only').': <input type="checkbox" name="ro" value="1"><br><br>
                    <input type="submit" value="'.l('save').'" class="btn btn-primary">
                </form>
            ';
            }
        }

################################################################################
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'save'){
            $out = l('sets_update_success').' <a href="'.$this->all_configs['prefix'].'settings/'.$this->all_configs['arrequest'][2].'">'.l('continue').'</a>';
        }
###############################################################################


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

