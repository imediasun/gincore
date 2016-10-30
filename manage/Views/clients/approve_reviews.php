<?php if ($comments && count($comments) > 0): ?>
    <table class="table table-striped small-font">
        <thead>
        <tr>
            <td><?= l('Маркет') ?></td>
            <td><?= l('Товар') ?></td>
            <td><?= l('ФИО') ?></td>
            <td><?= l('Текст') ?></td>
            <td><?= l('Р') ?></td>
            <td><?= l('Да') ?></td>
            <td><?= l('Нет') ?></td>
            <td></td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($comments as $comment): ?>
            <?php if (array_key_exists('markets', $parser_configs)
                && array_key_exists($comment['market_id'], $parser_configs['markets'])
            ): ?>
                <tr id="comment_parse_remove-<?= $comment['id'] ?>">
                    <td><?= htmlspecialchars($parser_configs['markets'][$comment['market_id']]['market-name']) ?></td>
                    <td>
                        <a href="<?= $this->all_configs['prefix'] ?>products/create/<?= $comment['goods_id'] ?>"
                           data-action="sidebar_product" data-id_product="<?= $comment['goods_id'] ?>">
                            <?= htmlspecialchars($comment['title']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($comment['fio']) ?></td>
                    <td id="comment_parse_edit-<?= $comment['id'] ?>">
                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        <?php if (!empty($comment['advantages'])): ?>
                            <br><br><strong><?= l('Плюсы') ?>:</strong>
                            <?= nl2br(htmlspecialchars($comment['advantages'])) ?>
                        <?php endif; ?>
                        <?php if (!empty($comment['disadvantages'])): ?>
                            <br><br><strong><?= l('Минусы') ?>:</strong>
                            <?= nl2br(htmlspecialchars($comment['disadvantages'])) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($comment['rating']) ?></td>
                    <td><?= htmlspecialchars($comment['usefulness_yes']) ?></td>
                    <td><?= htmlspecialchars($comment['usefulness_no']) ?></td>
                    <td id="comment_parse_empty-<?= $comment['id'] ?>">
                        <!--<div class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?= l('Настройки') ?><b class="caret"></b></a>-->
                        <ul class="<!--dropdown-menu--> sett-dd-btns">
                            <li><input
                                    onclick="window.location.href='<?= $this->all_configs['prefix'] ?>clients / approve - reviews / create /<?= $comment['id'] ?>'"
                                    class="btn btn-info btn-mini" type="button" value="<?= l('Редактировать') ?>"/></li>
                            <li><input onclick="confirm_parse_comment(<?= $comment['id'] ?>, 0)"
                                       class="btn btn - success btn - mini"
                                       type="button" value=" <?= l('Подтвердить') ?> (<?= l('откл') ?>)"/>
                            </li>
                            <li><input onclick="confirm_parse_comment(<?= $comment['id'] ?>, 1)"
                                       class="btn btn-success btn-mini"
                                       type="button" value="<?= l('Подтвердить') ?> (<?= l('вкл') ?>)"/></li>
                            <li><input onclick="refute_parse_comment(<?= $comment['id'] ?>)"
                                       class="btn btn - danger btn - mini"
                                       type="button" value=" <?= l('Удалить') ?>"/></li>
                        </ul>
                        <!--</div>-->
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <tr>
        <td colspan="4"><p class="text-error"><?= l('Нет ни одного комментария') ?></p></td>
    </tr>
<?php endif; ?>

<?php if ($count_page > 1): ?>
    <?= page_block($count_page, $count_comments); ?>
<?php endif; ?>
