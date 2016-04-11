<div class="row-fluid">
    <div class="col-sm-12">
        <h2>
            <?= $tariff['name'] ?>
        </h2>
        <table class="table ">
            <thead>
            <tr>
                <th><?= l('Параметр') ?></th>
                <th><?= l('Значение') ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?= l('Использутся с') ?> </td>
                <td> <?= date('d-m-Y', $tariff['start']) ?> </td>
            </tr>
            <tr>
                <td> <?= l('Максимальное число пользователей системой') ?> </td>
                <td> <?= $tariff['number_of_users'] ?></td>
            </tr>
            <tr>
                <td> <?= l('Текущее количество пользователей') ?> </td>
                <td> <?= $usersCount ?></td>
            </tr>
            <tr>
                <td> <?= l('Максимальное число заказов') ?> </td>
                <td> <?= $tariff['number_of_orders'] ?></td>
            </tr>
            <tr>
                <td> <?= l('Текущее количество заказов') ?> </td>
                <td> <?= $ordersCount ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
