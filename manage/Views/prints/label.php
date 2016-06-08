<?php if (!empty($product)): ?>
    <div class="label-box">
        <div class="label-box-code">
            <img
                src="<?= $this->all_configs['prefix'] . 'print.php?bartype=sn&barcode=' . suppliers_order_generate_serial($product); ?>"
                alt="S/N" title="S/N"/>
        </div>
        <div class="label-box-title"><?= h($product['title']) ?></div>
        <div class="label-box-order">
            <?= $this->all_configs['suppliers_orders']->supplier_order_number($product, null, false) ?>
        </div>
    </div>
<?php endif; ?>
