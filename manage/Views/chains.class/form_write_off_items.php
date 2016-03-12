<div class="well"><h4><?= l('Списание изделия') ?></h4>
    <?php if (empty($item_id)): ?>
        <p>Всего выбрано изделий: <span class="count-selected-items">0</span></p>
    <?php endif; ?>
    <form class="form-horizontal" method="post">
        <?php if ($can): ?>
            <input type="button" class="btn" onclick="write_off_item(this, <?= $item_id ?>)"
                   value="<?= l('Списать') ?>"/>
        <?php else: ?>
            <input disabled type="submit" class="btn" value="<?= l('Списать') ?>"/>
        <?php endif; ?>
    </form>
</div>


