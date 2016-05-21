<h4><?= l('Инструменты') ?></h4>
<ul>
    <li>
        <a style="<?= (isset($arrequest[1]) && $arrequest[1] == 'glue' ? 'font-weight:bold' : '') ?>"
           href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/glue"><?= l('Склейка') ?></a>
    </li>
    <li><a style="<?= (isset($arrequest[1]) && $arrequest[1] == 'map' ? 'font-weight:bold' : '') ?>"
           href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/map"><?= l('Страницы') ?></a>
    </li>
</ul>
