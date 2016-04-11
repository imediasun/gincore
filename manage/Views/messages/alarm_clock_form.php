<br/>
<div class="panel-" id="accordion-alarms">
    <div class="panel panel-default">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-alarms"
               href="#accordion-alarm-add">
                <?= l('Добавить напоминание') ?>
            </a>
        </div>
        <div id="accordion-alarm-add" class="panel-collapse collapse">
            <div class="panel-body">
                <form method="post" id="add-alarm">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>"/>
                    <textarea class="form-control" name="text"
                              placeholder="<?= l('комментарий к напоминанию') ?>"></textarea>
                    <div class="checkbox">
                        <label>
                            <input <?= ($order_id > 0 ? '' : 'disabled') ?> type="checkbox"
                                                                            name="text-to-private-comment">
                            <?= l('Продублировать в скрытый комментарий') ?>
                        </label>
                    </div>
                    <div class="form-group">
                        <input class="form-control datetimepicker" placeholder="<?= l('Дата напоминания') ?>"
                               data-format="yyyy-MM-dd hh:mm:ss" type="text" name="date_alarm" value=""/>
                    </div>
                    <div class="form-group">
                        <label>
                            <?= l('Ответственный'); ?>
                        </label>
                        <?= typeahead($this->all_configs['db'], 'users', false, $user_id, 26, 'input-xlarge') ?>
                    </div>
                    <input style="margin-left:0" type="button" class="btn btn-default" onclick="add_alarm(this)"
                           value="<?= l('Добавить') ?>"/>
                </form>
            </div>
        </div>
    </div>
    <?= show_alarms($this->all_configs, $user_id); ?>

    <div class="panel-group" id="accordion-alarms-history">
        <div class="panel-default panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-alarms-history"
                   href="#accordion-alarm-show">
                    <?= l('История') ?>
                </a>
            </div>
            <div id="accordion-alarm-show" class="panel-collapse collapse">
                <div class="panel-body">
                    <?= show_alarms($this->all_configs, $user_id, true); ?>
                </div>
            </div>
        </div>
    </div>
</div>
