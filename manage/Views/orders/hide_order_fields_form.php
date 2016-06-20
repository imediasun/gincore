<form method="post" id="hide-orders-fields-form" style="display:none">
    <input type="hidden" name="hide-fields" value="1">
    <div class="col-sm-1" style="min-width: 110px">
        <legend>&nbsp;</legend>
        <div class="form-group">
            <label>&nbsp; <b class="text-danger">&nbsp;</b></label>
            <div class="row row-15">
                <div class="col-sm-6">
                    <br>
                    <br>
                </div>
            </div>
        </div>
        <span class="toggle_btn">
            &nbsp;
        </span>
        <div class="form-group">
            <label>&nbsp; <b class="text-danger">&nbsp;</b></label>
            <div class="row row-15">
                <div class="col-sm-6">
                    <input type="checkbox"
                           name="config[crm-order-code]" <?= isset($hide['crm-order-code']) ? 'checked' : '' ?>
                           class="test-toggle">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>&nbsp; <b class="text-danger">&nbsp;</b></label>
            <div class="row row-15">
                <div class="col-sm-6">
                    <input type="checkbox" name="config[referrer]" <?= isset($hide['referrer']) ? 'checked' : '' ?>
                           class="test-toggle">
                </div>
            </div>
        </div>
        <legend>&nbsp;</legend>
        <div class="form-group">
            <label class="control-label">&nbsp; <b
                    class="text-danger">&nbsp;</b> </label>
            <br>
            <br>
            <br>
        </div>
        <div class="form-group" style="margin-top:-5px;">
            <label class="control-label">&nbsp;</label>
            <input type="checkbox" name="config[color]" <?= isset($hide['color']) ? 'checked' : '' ?>
                   class="test-toggle">
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <br>
            <input type="checkbox" name="config[serial]" <?= isset($hide['serial']) ? 'checked' : '' ?>
                   class="test-toggle">
        </div>
        <div class="form-group" style="margin-top:20px">
            <label>&nbsp;</label>
            <input type="checkbox" name="config[equipment]" <?= isset($hide['equipment']) ? 'checked' : '' ?>
                   class="test-toggle"><br>
        </div>
        <div class="form-group" style="margin-top: 50px">
            <label>&nbsp;</label>
            <input type="checkbox" name="config[repair-type]" <?= isset($hide['repair-type']) ? 'checked' : '' ?>
                   class="test-toggle"><br>
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <div class="row row-15 form-group" style="margin-top: -10px">
                <div class="col-sm-6">
                    <label>&nbsp;</label><br>
                    <input type="checkbox" name="config[defect]" <?= isset($hide['defect']) ? 'checked' : '' ?>
                           class="test-toggle"><br>
                </div>
            </div>
            <input type="checkbox"
                   name="config[defect-description]" <?= isset($hide['defect-description']) ? 'checked' : '' ?>
                   class="test-toggle"><br>
        </div>
        <div class="form-group" style="margin-top:37px;">
            <label class="control-label">&nbsp;</label><br>
            <input type="checkbox" name="config[appearance]" <?= isset($hide['appearance']) ? 'checked' : '' ?>
                   class="test-toggle"><br>
        </div>
        <br>
        <legend>&nbsp;</legend>
        <div class="form-group">
            <label>&nbsp;</label>
            <div class="input-group">
                <input type="checkbox" name="config[cost]" <?= isset($hide['cost']) ? 'checked' : '' ?>
                       class="test-toggle">
            </div>
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <div class="input-group">
                <input type="checkbox" name="config[prepaid]" <?= isset($hide['prepaid']) ? 'checked' : '' ?>
                       class="test-toggle">
            </div>
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <div class="input-group">
                <input type="checkbox"
                       name="config[available-date]" <?= isset($hide['available-date']) ? 'checked' : '' ?>
                       class="test-toggle">
            </div>
        </div>
        <div class="form-group" style="margin-top: 30px; margin-bottom: 130px">
            <label>&nbsp;</label><br>
            <div class="input-group">
                <input type="checkbox"
                       name="config[addition-info]" <?= isset($hide['addition-info']) ? 'checked' : '' ?>
                       class="test-toggle">
            </div>
        </div>
        <?php if (!empty($users_fields)): ?>
            <?php foreach ($users_fields as $field): ?>
                <div class="form-group" style="height: 78px">
                    <label>&nbsp;</label>
                    <div class="input-group">
                        <input type="checkbox"
                               name="config[<?= $field['name'] ?>]" <?= isset($hide[$field['name']]) ? 'checked' : '' ?>
                               class="test-toggle">
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="form-group js-new_field_height" style="height: 50px"></div>
        <div class="form-group" style="margin-top: 0px;">
            <button id='apply-hide' type="submit" class="btn btn-primary"
                    onclick="apply_hide(this)"><?= l('Применить') ?></button>
        </div>
    </div>
</form>
