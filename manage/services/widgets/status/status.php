<?php namespace services\widgets;

class status extends \service{
    
    private static $instance = null;

    public function load_widget(){
        $widgets = get_service('widgets');
        $loader = '';
        $loader .= $widgets->attach_css('status/css/main.css');
        $loader .= $widgets->attach_js('status/js/main.js');
        $html = $this->widget_html();
        $loader .= $widgets->add_html($html);
        return $loader;
    }
    
    private function widget_html(){
        return '
            <div class="gcw">
                <div class="gcw_title">Cтатус ремонта</div>
                <div class="gcw_body">
                    <form class="gcw_form" method="post">
                        <div class="gcw_form_group">
                            <label>Номер мобильного телефона</label>
                            <input class="gcw_form_control" type="text" name="phone">
                        </div>
                        <input type="submit" value="Отправить" class="gcw_btn">
                    </form>
                </div>
            </div>
        ';
    }
    
    public static function getInstanse(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct(){}
}
