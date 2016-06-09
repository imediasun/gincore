<form method="post" id="transaction_form">
    <fieldset>
        <input type="hidden" name="transaction_type" id="transaction_type" value="2"/>
        <input type="hidden" name="supplier_order_id" value="<?= $supplier_order_id ?>"/>
        <input type="hidden" name="client_order_id" value="<?= $co_id ?>"/>
        <input type="hidden" name="b_id" value="<?= $b_id ?>"/>
        <input type="hidden" name="transaction_extra" value="<?= $t_extra ?>"/>

        <div id="transaction_form_body" class="hide-conversion-3 transaction_type-repair">
            <table>
                <thead>
                <tr>
                    <td></td>
                    <td></td>
                    <td><?= l('Сумма') ?></td>
                    <td><?= l('Валюта') ?></td>
                    <td class="hide-not-tt-1 hide-not-tt-2 hide-conversion"><span><?= l('Курс') ?></span></td>
                    <td class="hide-not-tt-1 hide-not-tt-2"></td>
                </tr>
                </thead>
                <tbody>
                <tr class="hide-not-tt-2">
                    <td>* <?= l('С кассы') ?></td>
                    <td>
                        <select onchange="select_cashbox(this, 1)" name="cashbox_from"
                                class="form-control input-sm cashbox-1"><?= $select_cashbox ?></select>
                    </td>
                    <td>
                        <input <?= (empty($daf) ? '' : 'readonly') ?> class="form-control input-sm <?= $daf ?>"
                                                                      style="width:80px" onchange="get_course(1)"
                                                                      id="amount-1" type="text" name="amount_from"
                                                                      value="<?= $amount_from ?>"
                                                                      onkeydown="return isNumberKey(event, this)"/>
                    </td>
                    <td>
                        <select class="form-control input-sm cashbox_currencies-1" onchange="get_course(0)"
                                name="cashbox_currencies_from"><?= $cashbox_currencies ?></select>
                    </td>
                    <td class="hide-not-tt-1 hide-not-tt-2 hide-conversion">
                        <span>
                            <input id="conversion-course-1"
                                   style="width:80px"
                                   onchange="$('#amount-2').val(($('#amount-1').val()*$('#conversion-course-1').val()).toFixed(2));
                    if ($('#amount-2').val() > 0)
                    $('#conversion-course-2').val(($('#amount-1').val()/$('#amount-2').val()).toFixed(4));
                    else
                    $('#conversion-course-2').val(0.0000);"
                                   class="form-control input-mini"
                                   onkeydown="return isNumberKey(event, this)"
                                   type="text" value="1.0000"
                                   name="cashbox_course_from"/>
                        </span>
                    </td>
                    <td class="hide-not-tt-1 hide-not-tt-2 center cursor-pointer hide-conversion"
                        onclick="get_course(0)">
                        <span>
                            <small><?= l('Прямой') ?></small><br/>
                            <small id="conversion-course-db-1">1.0000</small>
                        </span>
                    </td>
                </tr>
                <tr class="hide-not-tt-1">
                    <td>* <?= l('В кассу') ?></td>
                    <td>
                        <select onchange="select_cashbox(this, 2)" name="cashbox_to"
                                class="form-control input-sm cashbox-2"><?= $select_cashbox ?></select>
                    </td>
                    <td class="hide-conversion">
                        <span><input class="form-control input-sm" onchange="
                    if ($('#amount-1').val() > 0 && $('#amount-2').val() > 0) {
                    $('#conversion-course-1').val(($('#amount-2').val()/$('#amount-1').val()).toFixed(4));
                    $('#conversion-course-2').val(($('#amount-1').val()/$('#amount-2').val()).toFixed(4));
                    } else {
                    $('#conversion-course-1').val(0.0000);
                    $('#conversion-course-2').val(0.0000);
                    }
                    "
                                     id="amount-2" type="text" style="width:80px"
                                     name="amount_to" value="<?= $amount_to ?>"
                                     onkeydown="return isNumberKey(event, this)"/>
                        </span>
                    </td>
                    <td>
                        <select class="form-control input-sm cashbox_currencies-2" onchange="get_course(0)"
                                name="cashbox_currencies_to"><?= $cashbox_currencies ?></select>
                    </td>
                    <td class="hide-not-tt-1 hide-not-tt-2 hide-conversion">
                        <span>
                            <input id="conversion-course-2"
                                   style="width:80px"
                                   onchange="
                    if ($('#conversion-course-2').val() > 0)
                    $('#amount-2').val(($('#amount-1').val()/$('#conversion-course-2').val()).toFixed(2));
                    else
                    $('#amount-2').val(0.0000);
                    if ($('#amount-2').val() > 0)
                    $('#conversion-course-1').val(($('#amount-2').val()/$('#amount-1').val()).toFixed(4));
                    else
                    $('#conversion-course-1').val(0.0000); "
                                   class="form-control input-sm"
                                   onkeydown="return isNumberKey(event, this)"
                                   type="text" value="1.0000"
                                   name="cashbox_course_to"/>
                        </span>
                    </td>
                    <td class="hide-not-tt-1 hide-not-tt-2 center cursor-pointer hide-conversion"
                        onclick="get_course(0)">
                        <span>
                            <small><?= l('Обратный') ?></small><br/>
                            <small id="conversion-course-db-2">1.0000</small>
                        </span>
                    </td>
                </tr>
                <?php if ($co_id == 0): ?>
                    <tr class="hide-not-tt-2 hide-not-tt-3">
                        <td>* <?= l('Статья') ?></td>
                        <td>
                            <select <?= $dcct ?> id="contractor_category-1"
                                                 class="multiselect input-sm form-control multiselect-sm"
                                                 onchange="select_contractor_category(this, 1)"
                                                 name="contractor_category_id_to">
                                <option value=''><?= l('Выберите') ?></option>
                                <?= build_array_tree($categories_to, $ccg_id) ?>
                            </select>
                            <a target="_blank"
                               href="<?= $this->all_configs["prefix"] . $this->all_configs["arrequest"][0] . '#settings-categories_expense' ?>">
                                <i class="glyphicon glyphicon-plus"></i>
                            </a>
                        </td>
                    </tr>
                    <tr class="hide-not-tt-1 hide-not-tt-3">
                        <td>* <?= l('Статья') ?></td>
                        <td>
                            <select <?= $dccf ?> id="contractor_category-2" class="multiselect multiselect-sm"
                                                 onchange="select_contractor_category(this, 2)"
                                                 name="contractor_category_id_from">
                                <option value=''><?= l('Выберите') ?></option>
                                <?= build_array_tree($categories_from, $ccg_id) ?>
                            </select>
                            <a target="_blank"
                               href="<?= $this->all_configs["prefix"] . $this->all_configs["arrequest"][0] . '#settings-categories_income' ?>">
                                <i class="glyphicon glyphicon-plus"></i>
                            </a>
                        </td>
                    </tr>
                    <tr class="hide-not-tt-3">
                        <td>*&nbsp;<?= l('Контрагент') ?></td>
                        <td>
                            <select <?= $dc ?> class="form-control input-sm select_contractors" name="contractors_id">
                                <?= $select_contractors ?>
                            </select>
                            <a target="_blank"
                               href="<?= $this->all_configs["prefix"] . $this->all_configs["arrequest"][0] . '#settings-contractors' ?>">
                                <i class="glyphicon glyphicon-plus"></i>
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr class="hide-not-tt-2 hide-not-tt-3 hide-not-tt-so-1">
                    <td colspan="2">
                        <div class="checkbox">
                            <label class="popover-info" data-original-title="" data-content="
                (<?= l('Ставим птичку в случае, если данная выплата производится за услуги или расходные материалы.') ?>
                <?= l('Не ставим птичку - если оплата производится за приобретаемые оборотные активы)'); ?>
                        ">
                                <input type="checkbox" onchange="javascript:
                                    if (this.checked) {
                                    if (!confirm('<?= l('Не зачислять контрагенту на баланс?') ?>)) {
                                    this.checked=false;
                                    }
                                    }
                                    " name="without_contractor" value="1"/><?= l('Без внесения на баланс') ?>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr class="hide-not-tt-1 hide-not-tt-3">
                    <td colspan="2">
                        <div class="checkbox">
                            <label class="popover-info" data-original-title=""
                                   data-content="(<?= l('Птичку ставим - когда поступление денежных средств не связано с приобретением или возвратом оборотных активов') ?>)">
                                <input type="checkbox"
                                       onchange="javascript:if (this.checked) { if (!confirm('<?= l('Не списывать у контрагента с баланса?') ?>')) { this.checked=false; } }"
                                       name="without_contractor" value="1"/>
                                <?= l('Без списания с баланса') ?>
                            </label>
                        </div>
                    </td>
                </tr>
                <?php if ($co_id > 0 && $client_contractor > 0): ?>
                    <?php $ct = $this->all_configs['configs']['erp-cashbox-transaction']; ?>
                    <tr>
                        <td colspan="6">
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

                <tr>
                    <td colspan="2">
                        <textarea class="form-control input-sm" name="comment"
                                  placeholder="<?= l('примечание') ?>"></textarea>
                    </td>
                    <td colspan="4" class="center">
                        <div class="form-group">
                            <input class="form-control daterangepicker_single input-sm" type="text"
                                   name="date_transaction" value="<?= $today ?>"/>
                        </div>
                </tr>
                </tbody>
            </table>
        </div>
    </fieldset>
</form>
