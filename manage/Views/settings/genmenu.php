<h4>
    <?= l('sets_list') ?> <a style="text-decoration:none" href="<?= $this->all_configs['prefix'] ?>settings/add">+</a>
</h4>

<ul>
    <?php foreach ($sqls as $pps): ?>
        <li>
            <a href="<?= $this->all_configs['prefix'] ?>settings/<?= $pps['id'] ?>" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
                <?= $pps['title'] ?>
            </a>
        </li>
        <?php if ($pps['name'] == 'turbosms-password'): ?>
            <li>
                <a class="<?= (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'restore4_sms_templates' ? ' active' : '') ?>"
                   href="<?= $this->all_configs['prefix'] ?>/sms_templates/sms_templates">
                    <?= l('Шаблоны для sms') ?>
                </a>
                <a href="<?= $this->all_configs['prefix'] ?>/sms_templates/sms_templates/add">+</a>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
    <li>
        <a href="<?= $this->all_configs['prefix'] ?>settings/crm_referers" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
            <?= l('Список каналов (источники продаж)') ?>
        </a>
        <a href="<?= $this->all_configs['prefix'] ?>settings/crm_referers/add" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
            +
        </a>
    </li>
    <li>
        <a href="<?= $this->all_configs['prefix'] ?>settings/brands" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
            <?= l('Список брендов') ?>
        </a>
        <a href="<?= $this->all_configs['prefix'] ?>settings/brands/add" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
            +
        </a>
    </li>
    <li>
        <a href="<?= $this->all_configs['prefix'] ?>print_templates/template_vars" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
            <?= l('Пользовательские шаблоны печатных документов') ?>
        </a>
        <a href="<?= $this->all_configs['prefix'] ?>print_templates/template_vars/add" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
            +
        </a>
    </li>
    <li>
        <a href="<?= $this->all_configs['prefix'] ?>custom_status">
            <?= l('Пользовательские статусы заказов') ?>
        </a>
    </li>
</ul>
