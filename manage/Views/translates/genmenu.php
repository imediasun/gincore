<h4><?= l('Таблицы') ?></h4>
<ul>
    <?php foreach ($config as $table => $conf): ?>
        <li>
            <a class="<?= (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == $table ? ' active' : '') ?>"
               href="<?= $this->all_configs['prefix'] . $url ?>/<?= $table ?>">
                <?= $conf['name'] ?>
            </a>
            <?= (isset($conf['add_link']) ? $conf['add_link'] : '') ?>
        </li>
    <?php endforeach; ?>
    <li style="margin-top:15px">
        <a class="<?= (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'changes' ? 'active' : '') ?>"
           href="<?= $this->all_configs['prefix'] . $url ?>/changes">
            <?= l('Изменения') ?>
        </a>
    </li>
</ul>
