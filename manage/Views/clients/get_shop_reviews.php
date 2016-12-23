<a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] ?>/create"><?= l('Создать новый') ?></a>
<?php if ($reviews && count($reviews) > 0): ?>
    <table class="table table-striped">
        <thead>
        <td><?= l('Клиент') ?></td>
        <td><?= l('Комментарий') ?></td>
        <td><?= l('Дата') ?></td>
        <td><?= l('Оценка') ?></td>
        <td><?= l('Оценка изменения') ?></td>
        <td><?= l('Одобрен') ?></td>
        </tr></thead>
        <tbody>
        <tr>
            <?php foreach ($reviews as $comment): ?>
        <tr>
            <td><?php if ($comment['user_id'] > 0): ?>
                    <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create/<?= $comment['user_id'] ?>">
                        <?= htmlspecialchars($comment['email']) ?>,
                        <?= htmlspecialchars($comment['phone']) ?>,
                        <?= htmlspecialchars($comment['fio']) ?>
                    </a>
                <?php else: ?>
                    <?= htmlspecialchars($comment['fio']) ?>
                <?php endif; ?>
            </td>
            <td>
                <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] ?> / create /<?= $comment['id'] ?>">
                    <?= ((mb_strlen($comment['text'], 'UTF-8') > 20) ? htmlspecialchars(mb_substr($comment['text'], 0,
                            20, 'UTF-8')) . '...' : htmlspecialchars($comment['text'])) ?>
                </a>
            </td>
            <td><span title="<?= do_nice_date($comment['date'], false) ?>"><?= do_nice_date($comment['date']) ?></span>
            </td>
            <td><?= ((array_key_exists($comment['become_status'],
                    $this->all_configs['configs']['reviews-shop-become_status'])) ?
                    $this->all_configs['configs']['reviews-shop-become_status'][$comment['become_status']] : '') ?>
            </td>
            <td><?= ((array_key_exists($comment['status'],
                    $this->all_configs['configs']['reviews-shop-status'])) ?
                    $this->all_configs['configs']['reviews-shop-status'][$comment['status']] : '') ?>
            </td>
            <td><?= $comment['avail'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-error"><?= l('Нет ни одного отзыва о магазине') ?></p>
<?php endif; ?>
