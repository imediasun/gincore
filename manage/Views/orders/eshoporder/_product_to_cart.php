<div class="form-group col-sm-3 no-right-padding">
    <label class="control-label">
        <?= l('Устройство') ?>:
    </label>
    <div class="form-group" id="categories-selected">
        <?= typeahead($this->all_configs['db'], 'new-goods', false,
            (!empty($order_data) ?
                $order_data['product_id'] : 0), 3, 'input-medium popover-info', '',
            'display_goods_information,get_requests', false, false, '', false,
            l('Введите'),
            array()) ?>
    </div>
</div>
<div class="form-group col-sm-1 no-right-padding left-padding-5">
    <label>
        <?= l('Кол-во') ?>
    </label>
    <div class="form-group">
        <input type="text" id="eshop_sale_poduct_quantity" class="form-control"
               name="quantity" onkeyup="return sum_calculate();"/>
    </div>
</div>
<div class="form-group col-sm-3 no-right-padding left-padding-5">
    <label>
        <input type="hidden" name="price_type" value="1"/>
        <div class="dropdown dropdown-inline">
            <button class="as_link" type="button" id="dropdownMenuCashboxes"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <span class="btn-title-price_type"><?= l('Цена, р') ?></span>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuCashboxes">
                <li><a href="#" data-price_type="1"
                       onclick="return select_price_type(this)"><?= l('Цена, р') ?></a>
                </li>
                <li><a href="#" data-price_type="2"
                       onclick="return select_price_type(this)"><?= l('Цена, о') ?></a>
                </li>
            </ul>
        </div>
    </label>
    <div class="input-group">
        <input type="text" id="eshop_sale_poduct_cost" class="form-control" value=""
               name="price" onkeyup="return sum_calculate();"/>
        <span class="input-group-addon"><?= viewCurrency() ?></span>
    </div>
    <ul id="eshop_sale_product_cost_error" class="parsley-errors-list filled"
        style="display: none">
        <li class="parsley-required"><?= l('Обязательное поле.') ?></li>
    </ul>
</div>
<div class="form-group col-sm-1 no-right-padding left-padding-5">
    <label>
        <input type="hidden" id="eshop_sale_poduct_discount_type" name="discount_type" value="1"/>
        <div class="dropdown dropdown-inline">
            <button class="as_link" type="button" id="dropdownMenuCashboxes"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <span class="btn-title-discount_type"><?= l('Скидка, %') ?></span>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuCashboxes">
                <li><a href="#" data-discount_type="1"
                       onclick="return select_discount_type(this)"><?= l('Скидка, %') ?></a>
                </li>
                <li><a href="#" data-discount_type="2"
                       onclick="return select_discount_type(this)"><?= l('Скидка, $') ?></a>
                </li>
            </ul>
        </div>
    </label>
    <div class="form-group">
        <input type="text" id="eshop_sale_poduct_discount" class="form-control"
               name="discount" onkeyup="return sum_calculate();"/>
    </div>
</div>
<div class="form-group col-sm-2 no-right-padding left-padding-5">
    <label><?= l('Сумма') ?>: </label>
    <div class="input-group">
        <input type="text" id="eshop_sale_poduct_sum" class="form-control disabled" value=""
               name="sum" disabled/>
        <span class="input-group-addon"><?= viewCurrency() ?></span>
    </div>
    <ul id="eshop_sale_product_cost_error" class="parsley-errors-list filled"
        style="display: none">
        <li class="parsley-required"><?= l('Обязательное поле.') ?></li>
    </ul>
</div>

<div class="form-group col-sm-2" style="padding: 0px">
    <label>&nbsp;</label><br>
    <button class="btn-sm btn-primary class" data-o_id="<?= isset($order['id'])?$order['id']:0 ?>" onclick="return <?= $from_shop?'add_eshop_item_to_table();':'update_order(this)' ?>"
            title="<?= l('Добавить товар') ?>">
        <span class="small"> <?= l('В&nbsp;корзину') ?> </span>
    </button>
</div>

