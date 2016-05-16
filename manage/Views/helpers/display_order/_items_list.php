<div class="dropdown dropdown-inline">
    <button class="as_item_list" type="button" id="dropdownItems<?= $orderId ?>" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="true">
        <span class="btn-title">
        <?= $count ?><?= l('шт.'); ?> 
        </span>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownItems_<?= $orderId ?>">
        <?php foreach ($list as $item): ?>
            <?php $style = 'style="color:#' . htmlspecialchars($property['color']) . '"'; ?>
            <li><a><?= $item ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
