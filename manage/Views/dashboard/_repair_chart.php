<script>
    $(function () {
        init_chart(
            '#flot-repair-line-chart',
            [
                <?php foreach($byItems as $id => $points): ?>
                {
                    points: [<?= implode(',', $points) ?>],
                    legend: "<?= htmlspecialchars($items[$id]['title']) ?>"
                },
                <?php endforeach; ?>
                <?php foreach($byModels as $id => $points): ?>
                {
                    points: [<?= implode(',', $points) ?>],
                    legend: "<?= htmlspecialchars($models[$id]['title']) ?>"
                },
                <?php endforeach; ?>
                <?php foreach($byCategories as $id => $points): ?>
                {
                    points: [<?= implode(',', $points) ?>],
                    legend: "<?= htmlspecialchars($categories[$id]['title']) ?>"
                },
                <?php endforeach; ?>
            ],
            [],
            [<?= implode(',', $ticks)?>],
            <?= $tickSize ?>
        );
    });
</script>
<div class="flot-chart-content" id="flot-repair-line-chart"></div>
