<?php

// выбор языка системы при регистрации
// распаковка базы данных и конфига, по заданному языку

class setup{

    protected $all_configs;
    private $lang;
    private $def_lang;
    private $langs;

    function __construct($all_configs, $lang, $def_lang, $langs){
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->all_configs = $all_configs;
        
        if(!empty($_GET['set_lang'])){
            $this->set_lang($_GET['set_lang']);
        }
        
        $this->gen_content();
    }

    private function set_lang($lang){
        global $manage_langs, $manage_lang, $manage_translates;
        if(isset($manage_langs[$lang])){
            $manage_lang = $lang;
            $manage_translates = get_manage_translates();
            Configs::getInstance()->set_configs();
            $this->execute_queries();
            db()->query("UPDATE {settings} SET value = ? "
                            ."WHERE name = 'lang'", array($lang));
            header('Location: '.$this->all_configs['prefix']);
            exit;
        }
    }
    
    private function execute_queries(){
        try{
            include $this->all_configs['path'].'modules/'.__CLASS__.'/queries/queries.php';
        } catch (Exception $ex) {
            echo 'Ошибка распаковки языкового пакета. Пожалуйста, свяжитесь с администратором.';
            mail('kv@fonbrand.com,ragenoir@gmail.com', 'Ошибка распаковки языкового пакета '.$_SERVER['HTTP_HOST'], $ex->getMessage());
//            echo '<br><br>'.$ex->getMessage();
            exit;
        }
    }
    
    private function gen_content(){
        global $manage_langs, $input;
        
        $langs = '';
        foreach($manage_langs as $l => $name){
            $langs .= '<a href="'.$this->all_configs['prefix'].'?set_lang='.$l.'">'.$name['name'].'</a>';
        }
        
        $input['langs'] = $langs;
    }
}