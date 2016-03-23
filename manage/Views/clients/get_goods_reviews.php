<a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/' . $this->all_configs['arrequest'][1] ?>/create"><?= l('Создать новый') ?></a>
<?php if ($reviews): ?>
    <table class="table table-striped">
        <thead>
        <td><?= l('Клиент') ?></td>
        <td><?= l('Комментарий') ?></td>
        <td><?= l('Дата') ?></td>
        <td><?= l('Рейтинг') ?></td>
        <td><?= l('Полезный') ?></td>
        <td><?= l('Бесполезный') ?></td>
        <td><?= l('Одобрен') ?></td>
        </tr></thead>
        <tbody>
        <tr>
            <?php foreach ($reviews as $comment): ?>
        <tr>
            <td>
                <?php if ($comment['user_id'] > 0): ?>
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
                <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/goods-reviews/create/<?= $comment['id'] ?>">
                    <?= ((mb_strlen($comment['text'], 'UTF-8') > 20) ? htmlspecialchars(mb_substr($comment['text'], 0,
                            20, 'UTF-8')) . '...' : htmlspecialchars($comment['text'])) ?>
                </a>
            </td>
            <td><span title="<?= do_nice_date($comment['date'], false) ?>"><?= do_nice_date($comment['date']) ?></span>
            </td>
            <td><?= $comment['rating'] ?></td>
            <td><?= (1 * $comment['usefulness_yes']) ?></td>
            <td><?= (1 * $comment['usefulness_no']) ?></td>
            <td><?= $comment['avail'] ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= page_block($count_page, $count_reviews); ?>
<?php else: ?>
    <p class="text-error"><?= l('Нет ни одного отзыва о товаре') ?></p>
<?php endif; ?>
