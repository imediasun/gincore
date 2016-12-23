<div style="max-height: 250px; overflow-y: auto; font-size: 0.85em">
    <table class="table">
        <thead>
        <tr>
            <td><?= l('Cоздана') ?></td>
            <td><?= l('Напоминить') ?></td>
            <td><?= l('Автор') ?></td>
            <td><?= l('Исполнитель') ?></td>
            <td><?= l('номер заказа') ?></td>
            <td><?= l('Текст') ?></td>
            <td></td>
        </tr>
        </thead>
        <tbody>
        <?php if ($alarms): ?>
            <?php foreach ($alarms as $alarm): ?>
                <tr>
                    <td>
                    <span title="<?= do_nice_date($alarm['date_add'],
                        false) ?>"><?= do_nice_date($alarm['date_add']) ?></span>
                    </td>
                    <td>
                    <span title="<?= do_nice_date($alarm['date_alarm'],
                        false) ?>"><?= do_nice_date($alarm['date_alarm']) ?></span>
                    </td>
                    <td><?= get_user_name($alarm) ?></td>
                    <td><?= get_user_name(array(
                            'fio' => $alarm['fu_fio'],
                            'login' => $alarm['fu_login'],
                            'phone' => $alarm['fu_phone'],
                            'email' => $alarm['fu_email']
                        )) ?></td>
                    <td>
                        <?php if ($alarm['order_id'] > 0): ?>
                            <a href="<?= $this->all_configs['prefix'] . 'orders/create/' . $alarm['order_id'] ?>"><?= $alarm['order_id'] ?></a>
                        <?php endif; ?>
                    </td>
                    <td><?= cut_string($alarm['text']) ?></td>
                    <td>
                        <i onclick="remove_alarm(this, <?= $alarm['id'] ?>)"
                           class="glyphicon glyphicon-remove cursor-pointer"></i>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5"><?= l('Напоминаний нет') ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
