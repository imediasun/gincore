<div class="row-fluid">
    <?= $filters_block ?>

    <?php if ($chains_moves): ?>
        <?php $i=1; ?>
        <?php foreach ($chains_moves as $move): ?>
            <div class="chains_new m-t-lg m-b-sm">
                <div class="chain_item">
                    <div class="chain_number"><?= $i++ ?></div>
                    <div class="chain_product">
                        <?php if ($move['item_type'] == 1): ?>
                            <!-- Заказ -->
                            <a href="<?= $this->all_configs['prefix'] . 'orders/create/' . $move['item_id'] ?>" >Заказ №<?= $move['item_id'] ?></a>
                        <?php elseif ($move['item_type'] == 2): ?>
                            <!-- изделие (серийник) -->
                            <?php $move['serial'] = ''; ?>
                            <?= suppliers_order_generate_serial($move, true, true) ?>
                            <?php if ($move['from_order_id']): ?>
                                <span style="color:#666">
                                        (в заказе
                                         <a style="color:#666" href=" <?= $this->all_configs['prefix'] . 'orders/create/' . $move['from_order_id'] ?>">
                                            № <?= $move['from_order_id'] ?>
                                         </a>)
                                        </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="chain_logistics first <?= ($move['from_move_id'] ? 'success' : '') ?>">
                        <?= $warehouses[$chains[$move['chain_id']]['from_wh_id']]['title'] ?> (<?= $warehouses[$chains[$move['chain_id']]['from_wh_id']]['locations'][$chains[$move['chain_id']]['from_wh_location_id']]['name'] ?>)
                        <span title="<?= $move['from_date_move'] ?>"><?= do_nice_date($move['from_date_move']) ?></span>
                    </div>
                    <div class="chain_logistics <?= ($move['logistics_move_id'] ? 'success' : '') ?>">
                        <?= $warehouses[$chains[$move['chain_id']]['logistic_wh_id']]['title'] . ($chains[$move['chain_id']]['logistic_wh_location_id'] ? ($warehouses[$chains[$move['chain_id']]['logistic_wh_id']]['locations'][$move['logistic_wh_location_id']]['name']) : '') ?>
                        <span title="<?= $move['log_date_move'] ?>"><?= do_nice_date($move['log_date_move']) ?></span>
                    </div>
                    <div class="chain_logistics last <?= ($move['to_move_id'] ? 'success' : '') ?>">
                        <?= $warehouses[$chains[$move['chain_id']]['to_wh_id']]['title'] ?> (<?= $warehouses[$chains[$move['chain_id']]['to_wh_id']]['locations'][$chains[$move['chain_id']]['to_wh_location_id']]['name'] ?>)
                        <span title="<?= $move['to_date_move'] ?>"><?= do_nice_date($move['to_date_move']) ?></span>
                    </div>
                    <div class="chain_status">sdsd</div>
                </div>
            </div>

        <?php endforeach; ?>
        <?= page_block($count_page, $chains_moves_count_all, '#motions') ?>
    <?php else: ?>
        <div class="col-md-12 center">
            <div style="padding-top: 5%; padding-bottom: 5%;">
                <span style="border-bottom: 1px grey dashed;">
                    <?= l('Нет цепочек') ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .chains_new .chain_item::after {
       clear: both;
    }
    .chains_new .chain_item {
        height: 25px;
        width: 100%;
    }

    .chains_new .chain_item  .chain_number {
        float: left;
        width: 5%;
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }

    .chains_new .chain_item  .chain_product {
        float: left;
        width: 10%;
    }

    .chains_new .chain_item .chain_logistics {
        float: left;
        width: 25%;
        border: 1px solid lightgrey;
        background-color: #fafafa;
        color: black;
        text-align: center;
    }

    .chains_new .chain_item .chain_logistics.success {
        background-color: #1ab394;
    }


    .chains_new .chain_item .chain_logistics.first {
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }

    .chains_new .chain_item .chain_logistics.last {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .chains_new .chain_item .chain_status {
        float: left;
        width: 10%;

    }

</style>




