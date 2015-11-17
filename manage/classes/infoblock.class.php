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
        if(isset($this->all_configs['configs']['manage-show-glossary']) && $this->all_configs['configs']['manage-show-glossary'] 
                && isset($this->all_configs['configs']['manage-glossary-url'])){
            $info_block = '
                <button id="show_glossary" type="button" class="show_glossary btn btn-info" style="padding: 5px 10px">
                    <i class="fa fa-info"></i>
                </button>
                <div class="glossary" id="glossary" data-url="'.$this->all_configs['configs']['manage-glossary-url'].($this->all_configs['curmod'] ? '&mod='.$this->all_configs['curmod'] : '').'"></div>
            ';
        }else{
            $info = $this->getinfo($this->all_configs['curmod'] . (isset($this->all_configs['arrequest'][1]) ? $this->all_configs['arrequest'][1] : ''));
            $info_block = 
            '<div id="infoblock" class="">'
               .'<span id="infoblock-call"><i class="fa fa-info-circle"></i></span>'
               .'<h6 class="title"></h6>'
               .'<div class="infoblock-container">'
                  .'<div class="infoblock">'
                      .$info['text']
                  .'</div>'
               .'</div>'
           .'</div>';
        }
        $html = 
            '<i title="Перемещение заказа" class="fa fa-arrows cursor-pointer" onclick="alert_box(this, false, \'stock_move-order\', undefined, undefined, \'messages.php\')" id="move-order"></i>'
           .$info_block;
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