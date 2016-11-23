<?php if ($this->all_configs['configs']['erp-use']): ?>
    <div class="m-l-md m-r-md" id="moving-item-sidebar">
        <h4><i class="fa fa-random"></i> &nbsp;&nbsp;<?= l('Перемещения') ?></h4>
        <span>
            <?= l('Здесь Вы можете перемещать товар или запчасти по территории склада/мастерской.') ?>
            <?= l('Привязывать детали к заказам на ремонт и т.д.') ?>
        </span>
        <hr style="margin-top: 5px"/>

        <h4>
            <img src="<?= $prefix ?>img/barcode-scanner.png" width="18px" style="opacity: 0.6"> &nbsp;&nbsp;<?= l('С помощью сканера штрих кодов') ?>
            <i class="fa fa-question-circle" data-toggle="tooltip" style="font-size: 14px" title='<?= l('l_warehouses_scanner_moves_info') ?>'></i>
        </h4>
        <span>
            <?= l('Для перемещения товара или заказов по территории склада/мастерской, необходимо просканировать штрих-код на товаре или квитанции, после чего просканировать штрих-код на лотке или полке, куда перемещается товар/заказ') ?>
        </span>
        <?php if ($this->all_configs['oRole']->hasPrivilege('scanner-moves')): ?>
            <input value="" id="scanner-moves-sidebar" type="text" placeholder="<?= l('заказ, изделие или локация') ?>"
                   class="form-control m-t-md"/>
            <input value="" id="scanner-moves-old-sidebar" type="hidden" placeholder="<?= l('заказ или локация') ?>"
                   class="form-control"/>
            <div id="scanner-moves-alert-sidebar" class="m-l-sm m-t-sm text-success" style="display: none;">
<!--                <button type="button" class="close" data-dismiss="alert">&times;</button>-->
                <div id="scanner-moves-alert-body-sidebar" style="font-size: 12px !important;"></div>
            </div>
        <?php endif; ?>

        <h4>
            <i class="fa fa-barcode"></i>  &nbsp;&nbsp;<?= l('Вручную') ?>
        </h4>
        <span>
            <?= l('Чтобы привязать деталь к заказу, укажите вручную серийный номер запчасти и номер заказа. Чтобы переместить товар/заказ - укажите номер товара/детали и номер локации, куда нужно переместить изделие.') ?>
        </span>

        <form method="post" id="moving-item-form-<?= $rand ?>">
            <input type="hidden" value="<?= $rand ?>" name="rand" id="moving-item-form-rand-value">
            <div class="form-group" style="position: relative;">

                <div class="input-group serial_input m-t-sm">
                    <?= typeahead($this->all_configs['db'], 'serials', false, 0, 3, 'input-small clone_clear_val', '',
                        'display_serial_product', true, false, '', false, l('серийный № изделия')) ?>
                    <span class="input-group-addon" style="cursor: pointer" onclick="$('#clone-serial').trigger('click');" >
                        <i class="glyphicon glyphicon-plus"></i>
                    </span>
                </div>
               <span class="cloneAndClear" id="clone-serial" style="display: none !important;" data-clone_siblings=".serial_input"></span>

            </div>
            <small class="clone_clear_html product-title"></small>


            <div class="form-group">
                <div class="controls">
                    <input name="order_id" type="text" value="<?= $order['id'] ?>"
                           placeholder="<?= l('номер заказа на ремонта') ?>"
                           class="form-control"/></div>
            </div>


            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders') || $this->all_configs['oRole']->hasPrivilege('engineer')): ?>
                <div class="form-group">
                    <select onchange="change_warehouse(this)" class="multiselect form-control select-warehouses-item-move"
                            name="wh_id_destination">
                        <?= $controller->get_options_for_move_item_form($with_logistic, $wh_id); ?>
                    </select></div>

                <div class="form-group m-b-xl">
                    <select class="multiselect form-control select-location" name="location">
                        <option><?= l('Выберите локацию') ?></option>
                        <?= $this->all_configs['suppliers_orders']->gen_locations($wh_id) ?>
                    </select></div>
            <?php endif; ?>

        </form>
    </div>
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function () {
        reset_multiselect();
        $('#right-sidebar [data-toggle="tooltip"]').uitooltip();
    });
</script>