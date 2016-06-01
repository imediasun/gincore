<?php if (!empty($locations)): ?>
    <?php foreach ($locations as $location_id => $location): ?>
        <input type="text" class="form-control" value="<?= h($location) ?>" name="location-id[<?= $location_id ?>]">
    <?php endforeach; ?>
    <input type="text" name="location[]" class="form-control">
<?php else: ?>
    <input type="text" name="location[]" class="form-control" required>
<?php endif; ?>

<i onclick="$('<input>').attr({type: 'text', name: 'location[]', class: 'form-control'}).insertBefore(this);"
   class="glyphicon glyphicon-plus cursor-pointer"></i>
