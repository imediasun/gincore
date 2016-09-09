<?php foreach ($countryIds as $title => $id): ?>
    <option value="<?= $id ?>"><?=  h($title) ?></option>
<?php endforeach;
