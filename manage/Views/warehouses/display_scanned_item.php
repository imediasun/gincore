<?php if (!empty($inv)): ?> {
    <?php $class = ($wh_id != $inv['wh_id']) ? 'error' : (array_key_exists('date_scan',
        $inv) && $inv['date_scan'] > 0 ? 'success' : ''); ?>
    <tr class="<?= $class ?>">
        <td><?= $inv['i'] ?></td>
        <?php if (array_key_exists('scanned', $inv)): ?>
            <td>
                <a href="<?= $this->all_configs['prefix'] ?>warehouses?serial=<?= $inv['scanned'] ?>#show_items">
                    <?= h($inv['scanned']) ?>
                </a>
            </td>
        <?php endif; ?>
        <?php if (array_key_exists('date_scan', $inv)): ?>
            <td>
            <span title="<?= do_nice_date($inv['date_scan'], false) ?>">
                <?= do_nice_date($inv['date_scan']) ?>
            </span>
            </td>
        <?php endif; ?>
        <?php if (array_key_exists('gtitle', $inv)): ?>
            <td>
                <a href="<?= $this->all_configs['prefix'] ?>products/create/<?= $inv['goods_id'] ?>"
                   data-action="sidebar_product" data-id_product="<?= $inv['goods_id'] ?>">
                    <?= h($inv['gtitle']) ?>
                </a>
            </td>
        <?php endif; ?>
        <?php if (array_key_exists('fio', $inv)): ?>
            <td><?= get_user_name($inv) ?></td>
        <?php endif; ?>
        <td class="<?= (empty($class) ? 'assumption' : '') ?>"><?= h($inv['wtitle']) ?></td>
        <?php if (array_key_exists('order_id', $inv)): ?>
            <td>
                <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $inv['order_id'] ?>">
                    <?= $inv['order_id'] ?>
                </a>
            </td>
        <?php endif; ?>
        <?php if (array_key_exists('price', $inv)): ?>
            <td><?= show_price($inv['price']) ?></td>
        <?php endif; ?>
        <?php if (array_key_exists('write_off_item_id', $inv)): ?>
            <td><input type="checkbox" value="<?= $inv['item_id'] ?>" class="writeoff-items"/></td>
        <?php endif; ?>
    </tr>
<?php endif; ?>
