<table class="table table-striped">
    <thead>
    <tr>
        <td></td>
        <td><?= l('Сер. номер') ?></td>
        <td><?= l('Наименование') ?></td>
        <td><?= l('Склад') ?></td>
        <td><?= l('Цена') ?>,
            <?= $this->all_configs['suppliers_orders']->currencies[$this->all_configs['suppliers_orders']->currency_suppliers_orders]['shortName']; ?>
        </td>
        <td><input type="checkbox" class="checked_all_writeoff" onchange="checked_all_writeoff(this)"/></td>
    </tr>
    </thead>
    <tbody>

    <tr>
        <?php if ($inventories): ?>
            <?php $i = 1; ?>
            <?php foreach ($inventories as $inv): ?>
                <?php $inv['scanned'] = suppliers_order_generate_serial($inv); ?>
                <?php $inv['i'] = $i; ?>
                <?php $i++; ?>
                <?= $controller->display_scanned_item($inv, $inv['inv_wh_id']); ?>

            <?php endforeach; ?>
            <td colspan="6">
                <input class="btn" onclick="write_off_item(this)" value="<?= l('Списать') ?>" type="button"/>
            </td>
        <?php else: ?>
            <td colspan="6"><?= l('Нет сканированых изделий') ?></td>
        <?php endif; ?>
    </tr>
    </tbody>
</table>
