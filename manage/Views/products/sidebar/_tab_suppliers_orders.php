<!--  Supplier Orders-->
<div class="hpanel panel-collapse">
    <div class="panel-heading hbuilt">
        <div class="panel-tools">
            <a class="showhide"><i class="fa fa-chevron-up"></i></a>
        </div>
        <?= l('Заказы поставщикам') ?>
    </div>
    <div class="panel-body" style="display: none;">
        <h5><?= l('Склады поставщиков Локально') ?></h5>
        <?php if ($goods_suppliers): ?>
            <?php foreach ($goods_suppliers as $product_supplier): ?>
                <input type="text" name="links[]" placeholder="<?= l('гиперссылка') ?>"
                       class="form-control"
                       value="<?= $product_supplier['link'] ?>"/>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="form-group">
            <div class="col-sm-10 p-xxs">
                <input type="text" name="links[]" placeholder="<?= l('гиперссылка') ?>" class="form-control"/>

                <div id="links-placeholder" class="hidden"></div>
            </div>
            <div class="col-sm-2 p-xxs">
                <div class="btn btn-default"
                        onclick="$('<input>').attr({type: 'text', name: 'links[]', class: 'form-control m-t-xs'}).insertBefore($('#links-placeholder'));">
                    <i class="glyphicon glyphicon-plus cursor-pointer"></i>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-12 p-xxs">
                <div class="btn btn-default form-control" >
                    <?= l('Создать заказ поставщику') ?>
                </div>
            </div>
        </div>

        <?= $this->all_configs['suppliers_orders']->show_suppliers_orders($orders, false, false, true) ?>

    </div>
</div>