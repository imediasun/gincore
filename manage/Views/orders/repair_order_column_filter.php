<div class="btn-group js-repair-order-column-filter" style="margin-left: 5px">
    <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
        <i class="fa fa-cog" aria-hidden="true"></i> <i class="fa fa-caret-down"></i>
    </a>
    <ul class="dropdown-menu pull-right" style="max-height: 600px">
        <li>
            <form method="POST" style="width: 200px">
                <fieldset>
                    <?php foreach (array(
                        'npp' => 'Номер заказа',
                        'notice' => 'Напоминания',
                        'date' => 'Дата',
                        'accepter' => 'Приемщик',
                        'manager' => 'Менеджер',
                        'engineer' => 'Инженер',
                        'status' => 'Статус',
                        'components' => 'Запчасти',
                        'services' => 'Работы',
                        'device' => 'Устройство',
                        'amount' => 'Стоимость',
                        'paid' => 'Оплачено',
                        'client' => 'Клиент',
                        'phone' => 'Контактный телефон',
                        'terms' => 'Сроки',
                        'location' => 'Склад',
                        'sn' => 'Серийный номер',
                        'repair' => 'Тип ремонта',
                        'date_end' => 'Дата готовности',
                        'warranty' => 'Гарантия',
                        'adv_channel' => 'Рекламный канал'
                    ) as $item => $name): ?>
                        <div class="checkbox col-sm-12">
                            <label>
                                <input type="checkbox" name="<?= $item ?>" <?= isset($columns[$item]) ? 'checked' : '' ?>> <?= l($name) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-sm-12" style="white-space: nowrap">
                        <button type="submit" name='repair-order-table-columns'
                                class="btn btn-primary"> <?= l('Применить') ?> </button>
                    </div>
                </fieldset>
            </form>
        </li>
    </ul>
</div>
