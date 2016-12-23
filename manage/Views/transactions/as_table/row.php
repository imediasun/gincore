<?php $group_url = $this->renderFile('transactions/as_table/group_url', array(
    'transaction' => $transaction
)); ?>
<tr>
    <td><?= $transaction_id ?></td>
    <td>
    <span title="<?= do_nice_date($transaction['date_transaction'], false, false) ?>">
                <?= do_nice_date($transaction['date_transaction'], true, false) ?>
                </span>
    </td>
    <td><?= ($transaction['count_t'] > 1 ? $group_url : $cashbox_info) ?></td>
    <td>
        <a class="hash_link"
           href="<?= $this->all_configs['prefix'] ?>accountings?ct=<?= $transaction['contractor_id'] ?>#transactions-contractors">
            <?= $transaction['contractor_name'] ?>
        </a>
    </td>
    <td>
        <?php if ($transaction['client_order_id'] > 0): ?>
            <a class="hash_link"
               href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $transaction['client_order_id'] ?>">
                № <?= $transaction['client_order_id'] ?>
            </a>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($transaction['supplier_order_id'] > 0): ?>
            <a class="hash_link"
               href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $transaction['supplier_order_id'] ?>#create_supplier_order">
                <?= supplier_order_number(array('id' => $transaction['supplier_order_id'])) ?>
            </a>
        <?php endif; ?>
    </td>
    <?php if ($contractors): ?>
        <td>
            <a class="hash_link"
               href="<?= $this->all_configs['prefix'] ?>accountings?t_id=<?= $transaction['transaction_id'] ?>#transactions-cashboxes">
                <?= $transaction['transaction_id'] ?>
            </a>
        </td>
        <?php if ((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2): ?>
            <td><?= $inc ?></td>
            <td><?= $exp ?></td>
        <?php else: ?>
            <td>&#931;&nbsp;<?= $inc ?></td>
            <td>&#931;&nbsp;<?= $exp ?></td>
        <?php endif; ?>

        <?php if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1): ?>
            <td><?= $group_url ?></td>
        <?php else: ?>
            <?php if ($transaction['item_id'] > 0): ?>
                <td><?= suppliers_order_generate_serial_by_id($transaction['item_id'], true, true) ?></td>
            <?php else: ?>
                <td></td>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <?php if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1): ?>
            <td><?= $group_url ?></td>
            <td>&#931;&nbsp;<?= $inc ?><br>&#931;&nbsp;<?= $inc_sc ?></td>
            <td>&#931;&nbsp;<?= $exp ?><br>&#931;&nbsp;<?= $exp_sc ?></td>
        <?php else: ?>
            <td><?= $transaction['chain_id'] ?></td>
            <td><?= $inc ?></td>
            <td><?= $exp ?></td>
        <?php endif; ?>
    <?php endif; ?>
    <td><?= get_user_name($transaction) ?></td>
    <td><?= cut_string($transaction['comment']) ?></td>
</tr>
