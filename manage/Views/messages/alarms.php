<?php if (!empty($alarms)): ?>
    <div class="alerts col-sm-12">
        <?php foreach ($alarms as $alarm): ?>
            <div class="alert alert-danger alert-clock">
                <?= $alarm['text'] ?>
                <?php if ($alarm['order_id']): ?>
                    <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $alarm['order_id'] ?>"><?= $alarm['order_id'] ?></a>
                <?php endif; ?>
                <span class="from"><?= l('От:') ?>&nbsp;<?= $alarm['user'] ?></span>
                <button type="button" class="close close_alarm" data-dismiss="alert"
                        data-alarm_id="<?= $alarm['id'] ?>">×
                </button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
