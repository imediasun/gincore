<div class="row-fluid">
    <div class="col-sm-12">
        <table class="table">
            <thead>
            <tr>
                <th> <?= l('Наименование') ?> </th>
                <th> <?= l('Цена') ?> </th>
                <th> <?= l('Количество') ?> </th>
                <th> <?= l('Сумма') ?> </th>
                <th></th>
            </tr>
            </thead>
            <?php if (!empty($cart)): ?>
                <tbody>
                <?php foreach ($cart as $id => $quantity): ?>
                    <tr>
                        <td> <?= h($goods[$id]['title']) ?> </td>
                        <td> <?= round($goods[$id]['price'] / 100, 2) ?> </td>
                        <td><input type="text" class="form-control" value="<?= $quantity ?>"/></td>
                        <td><a href="#" class="js-delete-item-from-cart"><i class="fa fa-times" aria-hidden="true"></i>
                            </a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            <?php endif; ?>
            <tfoot>
            <tr>
                <td> <?= l('Итого') ?> </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>