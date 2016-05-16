<div class="row">
    <div class="col-sm-12">
        <table class="table <?= $prefix ?>-table-items" style="display:none">
            <thead>
            <tr>
                <th class="<?= $prefix == 'quick' ? 'col-sm-4' : 'col-sm-3' ?>"><?= l('Товар') ?></th>
                <th class="<?= $prefix == 'quick' ? 'col-sm-2' : '' ?>"><?= l('Цена') ?>(<?= viewCurrency() ?>)</th>
                <th><?= l('Скидка') ?></th>
                <?php if ($prefix == 'eshop'): ?>
                    <th><?= l('Количество') ?></th>
                <?php endif; ?>
                    <th><?= l('Сумма') ?>(<?= viewCurrency() ?>)</th>
                <?php if ($prefix != 'eshop'): ?>
                <th class="<?= $prefix == 'quick' ? 'col-sm-3' : '' ?>"><?= l('Гарантия') ?></th>
                <?php endif; ?>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr class="js-<?= $prefix ?>-row-cloning" style="display: none">
                <td>
                        <input type="hidden" class="form-control js-<?= $prefix ?>-item-id" name="" value="">
                        <input type="text" readonly class="form-control js-<?= $prefix ?>-item-name" value=""/>
                </td>
                <td>
                        <input type="text" class="form-control js-<?= $prefix ?>-price"
                               onkeyup="recalculate_amount_<?= $prefix ?>();" value="" name=""/>
                </td>
                <td>
                        <input type="text" class="form-control js-<?= $prefix ?>-discount"
                               onkeyup="recalculate_amount_<?= $prefix ?>();" value="0"/>
                        <input type="hidden" class="form-control js-<?= $prefix ?>-discount_type" value="1"/>
                </td>
                <?php if ($prefix == 'eshop'): ?>
                    <td>
                            <input type="text" class="form-control js-<?= $prefix ?>-quantity"
                                   onkeyup="recalculate_amount_<?= $prefix ?>();" value=""/>
                    </td>
                <?php endif; ?>
                <td>
                        <input type="text" class="form-control js-<?= $prefix ?>-sum dasabled" readonly
                               onkeyup="recalculate_amount_<?= $prefix ?>(this);" value="" name=""/>
                </td>
                <?php if ($prefix != 'eshop'): ?>
                    <td>
                        <div class="input-group col-sm-12">
                            <select class="form-control js-<?= $prefix ?>-warranty" name="">
                                <option value=""><?= l('Без гарантии') ?></option>
                                <?php foreach ($orderWarranties as $warranty): ?>
                                    <option
                                        value="<?= intval($warranty) ?>" <?= $warranty == $defaultWarranty ? 'selected="selected"' : '' ?>><?= intval($warranty) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-addon"><?= l('мес') ?></div>
                        </div>
                    </td>
                <?php endif; ?>
                <td>
                    <a href="#" onclick="return remove_row_<?= $prefix ?>(this);">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                </td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="row-amount">
                <td>
                        <label><?= l('Итоговая стоимость:') ?></label>
                </td>
                <td></td>
                <?php if ($prefix == 'eshop'): ?>
                    <td></td>
                    <td>
                            <input type="checkbox" name="cashless" class="cashless-toggle" title="<?= l('Отфильтровать все безналичные счета для сверки Вы можете в разделе: Бухгалтерия-Заказы-Заказы клиентов') ?>">
                    </td>
                <?php else: ?>
                    <td></td>
                <?php endif; ?>
                <td>
                        <input type="text" readonly class="form-control js-<?= $prefix ?>-total" value=""/>
                </td>
                <?php if ($prefix != 'eshop'): ?>
                    <td>
                            <input type="checkbox" name="cashless" class="cashless-toggle" title="<?= l('Отфильтровать все безналичные счета для сверки Вы можете в разделе: Бухгалтерия-Заказы-Заказы клиентов') ?>">
                    </td>
                <?php endif; ?>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
