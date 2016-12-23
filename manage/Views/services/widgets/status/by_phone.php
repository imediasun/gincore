<?php if ($orders): ?>
    <?php foreach ($orders as $order): ?>
        <?php $status = isset($this->all_configs['configs']['order-status'][$order['status']])
        ? h($this->all_configs['configs']['order-status'][$order['status']]['name'])
        : ''; ?>
        <div class="gcw_status_order">
            <h2><?= l('Ремонт') ?> №<?= $order['id'] ?></h2>
            <p><b><?= l('Дата') ?></b>: <?= date("d/m/Y", strtotime($order['date_add'])) ?></p>
            <p><b><?= l('Статус') ?></b>: <?= $status ?></p>
            <p><b><?= l('Устройство') ?></b>: <?= h($order['title']) ?></p>
            <p><b><?= l('Серийный номер') ?></b>: <?= h($order['serial']) ?></p>

            <?php if ($order['comments']): ?>
                <table class="gcw_table gcw_table_stripped">
                    <thead>
                    <tr>
                        <td>
                            <center><?= l('Дата') ?></center>
                        </td>
                        <td><?= l('Текущий статус ремонта') ?></td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order['comments'] as $comment): ?>
                        <tr>
                            <td>
                                <center><?= date("d.m.Y<b\\r/>H:i", strtotime($comment['date_add'])) ?></center>
                            </td>
                            <td><?= h(wordwrap($comment['text'], 25, " ", true)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
