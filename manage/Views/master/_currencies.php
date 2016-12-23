<?php foreach ($currencies as $id => $currency): ?>
    <div class="clearfix checkbox-with-course">
        <div class="checkbox pull-left">
            <label>
                <input class="toggle-currency-course" type="checkbox" name="currencies[<?= $id ?>]"
                       value="<?= $id ?>">
                <?= h($currency['name']) ?>
            </label>
        </div>
        <div class="col-xs-3">
            <input class="hidden form-control currencies-courses" type="text" name="currencies_courses[<?= $id ?>]"
                   placeholder="<?= l('Укажите курс для') ?> <?= h($currency['name']) ?>">
        </div>
    </div>
<?php endforeach;
