<?php if ($orders): ?>
    <table class="show-suppliers-orders table">
        <thead>
        <tr>
            <td></td>
            <td><?= l('Дата созд.') ?></td>
            <td><?= l('Создал') ?></td>
            <td><?= l('Код') ?></td>
            <td><?= l('Поставщик') ?></td>
            <td><?= l('Наименование') ?></td>
            <td><?= l('Кол-во') ?></td>
            <td><?= l('Цена') ?></td>
            <td><?= l('Стоимость') ?></td>
            <td><?= l('Оплачено') ?></td>
            <td><?= l('Дата пост.') ?></td>
            <td><?= l('Принято') ?></td>
            <td><?= l('Принял') ?></td>
            <td><?= l('Склад') ?></td>
            <td><?= l('Оприх.') ?></td>
            <td><?= l('Примеч.') ?></td>
            <td></td>
        </tr>
        </thead>
        <tbody>


        <?php foreach ($orders as $order): ?>
            <?php
            $status_txt = 'Новый заказ, ожидание поставки';
            $class = '';
            if (strtotime($order['date_wait']) < time() && $order['confirm'] != 1) {
                $status_txt = 'Пропущена поставка (дата поставки в прошлом и заказ не был принят)';
                $class = ' danger ';
            }

            if ($order['confirm'] != 1 && ($order['count_debit'] != $order['count_come'] || $order['sum_paid'] == 0) &&
                $order['wh_id'] > 0 && $order['count_come'] > 0
            ) {
                $status_txt = 'Принят, но не приходован на склад';
                $class = ' info ';
            }

            if ($order['confirm'] == 1) {
                $status_txt = 'Успешно обработан';
                $class = ' success ';
            }

            if ($order['avail'] == 0) {
                $status_txt = 'Отменен';
                $class = ' red ';
            }
            ?>


            <tr title="<?= $status_txt ?>" class=" <?= $class ?>" id="supplier-order_id-<?= $order['id'] ?>">
                <td>
                    <?= show_marked($order['id'], 'so', $order['m_id']) ?>
                    <?= show_marked($order['id'], 'woi', $order['mi_id']) ?>
                </td>
                <td>
                    <span title="<?= do_nice_date($order['date_add'], false) ?>">
                        <?= do_nice_date($order['date_add']) ?>
                    </span>
                </td>
                <td><?= $controller->getClientIcons($order) . get_user_name($order) ?></td>
                <td><?= $controller->supplier_order_number($order) ?></td>
                <td><?= htmlspecialchars($order['stitle']) ?></td>
                <td>
                    <a class="hash_link" title="<?= $order['secret_title'] ?>"
                       href="<?= $this->all_configs['prefix'] ?>products/create/<?= $order['goods_id'] ?>">
                        <?= $order['goods_title'] ?>
                    </a>
                </td>
                <td><?= $order['count'] ?></td>
                <td><?= show_price($order['price'], 2, ' ', ',') ?></td>
                <td><?= show_price($order['price'], 2, ' ', ',', 100, array(), $order['count']) ?></td>
                <td><?= show_price($order['sum_paid'], 2, ' ', ',') ?></td>
                <td>
                    <span title="<?= do_nice_date($order['date_wait'],
                        false) ?>"><?= do_nice_date($order['date_wait']) ?></span>
                </td>
                <td>
        <span title="<?= do_nice_date($order['date_come'], false) ?>"><?= do_nice_date($order['date_come']) ?>
        </span>
                </td>
                <td><?= (($order['count_come'] > 0) ? get_user_name($order, 'accept_') : '') ?></td>
                <td>
                    <?php if ($order['wh_id'] > 0): ?>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] ?>warehouses?whs=<?= $order['wh_id'] ?>#show_items">
                            <?= htmlspecialchars($order['wh_title']) ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($order['count_debit'] > 0): ?>
                        <a href="<?= $this->all_configs['prefix'] ?>warehouses ? so_id =<?= $order['id'] ?>#show_items"><?= $order['count_debit'] ?></a>
                    <?php else: ?>
                        <?= $order['count_debit'] ?>
                    <?php endif; ?>

                    <?php if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')): ?>
                        <?php if (count($order['items']) > 0): ?>
                            <?php $url = $this->all_configs['prefix'] . 'print.php?act=label&object_id=' . implode(',',
                                    array_keys($order['items'])); ?>
                            <a target="_blank" title="Печать" href="<?= $url ?>"><i class="fa fa-print"></i></a>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <i class="glyphicon glyphicon-pencil editable-click pull-right" data-placement="left"
                       data-display="false" data-title="Редактировать комментарий"
                       data-url="messages.php?act=edit-supplier-order-comment" data-pk="<?= $order['id'] ?>"
                       data-type="textarea" data-value="<?= htmlspecialchars($order['comment']) ?>"></i>
                    <span id="supplier-order-comment-<?= $order['id'] ?>"><?= cut_string($order['comment'],
                            50) ?></span>
                </td>
                <td><?= $this->renderFile('suppliers.class/_buttons', array(
                        'controller' => $controller,
                        'order' => $order,
                        'only_debit' => $only_debit,
                        'only_pay' => $only_pay,
                    )) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-danger"><?= l('Нет заказов') ?></p>
<?php endif; ?>

<?= $controller->append_js(); ?>
