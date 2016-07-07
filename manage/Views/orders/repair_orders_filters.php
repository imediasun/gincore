<style>
    .input-group-btn:last-child > .btn, .input-group-btn:last-child > .btn-group {
        z-index: auto !important;
    }
</style>
<form method="post" action="<?= $link ?>" class="">
    <div class="clearfix theme_bg filters-box p-sm m-b-md">
        <div class="row row-15">
            <input type="hidden" name="repair-order"/>
            <div class="col-sm-2 b-r">
                <div class="btn-group-vertical">
                    <a class="btn btn-default <?= (!isset($_GET['fco']) && !isset($_GET['marked']) && count($_GET) <= 3 ? 'disabled' : '') ?> text-left"
                       href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>">
                        <?= l('Всего') ?>: <span id="count-clients-orders"><?= $count ?></span>
                    </a>
                    <a class="btn btn-default <?= (isset($_GET['fco']) && $_GET['fco'] == 'unworked' ? 'disabled' : '') ?> text-left"
                       href="
                            <?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?fco=unworked">
                        <?= l('Необработано') ?>: <span
                            id="count-clients-untreated-orders"><?= $count_unworked ?></span>
                    </a>
                    <a class="btn btn-default <?= (isset($_GET['marked']) && $_GET['marked'] == 'co' ? 'disabled' : '') ?> text-left"
                       href="
                            <?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?marked=co#show_orders">
                        <?= l('Отмеченные') ?>: <span class="icons-marked star-marked-active"> </span> <span
                            id="count-marked-co"><?= $count_marked ?></span>
                    </a>
                </div>
                <br><br>
                <input type="submit" name="filter-orders" class="btn btn-primary" value="<?= l('Фильтровать') ?>">
                <?= $this->LockButton->show($_GET['lock-button']) ?>
            </div>
            <div class="col-sm-2 b-r">
                <div class="form-group">
                    <input type="text" placeholder="<?= l('Дата') ?>" name="date" class="daterangepicker form-control"
                           value="<?= $date ?>"/>
                </div>
                <div class="form-group">
                    <input name="client"
                           value="<?= (isset($_GET['cl']) && !empty($_GET['cl']) ? trim(htmlspecialchars($_GET['cl'])) : '') ?>"
                           type="text" class="form-control" placeholder="<?= l('телефон') ?>/<?= l('ФИО клиента') ?>">
                </div>
                <div class="form-group">
                    <input name="order_id"
                           value="<?= (isset($_GET['co_id']) && $_GET['co_id'] > 0 ? intval($_GET['co_id']) : '') ?>"
                           type="text" class="form-control" placeholder="<?= l('номер заказа') ?>">
                </div>
                <input type="text" name="serial" class="form-control"
                       value="<?= (isset($_GET['serial']) ? $_GET['serial'] : '') ?>"
                       placeholder="<?= l('Серийный номер') ?>">
            </div>
            <div class="col-sm-3 b-r">
                <?= typeahead($this->all_configs['db'], 'categories-last', true,
                    isset($_GET['dev']) && $_GET['dev'] ? $_GET['dev'] : '', 5, 'input-small', 'input-mini', '', false,
                    false, '', false, l('Модель')) ?>
                <?= typeahead($this->all_configs['db'], 'goods-goods', true,
                    isset($_GET['by_gid']) && $_GET['by_gid'] ? $_GET['by_gid'] : 0, 6, 'input-small', 'input-mini', '',
                    false, false, '', false, l('Запчасть')) ?>
                <div class="checkbox">
                    <label><input type="checkbox"
                                  name="np" <?= (isset($_GET['np']) ? 'checked' : '') ?> /><?= l('Принято через почту') ?>
                    </label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox"
                                  name="rf" <?= (isset($_GET['rf']) ? 'checked' : '') ?> /><?= l('Выдан подменный фонд') ?>
                    </label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox"
                                  name="nm" <?= (isset($_GET['nm']) ? 'checked' : '') ?> /><?= l('Не оплаченные') ?>
                    </label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox"
                                  name="ar" <?= (isset($_GET['ar']) ? 'checked' : '') ?> /><?= l('Принимались на доработку') ?>
                    </label>
                </div>
            </div>
            <div class="col-sm-3 b-r">
                <div>
                    <div class="span5">
                        <p class="form-control-static"><?= l('Инженер') ?>:</p>
                    </div>
                    <div class="span6">
                            <span class="input-group-btn">
                                <select data-numberDisplayed="0" class="multiselect btn-sm" name="engineers[]"
                                        multiple="multiple">
                                    <?php foreach ($engineers as $engineer): ?>
                                        <option <?= ((isset($_GET['eng']) && in_array($engineer['id'],
                                                explode(',', $_GET['eng']))) ? 'selected' : ''); ?>
                                            value="<?= $engineer['id'] ?>">
                                            <?= htmlspecialchars($engineer['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </span>
                    </div>
                </div>
                <?= $filter_manager ?>
                <div>
                    <div class="span5">
                        <p class="form-control-static"><?= l('Приемщик') ?>:</p>
                    </div>
                    <div class="span6">
                            <span class="input-group-btn">
                                <select
                                    data-numberDisplayed="0" <?= ($this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration')
                                    ? 'disabled' : '') ?> class="multiselect btn-sm" name="accepter[]"
                                    multiple="multiple">
                                    <?php foreach ($accepters as $accepter): ?>
                                        <?php $selected = (($this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration') && $user_id == $accepter['id']) || (isset($_GET['acp']) && in_array($accepter['id'],
                                                    explode(',', $_GET['acp'])))) ? 'selected' : ''; ?>
                                        <option <?= $selected ?> value="<?= $accepter['id'] ?>">
                                            <?= htmlspecialchars($accepter['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </span>
                    </div>
                </div>
                <div>
                    <div class="span5">
                        <p class="form-control-static"><?= l('Статус') ?>:</p>
                    </div>
                    <div class="span6">
                            <span class="input-group-btn">
                                <select data-numberDisplayed="0" class="multiselect btn-sm" name="status[]"
                                        multiple="multiple">
                                    <?php foreach ($this->all_configs['configs']['order-status'] as $os_id => $os_v): ?>
                                        <option <?= ((isset($_GET['st']) && in_array($os_id,
                                                explode(',', $_GET['st']))) ? 'selected' : ''); ?>
                                            value="<?= $os_id ?>">
                                            <?= htmlspecialchars($os_v['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </span>
                    </div>
                </div>
            </div>
            <div class="col-sm-2" style="overflow:hidden">
                <?php if (!empty($wfs)): ?>
                    <?php $sw = isset($_GET['wh']) ? explode(',', $_GET['wh']) : array(); ?>
                    <ul class="nav nav-list well tree" id="tree">
                        <?php foreach ($wfs['groups'] as $wf): ?>
                            <li>
                                <label class="checkbox">
                                    <input type="checkbox"/><?= $wf['name'] ?>
                                </label>
                                <ul class="nav nav-list">
                                    <?php $i = 1; ?>
                                    <?php foreach ($wf['warehouses'] as $wh_id => $wh): ?>
                                        <li>
                                            <label class="checkbox"><?= $i ?>
                                                <i style="color:<?= $wh['color'] ?>;" class="<?= $wh['icon'] ?>"></i>&nbsp;
                                                <input <?= (in_array($wh_id, $sw) ? 'checked' : '') ?>
                                                    name="warehouse[]" value="<?= $wh_id ?>" type="checkbox"/>
                                                <?= $wh['title'] ?>
                                            </label>
                                        </li>
                                        <?php $i++; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                        <?php foreach ($wfs['nogroups'] as $wh_id => $wh): ?>
                            <li>
                                <label class="checkbox">
                                    <i style="color:<?= $wh['color'] ?>;" class="<?= $wh['icon'] ?>"></i>&nbsp;
                                    <input <?= (in_array($wh_id, $sw) ? 'checked' : '') ?> name="warehouse[]"
                                                                                           value="<?= $wh_id ?>"
                                                                                           type="checkbox"/>
                                    <?= $wh['title'] ?>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>
