<?php

$active_widgets = array(
    'status',
    'feedback'
);

// загружаем виджет
$name = isset($_GET['w']) ? trim($_GET['w']) : null;
if ($name) {
    $script = '';
    if (in_array($name, $active_widgets)) {
        include __DIR__ . '/manage/inc_config.php';
        include __DIR__ . '/manage/inc_func.php';
        include __DIR__ . '/manage/inc_settings.php';
        $widget = get_service('widgets/' . $name);
        if (!is_null($widget) && method_exists($widget, 'load_widget')) {
            $widgets_service = get_service('widgets');
            $has_jquery = isset($_GET['jquery']) && $_GET['jquery'] == 1;
            $script .= $widgets_service->load_widget_service($has_jquery);
            $script .= $widget->load_widget();
        }
    }

    header('Content-type:application/javascript;charset=utf-8');
    echo $script;
    exit;
}

// принимаем запросы
$is_ajax = isset($_GET['ajax']);
if ($is_ajax) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST,GET');
    if (isset($HTTP_RAW_POST_DATA)) {
        parse_str($HTTP_RAW_POST_DATA, $post);
    } else {
        $post = $_POST;
    }
    $widget_name = isset($post['widget']) ? trim($post['widget']) : null;
    if (in_array($widget_name, $active_widgets)) {
        include __DIR__ . '/manage/inc_config.php';
        include __DIR__ . '/manage/inc_func.php';
        include __DIR__ . '/manage/inc_settings.php';
        $widget = get_service('widgets/' . $widget_name);
        if (!is_null($widget) && method_exists($widget, 'ajax')) {
            $response = $widget->ajax($post);
            header('Content-type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }
    }
}