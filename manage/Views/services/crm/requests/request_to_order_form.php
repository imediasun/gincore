<div id="add_order_to_request" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" class="ajax_form" action="<?= $this->all_configs['prefix'] ?>services/ajax.php">
                <input type="hidden" name="service" value="crm/requests">
                <input type="hidden" name="action" value="requests_to_order">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><?= l('Привязать заявку к заказу') ?></h4>
                </div>
                <div class="modal-body">
                    <?= l('Введите номер заказа') ?>: <br>
                    <input type="hidden" name="request_id" id="order_to_request_id" value="" />
                    <input type="text" name="order_id" class="form-control" />
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?= l('Привязать') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
