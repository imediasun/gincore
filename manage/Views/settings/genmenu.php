<ul class="nav nav-tabs">
    <li>
        <h4>
            <?= l('sets_list') ?> <a style="text-decoration:none" href="<?= $this->all_configs['prefix'] ?>settings/add">+</a>
        </h4>
    </li>
    <?php foreach ($sqls as $section_id=>$setting): ?>
        <?php if($section_id== 1): ?>
            <?php foreach($setting as $key=>$val): ?>
                <li<?php if($val['id']==$current_setting_id): ?> class="active" <?php endif; ?>><a href="<?= $this->all_configs['prefix'] ?>settings/edit/<?= $val['id'] ?>" aria-expanded="true"> <?= $val['title'] ?></a></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li<?php if($section_id==$current_section_id): ?> class="active" <?php endif; ?>><a href="<?= $this->all_configs['prefix'] ?>settings/section/<?= $section_id ?>" aria-expanded="true"> <?= $sections[$section_id] ?></a></li>
        <?php endif; ?>

    <?php endforeach; ?>

    <li <?= (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'sms_templates' ? 'class="active"' : '') ?>>
        <a href="<?= $this->all_configs['prefix'] ?>sms_templates/sms_templates">
            <?= l('Шаблоны для sms') ?>
        </a>
    </li>

    <li <?= (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'crm_referers' ? 'class="active"' : '') ?>>
        <a href="<?= $this->all_configs['prefix'] ?>settings/crm_referers" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
            <?= l('Список каналов (источники продаж)') ?>
        </a>
    </li>
    <li <?= (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'brands' ? 'class="active"' : '') ?>>
        <a href="<?= $this->all_configs['prefix'] ?>settings/brands" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
            <?= l('Список брендов') ?>
        </a>
    </li>
    <li <?= (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'template_vars' ? 'class="active"' : '') ?>>
        <a href="<?= $this->all_configs['prefix'] ?>print_templates/template_vars" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
            <?= l('Пользовательские шаблоны печатных документов') ?>
        </a>
    </li>
    <li>
        <a href="<?= $this->all_configs['prefix'] ?>custom_status">
            <?= l('Пользовательские статусы заказов') ?>
        </a>
    </li>

</ul>
