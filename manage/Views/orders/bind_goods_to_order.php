<form action="" method="post" id="order-bind-item-order">
    <table class="table table-borderless">
        <thead>
            <tr>
                <td width="20%"><?= l('Склад') ?></td>
                <td width="10%"><?= l('Кол-во.') ?></td>
                <td width="66%"><?= l('Серийный №') ?></td>
                <td width="4%"><?= l('Отгрузить') ?></td>
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
                                    <b class="danger">(<?= $row['warehouse']['locations'][$item['location_id']]['location'] ?>)</b>
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
                    <input maxlength="3" max="<?= count($row['items']) ?>" type="number" class="form-control"
                           data-item_quantity data-item_id="<?= $product['id'] ?>"  value="0">
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
                    <input maxlength="3" type="number" max="<?= $products_count ?>" class="form-control"
                           data-item_quantity_sum value="0" readonly>
                </td>
            </tr>
        </tfoot>
    </table>
</form>


<script type="text/javascript">

    var orderBindItemForm = {
        $form: $('#order-bind-item-order'),
        $itemsNeded: 0,
        $currentSum: 0,
        $prevSum: 0,
        $itemsNededInput: 0,
        $items: [],

        init: function () {
            this.$itemsNededInput = this.$form.find('input[data-item_quantity_sum]');
            this.$itemsNeded = this.$itemsNededInput.attr('max');
            this.$items = this.$form.find('input[data-item_quantity]');

            this.initMultiselect();
            this.initCalculation();

        },

        initMultiselect: function () {
            var _this = this;
            this.$form.find('.multiselect').multiselect({
                'buttonWidth': '100%',
                onChange: function(option, checked, select) {
                    var selected_count = $(this.$select[0]).find('option:selected').length;

                    $(this.$select[0]).closest('tr').find('input[type=number]').val(selected_count).trigger('change');

                    // Если достигнуто максимальное значение
                    if(_this.$currentSum == _this.$prevSum) {
                        $(this.$select[0]).multiselect('deselect', $(option).val() );
                        _this.notify('<?= l('Достигнуто максимальное значение') ?>');
                    }


                }
            });
        },

        initCalculation: function () {
            var _this = this;

            this.$items.on('change', function (e) {
                var max = parseInt($(this).attr('max'));
                var sum = 0;
                var sum_without_elem = 0;
                var input_elem = this;

                _this.$prevSum = _this.$currentSum;

                if (this.value > parseInt($(this).attr('max'))){
                    this.value = parseInt($(this).attr('max'));
                }


                $.each(_this.$items, function (i, elem) {
                    sum = sum + parseInt($(elem).val());
                    if(input_elem != elem){
                        sum_without_elem = sum_without_elem + parseInt($(elem).val());
                    }

                });

                // Если достигнуто максимальное значение
                if (sum > _this.$itemsNeded) {
                    _this.notify('<?= l('Достигнуто максимальное значение') ?>');
                    this.value = _this.$itemsNeded - sum_without_elem;
                    _this.$currentSum = _this.$itemsNeded;
                } else {
                    _this.$currentSum = sum;
                }
                _this.$itemsNededInput.val(_this.$currentSum);

            });
        },

        notify : function (text, type) {
            if (typeof type === 'undefined') {
                type = 'default';
            }
            if($.noty){
                noty({
                    text: text,
                    timeout: 3000,
                    type: type,
                    layout: 'topRight'
                });
            } else {
                alert(text);
            }

        }
    };

    orderBindItemForm.init();

    $(document).ready(function () {

    })
</script>