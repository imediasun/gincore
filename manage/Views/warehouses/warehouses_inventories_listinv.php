<table class="table table-striped">
    <thead>
    <tr>
        <td></td>
        <td><?= l('Наименование') ?></td>
        <td><?= l('Кол-во на складе') ?></td>
        <td><?= l('Кол-во проинвентаризовано') ?></td>
        <td><?= l('Недостача') ?></td>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($inventories)): ?>
        <?php foreach ($inventories as $inv): ?>
            <?php $inv['count_items'] = isset($counts_items[$inv['id']]) ? $counts_items[$inv['id']] : 0; ?>
            <?php $inv['count_inv_items'] = isset($counts_inv_items[$inv['id']]) ? $counts_inv_items[$inv['id']] : 0; ?>
            <tr>
                <td></td>
                <td class="open-product-inv" onclick="open_product_inventory(this, <?= $inv['goods_id'] ?>)">
                    <i class="<?= ((isset($_GET['inv_p']) && $_GET['inv_p'] == $inv['goods_id']) ? 'glyphicon glyphicon-chevron-up' : 'glyphicon glyphicon-chevron-down') ?>"></i>
                    <?= h($inv['gtitle']) ?>
                </td>
                <td><?= $inv['count_items'] ?></td>
                <td><?= $inv['count_inv_items'] ?></td>
                <td><?= ($inv['count_items'] - $inv['count_inv_items']) ?></td>
            <tr>
                <td colspan="5" class="product-inventory">
                    <?php if (isset($_GET['inv_p']) && $_GET['inv_p'] == $inv['goods_id']): ?>
                        <?php $_inventories = $controller->getInventories($inv); ?>

                        <?php if (!empty($_inventories)): ?>
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <td></td>
                                    <td><?= l('Сер. номер') ?></td>
                                    <td><?= l('Дата') ?></td>
                                    <td><?= l('Кладовщик') ?></td>
                                    <td><?= l('Склад') ?></td>
                                    <td><?= l('Заказ') ?></td>
                                    <td>
                                        <?= l('Цена') ?>, <?= $this->all_configs['suppliers_orders']->currencies[$this->all_configs['suppliers_orders']->currency_suppliers_orders]['shortName']; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $j = 1; ?>
                                <?php foreach ($_inventories as $_inv): ?>
                                    <?php $_inv['scanned'] = suppliers_order_generate_serial($_inv); ?>
                                    <?php if ($_inv['inv_wh_id'] == $_inv['wh_id']): ?>
                                        <?php $_inv['i'] = $j; ?>
                                        <?php $j++; ?>
                                        <?= $controller->display_scanned_item($_inv, $_inv['inv_wh_id']); ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <td></td>
                                    <td><?= l('Сер. номер') ?></td>
                                    <td><?= l('Дата') ?></td>
                                    <td><?= l('Кладовщик') ?></td>
                                    <td><?= l('Склад') ?></td>
                                    <td><?= l('Заказ') ?></td>
                                    <td>
                                        <?= l('Цена') ?>
                                        , <?= $this->all_configs['suppliers_orders']->currencies[$this->all_configs['suppliers_orders']->currency_suppliers_orders]['shortName']; ?>
                                    </td>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $j = 1; ?>
                                <?php foreach ($_inventories as $_inv): ?>
                                    <?php $_inv['scanned'] = suppliers_order_generate_serial($_inv); ?>
                                    <?php if ($_inv['inv_wh_id'] != $_inv['wh_id']): ?>
                                        <?php $_inv['i'] = $j; ?>
                                        <?php $j++; ?>
                                        <?= $controller->display_scanned_item($_inv, $_inv['inv_wh_id']); ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <?= l('Изделий нет на складе'); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <td colspan="5"><?= l('Нет изделий') ?></td>
    <?php endif; ?>
    </tbody>
</table>
