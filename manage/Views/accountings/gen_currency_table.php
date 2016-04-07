<?php if (!empty($cashboxes_currencies)): ?>
    <?php foreach ($cashboxes_currencies as $cashbox_currency): ?>
        <?php
        $is_orders_currency = $cashbox_currency['currency'] == $this->all_configs['settings']['currency_orders'];
        $is_suppliers_currency = $cashbox_currency['currency'] == $this->all_configs['settings']['currency_suppliers_orders'];
        ?>
        <tr>
            <td><?= $this->all_configs['configs']['currencies'][$cashbox_currency['currency']]['name'] ?></td>
            <?php if (array_key_exists($cashbox_currency['currency'],
                    $this->all_configs['suppliers_orders']->currencies) && $is_orders_currency
            ): ?>
                <td>
                    1 <?= $cashbox_currency['short_name']?>
                    = 1 <?= $cashbox_currency['short_name']?>
                </td>
            <?php else: ?>
                <td>
                    1 <?= $cashbox_currency['short_name'] ?> =
                    <input style='width:60px' class='input-sm inline-block form-control' type='text'
                           name='cashbox_course[<?= $cashbox_currency['currency'] ?>]' placeholder='<?= l(' Курс') ?>'
                    value='<?= show_price($cashbox_currency['course']) ?>'
                    onkeydown='return isNumberKey(event, this)' />
                    <span class='main_currency_name'><?= viewCurrency('shortName') ?></span>
                </td>
            <?php endif; ?>
            <?php if ($is_orders_currency): ?>
                <td><b><?= l('Основная валюта') ?></b></td>
            <?php elseif ($is_suppliers_currency): ?>
                <td><b><?= l('Валюта заказов поставщикам') ?></b></td>
            <?php else: ?>
                <td>
                    <i class='glyphicon glyphicon-remove remove_currency' onclick='remove_currency(this)'
                       data-currency_id='<?= $cashbox_currency['currency'] ?>'></i>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan='3'>
            <input type='submit' class='btn btn-primary' name='cashboxes-currencies-edit'
                   value='<?= l('Сохранить') ?>'/>
        </td>
    </tr>

<?php endif; ?>