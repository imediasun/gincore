<select class="multiselect input-small" data-type="<?= $name ?>" multiple="multiple"
        name="<?= $name ?>_id[]">
    <?= build_array_tree($options, $selectedOptions) ?>
</select>
