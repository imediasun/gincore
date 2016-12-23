<form class="form-horizontal" method="post">
    <div class="well"><h4><?= l('Склады поставщиков Локально') ?></h4>
            <?php if ($goods_suppliers): ?>
                <?php foreach ($goods_suppliers as $product_supplier): ?>
                    <input type="text" name="links[]" placeholder="<?= l('гиперссылка') ?>"
                           class="form-control"
                           value="<?= $product_supplier['link'] ?>"/>
                <?php endforeach; ?>
            <?php endif; ?>
            <input type="text" name="links[]" placeholder="<?= l('гиперссылка') ?>" class="form-control"/>
            <i class="glyphicon glyphicon-plus cursor-pointer"
               onclick="$('<input>').attr({type: 'text', name: 'links[]', class: 'form-control'}).insertBefore(this);"></i>
    </div>
    <?= $btn_save; ?>
</form>
<?php if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')): ?>
    <br/>
    <div id="accordion_product_suppliers_orders">
        <div class="panel-group">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a class="panel-toggle" href="#collapse_create_product_supplier_order"
                       data-parent="#accordion_product_suppliers_orders" data-toggle="collapse">
                        <?= l('Создать заказ поставщику') ?>
                    </a>
                </div>
                <div id="collapse_create_product_supplier_order" class="panel-body collapse">
                    <div class="accordion-inner">
                        <?= $this->all_configs['suppliers_orders']->create_order_block(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->all_configs['suppliers_orders']->show_suppliers_orders($orders) ?>

<?php if ($count > 10): ?>
    <a href="<?= $this->all_configs['prefix'] ?>orders?goods=<?= $this->all_configs['arrequest'][2] ?>#show_suppliers_orders">
        <?= l('Еще') ?>
    </a>
<?php endif; ?>
