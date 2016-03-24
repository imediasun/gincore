<?php if ($tags): ?>
        <div class="col-sm-6">
            <label class="control-label"><?= l('Статус') ?>: </label>
        </div>
        <div class="col-sm-6">
            <select name="tag_id" class="form-control multiselect">
                <option value=""><?= l('Не выбран') ?></option>
                <?php foreach ($tags as $tag): ?>
                    <option <?= ($tag['id'] == $client['tag_id']) ? 'selected' : '' ?> value="<?= $tag['id'] ?>">
                        <?= htmlspecialchars($tag['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
<?php endif; ?>
