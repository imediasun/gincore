<a class="btn btn-default" href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>">
    <?= l('Список клиентов') ?></a>
<a class="btn btn-default" href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/create">
    <?= l('Создать клиента') ?></a>
<br><br>

<?= l('Редактирование клиента') ?> ID: <?= $client['id'] ?>
<fieldset>
    <legend>
        <?= htmlspecialchars(empty($client['fio']) ? $client['email'] : $client['fio']) ?>
        <?php if ($this->all_configs['configs']['can_see_client_infos']): ?>
            , <?= l('тел') ?> : <?= implode(', ', $phones) ?>
        <?php endif; ?>

        <?php if ($client['tag_id'] != 0): ?>
            <span class="tag" style="background-color: <?= $tags[$client['tag_id']]['color'] ?>">
                <?= htmlspecialchars($tags[$client['tag_id']]['title']) ?>
            </span>
        <?php endif; ?>
    </legend>
</fieldset>

<div class="tabbable">
    <ul class="nav nav-tabs">
        <li <?= (!$new_call_id ? ' class="active"' : '') ?>>
            <a href="#main" data-toggle="tab"> <?= l('Основные') ?> </a>
        </li>
        <li>
            <a href="#calls" data-toggle="tab"><?= l('Звонки') ?></a>
        </li>
        <li>
            <a href="#requests" data-toggle="tab"><?= l('Заявки') ?></a>
        </li>
        <li class="">
            <a href="#orders" data-toggle="tab"><?= l('Заказы') ?></a>
        </li>
        <?php if ($new_call_id): ?>
            <li class="active">
                <a href="#new_call" data-toggle="tab"> <?= l('Новый звонок') ?></a>
            </li>
        <?php endif; ?>
    </ul>
</div>
<div class="tab-content">
    <?php if ($new_call_id): ?>
        <?= $newCallForm; ?>
    <?php endif; ?>
    <div id="main" class="tab-pane<?= (!$new_call_id ? ' active' : '') ?>">
        <div class="row">
            <div class="col-sm-4">
                <?php if ($client['person'] == CLIENT_IS_LEGAL): ?>
                    <?= $this->renderFile('clients/_edit_legal', array(
                        'arrequest' => $arrequest,
                        'client' => $client,
                        'contractorsList' => $contractorsList,
                        'tagsList' => $tagsList,
                        'phones' => $phones
                    )); ?>
                <?php else: ?>
                    <?= $this->renderFile('clients/_edit_personal', array(
                        'arrequest' => $arrequest,
                        'client' => $client,
                        'contractorsList' => $contractorsList,
                        'tagsList' => $tagsList,
                        'phones' => $phones
                    )); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div id="calls" class="tab-pane">
        <?= get_service('crm/calls')->calls_list_table($client['id']) ?>
    </div>
    <div id="requests" class="tab-pane">
        <?= get_service('crm/requests')->requests_list($client['id']) ?>
    </div>

    <?= $ordersList; ?>
</div>
