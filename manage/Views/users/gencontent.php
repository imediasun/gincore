<div class="tabbable">
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="<?= $mod_submenu[0]['url'] ?>"><?= $mod_submenu[0]['name'] ?></a>
        </li>
        <li><a data-toggle="tab" href="<?= $mod_submenu[1]['url'] ?>"><?= $mod_submenu[1]['name'] ?></a></li>
        <li><a data-toggle="tab" href="<?= $mod_submenu[2]['url'] ?>"><?= $mod_submenu[2]['name'] ?></a></li>
        <li><a data-toggle="tab" href="<?= $mod_submenu[3]['url'] ?>"><?= $mod_submenu[3]['name'] ?></a></li>
        <?php if ($this->all_configs['oRole']->hasPrivilege('site-administration') && isset($mod_submenu[4])): ?>
            <li><a data-toggle="tab" href="<?= $mod_submenu[4]['url'] ?>"><?= $mod_submenu[4]['name'] ?></a></li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">
        <?= $users; ?>
        <?= $role_list ?>
        <?= $create_new_role ?>
        <?= $create_user_form ?>
        <?= $logins_log ?>
    </div>

</div>
