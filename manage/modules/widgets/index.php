<?php

// настройки
$modulename[132] = 'widgets';
$modulemenu[132] = l('Виджеты');  //карта сайта

$moduleactive[132] = !$ifauth['is_2'];

class widgets{

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

        $this->current = isset($this->all_configs['arrequest'][1]) ? $this->all_configs['arrequest'][1] : '';
        
        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    private function genmenu(){
        
        $out = '
            <ul>
                <li'.($this->current == 'status' ? ' class="active"' : '').'>
                    <a href="'.$this->all_configs['prefix'].'widgets/status">'.l('Статус ремонта').'</a>
                </li>
            </ul>
        ';

        return $out;
    }

    private function gencontent(){

        switch($this->current){
            case 'status':
                $out = '
                    <h2>'.l('Виджет «Статус заказа»').'</h2>
                    <pre>'.
                        htmlspecialchars(
                            "<script>\n".
                            "    (function () {\n".
                            "        var s = document.createElement(\"script\");\n".
                            "            s.type = \"text/javascript\";\n".
                            "            s.async = true;\n".
                            "            s.src = \"//".$_SERVER['HTTP_HOST'].$this->all_configs['prefix']."widget.php?w=status&jquery=\"+(\"jQuery\" in window?1:0);\n".
                            "        document.getElementsByTagName(\"head\")[0].appendChild(s);\n".
                            "    })();\n".
                            "</script>"
                        ).'
                    </pre>
                ';
            break;
            default:
                $out = l('Виджеты');
            break;
        }
        
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
