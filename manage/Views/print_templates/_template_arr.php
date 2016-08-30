<?= l('В шаблоне возможно использование следующих переменных:') ?>
<table class="table-compact">
    <?php for ($i = 0; $i < count($arr); $i += 2): ?>
        <tr>
            <?php foreach (array_slice($arr, $i, 2) as $var => $title): ?>
                <td> {{<?= h($var) ?>}}</td>
                <td> <?= h($title) ?> </td>
            <?php endforeach; ?>
        </tr>
    <?php endfor; ?>
</table>
