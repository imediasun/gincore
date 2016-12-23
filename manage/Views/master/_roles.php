<?php if (!empty($roles)): ?>
    <?php foreach ($roles as $id => $role): ?>
        <option value="<?= $id ?>"><?= h($role) ?></option>
    <?php endforeach; ?>
<?php endif;
