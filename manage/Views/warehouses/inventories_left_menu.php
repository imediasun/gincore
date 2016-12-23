<?php if ($inventory): ?>
    <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>#inventories-list">
        &#8592; <?= l('к списку') ?>
    </a>
    <p><?= l('Инвентаризация номер') ?><?= $inventory['id'] ?></p>
    <p><?= l('Склад') ?>:
        <a href="<?= $this->all_configs['prefix'] ?>warehouses?whs=<?= $inventory['wh_id'] ?>#show_items">
            <?= h($inventory['title']) ?>
        </a>
    </p>
    <p>
        <?= l('Дата открытия') ?>: <span title="<?= do_nice_date($inventory['date_start'],
            false) ?>"><?= do_nice_date($inventory['date_start']) ?></span>
    </p>
    <?php if ($inventory['date_stop'] > 0): ?>
        <p>
            <?= l('Дата закрытия') ?>: <span title="<?= do_nice_date($inventory['date_stop'],
                false) ?>"><?= do_nice_date($inventory['date_stop']) ?></span>
        </p>
    <?php else: ?>
        <input onclick="close_inventory(this, '<?= $inventory['id'] ?> ')"
               type="button" <?= ($user_id == $inventory['user_id'] ? '' : 'disabled') ?> value="<?= l('Закрыть') ?>"
               class="btn close-inv"/>
    <?php endif; ?>
    <div class="btn-group">
        <select class="multiselect-goods multiselect-goods-tab-<?= $active_btn ?>" multiple="multiple"></select>
        <button onclick="add_goods_to_inv(this, <?= $active_btn ?>)" class="btn btn-primary"><?= l('Ок') ?></button>
    </div>
    <br/><br/>
    <div class="btn-group" data-toggle="buttons-radio">
        <div>
            <button type="button" onclick="click_tab_hash('#inventories-journal')"
                    class="btn <?= ($active_btn == 1 ? 'active' : '') ?>"><?= l('Журнал.') ?>
            </button>
        </div>
        <div>
            <button type="button" onclick="click_tab_hash('#inventories-listinv')"
                    class="btn <?= ($active_btn == 2 ? 'active' : '') ?>"><?= l('Лист инв.') ?> </button>
        </div>
        <div>
            <button type="button" onclick="click_tab_hash('#inventories-writeoff')"
                    class="btn <?= ($active_btn == 3 ? 'active' : '') ?>"><?= l('Списание') ?> </button>
        </div>
    </div>
<?php endif; ?>
