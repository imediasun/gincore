<div class="span6">
    <div class="div-table order-comments div-table-scroll">
        <div class="div-thead">
            <div class="div-table-row">
                <div class="div-table-col span3" align="center"><?= l('Дата') ?></div>
                <div class="div-table-col span9">
                    <?= l('Скрытый комментарий') ?>
                </div>
            </div>
        </div>
        <div class="div-tbody">
            <?php if (count($comments_public) > 0 || count($comments_private) > 0): ?>
                <?php reset($comments_private); ?>
                <?php for ($i = 0; $i < count(max($comments_public, $comments_private)); $i++): ?>
                    <?php $comment_private = current($comments_private); ?>

                    <?php if ($comment_private): ?>
                        <div class="div-table-row">
                            <div class="div-table-col span3">
                                <small>
                        <span title="<?= do_nice_date($comment_private['date_add'],
                            false) ?>"><?= do_nice_date($comment_private['date_add']) ?>
                        </span>
                                </small>
                            </div>
                            <div class="div-table-col span9">
                                <small>
                                    <?= htmlspecialchars($comment_private['text']) ?>
                                    <span class="comment_user muted"><?= get_user_name($comment_private) ?></span>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php next($comments_private); ?>
                <?php endfor; ?>
            <?php endif; ?>
        </div>

        <?php if ($this->all_configs['oRole']->hasPrivilege('add-comment-to-clients-orders')): ?>
            <div class="div-tfoot">
                <div class="div-table-row">
                    <div class="div-table-col span12">
                        <textarea placeholder="<?= l('Данный комментарий видят только сотрудники') ?>"
                                  class="form-control" name="private_comment"></textarea>
                    </div>
                </div>
                <div class="div-table-row">
                    <div class="div-table-col span12">
                        <input name="add_private_comment" data-alert_box_not_disabled="true" class="btn btn-sm"
                               value="<?= l('Добавить') ?>" type="submit">
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
