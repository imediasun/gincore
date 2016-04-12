<select class="multiselect" name="cashboxes[]" multiple="multiple">
    <option <?= empty($cashboxes)?'selected':'' ?> value="-1"><?= l('Кассы не доступны') ?></option>
    <?php foreach ($cashboxes as $cashbox): ?>
        <?php if (!in_array($cashbox['id'], array(
            $this->all_configs['configs']['erp-cashbox-transaction'],
            $this->all_configs['configs']['erp-so-cashbox-terminal']
        ))): ?>
            <?php $sel = !empty($form_data['cashboxes']) && in_array($cashbox['id'],
                $form_data['cashboxes']) ? 'selected' : ''; ?>
            <option <?= $sel ?> value="<?= $cashbox['id'] ?>"><?= htmlspecialchars($cashbox['name']) ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>
