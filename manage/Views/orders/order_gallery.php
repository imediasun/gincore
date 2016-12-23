<div class="row-fluid">
    <div
        class="span3 order-fotos <?= ($this->all_configs['oRole']->hasPrivilege('client-order-photo') ? 'can-remove' : '') ?>">
        <?php if ($images): ?>
            <?php $img_path = $this->all_configs['siteprefix'] . $this->all_configs['configs']['orders-images-path']; ?>
            <?php foreach ($images as $image): ?>
                <?php $src = $img_path . $image['order_id'] . '/' . urldecode($image['image_name']); ?>
                <div class="order-foto">
                    <i class="glyphicon glyphicon-remove cursor-pointer"
                       onclick="remove_order_image(this, <?= $image['id'] ?>)"></i>
                    <img data-toggle="lightbox" href="#order-image-<?= $image['id'] ?>" src="<?= $src ?>"/>
                    <div id="order-image-<?= $image['id'] ?>" class="lightbox hide fade" tabindex="-1" role="dialog"
                         aria-hidden="true">
                        <div class="lightbox-content">
                            <img src="<?= $src ?>">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="span8">
        <?= $webcam->gen_html_body(); ?>
    </div>
</div>
