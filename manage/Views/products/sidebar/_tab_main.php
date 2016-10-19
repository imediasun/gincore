<!--     Main info-->
<div class="hpanel">
    <div class="panel-heading hbuilt showhide">
        <div class="panel-tools">
            <i class="fa fa-chevron-up"></i>
        </div>
        <?= l('Основная информация') ?>
    </div>
    <input type="hidden" name="id_product" value="<?= intval($product['id']) ?>" id="sidebar-id-product">
    <div class="panel-body">
        <div class="form-group">
            <label><?= l('Название') ?>: </label>
            <input class="form-control" placeholder="<?= l('введите название') ?>" name="title"
                   value="<?= (is_array($errors) && array_key_exists('post',
                           $errors) && array_key_exists('title',
                           $errors['post'])) ? h($errors['post']['title']) : h($product['title']); ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Артикул') ?>: </label>
            <input placeholder="<?= l('Артикул') ?>" class="form-control" name="vendor_code"
                   value="<?= ((is_array($errors) && array_key_exists('post',
                           $errors) && array_key_exists('vendor_code',
                           $errors['post'])) ? h($errors['post']['vendor_code']) : $product['vendor_code']) ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Приоритет') ?>: </label>
            <input onkeydown="return isNumberKey(event)" class="form-control" name="prio"
                   value="<?= ((is_array($errors) && array_key_exists('post',
                           $errors) && array_key_exists('prio',
                           $errors['post'])) ? h($errors['post']['prio']) : $product['prio']) ?>"/>
        </div>

        <div>
            <label><?= l('Картинка') ?></label>
            <div id="product-img-uploader"></div>
            <div class="m-t-sm" id="goods_images">
                <?php if($images): ?>
                    <?php foreach ($images as $image): ?>
                    <div class="col-sm-2 m-t-sm">
                        <div class="img_delete" data-id_product="<?= $product['id'] ?>" data-id_image="<?= $image['id'] ?>"><i class="fa fa-times"></i></div>
                        <img class="img-polaroid" src="/shop/goods/<?= $product['id'] ?>/<?= $image['image'] ?>" width="50px" title="">
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>


            </div>
        </div>

        <div class="clearfix"></div>


        <div class="m-t-md">
            <label><?= l('Розничная цена') ?> (<?= viewCurrency('shortName') ?>): </label>
            <?= number_format($product['price'] / 100, 2, '.', ' ') ?>
        </div>
        <div>
            <label><?= l('Оптовая цена') ?> (<?= viewCurrency('shortName') ?>): </label>
            <?= number_format($product['price_wholesale'] / 100, 2, '.', ' ') ?>
        </div>
        <div>
            <label><?= l('Закупочная цена последней партии') ?> (<?= viewCurrencySuppliers('shortName') ?>
                ): </label>
            <?= number_format($product['price_purchase'] / 100, 2, '.', ' ') ?>
        </div>
        <div>
            <label><?= l('Свободный остаток') ?>:</label>
            <?= intval($product['qty_store']) ?>
        </div>
        <div>
            <label><?= l('Общий остаток') ?>:</label>
            <?= intval($product['qty_wh']) ?>
        </div>
    </div>
</div>
<link type="text/css" rel="stylesheet" href="/manage/modules/products/css/fileuploader.css">
<style>
    .qq-upload-success {
        display: none;
    }
    .qq-upload-button {
        margin-bottom: 5px;
    }
</style>


<script async src="/manage/modules/products/js/fileuploader.js"></script>

<script type="text/javascript">
    var id_product = <?= intval($product['id']) ?>;
    $(document).ready(function () {

        if ($("#product-img-uploader").length) {
            //var pid = $("#cur_product").val();
            var pid = arrequest()[2];
            var uploader = new qq.FileUploader({
                // Pass the HTML element here
                uploadButtonText: L.qq_uploadButtonText,
                dragText: L.qq_dragText,
                cancelButtonText: L.qq_cancelButtonText,
                failUploadText: L.qq_failUploadText,
                element: document.getElementById('product-img-uploader'),
                maxConnections: 500,
                allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
                action: prefix + 'products/ajax/',
                params: {
                    act: 'upload_picture_for_goods',
                    product: id_product
                },
                onSubmit: function () {
                    uploader.setParams({
                        act: 'upload_picture_for_goods',
                        watermark: jQuery('#product_watermark').is(':checked') ? true : false,
                        oist: jQuery('#one-image-secret_title').is(':checked') ? true : false,
                        product: id_product
                    });
                },
                onComplete: function (id, fileName, responseJSON) {
                    if (responseJSON.success == true) {
                        //$("#goods_images").html(responseJSON.filename);
                        document.getElementById('goods_images').innerHTML += '<div class="col-sm-2 m-t-sm"><img class="img-polaroid" width="50px" title="" ' +
                            'src="' + siteprefix + 'shop/goods/' + id_product + '/' + responseJSON.filename + '" /> ' + '</div>';
                    }
                }
            });
        }
    })
</script>