<tr>
    <?php if (isset($columns['id'])): ?>
        <td class="small_ids"> <?= $good['id'] ?> </td>
    <?php endif; ?>
    <?php if (isset($columns['marker'])): ?>
        <td></td>
    <?php endif; ?>
    <?php if (isset($columns['photo'])): ?>
        <td>
            <?php if (array_key_exists('image', $good)): ?>
                <?php $path_parts = full_pathinfo($good['image']);
                $image = $path_parts['filename'] . $this->all_configs['configs']['small-image'] . $path_parts['extension']; ?>
                <img
                    src="<?= $this->all_configs['siteprefix'] . $this->all_configs['configs']['goods-images-path'] . $good['id'] ?>/<?= $image ?>">
            <?php endif; ?>
        </td>
    <?php endif; ?>
    <?php if (isset($columns['title'])): ?>
        <td class="js-item-title">
            <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create/<?= $good['id'] ?>/">
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
            <a href="#" onclick="return detail(<?= $good['id']?>, 'balance');"><i class="fa fa-long-arrow-up" aria-hidden="true"></i></a>
        </td>
    <?php endif; ?>
    <?php if (isset($columns['sbalance'])): ?>
        <td>наличие у поставщиков</td>
    <?php endif; ?>
    <?php if (isset($columns['delivery'])): ?>
        <td>ожидаемые поставки</td>
    <?php endif; ?>
    <?php if (isset($columns['cart'])): ?>
        <td><a href="#"><i class="fa fa-shopping-cart" aria-hidden="true"></i></a></td>
    <?php endif; ?>
    <?php if (isset($columns['mbalance'])): ?>
        <th><?= l('Неснижаемый остаток') ?></th>
    <?php endif; ?>
    <?php if (isset($columns['type'])): ?>
        <th><?= l('Товар/услуга') ?></th>
    <?php endif; ?>
    <?php if (isset($columns['manager'])): ?>
        <th><?= l('Менеджер') ?></th>
    <?php endif; ?>
    <?php if (isset($columns['date'])): ?>
        <th><?= l('Дата') ?></th>
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
