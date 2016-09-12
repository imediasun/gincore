<?php if (!empty($phones)): ?>
    <?php foreach ($phones as $phone): ?>
        <input <?= input_phone_mask_attr() ?> class="form-control" type="text"
                                             onkeydown="return isNumberKey(event)"
                                             name="phone[]" value="<?= h($phone) ?>"/>
    <?php endforeach; ?>
<?php endif; ?>
<input <?= input_phone_mask_attr() ?> class="form-control clone_clear_val" type="text"
                                     onkeydown="return isNumberKey(event)"
                                     name="phone[]" value=""/>
