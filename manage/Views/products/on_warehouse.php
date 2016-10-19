<p> <?= l('Детально по складам') ?></p>
<?php if(!empty($goods)): ?>
<table class="table table-borderless table-compact">
    <thead>
    <tr>
        <th style="border-bottom: 1px solid #ddd"> <?= l('Склад') ?> </th>
        <th style="border-bottom: 1px solid #ddd"> <?= l('Локация') ?> </th>
        <th style="border-bottom: 1px solid #ddd"> <?= l('Общий') ?> </th>
        <th style="border-bottom: 1px solid #ddd"> <?= l('Свободный') ?> </th>
    </tr>
    </thead>
    <tbody>
        <?php foreach ($goods as $good): ?>
            <tr>
                <td style="padding: 3px"> <?= h($good['wh']) ?> </td>
                <td style="padding: 3px"> <?= h($good['location']) ?> </td>
                <td style="text-align:center; padding: 3px"> <?= h($good['all_on_wh']) ?> </td>
                <td style="text-align:center; padding: 3px"> <?= h($good['free']) ?> </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>