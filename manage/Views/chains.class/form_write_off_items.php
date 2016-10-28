<?php if ($for_sidebar): ?>
    <div class="well">
        <div class="clearfix">
            <div class="col-sm-8 p-l-n">
                <h4><?= l('Списание изделия') ?></h4>
            </div>
            <div class="col-sm-4 p-r-n">
                <form class="form-horizontal" method="post">
                    <?php if ($can): ?>
                        <input type="button" class="btn item_btn" onclick="write_off_item(this, <?= $item_id ?>, true)"
                               value="<?= l('Списать') ?>"/>
                    <?php else: ?>
                        <input disabled type="submit" class="btn item_btn" value="<?= l('Списать') ?>"/>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    </div>
<?php else: ?>
    <div class="well" style="min-height: 115px">
        <h4><?= l('Списание изделия') ?></h4>
        <?php if (empty($item_id)): ?>
            <p><?= l('Всего выбрано изделий') ?>: <span class="count-selected-items">0</span></p>
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
<?php endif; ?>
