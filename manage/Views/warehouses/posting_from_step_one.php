<h4><?= l('Оприходовать товар можно несколькими способами') ?></h4>
<ol>
    <li style="margin-bottom: 20px">
        <?= l('Создать заказ поставщику в разделе Заказы-Создать заказ поставщику. После чего в разделе Склад-Заказы поставщикам выбрать из списка нужный заказ и оприходовать его') ?>
    </li>
    <li style="margin-bottom: 20px">
        <a href="<?= $this->all_configs['prefix'] ?>import?load=posting_items#import" class="btn btn-default btn-sm"><?= l('Загрузить') ?></a>
        <?= l('накладную от поставщика в формате Excel. Товар перечисленный в накладной будет оприходован на склад') ?>
    </li>
    <li>
        <a href="" class="btn btn-default btn-sm" onclick="return create_purchase_invoice();"><?= l('Создать') ?></a>
        <?= l('накладную самостоятельно') ?>
    </li>
</ol>