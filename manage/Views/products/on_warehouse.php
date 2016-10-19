<p><?= l('Детально по складам') ?></p>
<?php if(!empty($goods)): ?>
<table class="table table-borderless table-compact">
    <thead>
    <tr>
        <th style="border-bottom: 1px solid"> <?= l('Склад') ?> </th>
        <th style="border-bottom: 1px solid"> <?= l('Локация') ?> </th>
        <th style="border-bottom: 1px solid"> <?= l('Общий') ?> </th>
        <th style="border-bottom: 1px solid"> <?= l('Свободный') ?> </th>
    </tr>
    </thead>
    <tbody>
        <?php foreach ($goods as $good): ?>
            <tr>
                <td> <?= h($good['wh']) ?> </td>
                <td> <?= h($good['location']) ?> </td>
                <td style="text-align:center"> <?= h($good['all_on_wh']) ?> </td>
                <td style="text-align:center"> <?= h($good['free']) ?> </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>