<?php if ($year == null): ?>
<?php $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0]; ?>
<select class="form-control" onchange="window.location.href='<?= $url ?>?' + this.value + '#transactions'">
    <?php endif; ?>

    <?php foreach ($months as $number_month => $month): ?>
        <option
            <?= (($cur_month == $number_month) ? 'selected' : '') ?>
            value="df=01.<?= $number_month ?>.<?= $currentYear ?>&dt=<?= date("t.{$number_month}.{$currentYear}",
                strtotime("01.{$number_month}.{$currentYear}")) ?>"><?= $month . (($currentYear == $cur_year) ? '' : ", {$year}") ?>
        </option>
    <?php endforeach; ?>

    <?php if ($year == null): ?>
</select>
<?php endif; ?>
