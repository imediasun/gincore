<option value=""></option>
<?php if (!empty($warehouses)): ?>
    <?php foreach ($warehouses as $warehouse): ?>
        <?php if (($exclude > 0 && $exclude != $warehouse['id']) || $exclude == 0): ?>
            <?php $hide = ($warehouse['id'] == $this->all_configs['configs']['erp-warehouse-type-mir']) ? 'create-chain-cell-type' : ''; ?>
            <option class="<?= $hide ?>" <?= ($wh_id && $wh_id == $warehouse['id']) ? 'selected' : '' ?>
                    value="<?= $warehouse['id'] ?>">
                <?= htmlspecialchars($warehouse['title']); ?>
            </option>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
