<div class="well">
    <?php if (array_key_exists(($year - 1), $years) || $currentYear < $year): ?>
        <a href="<?= $url . ($year - 1) . $hash ?>" class="none-decoration">
            <i class="glyphicon glyphicon-chevron-left"></i>
        </a>
    <?php else: ?>
        <i class="glyphicon glyphicon-chevron-left"></i>
    <?php endif; ?>
    <?= $year; ?>
    <?php if (array_key_exists(($year + 1), $years) || $currentYear > $year): ?>
        <a href="<?= $url . ($year + 1) . $hash ?>" class="none-decoration">
            <i class="glyphicon glyphicon-chevron-right"></i>
        </a>
    <?php else: ?>
        <i class="glyphicon glyphicon-chevron-right"></i>
    <?php endif; ?>
</div>
