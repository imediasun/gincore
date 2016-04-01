<div class="span2">
    <form method="post">
        <legend><?= l('Фильтры') ?>:</legend>
        <label><?= l('manager') ?>:</label>
        <select class="multiselect input-small" name="managers[]" multiple="multiple">
            <?php foreach ($managers as $manager): ?>
                <option <?= ((isset($_GET['mg']) && in_array($manager['id'],
                        explode(',', $_GET['mg']))) ? 'selected' : ''); ?> value="<?= $manager['id'] ?>">
                    <?= htmlspecialchars($manager['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label><?= l('Дата') ?>:</label>
        <input type="text" placeholder="<?= l('Дата') ?>" name="date" class="daterangepicker input-medium"
               value="<?= $date ?>"/>
        <label><?= l('номер заказа') ?>:</label>
        <input name="client-order"
               value="<?= isset($_GET['co']) && !empty($_GET['co']) ? trim(htmlspecialchars($_GET['co'])) : ''; ?>"
               type="text" class="input-medium" placeholder="<?= l('номер заказа') ?>">
        <label><?= l('Клиент') ?>:</label>
        <div><?= typeahead($this->all_configs['db'], 'clients', false,
                (isset($_GET['c_id']) && $_GET['c_id'] > 0 ? $_GET['c_id'] : 0), 3) ?> </div>
        <input type="submit" name="filters" class="btn" value="<?= l('Фильтровать') ?>">
    </form>
</div>
<div class="span10">
    <?php if (count($orders) > 0): ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <td>№</td>
                <td><?= l('Дата') ?></td>
                <td><?= l('Кто обработал') ?></td>
                <td><?= l('ФИО клиента') ?></td>
                <td><?= l('Сумма') ?></td>
                <td><?= l('Оплачено') ?></td>
                <td><?= l('Способ оплаты') ?></td>
                <td><?= l('Оплата') ?></td>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <div class="text-success">Оплачено</div>
                <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td><span title="<?= do_nice_date($order['date_add'],
                            false) ?>"><?= do_nice_date($order['date_add']) ?></span></td>
                    <td><?= get_user_name($order, 'h_') ?></td>
                    <td><?= get_user_name($order, 'o_') ?></td>
                    <td><?= show_price($order['sum']) ?></td>
                    <td><?= show_price($order['sum_paid']) ?></td>
                    <td><?= (array_key_exists($order['payment'],
                            $this->all_configs['configs']['payment-msg'])) ? $this->all_configs['configs']['payment-msg'][$order['payment']]['name'] : ''; ?></td>
                    <td>
                        <?php if ($order['sum'] > $order['sum_paid'] && ($order['status'] == $this->all_configs['configs']['order-status-wait-pay']
                                || $order['status'] == $this->all_configs['configs']['order-status-part-pay'])
                        ): ?>
                            <input type="button" class="btn btn-xs" value="<?= l('Принять оплату') ?>"
                                   onclick="pay_client_order(this, 2, <?= $order['order_id'] ?>, 0)"/>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php $count = $this->all_configs['manageModel']->get_count_clients_orders($query); ?>
        <?php $count_page = ceil($count / $count_on_page); ?>
        <?= page_block($count_page, $count, '#orders_pre-noncash'); ?>
    <?php else: ?>
        <p class="text-error"><?= l('Нет заказов') ?></p>
    <?php endif; ?>
</div>
