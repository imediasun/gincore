<?php if (!empty($goods)): ?>
    <?php $i = 1; ?>
    <?php foreach ($goods as $good): ?>
        <tr>
            <td>
                <?= $i++ ?>
            </td>
            <td>
                <?= h($good['title']) ?>
            </td>
            <td>
                <?= h($good['count']) ?>
            </td>
            <td>
                <?= h($good['price'])/100 ?>
            </td>
            <td>
                <?= h($good['discount']) ?>
            </td>
            <td>
                <?= sum_with_discount($good) ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
