<?php if (!empty($phones)): ?>
    <?php foreach ($phones as $phone): ?>
        <input<?=input_phone_mask_attr()?> class="form-control clone_clear_val m-t-sm" type="text" onkeydown="return isNumberKey(event)"
               name="phone[]" value="<?= htmlspecialchars($phone) ?>"/>
    <?php endforeach; ?>
<?php else: ?>
    <input<?=input_phone_mask_attr()?> class="form-control clone_clear_val" type="text" onkeydown="return isNumberKey(event)"
           name="phone[]" value=""/>
<?php endif; ?>