<table class="table table-hover table-striped">
    <thead>
    <tr>
        <th>id</th>
        <th><?= l('Телефон') ?></th>
        <th><?= l('Заявок') ?></th>
        <th><?= l('Метки') ?></th>
        <th><?= l('Клиент') ?></th>
        <th><?= l('Оператор') ?></th>
        <th><?= l('Статус') ?></th>
        <th><?= l('Канал') ?></th>
        <th><?= l('Код') ?></th>
        <th><?= l('Дата') ?></th>
    </tr>
    </thead>
    <tbody><?= $list_items ?></tbody>
</table>
<?= page_block($count_pages, $counts) ?>
