<?php if ($item) : ?>
    <div class="m-l-sm">
        <h4>
            <?= l('Серийный №') ?> : <?= suppliers_order_generate_serial($item); ?>
            <?= $this->renderFile('warehouses/print_buttons', array(
                'objectId' => $item['goods_id'],
                'prefix' => '',
                'whItemId' => $item['item_id']
            )) ?>
        </h4>
    </div>
    <div class="m-l-sm m-r-sm">
        <table class="table">
            <tbody>
            <?php if (mb_strlen(trim($item['serial_old']), 'utf-8') > 0): ?>
                <tr>
                    <td><b><?= l('Серийный номер') ?> (<?= l('старый') ?>)</b></td>
                    <td><?= h($item['serial_old']) ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td><b><?= l('Наименование') ?></b></td>
                <td>
                    <a
                        href="<?= $this->all_configs['prefix'] ?>products/create/<?= $item['goods_id'] ?>#financestock-stock"
                        data-action="sidebar_product" data-id_product="<?= $item['id'] ?>">
                        <?= h($item['product_title']) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td><b><?= l('Артикул') ?></b></td>
                <td><?= h($item['vendor_code']) ?></td>
            </tr>
            <tr>
                <td><b><?= l('Поставщик') ?></b></td>
                <td><?= h($item['contractor_title']) ?></td>
            </tr>
            <tr>
                <td><b><?= l('Заказ поставщика') ?></b></td>
                <td>
                    <?php if ($item['supplier_order_id'] > 0): ?>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $item['supplier_order_id'] ?>#create_supplier_order">
                            <?= $this->all_configs['suppliers_orders']->supplier_order_number(array('id' => $item['supplier_order_id'])) ?>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><b><?= l('Дата приходования') ?></b></td>
                <td>
                <span title="<?= do_nice_date($item['date_add'],
                    false) ?>"><?= do_nice_date($item['date_add']) ?></span>
                </td>
            </tr>
            <tr>
                <td><b><?= l('Цена закупки') ?></b></td>
                <td><?= $controller->show_price($item['price']) ?></td>
            </tr>
            <tr>
                <td><b><?= l('Склад') ?></b></td>
                <td>
                    <a class="hash_link"
                       href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?whs=<?= $item['id'] ?>#show_items">
                        <?= h($item['title']) ?>
                </td>
            </tr>
            <tr>
                <td><b><?= l('Локация') ?></b></td>
                <td>
                    <a class="hash_link"
                       href="<?= $this->all_configs['prefix'] .= $this->all_configs['arrequest'][0] ?>?whs=<?= $item['id'] ?>&lcs=<?= $item['location_id'] ?>#show_items">
                        <?= h($item['location']) ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td><b><?= l('Заказ') ?></b></td>
                <td>
                    <?php if ($item['order_id'] > 0): ?>
                        <a class="hash_link"
                           href="orders/create/<?= $item['order_id'] ?>">
                            <?= $item['order_id'] ?>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><b><?= l('Дата продажи') ?></b></td>
                <td>
                <span title="<?= do_nice_date($item['date_sold'],
                    false) ?>"><?= do_nice_date($item['date_sold']) ?></span>
                </td>
            </tr>
            </tbody>
        </table>

        <?= $this->all_configs['chains']->form_write_off_items($item['item_id'], $this->errors, true); ?>
        <?= $this->all_configs['chains']->return_supplier_order_form($item['item_id'], true); ?>

        <?= $this->all_configs['chains']->form_sold_items($item['item_id'], $this->errors, true); ?>

        <?= $this->all_configs['chains']->moving_item_form($item['item_id'], null, null, null, true, null, true); ?>


    </div>

    <form action="" method="post" id="sidebar-item-form" class="one_hpanel">

        <input type="submit" class="btn" value="<?= l('Сохранить') ?>" id="sidebar-item-form-submit">
<!--           <input type="button" class="btn btn-default js_close_sidebar" value="<?= l('Отмена') ?>"> -->
    </form>


<?php endif; ?>


