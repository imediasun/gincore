<div class="btn-group select-print-form">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false" onclick="return toggle_on_click(this, event);">
        <i class="fa fa-print"></i>&nbsp;<span class="caret"></span>
    </button>
    <input id='order_id' type="hidden" name="order_id" value="<?= $order['id'] ?>"/>
    <ul class="keep-open dropdown-menu print_menu">
        <li>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="print[]"
                           value="check">
                    <?= l('Квитанция') ?>
                </label>
            </div>
        </li>
        <li>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="print[]"
                           value="invoice">
                    <?= l('Чек') ?>
                </label>
            </div>
        </li>
        <li>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="print[]"
                           value="warranty">
                    <?= l('Гарантия') ?>
                </label>
            </div>
        </li>
        <li>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="print[]" value="act">
                    <?= l('Акт выполненых работ') ?>
                </label>
            </div>
        </li>
        <li>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="print[]"
                           value="invoicing">
                    <?= l('Счет на оплату') ?>
                </label>
            </div>
        </li>
    </ul>
</div>
