<div class="row-fluid">
    <div class="col-sm-12">
        <table class="table">
            <tbody>
            <?php foreach ($ratings as $rating): ?>
                <tr>
                    <td>
                        <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $rating['order_id']?>"><?= $rating['order_id'] ?></a>
                    </td>
                    <td>
                        <?= $rating['rating'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>