<tr class="<?= $global_class ?>">
    <?php if (!$compact): ?>
        <td>
            <?php if ($op['order_id'] > 0): ?>
                <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $op['order_id'] ?>"><?= $op['order_id'] ?></a>
            <?php endif; ?>
            <?= show_marked($op['order_id'], 'wso' . $type, $selected) ?>
            <?= show_marked($op['order_id'], 'woi', $selected_oi) ?>
        </td>
        <td><span title="<?= do_nice_date($op['date_add'], false) ?>"> <?= do_nice_date($op['date_add']) ?></span></td>
        <td>
            <a href="<?= $this->all_configs['prefix'] ?>products/create/<?= $op['goods_id'] ?>"> <?= htmlspecialchars($op['title']) ?></a>
            <i class="fa fa-arrows popover-info" data-content="<?= $state ?>"></i>
        </td>
        <?php if ($type == 1): ?>
            <td><?= ($op['warehouse_type'] == 1 ? 'Локально' : ($op['warehouse_type'] == 2 ? 'Заграница' : '')) ?></td>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($type == 1): ?>
        <td></td>
        <td>
            <div class="input-group" style="<?= (!$compact ? 'max-width:200px;' : '') ?>">
                <?= $controller->select_bind_item_wh($op, $type, $serials); ?>
                <input class="form-control" type="text" value="" style="display:none;"
                       id="bind_item_serial_input-<?= $op['id'] ?>"/>
                <span class="input-group-btn" onclick="toogle_siblings(this, true)">
                    <button class="btn" type="button">
                        <i class="fa fa-keyboard-o"></i>
                    </button>
                </span>
            </div>
        </td>
    <?php endif; ?>
</tr>
<?php return ; ?>
    <?php if ($type == 4): ?>
        <td><?= $controller->select_bind_item_wh($op, $type, $serials) ?></td>
    <?php endif; ?>
    <td>
        <?php if ($type == 1 && $op['item_id'] == 0): ?>
            <input class="btn btn-xs" type="button" value="Привязать"
                   onclick="btn_bind_item_serial(this, '<?= $op['id'] ?>')"/>
        <?php endif; ?>
        <?php if ($type == 4 && $op['item_id'] > 0): ?>
            <input class="btn btn-xs" type="button" value="Отвязать" data-o_id="<?= $op['item_id'] ?>"
                   onclick="alert_box(this, null, 'bind-move-item-form')"/>
        <?php endif; ?>
    </td>
    <td>
        <i style="color:<?= htmlspecialchars($op['color']) ?>;" class="<?= htmlspecialchars($op['icon']) ?>"></i>
        <?= htmlspecialchars($op['name']) ?>
    </td>
</tr>
