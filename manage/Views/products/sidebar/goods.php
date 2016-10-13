<?php if ($product) : ?>
    <div class="p-sm">
        <h4><?= l('Редактирование товара ID') ?> : <?= $product['id'] ?>. <?= $product['title'] ?></h4>
    </div>
    <form action="" method="post">
        <?php include '_tab_main.php';?>
        <?php include '_tab_additional.php';?>
        <?php include '_tab_managers.php';?>
        <?php include '_tab_warehouses.php';?>
        <?php include '_tab_suppliers_orders.php';?>
        <?php include '_tab_notification.php';?>
        <?php include '_tab_procurement_mgmnt.php';?>
        <div class="form-group p-md">
            <input type="submit" class="btn btn-primary" value="<?= l('Сохранить') ?>">
            <input type="button" class="btn btn-default js_close_sidebar" value="<?= l('Отмена') ?>">
        </div>
    </form>



<?php endif; ?>