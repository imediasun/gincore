<?php

$active_widgets = array(
    'status'
);

$name = isset($_GET['w']) ? trim($_GET['w']) : null;

$script = '';
if(in_array($name, $active_widgets)){
    include __DIR__.'/manage/inc_config.php';
    include __DIR__.'/manage/inc_func.php';
    include __DIR__.'/manage/inc_settings.php';
    
    $widget = get_service('widgets/'.$name);
    if(!is_null($widget) && method_exists($widget, 'load_widget')){
        $widgets_service = get_service('widgets');        
        $script .= $widgets_service->load_widget_service();
        $script .= $widget->load_widget();
    }
}

header('Content-type:application/javascript;charset=utf-8');
echo $script;