<?php if (!empty($users_fields)): ?>
    <div class="row-fluid bordered">
        <?php $count = ceil(count($users_fields) / 2); ?>
        <?php $i = 0; ?>
        <div class="span6">
            <?php foreach ($users_fields as $field): ?>
            <?php if ($i == $count): ?>
            <?php $i = 0; ?>
        </div>
        <div class="span6">
            <?php endif; ?>
            <div class="form-group clearfix <?= !isset($hide[$field['name']]) ? 'hide-field' : '' ?>">
                <label>
                            <span style="margin:4px 10px 0 0"
                                  class="pull-left cursor-pointer glyphicon glyphicon-list muted"
                                  onclick="alert_box(this, false, 'changes:update-order-<?= $field['name'] ?>')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                    <?= $field['title'] ?>:
                </label>
                <textarea class="form-control"
                          name="users_fields[<?= $field['name'] ?>]"><?= h($field['value']) ?></textarea>
            </div>

            <?php $i++ ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
