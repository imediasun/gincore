<tr>
    <?php if (isset($columns['id'])): ?>
        <td class="small_ids"> <?= $good['id'] ?> </td>
    <?php endif; ?>
    <td>
        <input type="checkbox" class="js-selected-item" name="selected[<?= $good['id'] ?>]" data-id="<?= $good['id'] ?>" />
    </td>
    <?php if (isset($columns['photo'])): ?>
        <td>
            <?php if (!empty($good['image'])): ?>
                <img class="small-preview" data-preview=""
                    src="<?= $this->all_configs['prefix'] . $this->all_configs['configs']['goods-images-path'] . $good['image'] ?>">
            <?php endif; ?>
        </td>
    <?php endif; ?>
    <?php if (isset($columns['title'])): ?>
        <td class="js-item-title">
            <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create/<?= $good['id'] ?>/"
               data-action="sidebar_product" data-id_product="<?= $good['id'] ?>">
                <?= h($good['title']) . (isset($add_name) ? $add_name : '') ?>
            </a>
        </td>
    <?php endif; ?>
    <?php if (isset($columns['vc'])): ?>
        <td><?= h($good['vendor_code']) ?></td>
    <?php endif; ?>
    <?php if (isset($columns['price'])): ?>
        <td><?= number_format($good['price'] / 100, 2, '.', '') ?> </td>
    <?php endif; ?>
    <?php if (isset($columns['rprice'])): ?>
        <td><?= number_format($good['price_purchase'] / 100, 2, '.', '') ?></td>
    <?php endif; ?>
    <?php if (isset($columns['wprice'])): ?>
        <td><?= number_format($good['price_wholesale'] / 100, 2, '.', '') ?></td>
    <?php endif; ?>
    <?php if (isset($columns['balance'])): ?>
        <td><?= intval($good['qty_wh']) ?></td>
    <?php endif; ?>
    <?php if (isset($columns['fbalance'])): ?>
        <td>
            <?= intval($good['qty_store']); ?>
            <a href="#" onclick="return false;">
                <i class="fa fa-long-arrow-up" aria-hidden="true" style="transform: rotate(45deg); margin-top:-3px" data-warehouse="" data-id="<?= $good['id'] ?>"></i>
            </a>
        </td>
    <?php endif; ?>
    <?php if (isset($columns['sbalance'])): ?>
        <td><?= $good['have'] ?></td>
    <?php endif; ?>
    <?php if (isset($columns['delivery'])): ?>
        <td>
            <?= $good['expect'] ?>
            <?php if ($good['expect'] > 0): ?>
                <span style="opacity: 0.5">(<?= h(date('d/m/y', strtotime($good['min_date_come']))) ?>)</span>
            <?php endif; ?>
        </td>
    <?php endif; ?>
    <?php if (isset($columns['cart'])): ?>
        <td style="text-align: center">
            <?php if ($good['type'] == GOODS_TYPE_ITEM): ?>
                <a href="#" onclick="return add_to_cart(<?= $good['id'] ?>);">
                    <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                </a>
            <?php endif; ?>
        </td>
    <?php endif; ?>
    <?php if (isset($columns['mbalance'])): ?>
        <td>
            <?php if ($good['use_minimum_balance']): ?>
                <?= $good['minimum_balance'] ?>
            <?php endif; ?>
        </td>
    <?php endif; ?>
    <?php if (isset($columns['type'])): ?>
        <td>
            <?= $good['type'] == GOODS_TYPE_ITEM ? l('Т') : l('У') ?>
        </td>
    <?php endif; ?>
    <?php if (isset($columns['manager'])): ?>
        <td><?= h($good['manager']) ?></td>
    <?php endif; ?>
    <?php if (isset($columns['date'])): ?>
        <td><?= h(date('d/m/y', strtotime($good['date_add']))) ?></td>
    <?php endif; ?>
    <?php if (isset($columns['del'])): ?>
        <td>
            <?php if (!$good['deleted']): ?>
                <i class="js-delete-product fa fa-times" aria-hidden="true"
                   data-id="<?= $good['id'] ?>"></i>
            <?php endif; ?>
        </td>
    <?php endif; ?>
</tr>
