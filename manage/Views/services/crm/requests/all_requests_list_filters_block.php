<style>
    .filter_block > input {
        max-width: 100% !important;
    }
</style>
<form class="filter_block" method="get" action="<?= $this->all_configs['prefix'] ?>clients">
    <input type="hidden" name="tab" value="requests">
    <div class="form-group">
        <label><?= l('Оператор') ?>:</label><br>
        <?= $operators ?>
    </div>
    <div class="form-group">
        <label><?= l('Статус') ?>:</label><br>
        <?= $controller->get_statuses_list(!empty($_GET['status_id']) ? $_GET['status_id'] : null, '', true) ?>
    </div>
    <div class="form-group">
        <label><?= l('Дата') ?>:</label>
        <input type="text" placeholder="<?= l('Дата') ?>" name="date" class="form-control daterangepicker"
               value="<?= $date ?>"/>
    </div>
    <div class="form-group">
        <label><?= l('Клиент') ?>:</label>
        <?= typeahead($this->all_configs['db'], 'clients', false, (!empty($_GET['clients']) ? $_GET['clients'] : 0), 2,
            'input-xlarge', 'input-medium', '', false, false, '') ?>
    </div>
    <div class="form-group">
        <label><?= l('номер заявки') ?>:</label>
        <input type="text" name="request_id" class="form-control" placeholder="<?= l('номер заявки') ?>"
               value="<?= (!empty($_GET['request_id']) ? (int)$_GET['request_id'] : '') ?>">
    </div>
    <div class="form-group">
        <label><?= l('Устройство') ?>:</label>
        <?= typeahead($this->all_configs['db'], 'categories-goods', false,
            isset($_GET['categories-goods']) ? (int)$_GET['categories-goods'] : 0, '', 'input-xlarge', '', '', false,
            false, '') ?>
    </div>
    <input type="submit" class="btn btn-primary" value="<?= l('Фильтровать') ?>">
</form>
