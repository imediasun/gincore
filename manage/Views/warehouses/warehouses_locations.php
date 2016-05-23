<?php if (!empty($locations)): ?>
    <?php foreach ($warehouse['locations'] as $location_id => $location): ?>
        <input type="text" class="form-control" value="<?= h($location) ?>" name="location-id[<?= $location_id ?>]">
    <?php endforeach; ?>
<?php endif; ?>

<input type="text" name="location[]" class="form-control" required>
<i onclick="$('<input>').attr({type: 'text', name: 'location[]', class: 'form-control'}).insertBefore(this);"
   class="glyphicon glyphicon-plus cursor-pointer"></i>
