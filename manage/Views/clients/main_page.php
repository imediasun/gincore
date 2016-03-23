<a class="btn btn-default" href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create">
    <?= l('Создать клиента') ?>
</a>
<form class="form-horizontal" method="post" style="margin-bottom:0;float:left;max-width:300px">
    <div class="input-group">
        <input class="form-control" type="text" value="<?= (isset($_GET['s']) ? htmlspecialchars($_GET['s']) : '') ?>"
               name="text">
        <div class="input-group-btn">
            <input class="btn btn-default" type="submit" value="<?= l('Поиск') ?>" name="search">
        </div>
    </div>
</form>
<br><br>
<div class="tabbable clearfix">
    <ul class="nav nav-tabs pull-left">
        <li<?= ($_GET['tab'] == 'clients' ? ' class="active"' : '') ?>>
            <a href="<?= $this->all_configs['prefix'] ?>clients<?= $mod_submenu[0]['url'] ?>"><?= $mod_submenu[0]['name'] ?></a>
        </li>
        <li<?= ($_GET['tab'] == 'calls' ? ' class="active"' : '') ?>>
            <a href="<?= $this->all_configs['prefix'] ?>clients<?= $mod_submenu[1]['url'] ?>"><?= $mod_submenu[1]['name'] ?></a>
        </li>
        <li<?= ($_GET['tab'] == 'requests' ? ' class="active"' : '') ?>>
            <a href="<?= $this->all_configs['prefix'] ?>clients<?= $mod_submenu[2]['url'] ?>"><?= $mod_submenu[2]['name'] ?></a>
        </li>
        <li<?= ($_GET['tab'] == 'statistics' ? ' class="active"' : '') ?>>
            <a href="<?= $this->all_configs['prefix'] ?>clients<?= $mod_submenu[3]['url'] ?>"><?= $mod_submenu[3]['name'] ?></a>
        </li>
        <li<?= ($_GET['tab'] == 'group_clients' ? ' class="active"' : '') ?>>
            <a href="<?= $this->all_configs['prefix'] ?>clients<?= $mod_submenu[4]['url'] ?>"><?= $mod_submenu[4]['name'] ?></a>
        </li>
    </ul>
    <div class="pull-right">
        <form style="margin-right:30px" action="<?= $this->all_configs['prefix'] ?>clients" method="get">
            <input type="hidden" name="export" value="1">
            <input type="submit" class="btn btn-info" value="<?= l('Экспорт') ?>">
        </form>
    </div>
</div>
<div class="tab-content">
    <div class="tab-pane active">
        <?= $content ?>
    </div>
</div>
