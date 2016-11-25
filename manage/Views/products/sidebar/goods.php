<?php if ($product): ?>
    <div class="m-l-sm">
        <h4><?= l('Редактирование товара ID') ?> : <?= $product['id'] ?>. <?= $product['title'] ?></h4>
    </div>
    <form action="" method="post" id="sidebar-product-form" class="one_hpanel">
        <?php include '_tab_main.php'; ?>
        <?php include '_tab_additional.php'; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('external-marketing')): ?>
            <?php include '_tab_procurement_mgmnt.php'; ?>
        <?php endif; ?>
        <?php include '_tab_managers.php'; ?>
        <?php include '_tab_warehouses.php'; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('external-marketing')): ?>
            <?php include '_tab_suppliers_orders.php'; ?>
        <?php endif; ?>
        <?php include '_tab_notification.php'; ?>

    </form>

    <div class="clearfix m-b-xl"></div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#right-sidebar [data-toggle="tooltip"]').uitooltip();
        });
    </script>
<?php else: ?>
    <div class="m-l-sm">
        <h4><?= l('Данного товара не существует') ?></h4>
    </div>
<?php endif; ?>


