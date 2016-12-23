<style>
    .input-group-btn:last-child > .btn, .input-group-btn:last-child > .btn-group {
        z-index: auto !important;
    }
</style>
<form method="post" action="<?= $link ?>" class="js-filters" style="display: none">
    <div class="clearfix theme_bg filters-box p-sm m-b-md">
        <div class="row row-15">
            <input type="hidden" name="clients-filters"/>
            <div class="col-sm-2 b-r">
                <div class="btn-group-vertical">
                    <a class="btn btn-default <?= (!isset($_GET['fco']) && !isset($_GET['marked']) && count($_GET) <= 3 ? 'disabled' : '') ?> text-left"
                       href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>">
                        <?= l('Всего') ?>: <span id="count-clients-orders"><?= $count ?></span>
                    </a>
                    <a class="btn btn-default <?= (isset($_GET['marked']) && $_GET['marked'] == 'cl' ? 'disabled' : '') ?> text-left"
                       href="
                            <?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?tab=clients&marked=cl#show_orders">
                        <?= l('Отмеченные') ?>: <span class="icons-marked star-marked-active"> </span> <span
                            id="count-marked-co"><?= $count_marked ?></span>
                    </a>
                </div>
                <br><br>
                <div class="form-group" style="white-space: nowrap">
                    <input type="submit" name="filter-clients" class="btn btn-primary" value="<?= l('Фильтровать') ?>">
                    <?= $this->LockButton->show($_GET['lock-button']) ?>
                </div>
            </div>
            <div class="col-sm-2 b-r">
                <div class="form-group">
                    <input name="client"
                           value="<?= (isset($_GET['s']) && !empty($_GET['s']) ? trim(h($_GET['s'])) : '') ?>"
                           type="text" class="form-control" placeholder="<?= l('телефон') ?>/<?= l('ФИО клиента') ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="client_id" class="form-control"
                           value="<?= (isset($_GET['cl_id']) ? $_GET['cl_id'] : '') ?>"
                           placeholder="<?= l('ID клиента') ?>">
                </div>
                <div class="form-group">
                    <input name="order_id"
                           value="<?= (isset($_GET['co_id']) && $_GET['co_id'] > 0 ? intval($_GET['co_id']) : '') ?>"
                           type="text" class="form-control" placeholder="<?= l('номер заказа') ?>">
                </div>
            </div>
            <div class="col-sm-4 b-r">
                <table class="table table-borderless table-for-filters">
                    <tr>
                        <td class="span5">
                            <p class="form-control-static"><?= l('За период') ?>:</p>
                        </td>
                        <td class="span6" >
                            <div class="form-group">
                                <input type="text" placeholder="<?= l('Дата') ?>" name="date"
                                       class="daterangepicker form-control"
                                       value="<?= $date ?>"/>

                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="span5">
                            <p class="form-control-static">
                                <?= l('Ремонтировали') ?>:
                            </p>
                        </td>
                        <td class="span6">
                            <div class="form-group">
                                <?php $device = isset($_GET['dev']) && $_GET['dev'] ? $_GET['dev'] : ''; ?>
                                <?= typeahead($this->all_configs['db'], 'categories', false,
                                    isset($_GET['cat']) && $_GET['cat'] ? $_GET['cat'] : $device, 5, 'input-small',
                                    'input-mini',
                                    '',
                                    false,
                                    false, '', false, l('Категория') . ',' . l('Модель')) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="span5">
                            <p class="form-control-static">
                                <?= l('Приобретали') ?>:
                            </p>
                        </td>
                        <td class="span6" >
                            <div class="form-group">
                                <?= typeahead($this->all_configs['db'], 'goods-goods', false,
                                    isset($_GET['by_gid']) && $_GET['by_gid'] ? $_GET['by_gid'] : 0, 6, 'input-small',
                                    'input-mini',
                                    '',
                                    false, false, '', false, l('Товар') . ',' . l('Запчасть')) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="span5">
                            <p class="form-control-static"><?= l('Количество обращений') ?>:</p>
                        </td>
                        <td class="span6">
                            <div class="form-group" style="white-space: nowrap; text-align: right">
                                <?= l('от') ?> <input type="text" class="form-control"
                                                      value="<?= isset($_GET['cqf']) ? $_GET['cqf'] : 0 ?>"
                                                      name="cq_from" style="width: 75%; display:inline-block "/>
                            </div>
                            <div class="form-group" style="white-space: nowrap; text-align: right">
                                <?= l('до') ?> <input type="text" class="form-control"
                                                      value="<?= isset($_GET['cqt']) ? $_GET['cqt'] : 0 ?>" name="cq_to"
                                                      style="width: 75%; display: inline-block"/>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-sm-4">
                <table class="table table-borderless table-for-filters">
                    <tr>
                        <td class="span5">
                            <p class="form-control-static"><?= l('Оператор') ?>:</p>
                        </td>
                        <td class="span6">
                            <div class="form-group">
                                <select data-numberDisplayed="0" class="multiselect btn-sm" name="operators[]"
                                        multiple="multiple">
                                    <?php foreach ($operators as $operator): ?>
                                        <option <?= ((isset($_GET['ops']) && in_array($operator['id'],
                                                explode(',', $_GET['ops']))) ? 'selected' : ''); ?>
                                            value="<?= $operator['id'] ?>"> <?= h($operator['fio']) ?> </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="span5">
                            <p class="form-control-static"><?= l('Тип клиента') ?>:</p>
                        </td>
                        <td class="span6">
                            <div class="form-group">
                                <select data-numberDisplayed="0" class="multiselect btn-sm" name="persons[]"
                                        multiple="multiple">
                                    <option <?= ((isset($_GET['persons']) && in_array(1,
                                            explode(',', $_GET['persons']))) ? 'selected' : ''); ?>
                                        value="1"> <?= l('Физ. лицо') ?> </option>
                                    <option <?= ((isset($_GET['persons']) && in_array(2,
                                            explode(',', $_GET['persons']))) ? 'selected' : ''); ?>
                                        value="2"> <?= l('Юр. лицо') ?> </option>

                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="span5">
                            <p class="form-control-static"><?= l('Метка') ?>:</p>
                        </td>
                        <td class="span6">
                            <div class="form-group">
                                <select data-numberDisplayed="0" class="multiselect btn-sm" name="tags[]"
                                        multiple="multiple">
                                    <?php foreach ($tags as $tag): ?>
                                        <option <?= ((isset($_GET['tags']) && in_array($tag['id'],
                                                explode(',', $_GET['tags']))) ? 'selected' : ''); ?>
                                            value="<?= $tag['id'] ?>"> <?= h($tag['title']) ?> </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="span5">
                            <p class="form-control-static"><?= l('Рекламный канал') ?>:</p>
                        </td>
                        <td class="span6">
                            <div class="form-group">
                                <select data-numberDisplayed="0" class="multiselect btn-sm" name="referrers[]"
                                        multiple="multiple">
                                    <?php foreach ($referrers as $ref_id => $ref_name): ?>
                                        <option <?= ((isset($_GET['refs']) && in_array($ref_id,
                                                explode(',', $_GET['refs']))) ? 'selected' : ''); ?>
                                            value="<?= $ref_id ?>"> <?= h($ref_name) ?> </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="span5">
                            <p class="form-control-static"><?= l('Действия') ?>:</p>
                        </td>
                        <td class="span6">
                            <div class="form-group">
                                <select data-numberDisplayed="0" class="multiselect btn-sm" name="acts[]"
                                        multiple="multiple">
                                    <?php foreach (array(
                                        CLIENT_ACT_CALL => l('Были входящие звонки'),
                                        CLIENT_ACT_REQUEST => l('Создавались заявки'),
                                        CLIENT_ACT_ORDER => l('Создавались заказы'),
                                    ) as $act_id => $act): ?>
                                        <option <?= ((isset($_GET['acts']) && in_array($act_id,
                                                explode(',', $_GET['acts']))) ? 'selected' : ''); ?>
                                            value="<?= $act_id ?>">
                                            <?= h($act) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</form>
<style>
    .multiselect.dropdown-toggle {
        height: 34px;
    }
</style>