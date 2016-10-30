<div class="btn-group js-repair-order-column-filter" style="margin-left: 5px">
    <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
        <i class="fa fa-cog" aria-hidden="true"></i> <i class="fa fa-caret-down"></i>
    </a>
    <ul class="dropdown-menu pull-right" style="max-height: 600px">
        <li>
            <form method="POST" style="width: 200px" class="popup-form">
                <fieldset>
                    <?php foreach (array(
                        'id' => 'ID',
                        'photo' => 'Фото',
                        'title' => 'Название продукта',
                        'vc' => 'Артикул',
                        'price' => 'Цена',
                        'rprice' => 'Цена закупочная',
                        'wprice' => 'Цена оптовая',
                        'balance' => 'Общий остаток',
                        'fbalance' => 'Свободный остаток',
                        'sbalance' => 'Наличие у поставщиков',
                        'delivery' => 'Ожидаемые поставки',
                        'cart' => 'Корзина',
                        'mbalance' => 'Неснижаемый остаток',
                        'type' => 'Товар/услуга',
                        'manager' => 'Менеджер',
                        'date' => 'Дата',
                        'del' => 'Удалить товар'
                    ) as $item => $name): ?>
                        <div class="checkbox col-sm-12">
                            <label>
                                <input type="checkbox"
                                       name="<?= $item ?>" <?= isset($columns[$item]) ? 'checked' : '' ?>> <?= l($name) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-sm-12" style="white-space: nowrap">
                        <button type="submit" name='products-table-columns'
                                class="btn btn-primary"> <?= l('Применить') ?> </button>
                    </div>
                </fieldset>
            </form>
        </li>
    </ul>
</div>
