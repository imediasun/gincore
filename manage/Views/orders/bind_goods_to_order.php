<form action="" method="post" id="order-bind-items-form">
    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <table class="table table-borderless">
        <thead>
            <tr>
                <td width="20%"><?= l('Склад') ?></td>
                <td width="20%" class="text-center"><?= l('Кол-во.') ?></td>
                <td width="58%"><?= l('Серийный №') ?></td>
                <td width="2%"><?= l('Отгрузить') ?></td>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($warehouses_data as $wh_id=>$row): ?>
            <tr>
                <td><?= $row['warehouse']['title'] ?></td>
                <td class="text-center">
                    <?= count($row['items']) ?>
                    <input type="hidden" value="<?= count($row['items']) ?>" name="serials[<?= $wh_id ?>][quantities_exist]">
                </td>
                <td>
                    <div class="input-group">
                        <select class="form-control multiselect" id="bind_item_serial-<?= $product['id'] ?>"
                                name=serials[<?= $wh_id ?>][select][] multiple="multiple">
                            <?php foreach ($row['items'] as $item): ?>
                                <option value="<?= $item['id'] ?>">
                                    <?= $item['serial'] ?>
                                    <b class="danger">(<?= $row['warehouse']['locations'][$item['location_id']]['location'] ?>)</b>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input class="form-control" type="text" value="" style="display:none;"
                               name=serials[<?= $wh_id ?>][input] id="bind_item_serial_input-<?= $product['id'] ?>"/>
                    <span class="input-group-btn" onclick="toogle_siblings(this, true, true)">
                        <button class="btn" type="button">
                            <i class="fa fa-keyboard-o"></i>
                        </button>
                    </span>
                    </div>
                </td>
                <td>
                    <input maxlength="3" max="<?= count($row['items']) ?>" type="number" class="form-control"
                           name=serials[<?= $wh_id ?>][quantities] data-item_quantity data-item_id="<?= $product['id'] ?>"  value="0">
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

<style>
    .order_bind_items_form .modal-header .modal-title {
        font-size: 22px !important;
    }
    .order_bind_items_form .modal-dialog {
        width: 700px;
    }
</style>


<script type="text/javascript">

    var orderBindItemForm = {
        $form: $('#order-bind-items-form'),
        $itemsNeded: 0,
        $currentSum: 0,
        $prevSum: 0,
        $itemsNededInput: 0,
        $items: [],

        init: function () {
            var _this = this;
            this.$itemsNededInput = this.$form.find('input[data-item_quantity_sum]');
            this.$itemsNeded = this.$itemsNededInput.attr('max');
            this.$items = this.$form.find('input[data-item_quantity]');

            this.initMultiselect();
            this.initCalculation();

            this.$form.on('submit', function (e) {
                e.preventDefault();
                _this.submitForm();
            })

        },

        submitForm: function () {
            var _this = this;
//            var data = this.$form.serialize()
            $.ajax({
                url: prefix + 'warehouses/ajax/?act=bind-serials-to-order',
                type: 'POST',
                dataType: "json",
                data: _this.$form.serialize(),

                success: function (response) {
                    var whole_state = true;

                    if (response.bind_results) {
                        $.each(response.bind_results, function (index, value) {
                            if (value.state == false) {
                                _this.notify(value.message, 'error');
                                whole_state = false;
                            }
                        });
                    }

                    if (whole_state) {
                        _this.notify(response.message, 'success');
                    }

                    setTimeout(function () {
                        window.location.reload();
                    }, 4000);

                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(xhr.responseText);
                }
            });
        },

        initMultiselect: function () {
            var _this = this;
            this.$form.find('.multiselect').multiselect({
                'buttonWidth': '325px',
                onChange: function(option, checked, select) {
                    var selected_count = $(this.$select[0]).find('option:selected').length;

                    $(this.$select[0]).closest('tr').find('input[type=number]').val(selected_count).trigger('change');

                    // Если достигнуто максимальное значение
                    if(_this.$currentSum == _this.$prevSum) {
                        $(this.$select[0]).multiselect('deselect', $(option).val() );
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