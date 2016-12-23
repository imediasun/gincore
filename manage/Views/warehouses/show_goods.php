<?= $this->all_configs['suppliers_orders']->append_js(); ?>
<?= $this->all_configs['chains']->append_js(); ?>
<?php if (empty($goods)): ?>
    <p class="text-error"><?= l('Товаров нет') ?></p>
<?php else: ?>
    <?php switch ($type): ?>
<?php case 1: ?>
            <?= $this->renderFile('warehouses/show_goods/type_eq_1', array(
                'goods' => $goods,
                'query_for_noadmin' => $query_for_noadmin,
                'controller' => $controller,
                'open_item_in_sidebar' => $open_item_in_sidebar
            )); ?>
            <?php break; ?>
        <?php case 2: ?>
            <?= $this->renderFile('warehouses/show_goods/type_eq_2', array(
                'goods' => $goods,
                'query_for_noadmin' => $query_for_noadmin,
                'controller' => $controller
            )); ?>
            <?php break; ?>
        <?php default: ?>
            <?= $this->renderFile('warehouses/show_goods/default', array(
                'goods' => $goods,
                'query_for_noadmin' => $query_for_noadmin,
                'controller' => $controller
            )); ?>
        <?php endswitch; ?>
<?php endif; ?>
<?= page_block($count_page, count($goods), '#show_items'); ?>
