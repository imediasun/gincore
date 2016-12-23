<?php if (!empty($all_configs['configs']['manage-print-city-select']) && in_array($act,
        array('check', 'warranty', 'act', 'invoice'))
): ?>
    <div style="margin:0" class="well unprint">
        <form style="margin:0" method="get" action="<?= $this->all_configs['prefix'] ?>print.php">
            <input type="hidden" name="act" value="<?= $act ?>">
            <input type="hidden" name="object_id" value="<?= $object_id ?>">
            <select id="lang_change" name="lang">
                <?php foreach ($langs['langs'] as $l): ?>
                    <option <?= ($cur_lang == $l['url'] ? 'selected' : '') ?>
                        value="<?= $l['url'] ?>"><?= $l['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
<?php endif; ?>
