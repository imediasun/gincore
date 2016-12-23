<div class="row-fluid">
    <div class="span2">
        <?= $controller->all_requests_list_filters_block() ?>
    </div>
    <div class="span10">
        <form method="post" class="ajax_form" action="<?= $this->all_configs['prefix'] ?>services/ajax.php">
            <input type="hidden" name="service" value="crm/requests">
            <input type="hidden" name="action" value="save_requests">
            <input type="hidden" name="requests_ids" value="<?= implode(',', array_keys($req_data[0])) ?>">
            <table class="table table-hover table-striped">
                <thead>
                <tr>
                    <th>id</th>
                    <th><?= l('оператор') ?></th>
                    <th><?= l('клиент') ?></th>
                    <th><?= l('Дата') ?></th>
                    <th><?= l('Статус') ?></th>
                    <th><?= l('ссылка') ?></th>
                    <th><?= l('устройство') ?></th>
                    <th><?= l('комментарий') ?></th>
                    <th><?= l('номер ремонта') ?></th>
                    <th style="text-align:center">SMS</th>
                </tr>
                </thead>
                <tbody>
                <?= $list ?>
                </tbody>
            </table>
            <input id="save_all_fixed" class="btn btn-primary" type="submit" value="<?= l('Сохранить изменения') ?>">
        </form>
        <?= $controller->request_to_order_form() ?>
        <?= page_block($count_pages, $req_data[1]) ?>
        <?= get_service('crm/sms')->get_form('requests') ?>
    </div>
</div>
