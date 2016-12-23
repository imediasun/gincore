<a class="hash_link" href="<?= $this->all_configs['prefix'] ?>accountings?
    <?php if ($transaction['supplier_order_id'] > 0): ?>
        s_id=<?= $transaction['supplier_order_id'] ?>&grp=1#transactions-contractors"
    <?php elseif ($transaction['client_order_id'] > 0): ?>
        o_id=<?= $transaction['client_order_id'] ?>&grp=1#transactions-cashboxes"
    <?php endif; ?>
>
    (<?= $transaction['count_t'] ?> <?= l('транз.') ?>)
</a>
