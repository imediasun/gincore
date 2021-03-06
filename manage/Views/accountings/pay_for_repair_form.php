<div class="row-fluid">
    <div class="col-sm-6" style="margin-bottom: 0">
        <table class="table table-borderless table-compact" style="margin-bottom: 0; font-size: 13px">
            <tr>
                <td> <?= l('Стоимость ремонта') ?> </td>
                <td> <?= $order['sum'] / 100 ?> <?= viewCurrency(); ?> </td>
            </tr>
            <tr>
                <td> <?= l('Оплачено') ?> </td>
                <td> <?= $order['sum_paid'] / 100 ?> <?= viewCurrency(); ?> </td>
            </tr>
            <tr>
                <td> <?= l('Скидка') ?> </td>
                <td> <?= $order['discount'] / 100 ?> <?= viewCurrency(); ?> </td>
            </tr>
            <tr>
                <td> <?= l('Не оплачено') ?> </td>
                <td> <?= $amount_to ?> <?= viewCurrency(); ?> </td>
            </tr>
        </table>
    </div>
</div>
<hr>
<form method="post" id="transaction_form">
    <fieldset>
        <input type="hidden" name="transaction_type" id="transaction_type" value="<?= TRANSACTION_INPUT ?>"/>
        <input type="hidden" name="client_order_id" value="<?= $co_id ?>"/>
        <input type="hidden" name="b_id" value="<?= $b_id ?>"/>
        <input type="hidden" name="transaction_extra" value="<?= $t_extra ?>"/>
        <input type="hidden" name="cashbox_currencies_to"
               value="<?= $this->all_configs['settings']['currency_orders'] ?>"/>

        <div id="transaction_form_body" class="hide-conversion-3 transaction_type-repair">
            <table>
                <thead>
                <tr>
                    <td></td>
                    <td></td>
                    <td style="text-align: center">
                        <?= l('Сумма') ?>
                    </td>
                    <td style="text-align: center">
                        <input type="hidden" id="pay_for_repair_discount_type" name="discount_type" value="1"/>
                        <div class="dropdown dropdown-inline">
                            <button class="as_link" type="button" id="dropdownMenuCashboxes"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <span class="btn-title-discount_type"><?= l('%') ?></span>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuCashboxes">
                                <li><a href="#" data-discount_type="1"
                                       onclick="return select_discount_type(this) || recalculate_amount_pay()"><?= l('%') ?></a>
                                </li>
                                <li><a href="#" data-discount_type="2"
                                       onclick="return select_discount_type(this) || recalculate_amount_pay()"><?= l('$') ?></a>
                                </li>
                            </ul>
                        </div>
                    </td>
                    <td style="text-align: center"><?= l('К оплате') ?></td>
                </tr>
                </thead>
                <tbody>

                <tr class="hide-not-tt-1">
                    <td width="20%">
                        * <?= l('В кассу') ?>
                    </td>
                    <td width="35%">
                        <select onchange="select_cashbox(this, 2)" name="cashbox_to"
                                class="form-control input-sm cashbox-2"><?= $select_cashbox ?></select>
                    </td>
                    <td width="10%">
                        <span>
                            <input class="form-control input-sm"
                                   id="amount_without_discount" type="text" style="width:80px"
                                   name="amount_without_discount" value="<?= $amount_to ?>"
                                   onkeydown="return isNumberKey(event, this)"
                                   onkeyup="recalculate_amount_pay();"
                            />
                        </span>
                    </td>
                    <td width="15%">
                        <span>
                            <input type="text" class="form-control js-repair-discount input-sm"
                                   onkeyup="recalculate_amount_pay();" value="0" name='discount'/>
                        </span>
                    </td>
                    <td width="20%">
                        <span>
                            <input class="form-control input-sm" readonly
                                   id="amount-with-discount" type="text" style="width:80px"
                                   name="amount_to" value="<?= $amount_to ?>"/>
                        </span>
                    </td>
                </tr>
                <?php if ($co_id > 0 && $order['contractor_id'] > 0): ?>
                    <?php $ct = $this->all_configs['configs']['erp-cashbox-transaction']; ?>
                    <tr>
                        <td colspan="6" style="padding-left: 20px">
                            <label class="checkbox">
                                <input name="client_contractor" value="1" type="checkbox" onchange="javascript:
                                    if (this.checked) {
                                    $('.cashbox-1, .cashbox-2').val(<?= $ct ?>).prop('disabled', true);
                                    } else {
                                    $('.cashbox-1, .cashbox-2').val(<?= $selected_cashbox ?>).prop('disabled',false);
                                    }
                                    "/>
                                <?= l('Списать с баланса контрагента') ?>
                            </label>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </fieldset>
</form>

