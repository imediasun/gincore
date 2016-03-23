<div id="new_call" class="tab-pane active">
    <div style="max-width:800px;">
        <h3><?= l('Звонок') ?> №<?= $new_call_id ?></h3>
        <form class="ajax_form" method="get" action="<?= $this->all_configs['prefix'] ?>clients/ajax/">
            <input type="hidden" name="act" value="short_update_client">
            <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
            <input type="hidden" name="call_id" value="<?= $new_call_id ?>">
            <div class="row-fluid">
                <div class="span4">
                    <?= l('Заказчик') ?>: <br>
                    <input class="form-control" type="text" value="<?= htmlspecialchars($client['fio']) ?>" name="fio" /><br>
                </div>
                <div class="span4">
                    <?= l('Телефоны') ?>:  <br>
                    <?= $this->phones($client['id']) ?>
                    <i style="display:inline-block!important;position:relative;margin:-5px 0 0 0!important" class="cloneAndClear icon-plus"></i>
                </div>
                <div class="span4">
                    <?= l('Эл. адрес') ?>: <br>
                    <input class="form-control" type="text" value="<?= htmlspecialchars($client['email']) ?>" name="email" />
                </div>
            </div>
            <div class="row-fluid">
                <div class="span4" style="position:relative">
                    <?= l('Код') ?>:
                    <span class="text-success <?= (!$code_exists || !$code ? 'hidden' : '') ?> code_exists">(<?= l('найден') ?>)</span>
                    <span class="text-error <?= ($code_exists || !$code ? 'hidden' : '') ?> code_not_exists">(<?= l('не найден') ?>)</span>
                    <br>
                    <input style="margin-right:100px;max-width:85%;background-color:<?= ($code ? (!$code_exists ? '#F0BBC5' : '#D8FCD7') : '') ?>" class="form-control call_code_mask" type="text" name="code" value="<?= $code ?>"><br>
                    <div style="position: absolute;top: 25px;right: -5px;">
                        <?= l('или') ?>
                    </div>
                </div>
                <div class="span4">
                    <?= l('Источник') ?>: <br>
                    <?= get_service('crm/calls')->get_referers_list(isset($calldata['referer_id']) ? $calldata['referer_id'] : null) ?><br>
                </div>
                <div class="span4">
                    <br>
                    <input type="submit" value="<?= l('Сохранить данные о звонке') ?>" class="btn btn-info"><br>
                </div>
            </div>
        </form>
        <hr>
        <?= get_service('crm/requests')->get_new_request_form_for_call($client['id'], $new_call_id) ?>
    </div>
</div>
