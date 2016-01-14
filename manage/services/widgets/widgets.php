<?php namespace services\widgets;

class widgets extends \service{
    
    private static $instance = null;

    private function get_encoded_file($file){
        $file_path = __DIR__.'/'.$file;
        $data = str_replace("\n", "", file_get_contents($file_path));
        $data = rawUrlEncode($data);
        return $data;
    }
    
    public function attach_js($file){
        $script = $this->get_encoded_file($file);
        return 
            '(function () {'.
                'var s = document.createElement("script");'.
                    's.type = "text/javascript";'.
                    's.innerHTML = decodeURIComponent("'.$script.'");'.
                'document.getElementsByTagName("head")[0].appendChild(s);'.
            '})();';
        ;
    }

    public function attach_css($file){
        $style = $this->get_encoded_file($file);
        return 
            '(function () {'.
                'var s = document.createElement("style");'.
                    's.rel = "stylesheet";'.
                    's.innerHTML = decodeURIComponent("'.$style.'");'.
                'document.getElementsByTagName("head")[0].appendChild(s);'.
            '})();';
        ;
    }
    
    public function add_html($html){
        return 'document.body.innerHTML += decodeURIComponent("'.rawUrlEncode($html).'");';
    }
    
    public function load_widget_service(){
        $core_scripts = '';
        $core_scripts .= $this->attach_js('assets/jquery-1.10.2.min.js');
        return $core_scripts;
    }
    
    public static function getInstanse(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct(){}
}
