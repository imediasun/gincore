<?php foreach ($goods as $product): ?>
    <h4><?= l('Наименование') ?></h4>
    <table class="table table-striped">
        <tbody>
        <tr>
            <td><b><?= l('Серийный номер') ?></b>
                <?php if ($this->all_configs['oRole']->hasPrivilege('site-administration') && mb_strlen($product['serial'],
                        'UTF-8') > 0
                    && $product['id'] == $this->all_configs['configs']['erp-warehouse-type-mir']
                ): ?>
                    <input class="btn btn-small btn-danger"
                           onclick="clear_serial(this, <?= $product['item_id'] ?>)" type="button"
                           value="<?= l('Удалить серийник') ?>"/>
                <?php endif; ?>
            </td>
            <td>
                <?= suppliers_order_generate_serial($product); ?>
                <?= $this->renderFile('warehouses/print_buttons', array(
                    'objectId' => $product['goods_id'],
                    'prefix' => ''
                )) ?>
            </td>
        </tr>
        <?php if (mb_strlen(trim($product['serial_old']), 'utf-8') > 0): ?>
            <tr>
                <td><b><?= l('Серийный номер') ?> (<?= l('старый') ?>)</b></td>
                <td><?= h($product['serial_old']) ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td><b><?= l('Наименование') ?></b></td>
            <td>
                <a class="hash_link"
                   href="<?= $this->all_configs['prefix'] ?>products/create/<?= $product['goods_id'] ?>#financestock-stock">
                    <?= h($product['product_title']) ?>
                </a>
            </td>
        </tr>
        <tr>
            <td><b><?= l('Поставщик') ?></b></td>
            <td><?= h($product['contractor_title']) ?></td>
        </tr>
        <tr>
            <td><b><?= l('Заказ поставщика') ?></b></td>
            <td>
                <?php if ($product['supplier_order_id'] > 0): ?>
                    <a class="hash_link"
                       href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $product['supplier_order_id'] ?>#create_supplier_order">
                        <?= $this->all_configs['suppliers_orders']->supplier_order_number(array('id' => $product['supplier_order_id'])) ?>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><b><?= l('Дата приходования') ?></b></td>
            <td>
                <span title="<?= do_nice_date($product['date_add'],
                    false) ?>"><?= do_nice_date($product['date_add']) ?></span>
            </td>
        </tr>
        <tr>
            <td><b><?= l('Цена') ?></b></td>
            <td><?= $controller->show_price($product['price']) ?></td>
        </tr>
        <tr>
            <td><b><?= l('Склад') ?></b></td>
            <td>
                <a class="hash_link"
                   href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?whs=<?= $product['id'] ?>#show_items">
                    <?= h($product['title']) ?>
            </td>
        </tr>
        <tr>
            <td><b><?= l('Локация') ?></b></td>
            <td>
                <a class="hash_link"
                   href="<?= $this->all_configs['prefix'] .= $this->all_configs['arrequest'][0] ?>?whs=<?= $product['id'] ?>&lcs=<?= $product['location_id'] ?>#show_items">
                    <?= h($product['location']) ?>
                </a>
            </td>
        </tr>
        <tr>
            <td><b><?= l('Заказ') ?></b></td>
            <td>
                <?php if ($product['order_id'] > 0): ?>
                    <a class="hash_link"
                       href="orders/create/<?= $product['order_id'] ?>">
                        <?= $product['order_id'] ?>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><b><?= l('Дата продажи') ?></b></td>
            <td>
                <span title="<?= do_nice_date($product['date_sold'],
                    false) ?>"><?= do_nice_date($product['date_sold']) ?></span>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="span12">
        <div class="span4 well">
            <h4><?= l('Запрос на перемещение') ?></h4>
            <?= $this->all_configs['chains']->moving_item_form($product['item_id']/*, null, $product['id']*/); ?>
        </div>
        <div class="span4">
            <?= $this->all_configs['chains']->form_sold_items($product['item_id'], $this->errors); ?>
        </div>
        <div class="span3">
            <?= $this->all_configs['chains']->form_write_off_items($product['item_id'], $this->errors); ?>
            <?= $this->all_configs['chains']->return_supplier_order_form($product['item_id']); ?>
        </div>
    </div>
    <h4><?= l('История перемещений') ?></h4>

    <?php $item_history = $controller->getItemHistory($product, $query_for_noadmin); ?>

    <?php if (count($item_history) > 0): ?>
        <table class="table">
            <thead>
            <tr>
                <td><?= l('Склад') ?></td>
                <td><?= l('Локация') ?></td>
                <td><?= l('Ответственный') ?></td>
                <td><?= l('Дата') ?></td>
                <td><?= l('Операция') ?></td>
                <td><?= l('На основании') ?> (<?= l('номер заказа') ?>)</td>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($item_history as $history): ?>
                <tr>
                    <td>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?whs=<?= $history['wh_id'] ?>#show_items">
                            <?= h($history['title']) ?>
                        </a>
                    </td>
                    <td><?= h($history['location']) ?></td>
                    <td><?= get_user_name($history) ?></td>
                    <td><span title="<?= do_nice_date($history['date_move'],
                            false) ?>"><?= do_nice_date($history['date_move']) ?></span></td>
                    <td><?= h($history['comment']) ?></td>
                    <td>
                        <?php $prefix = str_replace('warehouses', '', $this->all_configs['prefix']); ?>
                        <a href="<?= $prefix ?>orders/create/<?= $history['order_id'] ?>">
                            <?= $history['order_id'] ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <?= l('История перемещений не найдена'); ?>
    <?php endif; ?>
<?php endforeach; ?>
