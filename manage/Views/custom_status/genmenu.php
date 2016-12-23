<ul>
    <li <?= ($current == 'repair' ? 'class="active"' : '') ?>>
        <a href="<?= $this->all_configs['prefix'] ?>custom_status?type=repair"><?= l('Статусы ремонтов') ?></a>
    </li>
    <li <?= ($current == 'sale' ? 'class="active"' : '') ?>>
        <a href="<?= $this->all_configs['prefix'] ?>custom_status?type=sale"><?= l('Статусы продаж') ?></a>
    </li>
</ul>
