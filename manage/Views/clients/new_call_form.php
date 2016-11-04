<div id="new_call" class="tab-pane active">
    <div style="max-width:900px;">
        <h3><?= l('Звонок') ?> №<?= $new_call_id ?></h3>
        <form class="ajax_form" method="get" action="<?= $this->all_configs['prefix'] ?>clients/ajax/" id="form-new-call">
            <input type="hidden" name="act" value="short_update_client">
            <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
            <input type="hidden" name="call_id" value="<?= $new_call_id ?>">

            <div class="col-md-6 p-l-n">
                <div class="form-group">
                    <label class="control-label"><?= l('Заказчик') ?>:<b class="text-danger">*</b></label>
                    <div class="input-group">
                        <input type="text" value="<?= htmlspecialchars($client['fio']) ?>" name="fio" class="form-control">
                        <span class="input-group-addon js-personal" id="personal"
                              onclick="return change_personal_to(2);"
                            <?php if ($client['person'] == 2) : ?> style="display: none" <?php endif; ?>
                        > <?= l('Физ') ?>
                            <i class="fa fa-sort" aria-hidden="true"></i>
                        </span>
                        <span class="input-group-addon js-personal" id="legal"
                              onclick="return change_personal_to(1);"
                            <?php if ($client['person'] == 1) : ?> style="display: none" <?php endif; ?>
                        ><?= l('Юр') ?>
                            <i class="fa fa-sort" aria-hidden="true"></i>
                        </span>
                        <input type="hidden" name="person" value="<?= $client['person'] ?>"/>
                    </div>

                </div>

                <div class="form-group">
                    <label class="control-label"><?= l('Телефоны') ?>:<b class="text-danger">*</b></label>

                    <?php if (!empty($phones)): ?>
                        <?php $i = 0; ?>
                        <?php foreach ($phones as $key=>$val): ?>
                            <?php if($i==0): ?>
                                <div class="input-group">
                                    <input<?=input_phone_mask_attr()?> id="add-phone-field"
                                        class="form-control" type="text" onkeydown="return isNumberKey(event)"
                                        name="phone[]" value="<?= $val ?>"
                                    />
                                    <span class="input-group-addon" id="add-phone">
                                        <i class="fa fa-plus cursor-pointer" aria-hidden="true"></i>
                                    </span>
                                </div>
                            <?php else: ?>
                                <input<?=input_phone_mask_attr()?> class="form-control clone_clear_val m-t-sm" type="text" onkeydown="return isNumberKey(event)"
                                                                   name="phone[]" value="<?= $val ?>"/>
                            <?php endif; ?>
                            <?php $i++ ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="input-group">
                            <input<?=input_phone_mask_attr()?> id="add-phone-field"
                               class="form-control" type="text" onkeydown="return isNumberKey(event)"
                               name="phone[]" value="<?= $val ?>"
                            />
                            <span class="input-group-addon" id="add-phone">
                                <i class="fa fa-plus cursor-pointer" aria-hidden="true"></i>
                            </span>
                        </div>
                    <?php endif; ?>

                </div>
                <div class="form-group">
                    <label class="control-label"><?= l('Эл. адрес') ?>:</label>
                    <input class="form-control" type="text" value="<?= htmlspecialchars($client['email']) ?>" name="email" />
                </div>

                <h4 class="m-t-md"><?= l('Рекламный источник') ?></h4>
                <div class="form-group">
                    <label class="control-label">
                        <?= l('Код, озвученный клиентом') ?>:
                        <i class="fa fa-question-circle" <?= InfoPopover::getInstance()->createOnHoverAttr('Код, озвученный клиентом') ?> ></i>
                    </label>
                    <input style="background-color:<?= ($code ? (!$code_exists ? '#F0BBC5' : '#D8FCD7') : '') ?>" class="form-control call_code_mask" type="text" name="code" value="<?= $code ?>">
                </div>
                <div class="form-group">
                    <label class="control-label"><?= l('Или укажите источник вручную') ?>:</label>
                    <?= get_service('crm/calls')->get_referers_list(isset($calldata['referer_id']) ? $calldata['referer_id'] : null) ?>
                </div>
            </div>

            <div class="clearfix"></div>

        </form>

        <div class="m-t-md m-b-md">
            <span class="h3"><?= l('Создать заявку') ?></span>
            &nbsp;<i class="fa fa-question-circle" <?= InfoPopover::getInstance()->createOnHoverAttr('Код, озвученный клиентом') ?> ></i>
        </div>

        <div>
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#repair-tab" aria-controls="repair-tab" role="tab" data-toggle="tab"><?= l('на ремонт') ?></a></li>
                <li role="presentation"><a href="#sell-tab" aria-controls="sell-tab" role="tab" data-toggle="tab"><?= l('на продажу') ?></a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active borderless" id="repair-tab">
                    <?= get_service('crm/requests')->get_new_request_form_for_call($client['id'], $new_call_id) ?>
                </div>
                <div role="tabpanel" class="tab-pane borderless" id="sell-tab">
                    <form method="post" id="eshop-sale-form" parsley-validate="">
                        <fieldset>
                            <div class="container-fluid items-container">
                                <div class="row">
                                    <?= $this->renderFile('orders/eshoporder/_product_to_cart', array(
                                        'from_shop' => true,
                                        'order_data' => $order_data,
                                    )); ?>
                                </div>
                                <?= $this->renderFile('orders/_cart_items_table', array(
                                    'prefix' => 'eshop',
                                    'orderWarranties' => $orderWarranties,
                                    'defaultWarranty' => $defaultWarranty,
                                    'cart' => $cart
                                )) ?>
                            </div>

                            <div class="form-group">
                                <label><?= l('Скрытый комментарий к заказу') ?>: </label>
                                <textarea name="private_comment" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label><?= l('Способ доставки') ?>: </label><br>
                                <?php foreach ($deliveryByList as $id => $name): ?>
                                    <label class="radio-inline">
                                        <input type="radio" <?= $id == 1 ? 'checked' : '' ?>
                                               value="<?= $id ?>" name="delivery_by"
                                               onclick="toggle_delivery_to(<?= (int)$id != 1 ?>)"/><?= $name ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-group">
                                <input type="text" name="delivery_to" class='form-control' value=""
                                       placeholder="<?= l('Укажите адрес') ?>" style="display: none;"/>
                            </div>
                        </fieldset>

                    </form>
                </div>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span6">
                <button class="btn btn-info" id="form-new-call-submit"><?= l('Сохранить данные о звонке') ?></button>
                <input id="add-client-order" class="btn btn-success submit-from-btn" style="display: none;" type="button" onclick="eshop_sale(this, false, false, true)" value="<?= l('Создать заказ') ?>">
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(function () {

        $('a[aria-controls="repair-tab"]').on('click', function () {
            $('#add-client-order').hide();
        });

        $('a[aria-controls="sell-tab"]').on('click', function () {
            $('#add-client-order').show();
        });

        $(".test-toggle").bootstrapSwitch();
        $('.cashless-toggle').bootstrapSwitch({
            onText: '<?= l('Безнал'); ?>',
            offText: '<?= l('Нал'); ?>',
            labelWidth: 0,
            size: 'normal'
        });

        $('#form-new-call-submit').on('click', function () {
            $('#form-new-call').submit();
        });

        $('#add-phone').on('click', function () {
            var elem = $(this);
            var phone_value = $('#add-phone-field').val();

            if (phone_value != ''){
                $('<input<?=input_phone_mask_attr()?> class="form-control clone_clear_val m-t-sm" type="text" onkeydown="return isNumberKey(event)" name="phone[]" value="'+phone_value+'"/>').insertAfter( elem.parent() );
                init_input_masks();
                $('#add-phone-field').val('');
            }
        })
    })
</script>
