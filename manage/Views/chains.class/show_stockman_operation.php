<tr class="<?= $global_class ?>">
    <?php if (!$compact): ?>
        <td>
            <?php if ($item['order_id'] > 0): ?>
                <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $item['order_id'] ?>"><?= $item['order_id'] ?></a>
            <?php endif; ?>
            <?= show_marked($item['order_id'], 'wso' . $type, $selected) ?>
            <?= show_marked($item['order_id'], 'woi', $selected_oi) ?>
        </td>
        <td>
            <i style="color:<?= htmlspecialchars($item['color']) ?>;" class="<?= htmlspecialchars($item['icon']) ?>"></i>
            <?= htmlspecialchars($item['name']) ?>
        </td>
        <td><?= h($item['location']) ?></td>
        <td><span title="<?= do_nice_date($item['date_add'], false) ?>"> <?= do_nice_date($item['date_add']) ?></span></td>
        <td>
            <a href="<?= $this->all_configs['prefix'] ?>products/create/<?= $item['goods_id'] ?>"> <?= htmlspecialchars($item['title']) ?></a>
            <i class="fa fa-arrows popover-info" data-content="<?= $state ?>"></i>
        </td>
    <?php endif; ?>
    <?php if ($type == 1): ?>
        <td></td>
        <td>
            <div class="input-group" style="<?= (!$compact ? 'max-width:200px;' : '') ?>">
                <?= $controller->select_bind_item_wh($item, $type, $serials); ?>
                <input class="form-control" type="text" value="" style="display:none;"
                       id="bind_item_serial_input-<?= $item['id'] ?>"/>
                <span class="input-group-btn" onclick="toogle_siblings(this, true)">
                    <button class="btn" type="button">
                        <i class="fa fa-keyboard-o"></i>
                    </button>
                </span>
            </div>
        </td>
    <?php endif; ?>
    <?php if ($type == 4): ?>
        <td><?= $controller->select_bind_item_wh($item, $type, $serials) ?></td>
    <?php endif; ?>
    <td>
        <?php if ($type == 1 && $item['item_id'] == 0): ?>
            <?php if (isset($isGroup) && $isGroup): ?>
                <input class="btn btn-xs bind-button" type="button" value="Привязать"
                       onclick="btn_bind_item_serial_for_group(this, '<?= $item['id'] ?>')" style=""/>
            <?php else: ?>
                <input class="btn btn-xs bind-button" type="button" value="Привязать"
                       onclick="btn_bind_item_serial(this, '<?= $item['id'] ?>')" style=""/>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($type == 4 && $item['item_id'] > 0): ?>
            <input class="btn btn-xs" type="button" value="Отвязать" data-o_id="<?= $item['item_id'] ?>"
                   onclick="alert_box(this, null, 'bind-move-item-form')"/>
        <?php endif; ?>
    </td>
</tr>
