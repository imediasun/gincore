<?php if ($product) : ?>
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
        <div class="form-group m-t-md m-l-md">
            <input type="submit" class="btn" value="<?= l('Сохранить') ?>" id="sidebar-product-form-submit">
<!--           <input type="button" class="btn btn-default js_close_sidebar" value="<?= l('Отмена') ?>"> -->
        </div>
    </form>
    
    
    <script type="text/javascript">

    $( function() {
        $('#right-sidebar').tooltip({
            items: "[data-tooltip]",
            content: function() {
                var element = $( this );

                if ( element.is( "[data-tooltip]" ) ) {
                    return element.data( "tooltip" );
                }

            }
        });
    });
    </script>
<?php endif; ?>


