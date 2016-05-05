<button class='btn btn-small' onclick='order_products(this, <?= $product['goods_id'] ?>, null, 1);close_alert_box();'>
    <?= l('Заказать локально') ?><br/>
    <small><?= l('срок 1-3 дня') ?> (<?= ($qty ? $qty['qty_1'] : '0') ?>)</small>
</button>
<button class='btn btn-small' onclick='order_products(this, <?= $product['goods_id'] ?>, null, 2);close_alert_box();'>
    <?= l('Заказать за границей') ?><br/>
    <small><?= l('срок 2-3 недели') ?> (<?= ($qty ? $qty['qty_2'] : '0') ?>)</small>
</button>
