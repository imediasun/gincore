<div class="col-sm-12">
    <legend><?= l('Добавление клиента') ?></legend>
</div>

<div class="row-fluid">
    <div class="tabbable col-sm-7">
        <ul class="nav nav-tabs">
            <li class=" <?= !isset($_POST['create-legal'])? 'active': '' ?>">
                <a href="#personal" data-toggle="tab"> <?= l('Физ. лицо') ?> </a>
            </li>
            <li class=" <?= isset($_POST['create-legal'])? 'active': '' ?>">
                <a href="#legal" data-toggle="tab"><?= l('Юр. лицо') ?></a>
            </li>
        </ul>
    </div>
</div>
<div class="row-fluid">
    <div class="tab-content col-sm-7">
        <div id="personal" class="tab-pane <?= !isset($_POST['create-legal'])? 'active': '' ?>">
            <?= $this->renderFile('clients/_create_personal', array(
                'contractors' => $contractors,
                'tags' => $tags
            )); ?>
        </div>
        <div id="legal" class="tab-pane <?= isset($_POST['create-legal'])? 'active': '' ?>">
            <?= $this->renderFile('clients/_create_legal', array(
                'contractors' => $contractors,
                'tags' => $tags
            )); ?>
        </div>
    </div>
</div>
