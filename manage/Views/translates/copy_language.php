<?php if ($success): ?>
    <h2><?= l('Копирование языка') ?> <?= $from ?> в <?= $to ?></h2>
    <?= l('Скопировано успешно') ?>
    <a href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/copy">
        <?= l('Вернуться назад') ?>
    </a>
<?php else: ?>
    <h2><?= l('Скопировать язык в пустые ячеки другого языка') ?></h2>
    <form onSubmit="return confirm('<?= l('Вы абсолютно уверены в том что хотите скопировать') ?>?')"
          action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/copy/make_magic"
          method="post">
        <div class="form-group">
            <label><?= l('Копировать') ?></label>
            <select name="from" class="form-control">
                <option value=""> ---</option>
                <?php foreach ($languages as $l => $lng): ?>
                    <option value="<?= $l ?>"><?= $lng['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label><?= l('Куда') ?></label>
            <select class="form-control" name="to">
                <option value=""> ---</option>
                <?php foreach ($languages as $l => $lng): ?>
                    <option value="<?= $l ?>"><?= $lng['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input class="btn btn-primary" type="submit" value="<?= l('Копировать') ?>">
    </form>
<?php endif; ?>