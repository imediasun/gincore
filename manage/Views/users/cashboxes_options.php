<select class="multiselect" name="cashboxes[]" multiple="multiple">
    <?php foreach ($cashboxes as $cashbox): ?>
        <?php $sel = !empty($form_data['cashboxes']) && in_array($cashbox['id'],
            $form_data['cashboxes']) ? 'selected' : ''; ?>
        <option <?= $sel ?> value="<?= $cashbox['id'] ?>"><?= htmlspecialchars($cashbox['name']) ?></option>
    <?php endforeach; ?>
</select>
