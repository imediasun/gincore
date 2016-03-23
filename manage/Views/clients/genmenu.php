<ul class="nav nav-list">
    <?php $active = '';
    if (!isset($arrequest[1]) || $arrequest[1] == 'create') {
        $active = 'active';
    } ?>
    <li class="<?= $active ?>">
        <a href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>"><?= l('Список клиентов') ?></a>
    </li>

    <?php $active = '';
    if (isset($arrequest[1]) && $arrequest[1] == 'inactive_clients') {
        $active = 'active';
    } ?>
    <li class="<?= $active ?>">
        <a href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/inactive_clients"><?= l('Неактивные клиенты') ?></a>
    </li>

    <?php $active = '';
    if (isset($arrequest[1]) && $arrequest[1] == 'goods-reviews') {
        $active = 'active';
    } ?>
    <li class="<?= $active ?>">
        <a href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/goods-reviews"><?= l('Отзывы о товаре') ?></a>
    </li>

    <?php $active = '';
    if (isset($arrequest[1]) && $arrequest[1] == 'shop-reviews') {
        $active = 'active';
    } ?>
    <li class="<?= $active ?>">
        <a href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/shop-reviews"><?= l('Отзывы о магазине') ?></a>
    </li>

    <?php $active = '';
    if (isset($arrequest[1]) && $arrequest[1] == 'approve-reviews') {
        $active = 'active';
    } ?>
    <li class="<?= $active ?>">
        <a href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/approve-reviews"><?= l('Утверждение отзывов') ?></a>
    </li>
</ul>

<?php if (isset($arrequest[1]) && $arrequest[1] == 'create' && isset($arrequest[2]) && $arrequest[2] > 0): ?>
    <a class="btn add-order"
       href="<?= $this->all_configs['prefix'] ?>orders?client_id=<?= $arrequest[2] ?>#create_order">
        <?= l('Создать заказ') ?>
    </a>
<?php elseif (!isset($arrequest[1]) || $arrequest[1] != 'create'): ?>
    <br>
    <a class="btn btn-default" href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/create">
        <?= l('Создать клиента') ?>
    </a>
<?php endif; ?>