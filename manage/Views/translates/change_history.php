<h3><?= l('Изменения за последние 3 недели') ?></h3>
<table class="table table-bordered table-hover">
    <thead>
    <tr>
        <th><?= l('Дата') ?></th>
        <th><?= l('Секция') ?></th>
        <th><?= l('Язык') ?></th>
        <th>ID</th>
        <th><?= l('данные') ?></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($changes as $change): ?>
        <tr>
            <td title="<?= do_nice_date($change['change']['change_date'], false) ?>">
                <?= do_nice_date($change['change']['change_date']) ?>
            </td>
            <td><?= (isset($config[$change['change']['tbl']]['name']) ?
                    $config[$change['change']['tbl']]['name'] : $change['change']['tbl']) ?>
            </td>
            <td><?= $change['change']['lang'] ?></td>
            <td><?= $change['change']['id'] ?></td>
            <td><?= $change['data'] ?></td>
            <td>
                <a href="<?= $this->all_configs['prefix'] . $change['link'] ?>" target="_blank">
                    <?= l('Редактировать') ?>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
