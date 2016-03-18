<select class="form-control" id="bind_item_serial-<?= $data['id'] ?>" onchange="show_bind_button(this);">
    <?php if (!$hasItems): ?>
        <option value=""><?= l('Изделий нет') ?></option>
    <?php else: ?>
        <?php if (empty($serials)): ?>
            <option value=""><?= l('Изделия в другом заказе поставщику') ?></option>
        <?php else: ?>
            <option value=""><?= l('Выберите изделие') ?></option>
            <?php if (!empty($serials['current'])): ?>
                <!--option value=""><?= l('Привязанный заказ') ?></option-->
                <?php foreach ($serials['current'] as $serial): ?>
                    <option class="<?= $serial['order_id'] > 0 ? 'text-danger' : '' ?>"
                            value="<?= $serial['item_id'] ?>">
                        <?= suppliers_order_generate_serial($serial) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($serials['another'])): ?>
                <!--option value=""><?= l('Другие заказы') ?></option-->
                <?php foreach ($serials['another'] as $serial): ?>
                    <option class="<?= $serial['order_id'] > 0 ? 'text-danger' : '' ?>"
                            value="<?= $serial['item_id'] ?>">
                        <?= suppliers_order_generate_serial($serial) ?>
                        (<?= l('Зак.#') . $serial['supplier_order_id'] ?> <?= l('Cкл.:') . $serial['wh_title'] ?> <?= ',' . $serial['location'] ?>)
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</select>
