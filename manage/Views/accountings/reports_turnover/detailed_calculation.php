<div class="detailed-calculation">
    <table class="table table-borderless">
        <thead>
        <tr>
            <th> <?= l('Id заказа'); ?> </th>
            <th> <?= l('Товары/Услуги') ?> </th>
            <th> <?= l('Себестоимость') ?> </th>
            <th> <?= l('Отпускная цена') ?> </th>
            <th> <?= l('Зарплата') ?> </th>
        </tr>
        </thead>
        <?php $salary = $selling_price = $cost_price = 0 ?>
        <?php if (!empty($user['detailed'])): ?>
            <tbody>
            <?php foreach ($user['detailed'] as $detailed): ?>
                <tr>
                    <th>
                        <?= $detailed['order_id']; ?>
                    </th>
                    <th>
                        <?= $detailed['product']; ?>
                    </th>
                    <th>
                        <?php $cost_price += $detailed['cost_price']; ?>
                        <?= $detailed['cost_price']; ?>
                    </th>
                    <th>
                        <?php $selling_price += $detailed['selling_price']; ?>
                        <?= $detailed['selling_price']; ?>
                    </th>
                    <th>
                        <?php $salary += $detailed['salary']; ?>
                        <?= $detailed['salary']; ?>
                        <?php if (!empty($detailed['percent'])): ?>
                            (<?= $detailed['percent'] ?>%)
                        <?php endif; ?>
                    </th>
                </tr>
            <?php endforeach; ?>
            </tbody>
        <?php endif; ?>
        <tfoot>
        <tr>
            <td> <?= count($user['detailed']) ?> <?= l('pcs.') ?> </td>
            <td></td>
            <td> <?= $cost_price ?> </td>
            <td> <?= $selling_price ?> </td>
            <td> <?= $salary ?> </td>
        </tr>
        </tfoot>
    </table>
</div>
