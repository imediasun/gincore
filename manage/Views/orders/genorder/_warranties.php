<div class="form-group clearfix">
    <label class="lh30">
    <span class="cursor-pointer glyphicon glyphicon-list muted"
          onclick="alert_box(this, false, 'changes:update-order-warranty')"
          data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>">
    </span>
        <?= l('Гарантия') ?>:
    </label>
    <div class="tw100">
        <?= $this->renderFile('orders/genorder/_warranty_select', array(
            'order' => $order,
            'orderWarranties' => $orderWarranties
        )); ?>
    </div>
</div>
