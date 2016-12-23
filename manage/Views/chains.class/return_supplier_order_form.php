<?php if ($this->all_configs['configs']['erp-use'] && $this->all_configs['oRole']->hasPrivilege('return-items-suppliers')): ?>
    <?php if ($for_sidebar): ?>
        <div class="well">
            <div class="clearfix">
                <div class="col-sm-8 p-l-n">
                    <h4><?= l('Возврат поставщику') ?></h4>
                </div>
                <div class="col-sm-4 p-r-n">
                    <form class="form-horizontal" method="post">
                        <?php if ($canUse): ?>
                            <input type="button" class="btn item_btn" onclick="return_item(this, <?= $item_id ?>, null, true)"
                                   value="<?= l('Вернуть') ?>"/>
                        <?php else: ?>
                            <input disabled type="submit" class="btn item_btn" value="<?= l('Вернуть') ?>"/>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="well" style="min-height: 118px">
            <h4><?= l('Возврат поставщику') ?></h4>
            <form class="form-horizontal" method="post">
                <?php if ($item_id === 0): ?>
                    <p><?= l('Всего выбрано изделий') ?>: <span class="count-selected-items">0</span></p>
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
<?php endif; ?>
