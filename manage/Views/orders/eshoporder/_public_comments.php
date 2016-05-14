<div class="span6">
    <div class="div-table order-comments div-table-scroll">
        <div class="div-thead">
            <div class="div-table-row">
                <div class="div-table-col span3" align="center"><?= l('Дата') ?></div>
                <div class="div-table-col span9">
                    <?= l('Публичный комментарий') ?>
                </div>
            </div>
        </div>
        <div class="div-tbody">
            <?php if (false && (count($comments_public) > 0 || count($comments_private) > 0)): ?>
                <?php reset($comments_public); ?>
                <?php for ($i = 0; $i < count(max($comments_public, $comments_private)); $i++): ?>
                    <?php $comment_public = current($comments_public); ?>

                    <?php if ($comment_public): ?>
                        <div class="div-table-row">
                            <div class="div-table-col span3">
                                <small><span title="<?= do_nice_date($comment_public['date_add'],
                                        false) ?>"><?= do_nice_date($comment_public['date_add']) ?></span></small>
                            </div>
                            <div class="div-table-col span9">
                                <small><?= htmlspecialchars($comment_public['text']); ?>
                                    <span class="comment_user muted"><?= get_user_name($comment_public) ?></span>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php next($comments_public); ?>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
        <?php if ($this->all_configs['oRole']->hasPrivilege('add-comment-to-clients-orders') && !$onlyEngineer): ?>
            <div class="div-tfoot">
                <div class="div-table-row">
                    <div class="div-table-col span12">
                        <textarea readonly placeholder="<?= l('Данный комментарий виден клиенту на сайте') ?>" class="form-control" name="public_comment"></textarea>
                    </div>
                </div>
                <div class="div-table-row">
                    <div class="div-table-col span12">
                        <input name="add_public_comment" data-alert_box_not_disabled="true" class="btn btn-sm disabled" disabled value="<?= l('Добавить') ?>" type="submit">
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
