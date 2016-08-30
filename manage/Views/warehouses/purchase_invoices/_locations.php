<?php if ($locations): ?>
    <?php foreach ($locations as $id => $location): ?>
        <option <?= (in_array($id, $location_id) ? 'selected' : '') ?> value="<?= $id ?>"><?= h($location) ?></option>
    <?php endforeach; ?>
<?php endif; ?>
