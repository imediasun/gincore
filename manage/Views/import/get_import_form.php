<?php if ($type): ?>
    <div class="form-group">
        <label><?= l('Провайдер') ?></label>
        <select class="form-control" name="handler">
            <?= $this->renderFile('import/gen_types_select_options', array(
                'selected' => $type,
                'options' => $options[$type]['handlers']
            )); ?>
        </select>
    </div>
<?php endif; ?>
<?php switch ($type): ?>
<?php case 'items': ?>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="accepter_as_manager" value="1">
                    <?= l('назначить приемщика менеджером, если последний не указан') ?>
                </label>
            </div>
        </div>
        <?php break; ?>
    <?php case 'orders': ?>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input<?= ($hasOrders ? ' disabled' : '') ?> type="checkbox" name="clear_categories"
                                                                          value="1">
                    <?= l('очистить категории (и товары) и заменить категориями с импорта') ?>
                </label>
            </div>
        </div>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="accepter_as_manager" value="1">
                    <?= l('назначить приемщика менеджером, если последний не указан') ?>
                </label>
            </div>
        </div>
        <?php break; ?>
    <?php endswitch; ?>
