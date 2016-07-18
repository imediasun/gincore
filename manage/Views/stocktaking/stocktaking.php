<div class="row-fluid">
    <?= $filters; ?>
</div>
<div class="row-fluid">
    <?php if (empty($goods)): ?>
        <?php if (empty($wh_selected)): ?>
            <p class="text-error"><?= l('Выберите склад') ?></p>
        <?php else: ?>
            <p class="text-error"><?= l('Товары не найдены') ?></p>
        <?php endif; ?>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <td><?= l('Серийный номер') ?></td>
                <td><?= l('Наименование') ?></td>
                <td><?= l('Дата') ?></td>
                <td><?= l('Склад') ?></td>
                <td><?= l('Локация') ?></td>
                <td><?= l('Заказ клиента') ?></td>
                <td><?= l('Заказ поставщику') ?></td>
                <td><?= l('Цена') ?></td>
                <td><?= l('Поставщик') ?></td>
                <td><?= l('Результат') ?></td>
            </tr>
            </thead>
            <tbody>

            <?php foreach ($goods as $product): ?>
                <tr>
                    <?php $serial = suppliers_order_generate_serial($product, true, false) ?>
                    <td><?= suppliers_order_generate_serial($product, true, true) ?></td>
                    <td>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] ?>products/create/<?= $product['goods_id'] ?>#financestock-stock">
                            <?= h($product['product_title']) ?>
                        </a>
                    </td>
                    <td>
                <span title="<?= do_nice_date($product['date_add'],
                    false) ?>"><?= do_nice_date($product['date_add']) ?></span>
                    </td>
                    <td>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] ?>warehouses?whs=<?= $product['id'] ?>#show_items">
                            <?= h($product['title']) ?>
                        </a>
                    </td>
                    <td>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] ?>warehouses?whs=<?= $product['id'] ?>&lcs=<?= $product['location_id'] ?>#show_items">
                            <?= h($product['location']) ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($product['order_id'] > 0): ?>
                            <a class="hash_link"
                               href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $product['order_id'] ?>">
                                <?= $product['order_id'] ?>
                            </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] ?>warehouses?so_id=<?= $product['supplier_order_id'] ?>#show_items">
                            <?= $product['supplier_order_id'] ?>
                        </a>
                    </td>
                    <td><?= $this->Numbers->price($product['price']) ?></td>
                    <td><?= h($product['contractor_title']) ?></td>
                    <td>
                        <?php if (in_array($serial, $stocktaking['checked_serials']['both'])): ?>
                            <i class="fa fa-check" aria-hidden="true" style="color: green"></i>
                        <?php else: ?>
                            <i class="fa fa-times" aria-hidden="true" style="color: red"></i>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <td colspan="6"></td>
                <td colspan="4" style="text-align: right; padding-right: 0;">
                    <form method="post" class="form-horizontal" style="display: inline-block">
                        <input name="stocktaking-id" value="<?= $stocktaking['id'] ?>" type="hidden"/>
                        <input type="submit" name='save-stocktaking' value="<?= l('Сохранить') ?>" class="btn btn-small btn-default">
                    </form>
                    <?php $url = $this->all_configs['prefix'] . (isset($this->all_configs['arrequest'][0]) ? $this->all_configs['arrequest'][0] . '/' : '') . 'export'; ?>
                    <form target="_blank" method="get" action="<?= $url ?>" class="form-horizontal"
                          style="display: inline-block">
                        <input name="stocktaking-id" value="<?= $stocktaking['id'] ?>" type="hidden"/>
                        <input name="act" value="export-stocktaking" type="hidden"/>
                        <input type="submit" value="<?= l('Выгрузить данные') ?>" class="btn btn-small btn-primary">
                    </form>
                </td>
            </tr>
            </tbody>
        </table>
        <?= page_block($count_page, count($goods), '#warehouses_stocktaking'); ?>
    <?php endif; ?>
</div>
