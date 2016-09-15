<?php foreach ($currencies as $id => $currency): ?>
    <option data-symbol="<?= h($currency['symbol']) ?>" value="<?= $id ?>"><?= h($currency['name']) ?></option>
<?php endforeach;
