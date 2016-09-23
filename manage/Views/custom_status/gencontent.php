<h2><?= $title ?> (<?= l($current) ?>)</h2>
<form action="<?= $this->all_configs['prefix'] ?>/custom_status/update" method="POST">
    <input type="hidden" name="custom-statuses" value=""/>
    <input type="hidden" name="order-type" value="<?= $current ?>"/>

    <table class="table">
        <thead>
        <tr>
            <th width="50%"><?= l('Наименование статуса') ?></th>
            <th width="10%"><?= l('Цвет') ?></th>
            <th class="text-center"><?= l('Использовать в менеджере заказов') ?></th>
            <th class="text-center"><?= l('Активный') ?></th>
            <th class="text-center"><?= l('Удалить') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($statuses as $status): ?>
            <tr>
                <td>
                    <input type="hidden" name="ids[]" value="<?= $status['id'] ?>"/>
                    <input class="form-control" type="text"
                           name="name[<?= $status['id'] ?>]" <?= $status['system'] ? 'disabled' : '' ?>
                           value="<?= h($status['name']) ?>"/>
                </td>
                <td class="text-center">
                    <div id="demo_endis" class="input-group colorpicker-auto colorpicker-component colorpicker-element">
                        <input class="form-control colorpicker" type="text" name="color[<?= $status['id'] ?>]"
                               placeholder='<?= l('введите цвет') ?>' value="#<?= h($status['color']) ?>">
                        <span class="input-group-addon">
                            <i class='show-color' style="background-color: #<?= h($status['color']) ?>"></i>
                        </span>
                    </div>
                </td>
                <td class="text-center">
                    <center>
                        <input class="checkbox" type="checkbox"
                               name="use_in_manager[<?= $status['id'] ?>]" <?= $status['use_in_manager'] != 0 ? 'checked' : '' ?>  <?= $status['system'] ? 'disabled' : '' ?>/>
                    </center>
                </td>
                <td class="text-center">
                    <center>
                        <input class="checkbox" type="checkbox"
                               name="active[<?= $status['id'] ?>]" <?= $status['active'] != 0 ? 'checked' : '' ?>  <?= $status['system'] ? 'disabled' : '' ?>/>
                    </center>
                </td>
                <td class="text-center">
                    <?php if (!$status['system']): ?>
                        <center>
                            <input class="checkbox" type="checkbox" name="delete[<?= $status['id'] ?>]"/>
                        </center>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td>
                <input type="hidden" name="ids[]" value="new"/>
                <input class="form-control" type="text" name="name[new]" value=""
                       placeholder="<?= l('Введите название нового статуса') ?>"/>
            </td>
            <td>
                <div id="demo_endis" class="input-group colorpicker-auto colorpicker-component colorpicker-element">
                    <input class="form-control colorpicker" type="text" name="color[new]"
                           placeholder='<?= l('введите цвет') ?>' value="#ffffff">
                    <span class="input-group-addon">
                        <i class='show-color' style="background-color: #ffffff"></i>
                    </span>
                </div>
            </td>
            <td style="text-align: center">
                <center>
                    <input class="checkbox" type="checkbox" name="use_in_manager[new]" checked/>
                </center>
            </td>
            <td style="text-align: center">
                <center>
                    <input class="checkbox" type="checkbox" name="active[new]" checked/>
                </center>
            </td>
            <td>
            </td>
        </tr>
        </tfoot>
    </table>
    <div class="form-group">
        <input type="submit" name='update-status' value="<?= l('save') ?>" class="btn btn-primary">
    </div>
</form>
<script>
    function init_colorpickers() {
        $('.colorpicker.colorpicker-element').colorpicker('destroy');
        $('.colorpicker-auto').colorpicker();
    }
    jQuery(document).ready(function () {
        init_colorpickers();
    });
</script>
