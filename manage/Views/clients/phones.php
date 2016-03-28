<?php if ($phones && $phones > 0): ?>
    <?php foreach ($phones as $phone): ?>
        <input class="form-control clone_clear_val" type="text" onkeydown="return isNumberKey(event)"
               name="phone[]" value="<?= htmlspecialchars($phone) ?>"/>
    <?php endforeach; ?>
<?php else: ?>
    <input class="form-control clone_clear_val" type="text" onkeydown="return isNumberKey(event)"
           name="phone[]" value=""/>
<?php endif; ?>