<table class="table table-striped">
    <thead>
    <tr>
        <td><?= l('Серийный номер') ?></td>
        <td><?= l('Артикул') ?></td>
        <td><?= l('Наименование') ?></td>
        <td><?= l('Дата') ?></td>
        <td><?= l('Склад') ?></td>
        <td><?= l('Локация') ?></td>
        <td><?= l('Заказ') ?><br> <?= l('клиента') ?></td>
        <td><?= l('Заказ') ?> <br> <?= mb_convert_case(l('Поставщику'), MB_CASE_LOWER) ?></td>
        <td><?= l('Цена') ?><br><?= l('закупки') ?></td>
        <td><?= l('Поставщик') ?></td>
    </tr>
    </thead>
    <tbody>


    <?php foreach ($goods as $product): ?>
        <tr>
            <td><?= suppliers_order_generate_serial($product, true, true) ?></td>
            <td><?= h($product['vendor_code']) ?></td>
            <td>
                <a 
                   href="<?= $this->all_configs['prefix'] ?>products/create/<?= $product['goods_id'] ?>#financestock-stock"
                   data-action="sidebar_product" data-id_product="<?= $product['goods_id'] ?>">
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
                   href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $product['supplier_order_id'] ?>#create_supplier_order">
                    <?= $product['supplier_order_id'] ?>
                </a>
            </td>
            <td><?= $controller->show_price($product['price']) ?></td>
            <td><?= h($product['contractor_title']) ?></td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td colspan="6"></td>
        <td colspan="3"  style="text-align: right; padding-right: 0">
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
            <?php $url = $this->all_configs['prefix'] . (isset($this->all_configs['arrequest'][0]) ? $this->all_configs['arrequest'][0] . '/' : '') . 'ajax'; ?>
            <form target="_blank" method="get" action="<?= $url ?>" class="form-horizontal" style="display: inline-block">
                <input name="act" value="exports-items" type="hidden"/>
                <?php if (isset($_GET['whs'])): ?>
                    <input name="whs" value="<?= $_GET['whs'] ?>" type="hidden"/>
                <?php endif; ?>
                <?php if (isset($_GET['lcs'])): ?>
                    <input name="lcs" value="<?= $_GET['lcs'] ?>" type="hidden"/>
                <?php endif; ?>
                <?php if (isset($_GET['pid'])): ?>
                    <input name="pid" value="<?= $_GET['pid'] ?>" type="hidden"/>
                <?php endif; ?>
                <?php if (isset($_GET['d'])): ?>
                    <input name="d" value="<?= $_GET['d'] ?>" type="hidden"/>
                <?php endif; ?>
                <input type="submit" value="<?= l('Выгрузить данные') ?>" class="btn btn-small btn-primary">
            </form>
        </td>
    </tr>
    </tbody>
</table>
<?php if (count($goods) == 1): ?>
    <script type="text/javascript">
        var item_id = '<?= suppliers_order_generate_serial($goods[0], true, false) ?>';
        $( document ).ready(function() {
            rightSidebar.load_item(item_id);
        });
    </script>
<?php endif; ?>
