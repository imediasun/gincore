<div class="row">
    <div class="col-sm-12">
        <table class="table <?= $prefix?>-table-items" style="display:none">
            <thead>
            <tr>
                <th class="col-sm-6"><?= l('Товар') ?></th>
                <th class="<?= $prefix == 'quick'? 'col-sm-3': '' ?>"><?= l('Цена') ?></th>
                <?php if ($prefix == 'eshop'): ?>
                    <th><?= l('Скидка') ?></th>
                    <th><?= l('Количество') ?></th>
                    <th><?= l('Сумма') ?></th>
                <?php endif; ?>
                <th class="<?= $prefix == 'quick'? 'col-sm-3': '' ?>"><?= l('Гарантия') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr class="js-<?= $prefix ?>-row-cloning" style="display: none">
                <td>
                    <div class="input-group col-sm-12">
                        <input type="hidden" class="form-control js-<?= $prefix ?>-item-id" name="" value="">
                        <input type="text" readonly class="form-control js-<?= $prefix ?>-item-name" value=""/>
                    </div>
                </td>
                <td>
                    <div class="input-group col-sm-12">
                        <input type="text" class="form-control js-<?= $prefix ?>-price"
                               onkeyup="recalculate_amount_<?= $prefix ?>();" value="" name=""/>
                        <span class="input-group-addon"><?= viewCurrency() ?></span>
                    </div>
                </td>
                <?php if ($prefix == 'eshop'): ?>
                    <td>
                        <div class="input-group col-sm-12">
                            <input type="text" class="form-control js-<?= $prefix ?>-discount" value=""/>
                        </div>
                    </td>
                    <td>
                        <div class="input-group col-sm-12">
                            <input type="text" class="form-control js-<?= $prefix ?>-quantity" value=""/>
                        </div>
                    </td>
                    <td>
                        <div class="input-group col-sm-12">
                            <input type="text" class="form-control js-<?= $prefix ?>-sum" readonly
                                   onkeyup="recalculate_amount_<?= $prefix ?>();" value="" name=""/>
                            <span class="input-group-addon"><?= viewCurrency() ?></span>
                        </div>
                    </td>
                <?php endif; ?>
                <td>
                    <div class="input-group col-sm-12">
                        <select class="form-control js-<?= $prefix ?>-warranty" name="">
                            <option value=""><?= l('Без гарантии') ?></option>
                            <?php foreach ($orderWarranties as $warranty): ?>
                                <option
                                    value="<?= intval($warranty) ?>"><?= intval($warranty) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-group-addon"><?= l('мес') ?></div>
                    </div>
                </td>
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
                    <div class="input-group col-sm-12">
                        <label><?= l('Итоговая стоимость:') ?></label>
                    </div>
                </td>
                <?php if ($prefix == 'eshop'): ?>
                    <td></td>
                    <td></td>
                    <td></td>
                <?php endif; ?>
                <td>
                    <div class="input-group col-sm-12">
                        <input type="text" readonly class="form-control js-<?= $prefix ?>-total" value=""/>
                        <span class="input-group-addon"><?= viewCurrency() ?></span>
                    </div>
                </td>
                <td>
                    <div class="input-group"
                         title="<?= l('Отфильтровать все безналичные счета для сверки Вы можете в разделе: Бухгалтерия-Заказы-Заказы клиентов') ?>">
                        <input type="checkbox" name="cashless" class="cashless-toggle">
                    </div>
                </td>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
