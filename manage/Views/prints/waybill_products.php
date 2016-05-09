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
                <?= h($good['price']) ?>
            </td>
            <td>
                <?= h($good['discount']) ?>
            </td>
            <td>
                <?= $good['count'] * $good['price'] * (1 - $good['discount']/100) ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
