<?php $start = isset($_GET['ds']) && strtotime($_GET['ds']) > 0 ? date("j/n/y",
    strtotime($_GET['ds'])) : date("1/n/y"); ?>
<?php $end = isset($_GET['de']) && strtotime($_GET['de']) > 0 ? date("j/n/y",
    strtotime($_GET['de'])) : date("j/n/y"); ?>

<div id="daterange" class="btn btn-info">
    <span><?= $start ?> - <?= $end ?></span> <b class="caret"></b>
</div>
