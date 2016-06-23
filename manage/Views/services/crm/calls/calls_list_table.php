<form method="post" class="ajax_form" action="<?= $this->all_configs['prefix'] ?>services/ajax.php">
    <input type="hidden" name="service" value="crm/calls">
    <input type="hidden" name="action" value="save_calls">
    <input type="hidden" name="client_id" value="<?= $client_id ?>">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>id</th>
            <th><?= l('Оператор') ?></th>
            <th><?= l('Статус') ?></th>
            <th><?= l('Канал') ?></th>
            <th><?= l('Код') ?></th>
            <th><?= l('Дата') ?></th>
            <th><?= l('Создать заявку') ?></th>
        </tr>
        </thead>
        <tbody><?= $list_items ?></tbody>
    </table>
    <input id="save_all_fixed" class="btn btn-primary" type="submit" value="<?= l('Сохранить изменения') ?>">
</form>
