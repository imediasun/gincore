<?php if ($this->all_configs['configs']['erp-use'] && $this->all_configs['oRole']->hasPrivilege('write-off-items')): ?>

    <?php if ($for_sidebar): ?>
        <h4><?= l('Продажа изделия') ?></h4>
        <div class="well">

            <div class="clearfix">
                <div class="col-sm-5 p-l-n">
                    <label><?= l('Клиент') ?>:</label>
                </div>
                <div class="col-sm-3 p-l-n p-r-n">
                    <label><?= l('Стоимость') ?>:</label>
                </div>
                <div class="col-sm-4 p-r-n">

                </div>
            </div>

            <form method="post" id="sold-item-form" style="padding-bottom: 5px">
                <div class="clearfix">
                    <div class="col-sm-5 p-l-n">
                        <div class="form-group">
                            <?= typeahead($db, 'clients', false, 0, 2, 'fonm-control') ?>
                        </div>
                    </div>
                    <div class="col-sm-3 p-l-n p-r-n">
                        <div class="form-group">
                            <input type="text" name="price" required class="form-control"
                                   placeholder="<?= l('укажите стоимость') ?>"/>
                            <input type="hidden" name="total_as_sum" class="form-control" value="1"/>
                            <input type="hidden" name="set-order-status" class="form-control" value="0"/>
                        </div>
                    </div>
                    <div class="col-sm-4 p-r-n">
                        <?php if ($can): ?>
                            <input type="button" class="btn item_btn" onclick="sold_item(this, <?= $item_id ?>)"
                                   value="<?= l('Продать') ?>"/>
                        <?php else: ?>
                            <input disabled type="submit" class="btn item_btn" value="<?= l('Продать') ?>"/>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

    <?php else: ?>
        <div class="well">
            <h4><?= l('Продажа изделия') ?></h4>
            <?php if ($item_id === 0): ?>
                <p>Всего выбрано изделий: <span class="count-selected-items">0</span></p>
            <?php endif; ?>
            <form method="post" id="sold-item-form" style="padding-bottom: 5px">
                <div class="form-group"><label><?= l('Клиент') ?>:</label>
                    <?= typeahead($db, 'clients', false, 0, 2, 'fonm-control') ?>
                </div>
                <div class="form-group"><label><?= l('Стоимость') ?>:</label>
                    <input type="text" name="price" required class="form-control"
                           placeholder="<?= l('укажите стоимость') ?>"/>
                </div>
                <input type="hidden" name="total_as_sum" class="form-control" value="1"/>
                <input type="hidden" name="set-order-status" class="form-control" value="0"/>
                <?php if ($can): ?>
                    <input type="button" class="btn" onclick="sold_item(this, <?= $item_id ?>)"
                           value="<?= l('Продать') ?>"/>
                <?php else: ?>
                    <input disabled type="submit" class="btn" value="<?= l('Продать') ?>"/>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>


<?php endif; ?>

