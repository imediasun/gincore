<select class="form-control" id="sms_template_select" name="template_id">
    <option disabled selected><?= l('Выберите шаблон') ?></option>
    <?php if (!empty($default)): ?>
        <?php foreach ($default as $name => $text): ?>
            <option data-body="<?= h($text) ?>" value="<?= $name ?>">
                <?= h($name) ?>
            </option>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php foreach ($templates as $template): ?>
        <option data-body="<?= h($template['body']) ?>" value="<?= $template['id'] ?>">
            <?= h($template['var']) ?>
        </option>
    <?php endforeach; ?>
</select>
