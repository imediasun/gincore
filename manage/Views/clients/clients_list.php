<?= $clients_filters ?>
<?php if ($clients && count($clients) > 0): ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <td>ID</td>
            <td></td>
            <td><?= l('Метка') ?></td>
            <td><?= l('Ф.И.О.') ?></td>
            <td><?= l('Телефон') ?></td>
            <td><?= l('Адрес') ?></td>
            <td><?= l('Эл.почта') ?></td>
            <td><?= l('Дата регистрации') ?></td>
        </tr>
        <tbody>
        <?php foreach ($clients as $client): ?>
            <?php if (array_key_exists('manage-system-clients', $this->all_configs['configs'])
                && is_array($this->all_configs['configs']['manage-system-clients'])
                && count($this->all_configs['configs']['manage-system-clients']) > 0
                && in_array($client['id'], $this->all_configs['configs']['manage-system-clients'])
            ): ?>
                <?php continue; ?>
            <?php endif; ?>
            <tr>
                <td>
                    <a href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/create/<?= $client['id'] ?>">
                        <?= $client['id'] ?>
                    </a>
                </td>
                <td>
                    <?= show_marked($client['id'], 'cl', $client['m_id']) ?>
                </td>
                <td>
                    <?php if ($client['tag_id'] != 0): ?>
                        <a href=" <?= $this->all_configs['prefix'] . $arrequest[0] ?>/create/<?= $client['id'] ?>">
                            <span class="tag" style="background-color: <?= $tags[$client['tag_id']]['color'] ?>">
                                <?= h($tags[$client['tag_id']]['title']) ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </td>
                <td>
                    <a href=" <?= $this->all_configs['prefix'] . $arrequest[0] ?>/create/<?= $client['id'] ?>">
                        <?= h($client['fio']) ?>
                    </a>
                </td>
                <td>
                    <a href=" <?= $this->all_configs['prefix'] . $arrequest[0] ?>/create/<?= $client['id'] ?>">
                        <?= h($client['phone']) ?>
                    </a>
                </td>
                <td>
                    <a href=" <?= $this->all_configs['prefix'] . $arrequest[0] ?>/create/<?= $client['id'] ?>">
                        <?= h($client['legal_address']) ?>
                    </a>
                </td>
                <td>
                    <a href=" <?= $this->all_configs['prefix'] . $arrequest[0] ?>/create/<?= $client['id'] ?>">
                        <?= h($client['email']) ?>
                    </a>
                </td>
                <td>
                    <span title=" <?= do_nice_date($client['date_add'], false) ?>">
                        <?= do_nice_date($client['date_add']) ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?= page_block($count_page, $count); ?>
<?php else: ?>
    <p class="text-error"><?= l('Нет ни одного клиента') ?></p>
<?php endif; ?>
