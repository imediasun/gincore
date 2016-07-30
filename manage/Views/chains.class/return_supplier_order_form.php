<?php if ($this->all_configs['configs']['erp-use'] && $this->all_configs['oRole']->hasPrivilege('return-items-suppliers')): ?>
    <div class="well" style="min-height: 118px">
        <h4><?= l('Возврат поставщику') ?></h4>
        <form class="form-horizontal" method="post">
            <?php if ($item_id === 0): ?>
                <p>Всего выбрано изделий: <span class="count-selected-items">0</span></p>
            <?php endif; ?>
            <?php if ($canUse): ?>
                <input type="button" class="btn" onclick="return_item(this,'<?= $item_id ?>')"
                       value="<?= l('Вернуть') ?>"/>
            <?php else: ?>
                <input disabled type="submit" class="btn" value="<?= l('Вернуть') ?>"/>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>
