<?php $status = $this->all_configs['configs']['sale-order-status']; ?>
<div class="dropdown dropdown-inline">
    <button class="as_button" type="button" id="dropdownStatus_<?= $orderId ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="background-color: #F9F9F9;color:#<?= isset($status[$active])?$status[$active]['color']:'black'?>">
        <span class="btn-title"><?= isset($status[$active])?$status[$active]['name']:l('Выбрать') ?></span>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownStatus_<?= $orderId ?>">
        <?php foreach ($status as $id => $property): ?>
            <?php $style = 'style="color:#' . htmlspecialchars($property['color']) . '"'; ?>
            <li><a href="#" data-order_id="<?= $orderId ?>" data-status_id="<?= $id ?>" onclick="return change_status(this)" <?= $style ?>><?= $property['name'] ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
