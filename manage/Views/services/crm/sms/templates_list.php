<select class="form-control" id="sms_template_select" name="template_id">
    <option disabled selected><?= l('Выберите') ?></option>
    <?php foreach ($templates as $template): ?>
        <option data-body="<?= h($template['body']) ?>" value="<?= $template['id'] ?>">
            <?= h($template['var']) ?>
        </option>
    <?php endforeach; ?>
</select>
