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
        global $manage_lang;
        if(isset($this->all_configs['configs']['manage-show-glossary']) && $this->all_configs['configs']['manage-show-glossary'] 
                && isset($this->all_configs['configs']['manage-glossary-url'])){
            $gu = parse_url($this->all_configs['configs']['manage-glossary-url']);
            $url = $gu['scheme'].'://'.$gu['host'].'/'.$manage_lang.$gu['path'].'?'.$gu['query'];
            $info_block = '
                <button data-title="'.l('intro_glossary_btn_hint').'" id="show_glossary" type="button" class="show_glossary btn btn-info" style="padding: 5px 10px">
                    <i class="fa fa-info"></i>
                </button>
                <div class="glossary" id="glossary" data-url="'.$url.($this->all_configs['curmod'] ? '&mod='.$this->all_configs['curmod'] : '').'">
                    <div class="glossary_close">
                        <button id="glossary_close" type="button" class="close">×</button>
                    </div>
                    <div id="glossary_content" class="glossary_content"></div>
                </div>
                <div id="glossary_alpha" class="glossary_alpha"></div>
                
                <!--
                <div class="zadarma_button_call_consultant" id="zadarma_button_call_consultant">
                    <script type="text/javascript" src="https://zadarma.com/swfobject.js"></script>
                    <script type="text/javascript">
                    var flashvars = {};
                    flashvars.phone="381801";
                    flashvars.img1="' . $this->all_configs['prefix'] . 'img/call3_blue_ru_free.png";
                    flashvars.img2="' . $this->all_configs['prefix'] . 'img/call2_green_ru_connecting.png";
                    flashvars.img3="' . $this->all_configs['prefix'] . 'img/call2_green_ru_reset.png";
                    flashvars.img4="' . $this->all_configs['prefix'] . 'img/call2_green_ru_error.png";
                    var params = {};
                    params.wmode="transparent";
                    var attributes = {};
                    swfobject.embedSWF("' . $this->all_configs['prefix'] . 'img/pbutton.swf", "myAlternativeContent", "215", "138", "9.0.0", false, flashvars, params, attributes);
                    </script>
                    <div id="myAlternativeContent">
                    <a href="http://www.adobe.com/go/getflashplayer">
                    <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
                    </a>
                    </div>
                </div>
                -->
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
            '<button title="'.l('Перемещения').'" type="button" class="btn btn-default" id="move-order">
                    <i class="fa fa-random"></i>
            </button>'
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