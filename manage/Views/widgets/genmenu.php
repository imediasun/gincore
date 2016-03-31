<ul>
    <li <?= ($current == 'status' ? 'class="active"' : '') ?>>
        <a href="<?= $this->all_configs['prefix'] ?>widgets/status"><?= l('Статус ремонта') ?></a>
    </li>
    <li <?= ($current == 'feedback' ? 'class="active"' : '') ?>>
        <a href="<?= $this->all_configs['prefix'] ?>widgets/feedback"><?= l('Отзыввы о работе персонала') ?></a>
    </li>
</ul>
