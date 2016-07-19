<?php if ($this->all_configs['configs']['erp-use']): ?>
    <form method="post" id="moving-item-form-<?= $rand ?>">
        <?php if ($item_id === 0 && $order === null): ?>
            <p>Всего выбрано изделий: <span class="count-selected-items">0</span></p>
        <?php endif; ?>
        <?php if ($item_id > 0): ?>
            <input type="hidden" name="item_id" value="<?= $item_id ?>"/>
        <?php endif; ?>
        <?php if ($goods_id > 0): ?>
            <input type="hidden" name="goods_id" value="<?= $goods_id ?>"/>
        <?php endif; ?>
        <?php if ($item_id === 0 && is_array($order) && array_key_exists('id', $order) && intval($order['id']) == 0): ?>
            <div class="form-group relative"><label><?= l('Серийный номер') ?>:</label>
                <div class="serial_input">
                    <?= typeahead($this->all_configs['db'], 'serials', false, 0, 3, 'input-small clone_clear_val', '',
                        'display_serial_product', true) ?>
                </div>
                <i class="fa fa-plus cloneAndClear" data-clone_siblings=".serial_input"
                   style="position:relative;margin:5px 0 0 0 !important" title="<?= l('Добавить') ?>"></i></div>
            <small class="clone_clear_html product-title"></small>
        <?php endif; ?>
        <?php if (is_array($order) && array_key_exists('id', $order) && array_key_exists('status', $order)): ?>
            <div class="form-group"><label class="control-label"><?= l('Номер ремонта') ?>:</label>
                <div class="controls">
                    <input name="order_id" type="text" value="<?= $order['id'] ?>"
                           placeholder="<?= l('Номер ремонта') ?>"
                           class="form-control"/></div>
            </div>
        <?php endif; ?>
        <?php if ($item_id === null): ?>
            <div class="form-group"><label>Количество:</label>
                <input class="form-control" type="text" maxlength="2" placeholder="Количество" name="count"
                       onkeydown="return isNumberKey(event)" value="1"/>
            </div>
            <div class="form-group"><label>Склад откуда:</label>
                <select class="select-warehouses-item-move form-control" name="wh_id">
                    <?= $controller->get_options_for_move_item_form($with_logistic); ?>
                </select></div>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders') || $this->all_configs['oRole']->hasPrivilege('engineer')): ?>
            <div class="form-group"><label><?= l('Склад куда') ?>:</label>
                <select onchange="change_warehouse(this)" class="form-control select-warehouses-item-move"
                        name="wh_id_destination">
                    <?= $controller->get_options_for_move_item_form($with_logistic, $wh_id); ?>
                </select></div>

            <div class="form-group"><label><?= l('Локация') ?>:</label><br>
                <select class="multiselect form-control select-location" name="location">
                    <?= $this->all_configs['suppliers_orders']->gen_locations($wh_id) ?>
                </select></div>
        <?php endif; ?>
        <?php if (is_array($order) && array_key_exists('id', $order) && array_key_exists('status', $order)): ?>
            <div class="control-group"><label class="control-label"><?= l('Статус') ?>:</label>
                <div class="controls">
                    <?= $controller->order_status($order['status'], true) ?>
                </div>
            </div>
            <div class="control-group"><label class="control-label"><?= l('Публичный комментарий') ?>:</label>
                <div class="controls">
                    <textarea name="public_comment" class="form-control"></textarea></div>
            </div>
            <div class="control-group"><label class="control-label"><?= l('Скрытый комментарий') ?>:</label>
                <div class="controls">
                    <textarea name="private_comment" class="form-control"></textarea></div>
            </div>
        <?php endif; ?>
        <?php if ($show_btn || $this->all_configs['configs']['erp-move-item-logistics'] == true): ?>
            <div class="control-group">
                <label class="control-label">
                    <?php if ($show_btn): ?>
                        <?php $attr = $controller->can_use_item($item_id) ? 'onclick="move_item(this, ' . $rand . ')"' : 'disabled'; ?>
                        <input <?= $attr ?> type="button" value="<?= l('Создать') ?>" class="btn"/>
                    <?php endif; ?>
                </label>
                <?php if ($this->all_configs['configs']['erp-move-item-logistics'] == true): ?>
                    <div class="controls">
                        <label class="checkbox">
                            <?php if ($with_logistic): ?>
                                <input type="hidden" name="logistic" value="1"/>
                                <input checked disabled type="checkbox" value="1"/>
                            <?php else: ?>
                                <input onchange="item_move_logistic(this)" type="checkbox" name="logistic" value="1"/>
                            <?php endif; ?>
                            С участием логистики
                        </label></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </form>
<?php endif; ?>
