<div class="m-t-xs">
    <span class="font-bold no-margins">
        <?= $name ?> <span class="pull-right"><?= $count ?> (<?= $percents ?>%)</span>
    </span>
    <div class="progress m-t-xs full progress-small">
        <div style="width:<?= $percents ?>%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="55" role="progressbar"
             class="<?= (!$count ? 'hidden ' : '') ?> progress-bar progress-bar-success">
            <span class="sr-only"></span>
        </div>
    </div>
</div>
