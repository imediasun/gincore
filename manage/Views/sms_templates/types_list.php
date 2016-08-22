<select required class="form-control" name="data[type]" onchange="return show_variables(this);">
    <option disabled selected><?= l('Выберите тип шаблона') ?></option>
    <?php foreach ($types as $type => $id): ?>
        <option value="<?= $id ?>" <?= $current == $id? 'selected': ''?> data-type='<?= $type ?>' > <?= l($type) ?></option>
    <?php endforeach; ?>
</select>

<div class="js-variables js-orders_variables" style="display:none">
    <?= l('В шаблоне возможно использование следующих переменных:') ?>
    <table class="table-compact">
        <tr>
            <td> {{order_id}}</td>
            <td> <?= l('Номер заказа') ?> </td>
        </tr>
        <tr>
            <td> {{pay}}</td>
            <td> <?= l('Сумма к оплате') ?> </td>
        </tr>
        <tr>
            <td> {{order_sum}}</td>
            <td> <?= l('Сумма заказа') ?> </td>
        </tr>
        <tr>
            <td> {{client}}</td>
            <td> <?= l('ФИО клиента') ?> </td>
        </tr>
        <tr>
            <td> {{warehouse}}</td>
            <td> <?= l('Склад') ?> </td>
        </tr>
        <tr>
            <td> {{location}}</td>
            <td> <?= l('Локация') ?> </td>
        </tr>
        <tr>
            <td> {{warehouse_address}}</td>
            <td> <?= l('Адрес склада') ?> </td>
        </tr>
        <tr>
            <td> {{warehouse_phone}}</td>
            <td> <?= l('Телефон склада') ?> </td>
        </tr>
    </table>
</div>
<div class="js-variables js-engineer_notify_variables" style="display:none">
    <?= l('В шаблоне возможно использование следующих переменных:') ?>
    <table class="table-compact">
        <tr>
            <td> {{order_id}}</td>
            <td> <?= l('Номер заказа') ?> </td>
        </tr>
        <tr>
            <td> {{client}}</td>
            <td> <?= l('ФИО клиента') ?> </td>
        </tr>
        <tr>
            <td> {{phone}}</td>
            <td> <?= l('Телефон клиента') ?> </td>
        </tr>
        <tr>
            <td> {{address}}</td>
            <td> <?= l('Адрес клиента') ?> </td>
        </tr>
    </table>
</div>

<script>
    function show_variables(_this) {
        var type = $(_this).find("option:selected").first().data('type');
        $('.js-variables').hide();
        $('.js-' + type + '_variables').show();
        return true;
    }
</script>