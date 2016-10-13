<?= $this->renderFile('products/list/column_filter', array(
    'columns' => $columns
)) ?>
<table class="table table-striped">
    <thead>
    <tr>
        <?php if (isset($columns['id'])): ?>
            <th>
                <?php switch ($_GET['sort']): ?>
<?php case 'id': ?>
                        <a href="?sort=rid">
                            ID<i class="glyphicon glyphicon-chevron-down"></i>
                        </a>
                        <?php break; ?>
                    <?php case 'rid': ?>
                        <a href="?sort=id">
                            ID<i class="glyphicon glyphicon-chevron-up"></i>
                        </a>
                        <?php break; ?>
                    <?php default: ?>
                        <a href="?sort=rid"> ID
                            <?php if (!isset($_GET['sort'])): ?>
                                <i class="glyphicon glyphicon-chevron-down"></i>
                            <?php endif; ?>
                        </a>
                    <?php endswitch; ?>
            </th>
        <?php endif; ?>
        <?php if (isset($columns['marker'])): ?>
            <th></th>
        <?php endif; ?>
        <?php if (isset($columns['photo'])): ?>
            <th><?= l('Фото') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['title'])): ?>
            <th>
                <?php switch ($_GET['sort']): ?>
<?php case 'title': ?>
                        <a href="?sort=rtitle">
                            <?= l('Название продукта') ?><i class="glyphicon glyphicon-chevron-down"></i>
                        </a>
                        <?php break; ?>
                    <?php case 'rtitle': ?>
                        <a href="?sort=title">
                            <?= l('Название продукта') ?><i class="glyphicon glyphicon-chevron-up"></i>
                        </a>
                        <?php break; ?>
                    <?php default: ?>
                        <a href="?sort=title"><?= l('Название продукта') ?> </a>
                    <?php endswitch; ?>
            </th>
        <?php endif; ?>
        <?php if (isset($columns['vc'])): ?>
            <th><?= l('Артикул') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['price'])): ?>
            <th>
                <?php switch ($_GET['sort']): ?>
<?php case 'price': ?>
                        <a href="?sort=rprice">
                            <?= l('Цена') ?><i class="glyphicon glyphicon-chevron-down"></i>
                        </a>
                        <?php break; ?>
                    <?php case 'rprice': ?>
                        <a href="?sort=price">
                            <?= l('Цена') ?><i class="glyphicon glyphicon-chevron-up"></i>
                        </a>
                        <?php break; ?>
                    <?php default: ?>
                        <a href="?sort=price"><?= l('Цена') ?></a>
                    <?php endswitch; ?>
            </th>
        <?php endif; ?>
        <?php if (isset($columns['rprice'])): ?>
            <th><?= l('Цена закупочная') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['wprice'])): ?>
            <th><?= l('Цена оптовая') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['balance'])): ?>
            <th><?= l('Общий остаток') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['fbalance'])): ?>
            <th><?= l('Свободный остаток') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['sbalance'])): ?>
            <th><?= l('Наличие у поставщиков') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['delivery'])): ?>
            <th><?= l('Ожидаемые поставки') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['cart'])): ?>
            <th><a href="#" class="btn btn-default" title="<?= l('Корзина') ?>" onclick="return show_cart();"><i class="fa fa-shopping-cart" aria-hidden="true"></i>&nbsp;<span id="cart-quantity"><?= $item_in_cart ?></span> <?= l('шт.') ?></a></th>
        <?php endif; ?>
        <?php if (isset($columns['mbalance'])): ?>
            <th><?= l('Неснижаемый остаток') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['type'])): ?>
            <th><?= l('Товар/услуга') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['manager'])): ?>
            <th><?= l('Менеджер') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['date'])): ?>
            <th><?= l('Дата') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['del'])): ?>
            <th title="<?= l('Удалить товар') ?>"></th>
        <?php endif; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($goods as $id => $good): ?>
        <?= $this->renderFile('products/list/_row', array(
            'good' => $good,
            'id' => $id,
            'isEditable' => $isEditable,
            'columns' => $columns
        )) ?>
    <?php endforeach; ?>
    </tbody>
</table>
