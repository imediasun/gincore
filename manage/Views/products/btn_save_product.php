<?php if ($this->all_configs['oRole']->hasPrivilege('edit-goods')): ?>
    <div class="control-group">
        <div class="controls">
            <input class="btn btn-primary" type="submit" value="<?= l('Сохранить изменения') ?>"
                   name="edit-product-<?= $tab ?>">
            <?php if ($this->all_configs['configs']['save_goods-export_to_1c'] &&
                $this->all_configs['configs']['onec-use']
            ): ?>
                <label class="checkbox"><input type="checkbox" checked name="1c-export"/>
                    <?= l('Отправить в 1с') ?>
                </label>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <script>
        jQuery(document).ready(function ($) {
            $(":input:not(:disabled)").prop("disabled", true)
        });
    </script>
<?php endif; ?>
