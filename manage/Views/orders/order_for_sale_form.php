<div class="container-fluid">
    <div class="row">
        <div class="col-sm-6">
            <form method="post" id="sale-form" parsley-validate>
                <input type="hidden" name="type" value="3">
                <?= $client['id'] ?>
                <fieldset>
                    <legend><?= l('Клиент') ?></legend>
                    <div class="form-group">
                        <label><?= l('Укажите данные клиента') ?> <b class="text-danger">*</b>: </label>
                        <div class="row row-15">
                            <div class="col-sm-6">
                                <?= $client['phone'] ?>
                            </div>
                            <div class="col-sm-6">
                                <?= $client['fio'] ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="padding-top:0"><?= l('Код на скидку') ?>: </label>
                        <input type="text" name="code" class="form-control call_code_mask">
                    </div>
                    <div class="form-group">
                        <label><?= l('Рекламный канал') ?> (<?= l('источник') ?>): </label>
                        <?= get_service('crm/calls')->get_referers_list() ?>
                    </div>
                </fieldset>
                <fieldset>
                    <div class="container-fluid items-container">
                        <div class="row">
                            <div class="col-sm-12 no-padding-right">
                                <legend><?= l('Товар') ?></legend>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-7">
                                <label class="control-label">
                                    <?= l('Код товара') ?> (<?= l('серийный номер') ?>) <b class="text-danger">*</b>:
                                </label>
                                <?= typeahead($this->all_configs['db'], 'serials', false, '', 4,
                                    'input-medium clone_clear_val',
                                    '', 'display_serial_product_title_and_price', false, true) ?>
                                <small class="clone_clear_html product-title"></small>
                                <input type="hidden" name="items" id="item_id" value="">
                            </div>
                            <div class="form-group col-sm-4">
                                <label><?= l('Цена продажи') ?> <b class="text-danger">*</b>: </label>
                                <div class="input-group">
                                    <input type="text" id="sale_poduct_cost" class="form-control" value=""
                                           name="price"/>
                                    <span class="input-group-addon"><?= viewCurrency() ?></span>
                                </div>
                            </div>

                            <div class="form-group col-sm-1">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary class" onclick="return add_item_to_table();"
                                        title="<?= l('Добавить товар') ?>">
                                    <i class="glyphicon glyphicon-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-items" style="display:none">
                                    <thead>
                                    <tr>
                                        <th class="col-sm-7"><?= l('Товар') ?></th>
                                        <th class="col-sm-4"><?= l('Цена') ?></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="js-row-cloning" style="display: none">
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <input type="hidden" class="form-control js-item-id" name="" value="">
                                                <input type="text" readonly class="form-control js-item-name" value=""/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <input type="text" class="form-control js-price"
                                                       onkeyup="recalculate_amount();" value="" name=""/>
                                                <span class="input-group-addon"><?= viewCurrency() ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="#" onclick="return remove_row(this);">
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
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <input type="text" readonly class="form-control js-total" value=""/>
                                                <span class="input-group-addon"><?= viewCurrency() ?></span>
                                            </div>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('Гарантия') ?>: </label>
                        <div class="input-group">
                            <select class="form-control" name="warranty">
                                <option value=""><?= l('Без гарантии') ?></option>
                                <?php foreach ($orderWarranties as $warranty): ?>
                                    <option value="<?= intval($warranty) ?>"><?= intval($warranty) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-addon"><?= l('мес') ?></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?= l('Скрытый комментарий к заказу') ?>: </label>
                        <textarea name="private_comment" class="form-control" rows="3"></textarea>
                    </div>
                </fieldset>
                <input class="btn btn-primary" type="button" onclick="sale_order(this)" value="<?= l('Добавить') ?>"/>
            </form>
            <div class="col-sm-6 relative"></div>
        </div>
    </div>
</div>
