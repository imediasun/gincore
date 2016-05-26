<div class="clearfix">
    <form class='date-filter form-inline' method='get'>
        <div class='input-group'>
            <input type='text' name='d' class='form-control daterangepicker_single' value='<?= $day_html ?>'/>
            <span class='input-group-btn'> <input class='btn' type='submit' value='<?= l(' Применить') ?>'/></span>
        </div>

        <?php if ($amounts_by_day): ?>
            <p>На <?= $day ?>. <?= l('Всего') ?>:
                <?php if ($this->all_configs['configs']['manage-actngs-in-1-amount']): ?>
                    <?= show_price($all_amount) . (empty($out_amounts) ? '' : ' (' . $out_amounts . ')') ?>
                <?php else: ?>
                    <?= (empty($out_amounts) ? '' : $out_amounts) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </form>
    <p><?= l('Всего') ?>
        : <?= ($this->all_configs['configs']['manage-actngs-in-1-amount'] ? show_price($amounts['all']) : ''); ?>
        <?= $this->all_configs['configs']['manage-actngs-in-1-amount'] == true ? ' (' : ''; ?>
        <?= $total_cashboxes['html']; ?>
        <?= $this->all_configs['configs']['manage-actngs-in-1-amount'] == true ? ')' : ''; ?>
    <p>
</div>
<div class="clearfix">
    <div class="cashbox-tables">
    <?php if (count($cashboxes) > 0): ?>
        <table class="cashboxes-table m-b-md m-t-md">
            <tbody>
            <tr>
                <?php foreach ($cashboxes as $cashbox): ?>
                    <?php if ($controller->cashboxAvailable($cashbox)): ?>
                        <td><h4 class="center" style="max-width:150px"><?= $cashbox['name'] ?></h4></td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>

            <?php foreach ($currencies as $cur_id => $currency): ?>
                <tr>
                    <?php if ($cashboxes_cur): ?>
                        <?php foreach ($cashboxes_cur as $cashbox_cur): ?>
                            <?php if(array_key_exists($cur_id, $cashbox_cur)): ?>
                                <td class="text-success center cashbox-currency-value">
                                    <div><?= $cashbox_cur[$cur_id] ?></div>
                                </td>
                            <?php else: ?>
                                <td></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <tr>
                <?php foreach ($cashboxes as $cashbox): ?>
                    <?php if ($controller->cashboxAvailable($cashbox)): ?>
                        <td>
                            <div class="btns-cashbox">
                                <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
                                    <div>
                                        <button data-o_id="<?= $cashbox['id'] ?>"
                                                onclick="alert_box(this, false, 'begin-transaction-1')"
                                                class="btn btn-default btn-cashboxes"><?= l('Выдача') ?></button>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <button data-o_id="<?= $cashbox['id'] ?>"
                                            onclick="alert_box(this, false, 'begin-transaction-2')"
                                            class="btn btn-default btn-cashboxes"><?= l('Внесение') ?></button>
                                </div>
                                <div>
                                    <button data-o_id="<?= $cashbox['id'] ?>"
                                            onclick="alert_box(this, false, 'begin-transaction-3')"
                                            class="btn btn-default btn-cashboxes"><?= l('Перемещение') ?></button>
                                </div>
                                <div>
                                    <button
                                        onclick="javascript:window.location.href='<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0]; ?>?cb=<?= $cashbox['id'] ?>#transactions'"
                                        class="btn btn-default btn-cashboxes"><?= l('Отчеты') ?></button>
                                </div>
                            </div>
                        </td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="add-cashbox-table" onclick="alert_box(this, false, 'create-cashbox')" data-toggle="tooltip" data-placement="top" title="<?=l('Добавить кассу')?>">
        <img src="<?=$prefix?>img/add_new_cashbox.png">
    </div>
</div>
<?php else: ?>
    <p class="text-error"><?= l('Нет касс') ?></p>
<?php endif; ?>

<?= $this->all_configs['transactions']->get_transactions($currencies, true, 30); ?>
