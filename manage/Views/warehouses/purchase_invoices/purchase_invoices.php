<div class="row-fluid">
    <ul class="list-unstyled inline clearfix">
        <li class="pull-right">
            <button type="button" class="btn btn-default" onclick="return create_purchase_invoice();">
                <i class="fa fa-plus-circle" aria-hidden="true" style="color: blue"></i>
                <?= l('Создать новую') ?>
            </button>
        </li>
    </ul>
</div>
<div class="row-fluid">
    <div class="col-sm-12" id="show_orders">
        <?php if ($invoices): ?>
            <table class="show-suppliers-orders table table-striped table-fs-12">
                <thead>
                <tr>
                    <td></td>
                    <td><?= l('Дата созд.') ?></td>
                    <td><?= l('Создал') ?></td>
                    <td><?= l('Поставщик') ?></td>
                    <td><?= l('Позиций') ?></td>
                    <td><?= l('Стоимость') ?></td>
                    <td><?= l('Оприх.') ?></td>
                    <td><?= l('Склад') ?></td>
                    <td><?= l('Примеч.') ?></td>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <?php $status_txt = $class = '' ?>

                    <tr title="<?= $status_txt ?>" class=" <?= $class ?>" id="supplier-order_id-<?= $invoice['id'] ?>">
                        <td>
                            <?= $invoice['id'] ?>
                        </td>
                        <td>
                            <span title="<?= $invoice['date'] ?>">
                                <?= $invoice['date'] ?>
                            </span>
                        </td>
                        <td><?= h(get_user_name($invoice)) ?></td>
                        <td><?= h($invoice['stitle']) ?></td>
                        <td><?= $invoice['quantity'] ?></td>
                        <td><?= show_price($invoice['amount'], 2, ' ', ',') ?></td>
                        <td>
                            <span title="<?= do_nice_date($invoice['purchase_date'],
                                false) ?>"><?= do_nice_date($invoice['purchase_date']) ?> </span>
                        </td>
                        <td>
                            <?php if ($invoice['wh_id'] > 0): ?>
                                <a class="hash_link"
                                   href="<?= $this->all_configs['prefix'] ?>warehouses?whs=<?= $invoice['wh_id'] ?>#show_items">
                                    <?= h($invoice['wh_title']) ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <i class="glyphicon glyphicon-pencil editable-click pull-right" data-placement="left"
                               data-display="false" data-title="Редактировать комментарий"
                               data-url="messages.php?act=edit-purchase-invoice-comment" data-pk="<?= $invoice['id'] ?>"
                               data-type="textarea" data-value="<?= h($invoice['description']) ?>"></i>
                            <span id="supplier-order-comment-<?= $invoice['id'] ?>"><?= cut_string($invoice['description'],
                                    50) ?></span>
                        </td>
                        <td><?= $this->renderFile('warehouses/purchase_invoices/_purchase_invoice_buttons', array(
                                'controller' => $controller,
                                'invoice' => $invoice,
                            )) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-danger"><?= l('Нет накладных') ?></p>
        <?php endif; ?>
    </div>
</div>