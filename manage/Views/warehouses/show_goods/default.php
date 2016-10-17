<table class="table table-hover table-medium">
    <thead>
    <tr>
        <td></td>
        <td><?= l('Серийный номер') ?></td>
        <td><?= l('Наименование') ?></td>
        <td><?= l('Дата') ?></td>
        <td><?= l('Склад') ?></td>
        <td><?= l('Заказ') ?></td>
        <?php if ($this->all_configs['oRole']->hasPrivilege('logistics')): ?>
            <td><?= l('Цена') ?></td>
        <?php endif; ?>
        <td><?= l('Кол-во') ?></td>
        <td><?= l('Поставщик') ?></td>
    </tr>
    </thead>
    <tbody>

    <?php $queryString = array(); ?>
    <?php foreach ($_GET as $key => $value): ?>
        <?php if ($key != 'act' && $key != 'goods'): ?>
            <?php $queryString[] = $key . '=' . $value; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php $queryString = $this->all_configs['prefix'] . 'warehouses?' . implode('&', $queryString) . '&goods='; ?>

    <?php foreach ($goods as $product): ?>
        <?php $f_goods = $_f_goods = isset($_GET['goods']) ? array_filter(explode('-', $_GET['goods'])) : array(); ?>
        <?php if (in_array($product['goods_id'], $f_goods)): ?>
            <?php $pos = array_search($product['goods_id'], $f_goods); ?>
            <?php if ($pos !== false): ?>
                <?php unset($_f_goods[$pos]); ?>
            <?php endif; ?>
            <?php $url = $queryString . implode('-', $_f_goods); ?>
            <tr class="border-top well cursor-pointer" onclick="window.location.href='<?= $url ?>' + window.location.hash">
            <td><i class="glyphicon glyphicon-chevron-up"></i></td>
        <?php else: ?>
            <?php array_push($_f_goods, $product['goods_id']); ?>
            <?php $url = $queryString . implode('-', $_f_goods); ?>
            <tr class="border-top cursor-pointer" onclick="window.location.href='<?= $url ?>' + window.location.hash">
            <td><i class="glyphicon glyphicon-chevron-down"></i></td>
        <?php endif; ?>
        <td></td>
        <td>
            <a 
               href="<?= $this->all_configs['prefix'] ?>products/create/<?= $product['goods_id'] ?>#financestock-stock"
               data-action="sidebar_product" data-id_product="<?= $product['id'] ?>">
                <?= h($product['product_title']) ?>
            </a>
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><?= $product['qty_wh'] ?></td>
        <td></td>
        </tr>
        <?php if (in_array($product['goods_id'], $f_goods)): ?>
            <?php $items = $controller->getWarehousesItems($product); ?>
            <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                    <?php $can = $this->all_configs['chains']->can_use_item($item['item_id']); ?>
                    <tr>
                        <td>
                            <?php if ($can): ?>
                                <input onclick="checked_item()" type="checkbox" class="check-item"
                                       value="<?= $item['item_id'] ?>"/>
                            <?php endif; ?>
                        </td>
                        <td><?= suppliers_order_generate_serial($item, true, true) ?></td>
                        <td>
                            <a class="hash_link"
                               href="<?= $this->all_configs['prefix'] ?>products/create/<?= $product['goods_id'] ?>#financestock-stock">
                                <?= h($product['product_title']) ?>
                            </a>
                        </td>
                        <td><span title="<?= do_nice_date($item['date_add'],
                                false) ?>"><?= do_nice_date($item['date_add']) ?></span></td>
                        <td><?= h($item['wtitle']) ?></td>
                        <td><?= $item['order_id'] ?></td>
                        <?php if ($this->all_configs['oRole']->hasPrivilege('logistics')): ?>
                            <td><?= show_price($item['price']) ?></td>
                        <?php endif; ?>
                        <td><?= (int)$can ?></td>
                        <td><?= h($item['title']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9"><?= l('Изделий нет') ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>

    </tbody>
</table>

<?php $items = $controller->getFilteredItems($product); ?>
<p><?= l('Всего отфильтровано') ?>: <?= ($items ? 1 * $items['count'] : 0) ?> <?= l('шт.') ?></p>
<?php if ($this->all_configs['oRole']->hasPrivilege('logistics')): ?>
    <p><?= l('На сумму') ?>: <?= show_price($items['sum']) ?>
        <?php $currency_suppliers_orders = $this->all_configs['suppliers_orders']->currency_suppliers_orders; ?>
        <?php $currencies = $this->all_configs['suppliers_orders']->currencies; ?>
        <?= $currencies[$currency_suppliers_orders]['shortName'] ?></p>
<?php endif; ?>
<!--<p>--><?//= l('Печать') ?><!--: <a onclick="global_print_labels()"><i class="cursor-pointer fa fa-print"></i></a></p>-->
<?php $addition = ''; ?>
<?php if (isset($_GET['whs'])): ?>
    <?php $addition .= '&whs=' . $_GET['whs'] ?>
<?php endif; ?>
<?php if (isset($_GET['lcs'])): ?>
    <?php $addition .= '&lcs=' . $_GET['lcs'] ?>
<?php endif; ?>
<?php if (isset($_GET['pid'])): ?>
    <?php $addition .= '&pid=' . $_GET['pid'] ?>
<?php endif; ?>
<?= $this->renderFile('warehouses/print_buttons', array(
    'prefix' => '_filtered',
    'objectId' => 'none',
    'addition' => $addition
)) ?>
