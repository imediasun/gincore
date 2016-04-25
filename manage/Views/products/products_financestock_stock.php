<?php if ($counts): ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <td><?= l('Склад') ?></td>
            <td><?= l('Общий остаток') ?></td>
            <td><?= l('Свободный остаток') ?></td>
        </tr>
        </thead>
        <tbody>
        <?php
        $all_qty_wh = 0;
        $all_qty_store = 0; ?>
        <?php foreach ($counts as $vgw): ?>
            <?php $vgw['qty_store'] = $vgw['qty_store'] > 0 ? $vgw['qty_store'] : 0; ?>
            <?php $all_qty_wh += intval($vgw['qty_wh']);
            $all_qty_store += intval($vgw['qty_store']); ?>
            <tr>
                <td>
                    <a href="<?= $this->all_configs['prefix'] . 'warehouses?pid=' . $this->all_configs['arrequest'][2] . '&whs=' . $vgw['wh_id'] . '#show_items' ?>"><?= htmlspecialchars($vgw['title']) ?></a>
                </td>
                <td><?= intval($vgw['qty_wh']) ?></td>
                <td><?= intval($vgw['qty_store']) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td><b><?= l('Всего') ?></b></td>
            <td><?= $all_qty_wh ?></td>
            <td><?= $all_qty_store ?></td>
        </tr>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-error"><?= l('Нет информации') ?></p>
<?php endif; ?>
