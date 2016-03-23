<?php if (!$review): ?>
    <p class="text-error"><?= l('Нет такого отзыва') ?></p>
<?php else: ?>
    <form class="form-horizontal" method="post">
        <fieldset>
            <legend><?= l('Редактирование не подтвержденного отзыва о товаре') ?> ID: <?= $review['id'] ?>.</legend>
            <div class="control-group">
                <label class="control-label"><?= l('Клиент') ?>: </label>
                <div class="controls">
                    <input type="text" value="<?= htmlspecialchars($review['fio']) ?>" class="span5" name="fio"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Товар') ?>: </label>
                <div class="controls">
                    <a href="<?= $this->all_configs['prefix'] ?>products/create/<?= $review['goods_id'] ?>/">
                        <?= htmlspecialchars($review['title']) ?>
                    </a>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Комментарий') ?>: </label>
                <div class="controls">
                    <textarea class="span5" name="text"><?= htmlspecialchars($review['content']) ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Плюсы') ?>: </label>
                <div class="controls">
                    <textarea class="span5" name="advantages"><?= htmlspecialchars($review['advantages']) ?></textarea>
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
                           name="usefulness_yes" value="<?= (1 * $review['usefulness_yes']) ?>"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Бесполезность') ?>: </label>
                <div class="controls">
                    <input type="text" class="span5" onkeydown="return isNumberKey(event)"
                           name="usefulness_no" value="<?= (1 * $review['usefulness_no']) ?>"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?= l('Дата') ?>: </label>
                <div class="controls">
                    <input type="text" value="<?= date(" d.m.Y", strtotime($review['date_add'])) ?>"
                           class="span5 edit_date" name="date_add"/>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <input id="save_all_fixed" class="btn btn-primary" type="submit"
                           value="<?= l('Сохранить изменения') ?>" name="edit-approve-reviews">
                </div>
            </div>
        </fieldset>
    </form>
<?php endif; ?>