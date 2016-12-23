<?php if (!empty($locations)): ?>
    <?php foreach ($locations as $location_id => $location): ?>
        <input type="text" class="form-control m-t-sm" value="<?= h($location) ?>"
               name="location-id[<?= $location_id ?>]" <?= $readonly ?>>
    <?php endforeach; ?>
    <?php if (empty($readonly)): ?>
        <input type="text" name="location[]" class="form-control m-t-sm"/>
    <?php endif; ?>
<?php else: ?>
    <input type="text" name="location[]" class="form-control" required>
<?php endif; ?>

<?php if (empty($readonly)): ?>
    <i onclick="$('<input>').attr({type: 'text', name: 'location[]', class: 'form-control m-t-sm'}).insertBefore(this);"
       class="glyphicon glyphicon-plus cursor-pointer"></i>
<?php endif; ?>
