<select class="form-control" name="sender_id" id="sms_sender_select">
    <option disabled selected><?= l('Выберите') ?></option>
    <?php foreach ($senders as $sender): ?>
        <option value="<?= $sender['id'] ?>"><?= h($sender['sender']) ?></option>
    <?php endforeach; ?>
</select>
