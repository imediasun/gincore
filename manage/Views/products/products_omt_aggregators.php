<?php if (!empty($aMarkets) > 0): ?>

    <?php foreach ($aMarkets as $m_id => $aMarket): ?>
        <?php $title1 = '';
        $title2 = '';
        if (isset($aMarket['title1'])) {
            $title1 = $aMarket['title1'];
        }
        if (isset($aMarket['title2'])) {
            $title2 = $aMarket['title2'];
        } ?>
        <div class="control-group">
            <label class="control-label"><?= htmlspecialchars($aMarket['title']) ?></label>
            <div class="controls">
                <input <?= $aMarket['avail'] == 1 ? 'checked' : '' ?> class="span5" type="checkbox"
                                                                      name="market-avail[<?= $m_id ?>]"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?= l('Название') ?> <?= htmlspecialchars($aMarket['title']) ?>&nbsp;1:</label>
            <div class="controls">
                <input class="span5" type="text" name="market-title1[<?= $m_id ?>]"
                       value="<?= htmlspecialchars($title1) ?>"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?= l('Название') ?> <?= htmlspecialchars($aMarket['title']) ?>&nbsp;2:</label>
            <div class="controls">
                <input class="span5" type="text" name="market-title2[<?= $m_id ?>]"
                       value="<?= htmlspecialchars($title2) ?>"/>
            </div>
        </div>
        <div class="controls">
            <textarea rows="5" name="market-content[<?= $m_id ?>]"
                      class="span5"><?= htmlspecialchars($aMarket['content']) ?></textarea>
        </div>
        <div class="control-group">
            <label class="control-label"><?= l('Категория') ?> <?= htmlspecialchars($aMarket['title']) ?>:</label>
            <div class="controls">
                <select class="span5" id="market-category-<?= $m_id ?>" name="market-category[<?= $m_id ?>]">
                    <option value=""></option>
                    <?php if (isset($aMarket['categories']) && count($aMarket['categories']) > 0): ?>
                        <?php foreach ($aMarket['categories'] as $cat_id => $val): ?>
                            <option <?= ($aMarket['category_id'] == $cat_id) ? 'selected' : '' ?> value="<?= $cat_id ?>">
                                <?= htmlspecialchars($val) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <input type="button" onclick="add_cat(this, '<?= $m_id ?>')" class="btn add-cat"
                       value="<?= l('Добавить категорию') ?> +"/>
            </div>
        </div>
        <br><br><br>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-error"><?= l('Нет ни одного магазина в базе данных') ?></p>
<?php endif; ?>
<?= $btn_save; ?>
