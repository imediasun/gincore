<button data-target="#create_call" data-toggle="modal" type="button" class="create_call_btn btn btn-success"
        style="padding: 5px 10px"><i class="fa fa-phone"></i></button>
<div id="create_call" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= l('Создать новый звонок') ?> <?= InfoPopover::getInstance()->createQuestion('l_create_new_call_info') ?></h4>
            </div>
            <form autocomplete="off" method="post" action="<?= $all_configs['prefix'] ?>services/ajax.php"
                  class="ajax_form">
                <input type="hidden" name="service" value="crm/calls">
                <input type="hidden" name="action" value="new_call">
                <div class="modal-body">
                    <?= l('Номер телефона') ?>: <br>
                    <?php $object_id = isset($id_client) ? $id_client : 0; ?>
                    <?= typeahead($all_configs['db'], 'clients', false, $object_id , 1001, 'input-xlarge', 'input-medium', '',
                    false, false, '', true, '', array(), false, input_phone_mask_attr()) ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?= l('Сохранить') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
