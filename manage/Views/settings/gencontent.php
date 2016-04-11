<h3>«<?= $pp['title'] ?>»</h3>

<?php if (!isset($this->all_configs['arrequest'][2])): ?>

    <?php if (isset($pp['description'])): ?>
        <h5 class="text-info"><?= htmlspecialchars($pp['description']) ?></h5>
    <?php endif; ?>

    <br>
    <form action="<?= $this->all_configs['prefix'] ?>settings/<?= $pp['id'] ?>/update" method="POST">
        <div class="form-group">
            <label><?= l('sets_param') ?></label>: <?= $pp['name'] ?>
        </div>
        <?php if ($pp['name'] == 'default_order_warranty'): ?>
            <div class="form-group">
                <label><?= l('sets_value') ?>:</label>
                <div class="input-group">
                    <select class="form-control" name="value">
                        <option value=""><?= l('Без гарантии') ?></option>
                        <?php foreach ($orderWarranties as $warranty): ?>
                            <option <?= ($pp['value'] == intval($warranty) ? 'selected' : '') ?>
                                value="<?= intval($warranty) ?>"><?= intval($warranty) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-addon"><?= l('мес') ?></div>
                </div>
            </div>
        <?php else: ?>
            <div class="form-group">
                <label><?= l('sets_value') ?>:</label>
            <textarea class="form-control" id="inputParam" <?= ($pp['ro'] == '1' ? 'disabled="disabled"' : '') ?>
                      name="value" rows="5" cols="60"><?= $pp['value'] ?></textarea>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <input type="submit" value="<?= l('save') ?>" class="btn btn-primary">
        </div>
    </form>

<?php endif; ?>
