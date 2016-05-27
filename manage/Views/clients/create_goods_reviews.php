<form class="form-horizontal" method="post">
    <fieldset>
        <legend><?= l('Редактирование отзыва о товаре') ?> ID: <?= $review['id'] ?>.</legend>
        <div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#review" data-toggle="tab"><?= l('Отзыв') ?></a></li>
                <li><a href="#comments" data-toggle="tab"><?= l('Комментарии') ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="review" class="tab-pane active">
                    <div class="control-group">
                        <label class="control-label"><?= l('Клиент') ?>: </label>
                        <div class="controls">
                            <?php if ($review['user_id'] > 0): ?>
                                <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create/<?= $review['user_id'] ?> /">
                                    <?= htmlspecialchars($review['email']) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($review['fio']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?= l('Товар') ?>: </label>
                        <div class="controls">
                            <a href="<?= $this->all_configs['prefix'] ?>products/create/<?= $review['goods_id'] ?>/#imt-comments">
                                <?= htmlspecialchars($review['title']) ?>
                            </a>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?= l('Комментарий') ?>: </label>
                        <div class="controls">
                            <textarea class="span5" name="text"><?= htmlspecialchars($review['text']) ?></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?= l('Плюсы') ?>: </label>
                        <div class="controls">
                            <textarea class="span5"
                                      name="advantages"><?= htmlspecialchars($review['advantages']) ?></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?= l('Минусы') ?>: </label>
                        <div class="controls">
                            <textarea class="span5"
                                      name="disadvantages"><?= htmlspecialchars($review['disadvantages']) ?></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?= l('Рейтинг') ?>: </label>
                        <div class="controls">
                            <select name="rating" class="span5">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($review['rating'] == $i): ?>
                                        <option value="<?= $i ?>" selected><?= $i ?></option>
                                    <?php else: ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?= l('Полезность') ?>: </label>
                        <div class="controls">
                            <input type="text" class="span5" onkeydown="return isNumberKey(event)"
                                   name="usefulness_yes"
                                   value="<?= (1 * $review['usefulness_yes']) ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?= l('Бесполезность') ?>: </label>
                        <div class="controls">
                            <input type="text" class="span5" onkeydown="return isNumberKey(event)"
                                   name="usefulness_no"
                                   value="<?= (1 * $review['usefulness_no']) ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?= l('Одобрен') ?>: </label>
                        <div class="controls">
                            <input type="checkbox" <?= $review['avail'] == 1 ? 'checked' : '' ?> name="avail"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?= l('Дата') ?>: </label>
                        <div class="controls">
                            <span title="<?= do_nice_date($review['date'],
                                false) ?>"><?= do_nice_date($review['date']) ?></span>
                        </div>
                    </div>
                </div>
                <div id="comments" class="tab-pane">
                    <table class="table table-striped">
                        <thead>
                        <td><?= l('Клиент') ?></td>
                        <td><?= l('Комментарий') ?></td>
                        <td><?= l('Дата') ?></td>
                        <td><?= l('Одобрен') ?></td>
                        </tr></thead>
                        <tbody>
                        <?php if ($comments && count($comments) > 0): ?>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <input type="hidden" name="comments_client[<?= $comment['id'] ?>]"
                                           value="<?= $comment['id'] ?>"/>
                                    <td>
                                        <a href="<?= $this->all_configs['prefix'] ?>clients/create/<?= $comment['client_id'] ?>">
                                            <?= htmlspecialchars($comment['email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <textarea сlass="span5"
                                          name="comments_text[<?= $comment['id'] ?>]"><?= htmlspecialchars($comment['text']) ?></textarea>
                                    </td>
                                    <td>
                                        <span title="<?= do_nice_date($comment['date'], false) ?>">
                                            <?= do_nice_date($comment['date']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input сlass="span5" <?= $comment['avail'] == 1 ? 'checked' : '' ?>
                                               type="checkbox"
                                               name="comments_avail[<?= $comment['id'] ?>]"/>
                                    </td>
                                </tr>
                            <?php endforeach;; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4"><p class="text-error"><?= l('Нет ни одного комментария') ?></p></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <input id="save_all_fixed" class="btn btn-primary" type="submit"
                       value="<?= l('Сохранить изменения') ?>" name="edit-goods-reviews">
            </div>
        </div>
    </fieldset>
</form>
