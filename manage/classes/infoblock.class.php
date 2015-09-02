<?php

/**
 *
 * Определяет текущий таб, выбирает соответствующую страницу и выводит помощь.
 * Позволяет редактировать текст помощи админам.
 *
 * Использует глобальный шаблон, css, js.
 *
 */
class Infoblock
{

    private $all_configs;

    function __construct(&$all_configs)
    {
        $this->all_configs = $all_configs;
    }

    function genblock()
    {
        $info = $this->getinfo($this->all_configs['curmod'] . (isset($this->all_configs['arrequest'][1]) ? $this->all_configs['arrequest'][1] : ''));

        $html = '<i title="Перемещение заказа" class="icon-move cursor-pointer" onclick="alert_box(this, false, \'stock_move-order\', undefined, undefined, \'messages.php\')" id="move-order"></i>'
            . '<div id="infoblock" class="">'
            . '<span id="infoblock-call"><i class="icon-info-sign"></i></span>'
            . '<h6 class="title"></h6>'
            //Для этой страницы помощь нельзя добавить
            . '<div class="infoblock-container">'
            . '<div class="infoblock">'
            . $info['text']
            . '</div>'
            . '</div>'
            . '</div>';
        return $html;
    }


    function setinfo($page, $value)
    {
        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $this->all_configs['db']->query(
                'INSERT INTO {infoblocks} (page, infoblock) VALUES (?, ?) ON DUPLICATE KEY UPDATE infoblock=?;',
                array($page, $value, $value));
            return array();
        } else {
            return false;
        }

    }

    function getinfo($page)
    {
        $q = $this->all_configs['db']->query('SELECT * FROM {infoblocks} WHERE page = ?',
            array($page))->row();

        return array('text' => $q['infoblock'], 'title' => '');
    }
}