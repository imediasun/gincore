<table class="table table-striped table-compact">
    <thead>
    <tr>
        <td></td>
        <td><?= l('Дата') ?></td>
        <td><?= l('Касса') ?></td>
        <td><?= l('Контрагент') ?></td>
        <td><?= l('Заказ клиента') ?></td>
        <td><?= l('Заказ поставщика') ?></td>
        <?php if ($contractors): ?>
            <td><?= l('Транзакция') ?></td>
            <td><?= l('Доход') ?></td>
            <td><?= l('Расход') ?></td>
            <td><?= l('Серийник') ?></td>
        <?php else: ?>
            <td><?= l('Цепочка') ?> <?= InfoPopover::getInstance()->createQuestion('l_transaction_chain_info') ?></td>
            <td><?= l('Доход') ?> <?= InfoPopover::getInstance()->createQuestion('l_transaction_income_info') ?></td>
            <td><?= l('Расход') ?> <?= InfoPopover::getInstance()->createQuestion('l_transaction_expence_info') ?></td>
        <?php endif; ?>
        <td><?= l('Ответственный') ?></td>
        <td><?= l('Примечание') ?></td>
    </tr>
    </thead>
    <tbody>
