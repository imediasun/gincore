<form method="post">
    <?php if (!empty($section) && is_array($section)): ?>
        <select id="goods_section_name">
            <option value=""><?= l('Выберите') ?></option>
            <?php foreach ($sections as $section): ?>
                <option value="<?= h($section['name']) ?>"><?= h($section['name']) ?></option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <input type="text" id="goods_section_name" value=""
               placeholder="<?= l('новый раздел') ?>"/>
    <?php endif; ?>
</form>
