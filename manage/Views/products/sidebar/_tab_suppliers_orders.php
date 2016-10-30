<!--  Supplier Orders-->
<div class="hpanel panel-collapse">
    <div class="panel-heading hbuilt showhide">
        <div class="panel-tools">
            <i class="fa fa-chevron-up"></i>
        </div>
        <?= l('Заказы поставщику') ?>
    </div>
    <div class="panel-body" style="display: none;">
        <h5><?= l('Склады поставщиков Локально') ?></h5>
        <div class="">
            <?php if ($goods_suppliers): ?>
                <?php foreach ($goods_suppliers as $product_supplier): ?>
                    <input type="text" name="links[]" placeholder="<?= l('гиперссылка') ?>"
                           class="form-control m-t-xs"
                           value="<?= $product_supplier['link'] ?>"/>
                <?php endforeach; ?>
            <?php endif; ?>


            <div class="col-sm-10 m-t-xs p-l-n">
                <input type="text" name="links[]" placeholder="<?= l('гиперссылка') ?>" class="form-control"/>

                <div id="links-placeholder" class="hidden"></div>
            </div>
            <div class="col-sm-2 p-xxs">
                <div class="btn btn-default pull-right"
                        onclick="$('<input>').attr({type: 'text', name: 'links[]', class: 'form-control m-t-xs'}).insertBefore($('#links-placeholder'));">
                    <i class="glyphicon glyphicon-plus cursor-pointer"></i>
                </div>
            </div>
        </div>

        <a href="<?= $this->allconfigs['prefix'] ?>orders?id_product=<?= $product['id'] ?>#create_supplier_order"
           target="_blank" class="btn btn-default form-control m-t-xs" >
            <?= l('Создать заказ поставщику') ?>
        </a>

        <?= $this->all_configs['suppliers_orders']->show_suppliers_orders($orders, false, false, true) ?>

    </div>
</div>