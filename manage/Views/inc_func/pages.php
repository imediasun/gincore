<?php foreach (check_page($count_page, (isset($_GET['p']) ? $_GET['p'] : 1), 1) as $p): ?>
    <?php if ($p == (isset($_GET['p']) ? $_GET['p'] : 1)): ?>
        <li class="disabled"><a href="?p=<?= $p . $url ?>" class="text-bold"><?= $p ?></a></li>
    <?php else: ?>
        <?php if (intval($p) > 0): ?>
            <li><a href="?p=<?= $p . $url ?>"><?= $p ?></a></li>
        <?php else: ?>
            <li class="disabled"><a><?= $p ?></a></li>
        <?php endif; ?>
    <?php endif; ?>
<?php endforeach; ?>

