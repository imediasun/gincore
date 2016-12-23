<div class="count_on_page"><?= select_count_on_page() ?></div>
<ul style="margin:1px" class="pagination">
    <?php if ($count_page > 1): ?>

        <?php $url = '&' . get_to_string('p', $a_url) . $hash; ?>

        <?php if ((isset($_GET['p']) && $_GET['p'] == 1) || !isset($_GET['p'])): ?>
            <li class="disabled"><a href="?p=1<?= $url ?>">« <?= l('Предыдущая') ?></a></li>
            <?= $this->renderFile('inc_func/pages', array(
                'count_page' => $count_page,
                'url' => $url
            )); ?>
            <li><a href="?p=2<?= $url ?>"><?= l('Следующая') ?> »</a></li>
        <?php else: ?>
            <?php if ($count_page == $_GET['p']): ?>
                <li><a href="?p=<?= ($_GET['p'] - 1) . $url ?>">« <?= l('Предыдущая') ?></a></li>
                <?= $this->renderFile('inc_func/pages', array(
                    'count_page' => $count_page,
                    'url' => $url
                )); ?>
                <li class="disabled"><a href="?p=<?= $_GET['p'] . $url ?>"><?= l('Следующая') ?> »</a></li>
            <?php else: ?>
                <li><a href="?p=<?= ($_GET['p'] - 1) ?>">« <?= l('Предыдущая') ?></a></li>
                <?= $this->renderFile('inc_func/pages', array(
                    'count_page' => $count_page,
                    'url' => $url
                )); ?>
                <li><a href="?p=<?= ($_GET['p'] + 1) . $url ?>"><?= l('Следующая') ?> »</a></li>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</ul>
<div class="count_all_records">
    <span class="form-control"><?= l('Всего') ?>: <?= $count .' '. l('записей') ?></span>
</div>
<?= $after ?>