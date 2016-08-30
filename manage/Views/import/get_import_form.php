<?php if ($type): ?>
    <div class="form-group">
        <input type="hidden" name="handler" value="<?= reset($handlers) ?>"/>
        <?php if (false): ?>
            <?php // закоментировал возможность выбирать из списка провайдеров ?>
            <label><?= l('Провайдер') ?></label>
            <select class="form-control" name="handler">
                <?= $this->renderFile('import/gen_types_select_options', array(
                    'selected' => $type,
                    'options' => $options[$type]['handlers']
                )); ?>
            </select>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <?php if ($type !== 'gincore_items'): ?>
            <?php if (file_exists(GINCORE_ROOT . '/manage/modules/import/templates/' . $type . '.xls')): ?>
                <?php $extension = '.xls'; ?>
            <?php else: ?>
                <?php $extension = '.csv'; ?>
            <?php endif; ?>
            <a href="<?= $this->all_configs['prefix'] ?>modules/import/templates/<?= $type . $extension ?>">
                <i class="fa fa-file-excel-o"
                   aria-hidden="true"></i>&nbsp;<?= l(sprintf('Скачать образец файла для импорта %s', $type)) ?>
            </a>
        <?php else: ?>
            <?= l('Для импорта принимаются только файлы XLS ранее экспортированные из базы Gincore.'); ?> <br>
            <?= l('В файле обязательно должно присутствовать поле ID.'); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?= $body ?>
