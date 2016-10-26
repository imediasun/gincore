<div class="row-fluid">
    <div class="col-sm-12">
        <form id="cart-form" method="POST">
            <input type="hidden" name="cart"/>
            <table class="table cart-items">
                <thead>
                <tr>
                    <th> <?= l('Наименование') ?> </th>
                    <th style="text-align: center">
                        <input type="hidden" name="price_type" value="1"/>
                        <div class="dropdown dropdown-inline">
                            <button class="as_link" type="button" id="dropdownMenuCashboxes"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <span class="btn-title-price_type"><?= l('Цена, р') ?></span>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuCashboxes">
                                <li><a href="#" data-price_type="1"
                                       onclick="return cart_select_price_type(this)"
                                       data-title="<?= l('Цена, р') ?>"><?= l('Цена розничная') ?></a>
                                </li>
                                <li><a href="#" data-price_type="2"
                                       onclick="return cart_select_price_type(this)"
                                       data-title="<?= l('Цена, о') ?>"><?= l('Цена оптовая') ?></a>
                                </li>
                                <li><a href="#" data-price_type="3"
                                       onclick="return cart_select_price_type(this)"
                                       data-title="<?= l('Цена, з') ?>"><?= l('Цена закупки') ?></a>
                                </li>
                            </ul>
                        </div>
                    </th>
                    <th> <?= l('Количество') ?> </th>
                    <th style="text-align: center"> <?= l('Сумма') ?> </th>
                    <th><i class="fa fa-times-circle" aria-hidden="true"></i></th>
                </tr>
                </thead>
                <?php if (!empty($cart)): ?>
                    <tbody>
                    <?php foreach ($cart as $id => $quantity): ?>
                        <tr>
                            <td width="45%"> <?= h($goods[$id]['title']) ?> </td>
                            <td width="20%" style="text-align: center;">
                                <span class="js-price js-price-sale"><?= round($goods[$id]['price'] / 100, 2) ?></span>
                                <span class="js-price js-price-purchase"
                                      style="display: none"><?= round($goods[$id]['price_purchase'] / 100, 2) ?></span>
                                <span class="js-price js-price-wholesale"
                                      style="display:none"><?= round($goods[$id]['price_wholesale'] / 100, 2) ?> </span>
                            </td>
                            <td width="15%">
                                <input type="text" class="form-control quantity" onkeyup="recalculate_cart_sum()"
                                       name='quantity[<?= $id ?>]' value="<?= $quantity ?>"/>
                            </td>
                            <td width="15%" style="text-align: center">
                                <span class="js-sum">
                                    <?= round($goods[$id]['price'] / 100, 2) * $quantity ?>
                                </span>
                            </td>
                            <td width="5%">
                                <a href="#" class="js-delete-item-from-cart"
                                   onclick="return delete_from_cart(this, <?= $id ?>);">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                <?php endif; ?>
            </table>
        </form>
    </div>
</div>
