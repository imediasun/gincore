<?php if ($tags): ?>
    <div class="<?= !empty($new_client) ? 'span7' : 'col-sm-7' ?>">
        <label class="control-label"><?= l('Статус клиента(метка)') ?>: </label>
        <?= !empty($infopopover) ? $infopopover : '' ?>
    </div>
    <div class="<?= !empty($new_client) ? 'span5' : 'col-sm-5' ?>" style="text-align: right; min-width: 150px">
        <select name="tag_id" class="form-control multiselect">
            <option value=""><?= l('Не выбран') ?></option>
            <?php foreach ($tags as $tag): ?>
                <option <?= ($tag['id'] == $client['tag_id']) ? 'selected' : '' ?> value="<?= $tag['id'] ?>">
                    <?= htmlspecialchars($tag['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="clearfix"></div>
<?php endif; ?>
