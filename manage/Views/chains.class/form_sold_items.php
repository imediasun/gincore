<?php if ($this->all_configs['configs']['erp-use'] && $this->all_configs['oRole']->hasPrivilege('write-off-items')): ?>
    <div class="well"><h4><?= l('Продажа изделия') ?></h4>
        <?php if ($item_id === 0): ?>
            <p>Всего выбрано изделий: <span class="count-selected-items">0</span></p>
        <?php endif; ?>
        <form method="post" id="sold-item-form">
            <div class="form-group"><label><?= l('Клиент') ?>:</label>
                <?= typeahead($db, 'clients', false, 0, 2, 'fonm-control') ?>
            </div>
            <div class="form-group"><label><?= l('Стоимость') ?>:</label>
                <input type="text" name="price" required class="form-control"
                       placeholder="<?= l('укажите стоимость') ?>"/>
            </div>
            <?php if ($can): ?>
                <input type="button" class="btn" onclick="sold_item(this, <?= $item_id ?>)"
                       value="<?= l('Продать') ?>"/>
            <?php else: ?>
                <input disabled type="submit" class="btn" value="<?= l('Продать') ?>"/>
            <?php endif; ?>
        </form>
    </div>
<?php endif; ?>

