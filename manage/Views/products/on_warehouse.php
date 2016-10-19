<div class="row-fluid" style="min-width: 350px">
    <div class="col-sm-12">
        <p> <?= l('Детально по складам') ?></p>
        <?php if (!empty($goods)): ?>
            <table class="table table-borderless table-compact table-goods">
                <thead>
                <tr>
                    <th> <?= l('Склад') ?> </th>
                    <th> <?= l('Локация') ?> </th>
                    <th> <?= l('Общий') ?> </th>
                    <th> <?= l('Свободный') ?> </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($goods as $good): ?>
                    <tr>
                        <td> <?= h($good['wh']) ?> </td>
                        <td> <?= h($good['location']) ?> </td>
                        <td style="text-align:center;"> <?= h($good['all_on_wh']) ?> </td>
                        <td style="text-align:center;"> <?= h($good['free']) ?> </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>
