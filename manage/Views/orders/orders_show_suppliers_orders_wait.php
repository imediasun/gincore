<?php if (!empty($orders)): ?>
    <table class="show-suppliers-orders table">
        <thead>
        <tr>
            <td></td>
            <td><?= l('Дата созд.') ?></td>
            <td><?= l('Код') ?></td>
            <td><?= l('Наименование') ?></td>
            <td><?= l('Кол-во') ?></td>
            <td><?= l('Оприх.') ?></td>
            <td><?= l('Склад') ?></td>
            <td><?= l('Локация') ?></td>
            <td><?= l('Проверить до') ?></td>
            <td width="15%"><?= l('Проверка') ?></td>
            <td><?= l('Номера ремонтов, на которых можно проверить запчасти') ?></td>
            <td><?= l('Комментарий') ?></td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <?php
            $sec = strtotime($order['date_check']);
            $class = $sec > 0 ? ($sec < time() ? 'danger' : ($sec < (time() + (2 * 60 * 60 * 24)) ? 'warning' : '')) : '';
            ?>
            <tr class=" <?= $class ?>" id="supplier-wait-order_id-<?= $order['id'] ?>">
                <td><?= show_marked($order['id'], 'so', $order['m_id']) ?></td>
                <td>
                    <span title="<?= do_nice_date($order['date_add'],
                        false) ?>"><?= do_nice_date($order['date_add']) ?></span>
                </td>
                <td><?= $this->all_configs['suppliers_orders']->supplier_order_number($order) ?></td>
                <td>
                    <a title="<?= $order['secret_title'] ?>"
                       href="<?= $this->all_configs['prefix'] ?>products/create/<?= $order['goods_id'] ?>"
                       data-action="sidebar_product" data-id_product="<?= $order['goods_id'] ?>">
                        <span class="visible-lg"><?= cut_string($order['goods_title'], 20) ?></span>
                        <span class="hidden-lg"><?= cut_string($order['goods_title'], 10) ?></span>
                    </a>
                </td>
                <td><?= $order['count'] ?></td>
                <td>
                    <?php if ($order['count_debit'] > 0): ?>
                        <a href="<?= $this->all_configs['prefix'] ?>warehouses?so_id=<?= $order['id'] ?>#show_items">
                            <?= $order['count_debit'] ?>
                        </a>
                    <?php else: ?>
                        <?= $order['count_debit'];  ?> 
                        <?php if (count($order['items']) > 0): ?>
                            <?php $url = $this->all_configs['prefix'] . 'print.php?act=label&object_id=' . implode(',',
                                    array_keys($order['items'])); ?>
                            <a target="_blank" title="<?= l('Печать') ?>" href="<?= $url ?>">
                                <i class="fa fa-print"></i>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td> <?php if ($order['wh_id'] > 0): ?>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] ?>warehouses?whs=<?= $order['wh_id'] ?>#show_items">
                            <?= htmlspecialchars($order['wh_title']) ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($order['wh_id'] > 0): ?>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] ?>warehouses?whs=<?= $order['wh_id'] ?>&lcs=<?= $order['location_id'] ?>#show_items">
                            <?= htmlspecialchars($order['location']) ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="input-group" style="width: 150px; position: relative">
                        <input class="datetimepicker form-control input-xs" placeholder="<?= l('Дата проверки') ?>"
                               data-format="yyyy-MM-dd hh:mm:ss" type="text" name="date_check"
                               value="<?= $order['date_check'] ?>"/>
                                <span class="input-group-btn">
                                    <button onclick="edit_so_date_check(this, event, <?= $order['id'] ?>)"
                                            class="btn btn-info btn-xs" type="button">
                                        <i class="glyphicon glyphicon-ok"></i>
                                    </button>
                                </span>
                    </div>
                </td>
                <td>
                    <?php if (count($order['items']) > 0): ?>
                        <?php foreach ($order['items'] as $item): ?>
                            <?php if (strtotime($item['date_checked']) <= 0): ?>
                                <button onclick="check_item(this, <?= $item['item_id'] ?>)"
                                        class="btn btn-default btn-xs">
                                    <?= suppliers_order_generate_serial($item) ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
                <td><?= (isset($serials[$order['goods_id']]) ? implode(', ', $serials[$order['goods_id']]) : '') ?>
                </td>
                <td><?= cut_string($order['comment'], 40) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-danger"><?= l('Нет заказов') ?></p>
<?php endif; ?>


<?php $count_page = $count_on_page > 0 ? ceil($count / $count_on_page) : 0; ?>

<?= page_block($count_page, $count, '#show_suppliers_orders-wait'); ?>
