<?php foreach ($countryIds as $title => $id): ?>
    <option value="<?= $id ?>" <?= (!empty($country) && $country['id'] == $id ? 'selected' : '') ?>><?=  h($title) ?></option>
<?php endforeach;
