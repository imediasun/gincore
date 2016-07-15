<div class="input-group">
    <p class="form-control-static" style="display: inline-block; margin-right: 10px;"><?= l('Сервисный Центр') ?>:</p>
    <span class="input-group-btn">
            <select class="multiselect form-control" multiple="multiple" name="wh_groups[]">
                <?php $wg_get = isset($_GET['wg']) ? explode(',',
                    $_GET['wg']) : (isset($_GET['wh_groups']) ? $_GET['wh_groups'] : array()); ?>
                <?php foreach ($wh_groups as $wh_group): ?>
                    <option <?= ($wg_get && in_array($wh_group['id'], $wg_get) ? 'selected' : ''); ?>
                        value="<?= $wh_group['id'] ?>"><?= $wh_group['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </span>
</div>
