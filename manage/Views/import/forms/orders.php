<div class="form-group">
    <p class="bg-danger" style="padding: 15px">
        <?= l('Внимание!') ?> <br><br>
        <?= l('Перед импортом файла, обязательно добавьте в систему Gincore сотрудников.') ?><br>
        <?= l('ФИО сотрудника в Gincore долно совпадать с именем сотрудника в файле иморта.') ?><br>
        <?= l('Иначе система не сможет привязать к заказам Приемщика, Инженера, Менеджера.') ?>
    </p>
</div>
<div class="form-group">
    <div class="checkbox">
        <label>
            <input type="checkbox" name="accepter_as_manager" value="1">
            <?= l('назначить приемщика менеджером, если последний не указан') ?>
        </label>
    </div>
</div>
