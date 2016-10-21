<table class="table table-striped table-hover table-lh-30">
    <thead>
    <tr>
        <?php if (isset($columns['id'])): ?>
            <th>
                ID
            </th>
        <?php endif; ?>
        <th>
            <input type="checkbox" class="js-select-all" title="<?= l('Выбрать все') ?>"/>
        </th>
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
            <th><?= crop_title(l('Артикул')) ?></th>
        <?php endif; ?>
        <?php if (isset($columns['price'])): ?>
            <th>
                <?= crop_title(l('Розничная цена')) ?>
            </th>
        <?php endif; ?>
        <?php if (isset($columns['rprice'])): ?>
            <th><?= crop_title(l('Закупочная цена')) ?></th>
        <?php endif; ?>
        <?php if (isset($columns['wprice'])): ?>
            <th><?= crop_title(l('Оптовая цена')) ?></th>
        <?php endif; ?>
        <?php if (isset($columns['balance'])): ?>
            <th><?= crop_title(l('Общий остаток')) ?></th>
        <?php endif; ?>
        <?php if (isset($columns['fbalance'])): ?>
            <th><?= crop_title(l('Свободный остаток')) ?></th>
        <?php endif; ?>
        <?php if (isset($columns['sbalance'])): ?>
            <th><?= crop_title(l('Наличие у поставщиков')) ?></th>
        <?php endif; ?>
        <?php if (isset($columns['delivery'])): ?>
            <th><?= crop_title(l('Ожидаемые поставки')) ?></th>
        <?php endif; ?>
        <?php if (isset($columns['cart'])): ?>
            <th><a href="#" class="btn btn-default" title="<?= l('Корзина') ?>" onclick="return show_cart();"><i
                        class="fa fa-shopping-cart" aria-hidden="true"></i>&nbsp;<span
                        id="cart-quantity"><?= $item_in_cart ?></span> <?= l('шт.') ?></a></th>
        <?php endif; ?>
        <?php if (isset($columns['mbalance'])): ?>
            <th><?= crop_title(l('Неснижаемый остаток')) ?></th>
        <?php endif; ?>
        <?php if (isset($columns['type'])): ?>
            <th><span title="<?= l('Товар/Услуга') ?>"><?= l('Т/У') ?></span></th>
        <?php endif; ?>
        <?php if (isset($columns['manager'])): ?>
            <th><?= l('Менеджер') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['date'])): ?>
            <th><?= l('Дата') ?></th>
        <?php endif; ?>
        <?php if (isset($columns['del'])): ?>
            <th title="<?= l('Удалить товар') ?>">
                <?= $this->renderFile('products/list/column_filter', array(
                    'columns' => $columns
                )) ?>
            </th>
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
<script src="/manage/js/jquery-ui-1.9.0.custom.min.js"></script>
<script>
    $(function () {
        $(document).tooltip({
            items: "[data-preview], [data-warehouse]",
            content: function (callback) {
                var element = $(this);
                if (element.is("[data-preview]")) {
                    callback("<img class='large-preview' src='" + element.attr('src') + "' />");
                }

                if (element.is("[data-warehouse]")) {
                    $.get(prefix + 'products/ajax', {
                        act: 'on-warehouse',
                        id: element.attr('data-id')
                    }, function (data) {
                        if (data.state) {
                            callback(data.html);
                        }
                    });
                }
            }
        });
    });
</script>