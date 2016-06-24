<select class="form-control" name="data[type]">
    <option disabled selected><?= l('Выберите тип шаблона') ?></option>
    <?php foreach ($types as $type => $id): ?>
        <option value="<?= $id ?>" data-type='<?= $type ?>' onclick="show_variables(this);"> <?= l($type) ?></option>
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

<script>
    function show_variables(_this) {
        var type = $(_this).data('type');
        console.log(type);
        $('.js-variables').hide();
        $('.js-' + type + '_variables').show();
    }
</script>