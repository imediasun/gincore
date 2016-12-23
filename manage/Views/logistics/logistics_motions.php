<div class="row-fluid">
    <?= $filters_block ?>

    <?php if ($chains_moves): ?>
        <div class="m-t-lg m-b-sm">
            <div class="chains_new m-t-sm">
                <div class="chain_item">
                    <div class="chain_number">№</div>
                    <div class="chain_product"><?= l('Груз') ?></div>
                    <div class="chain_logistics"><?= l('Отправная точка') ?></div>
                    <div class="chain_logistics"><?= l('Логистика') ?></div>
                    <div class="chain_logistics"><?= l('Точка назначения') ?></div>
                    <div class="chain_status"><?= l('Статус') ?></div>
                </div>
            </div>


            <?php $i=1; ?>
            <?php foreach ($chains_moves as $move): ?>
                <div class="chains_new m-t-sm">
                    <div class="chain_item">
                        <div class="chain_number"><?= $i++ ?></div>
                        <div class="chain_product">
                            <?php if ($move['item_type'] == 1): ?>
                                <!-- Заказ -->
                                <a href="<?= $this->all_configs['prefix'] . 'orders/create/' . $move['item_id'] ?>" ><?= l('Заказ') ?> №<?= $move['item_id'] ?></a>
                            <?php elseif ($move['item_type'] == 2): ?>
                                <!-- изделие (серийник) -->
                                <?php $move['serial'] = ''; ?>
                                <?= suppliers_order_generate_serial($move, true, true) ?>
                                <?php if ($move['from_order_id']): ?>
                                    <span style="color:#666">
                                            (<?= l('в заказе') ?>
                                             <a style="color:#666" href=" <?= $this->all_configs['prefix'] . 'orders/create/' . $move['from_order_id'] ?>">
                                                № <?= $move['from_order_id'] ?>
                                             </a>)
                                            </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="chain_logistics with_bordered with_arrrow first <?= ($move['from_move_id'] ? 'success' : '') ?>">
                            <div class="text_container">
                                <?= cut_string( $warehouses[$chains[$move['chain_id']]['from_wh_id']]['title']  .
                                    ' (' . $warehouses[$chains[$move['chain_id']]['from_wh_id']]['locations'][$chains[$move['chain_id']]['from_wh_location_id']]['name'] . ')') ?>
                                <span title="<?= $move['from_date_move'] ?>"><?= do_nice_date($move['from_date_move']) ?></span>
                            </div>
                        </div>
                        <div class="chain_logistics with_bordered with_arrrow <?= ($move['logistics_move_id'] ? 'success' : '') ?>">
                            <div class="text_container">
                                <?= cut_string($warehouses[$chains[$move['chain_id']]['logistic_wh_id']]['title'] .
                                    (
                                    $chains[$move['chain_id']]['logistic_wh_location_id'] ?
                                        ' (' . $warehouses[$chains[$move['chain_id']]['logistic_wh_id']]['locations'][$move['logistic_wh_location_id']]['name'] . ')' : ''
                                    )
                                ) ?>
                                <span title="<?= $move['log_date_move'] ?>"><?= do_nice_date($move['log_date_move']) ?></span>
                            </div>
                        </div>
                        <div class="chain_logistics with_bordered last <?= ($move['to_move_id'] ? 'success' : '') ?>">
                            <div class="text_container">
                                <?= cut_string($warehouses[$chains[$move['chain_id']]['to_wh_id']]['title'] .
                                    ' (' . $warehouses[$chains[$move['chain_id']]['to_wh_id']]['locations'][$chains[$move['chain_id']]['to_wh_location_id']]['name'] . ')') ?>
                                <span title="<?= $move['to_date_move'] ?>"><?= do_nice_date($move['to_date_move']) ?></span>
                            </div>
                        </div>
                        <div class="chain_status">
                            <?= ($move['to_move_id'] ? '<i class="fa fa-check"></i>' : '') ?>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            <?php endforeach; ?>
        </div>
        <?= page_block($count_page, $chains_moves_count_all, '#motions') ?>
    <?php else: ?>
        <div class="col-md-12 center">
            <div style="padding-top: 5%; padding-bottom: 5%;">
                <span style="border-bottom: 1px grey dashed;">
                    <?= l('Нет цепочек') ?>
                </span>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php endif; ?>
</div>

