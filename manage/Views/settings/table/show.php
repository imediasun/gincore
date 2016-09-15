<h3>
    <?= $conf[$table]['settings']['name'] ?>
    <?php if ($table === 'restore4_brands'): ?>
        <a href="<?= $this->all_configs['prefix'] . 'settings' . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add"
           class="btn btn-primary"><?= l('Добавить') ?></a>
    <?php endif; ?>
</h3>
<table class="table table-bordered table-hover">
    <thead>
    <tr>
        <th width="14"></th>
        <th width="14"></th>
        <?php foreach ($columns as $pp): ?>
            <?php if (isset($conf[$table]['columns'][$pp['Field']][0]) && $conf[$table]['columns'][$pp['Field']][0] != 1): ?>
                <th>
                    <?= isset($conf[$table]['columns'][$pp['Field']]) && $conf[$table]['columns'][$pp['Field']] ?
                        $conf[$table]['columns'][$pp['Field']][2] : $pp['Field']; ?>
                </th>
            <?php endif; ?>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($rows as $mm): ?>
        <?php $mm = array_values($mm); ?>
        <tr>
            <td>
                <a href="<?= $this->all_configs['prefix'] ?>settings/<?= $this->all_configs['arrequest'][1] ?>/edit/<?= $mm[0] ?>"
                   class="glyphicon glyphicon-pencil"></a>
            </td>
            <td>
                <a href="<?= $this->all_configs['prefix'] ?>settings/<?= $this->all_configs['arrequest'][1] ?>/del/<?= $mm[0] ?>"
                   class="glyphicon glyphicon-remove" onclick="return confirm('<?= l('Удалить') ?>?');">
                </a>
            </td>

            <?php for ($i = 0; $i < count($cols); $i++): ?>
                <?php if (!isset($conf[$table]['columns'][$cols[$i]][0]) || $conf[$table]['columns'][$cols[$i]][0] != 1): ?>
                    <td><?= $mm[$i] ?></td>
                <?php endif; ?>
            <?php endfor; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

