<?php if (!empty($products)): ?>
    <?php foreach ($products as $product): ?>
        <?= $this->renderFile('prints/label', array(
            'product' => $product
        )) ?>
    <?php endforeach; ?>
<?php endif; ?>
