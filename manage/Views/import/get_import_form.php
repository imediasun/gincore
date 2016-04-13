<?php if ($type): ?>
    <div class="form-group">
        <label><?= l('Провайдер') ?></label>
        <input type="hidden" name="handler" value="<?= reset(array_keys($options[$type]['handlers'])) ?>"/>
        <?php if (false): ?>
            <?php // закоментировал возможность выбирать из списка провайдеров ?>
            <select class="form-control" name="handler">
                <?= $this->renderFile('import/gen_types_select_options', array(
                    'selected' => $type,
                    'options' => $options[$type]['handlers']
                )); ?>
            </select>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <a href="<?= $this->all_configs['prefix'] ?>modules/import/templates/<?= $type ?>.csv">
            <i class="fa fa-file-excel-o"
               aria-hidden="true"></i>&nbsp;<?= l(sprintf('Скачать образец файла для импорта %s', $type)) ?>
        </a>
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
            <p class="bg-danger" style="padding: 15px">
                <?= l('Внимание!') ?> <br><br>
                <?= l('Перед импортом файла, обязательно добавьте в систему Gincore сотрудников.') ?><br>
                <?= l('ФИО сотрудника в Gincore долно совпадать с именем сотрудника в файле иморта.') ?><br>
                <?= l('Иначе система не сможет привязать к заказам Приемщика, Инженера, Менеджера.') ?>
            </p>
        </div>
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
