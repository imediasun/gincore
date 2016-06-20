<div id="request_sms" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form data-callback="send_sms_callback" method="post" class="ajax_form"
                  action="<?= $this->all_configs['prefix'] ?>services/ajax.php">
                <input type="hidden" name="service" value="crm/sms">
                <input type="hidden" name="action" value="send_sms">
                <input type="hidden" name="type" value="<?= $sms_type ?>">
                <input type="hidden" name="object_id" id="sms_object" value="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3><?= l('Отправить смс') ?></h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><?= l('Отправитель') ?></label>
                        <?= $this->renderFile('services/crm/sms/senders_list', array(
                            'senders' => $senders
                        )) ?>
                    </div>
                    <div class="form-group">
                        <label><?= l('Телефон') ?></label>
                        <input id="sms_phone" type="text" class="form-control" name="phone">
                    </div>
                    <div class="form-group">
                        <label><?= l('Шаблон') ?></label>
                        <?= $this->renderFile('services/crm/sms/templates_list', array(
                            'templates' => $templates
                        )) ?>
                        <textarea id="sms_body" name="body" style="min-width:80%" class="form-control"
                                  rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?= l('Отправить') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
