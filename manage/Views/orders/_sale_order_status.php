<div class="dropdown dropdown-inline">
    <button class="as_button" type="button" id="dropdownStatus_<?= $orderId ?>" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="true"
            style="background-color: <?= isset($status[$active]) ? '#' . $status[$active]['color'] : 'grey' ?>">
        <span class="btn-title"><?= isset($status[$active]) ? $status[$active]['name'] : l('Выбрать') ?></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownStatus_<?= $orderId ?>">
        <?php foreach ($status as $id => $property): ?>
            <?php $style = 'style="color:#' . htmlspecialchars($property['color']) . '"'; ?>
            <li>
                <?php if ($id == $this->all_configs['configs']['order-status-issued'] && $this->all_configs['oRole']->hasCashierPermission($this->getUserId()) && $showPayForm): ?>
                    <a href="#" data-order_id="<?= $orderId ?>" data-status_id="<?= $id ?>"
                       onclick="return pay_client_order(this, '<?= $type ?>', <?= $orderId ?>, null, null, {issued: 1})" <?= $style ?>><?= $property['name'] ?></a>
                <?php else: ?>
                    <a href="#" data-order_id="<?= $orderId ?>" data-status_id="<?= $id ?>"
                       onclick="return change_status(this)" <?= $style ?>><?= $property['name'] ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
