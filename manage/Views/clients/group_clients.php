<form method="POST" class="form-horizontal" id="group_clients_form">
    <p><?= l('К клиенту 1 будут перенесены данные (телефоны, звонки, заказы и т.д.) клиента 2.') ?> <br>
       <?= l('Эл. адрес, фио и контрагент переносятся от клиента 2 в том случае, если у клиента 1 эти поля пустые.') ?> <br>
       <?= l('После этого клиент 2 будет удален.') ?>
    </p><br>
    <div class="control-group">
        <label class="control-label"><?= l('Клиент') ?> 1: </label>
        <div class="controls">
            <?= typeahead($this->all_configs['db'], 'clients', false, isset($_GET['client_1']) ?
                $_GET['client_1'] : 0, 1, 'input-medium', 'input-small', '', true, false, '1') ?>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label"><?= l('Клиент') ?> 2: </label>
        <div class="controls">
            <?= typeahead($this->all_configs['db'], 'clients', false, isset($_GET['client_2']) ?
                $_GET['client_2'] : 0, 2, 'input-medium', 'input-small', '', true, false, '2') ?>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label"></label>
        <div class="controls">
            <input type="button" value="<?= l('Склеить') ?>" onclick="group_clients(this)"
                   class="btn btn-primary"/>
        </div>
    </div>
</form>
