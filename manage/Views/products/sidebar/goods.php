<?php if ($product): ?>
    <div class="m-l-sm">
        <h4><?= l('Редактирование товара ID') ?> : <?= $product['id'] ?>. <?= $product['title'] ?></h4>
    </div>
    <form action="" method="post" id="sidebar-product-form" class="one_hpanel">
        <?php include '_tab_main.php';?>
        <?php include '_tab_additional.php';?>
        <?php include '_tab_procurement_mgmnt.php';?>
        <?php include '_tab_managers.php';?>
        <?php include '_tab_warehouses.php';?>
        <?php include '_tab_suppliers_orders.php';?>
        <?php include '_tab_notification.php';?>
        
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


