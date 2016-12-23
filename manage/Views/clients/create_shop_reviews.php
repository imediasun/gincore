<?php if (!$review): ?>
    <p class="text-error"><?= l('Нет такого отзыва') ?></p>
<?php else: ?>
    <form class="form-horizontal" method="post">
        <fieldset>
            <legend><?= l('Редактирование комментария о магазине') ?> ID: <?= $review['id'] ?>.</legend>
            <div class="control-group"><label class="control-label"><?= l('Клиент') ?>: </label>
                <div class="controls">
                    <?php if ($review['user_id'] > 0): ?>
                        <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create/<?= $review['user_id'] ?>/">
                            <?= htmlspecialchars($review['email']) ?>
                        </a>
                    <?php else: ?>
                        <?= htmlspecialchars($review['fio']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Комментарий') ?>: </label>
                <div class="controls">
                    <textarea class="span5" name="text"><?= htmlspecialchars($review['text']) ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Оценка') ?>: </label>
                <div class="controls">
                    <input type="radio" name="status"
                           value="1" <?= ($review['status'] == 1) ? 'checked' : '' ?> />
                    <?= $this->all_configs['configs']['reviews-shop-status'][1] ?>
                </div>
                <div class="controls">
                    <input type="radio" name="status"
                           value="2" <?= ($review['status'] == 2) ? 'checked' : '' ?> />
                    <?= $this->all_configs['configs']['reviews-shop-status'][2] ?>
                </div>
                <div class="controls">
                    <input type="radio" name="status"
                           value="3" <?= ($review['status'] == 2) ? 'checked' : '' ?> />
                    <?= $this->all_configs['configs']['reviews-shop-status'][3] ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Оценка изменения') ?>: </label>
                <div class="controls">
                    <input type="radio" name="become_status"
                           value="1" <?= ($review['become_status'] == 1) ? 'checked' : '' ?> />
                    <?= $this->all_configs['configs']['reviews-shop-become_status'][1] ?>
                </div>
                <div class="controls">
                    <input type="radio" name="become_status"
                           value="2" <?= ($review['status'] == 2) ? 'checked' : '' ?> />
                    <?= $this->all_configs['configs']['reviews-shop-become_status'][2] ?>
                </div>
                <div class="controls">
                    <input type="radio" name="become_status"
                           value="3" <?= ($review['status'] == 2) ? 'checked' : '' ?> />
                    <?= $this->all_configs['configs']['reviews-shop-become_status'][3] ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Одобрен') ?>: </label>
                <div class="controls">
                    <input type="checkbox" <?= ($review['avail'] == 1) ? 'checked' : '' ?> name="avail"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Дата') ?>: </label>
                <div class="controls">
                <span title="<?= do_nice_date($review['date'], false) ?>">
                    <?= do_nice_date($review['date']) ?>
                </span>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <input id="save_all_fixed" class="btn btn-primary" type="submit"
                           value="<?= l('Сохранить изменения') ?>" name="edit-shop-reviews">
                </div>
            </div>
        </fieldset>
    </form>
<?php endif; ?>

