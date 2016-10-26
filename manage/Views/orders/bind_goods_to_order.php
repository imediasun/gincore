<form action="" method="post" id="order-bind-item-order">
    <table class="table table-borderless">
        <thead>
            <tr>
                <td width="20%"><?= l('Склад') ?></td>
                <td width="5%"><?= l('Кол-во.') ?></td>
                <td width="70%"><?= l('Серийный №') ?></td>
                <td width="5%"><?= l('Отгрузить') ?></td>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($warehouses_data as $wh_id=>$row): ?>
            <tr>
                <td><?= $row['warehouse']['title'] ?></td>
                <td><?= count($row['items']) ?></td>
                <td>
                    <div class="input-group">
                        <select class="form-control multiselect" id="bind_item_serial-<?= $product['id'] ?>"
                                multiple="multiple">
                            <?php foreach ($row['items'] as $item): ?>
                                <option class="<?= $item['order_id'] > 0 ? 'text-danger' : '' ?>"
                                        value="<?= $item['id'] ?>">
                                    <?= $item['serial'] ?>
                                    <b class="danger">(<?= ',' . $row['warehouse']['locations'][$item['location_id']]['location'] ?>)</b>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input class="form-control" type="text" value="" style="display:none;"
                               id="bind_item_serial_input-<?= $product['id'] ?>"/>
                    <span class="input-group-btn" onclick="toogle_siblings(this, true)">
                        <button class="btn" type="button">
                            <i class="fa fa-keyboard-o"></i>
                        </button>
                    </span>
                    </div>
                </td>
                <td>
                    <input maxlength="3" max="<?= count($row['items']) ?>" type="number" class="form-control">
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">
                    <span><?= l('Требуется') ?> <?= $products_count ?> <?= l('шт.') ?> &nbsp;&nbsp;&nbsp;&nbsp;</span> <strong><?= l('Итого') ?>:</strong>
                </td>
                <td>
                    <input maxlength="3" type="number" max="<?= $products_count ?>" class="form-control">
                </td>
            </tr>
        </tfoot>
    </table>
</form>


<script type="text/javascript">
    $(document).ready(function () {
        $('#order-bind-item-order .multiselect').multiselect({
            'buttonWidth': '100%'
        });
    })
</script>