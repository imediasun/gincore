<?php if ($tags): ?>
    <div class="col-sm-6">
        <label class="control-label"><?= l('Статус клиента(метка)') ?>: </label>
        <div class="controls">
            <select name="tag_id" class="form-control tags-list">
                <option value=""><?= l('Не выбран') ?></option>
                <?php foreach ($tags as $tag): ?>
                    <option <?= ($tag['id'] == $client['tag_id']) ? 'selected' : '' ?>value="<?= $tag['id'] ?>" style="background-color: <?= $tag['color'] ?>;">
                        <?= htmlspecialchars($tag['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
<?php endif; ?>
