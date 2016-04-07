<div class="row-fluid">
    <div class="col-sm-12">
        <table class="table">
            <thead>
                <tr>
                    <th><?= l('Номер заказа') ?></th>
                    <th><?= l('Рейтинг') ?></th>
                    <th><?= l('Комментарий') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ratings as $rating): ?>
                <tr>
                    <td>
                        <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $rating['order_id'] ?>"><?= $rating['order_id'] ?></a>
                    </td>
                    <td>
                        <?= $rating['rating'] ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($rating['comment']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>