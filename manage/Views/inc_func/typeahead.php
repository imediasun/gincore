<?php if ($show_categories): ?>
<div class="form-group-row clearfix">
    <div class="col-sm-5">
        <select <?= ($disabled ? 'disabled' : '') ?>
            class="<?= $class_select ?> select-typeahead-<?= $iterator ?> form-control">
            <option value="0"><?= l('Все разделы') ?></option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= $category['title'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-7" style="padding-left: 15px !important;">
        <?php endif; ?>



        <input <?= ($no_clear_if_null ? ' data-no_clear_if_null="1"' : '') ?> data-required="true"
                                                                              data-placement="right"
                                                                              name="<?= $table ?>-value<?= ($multi ? '[' . $m . ']' : '') ?>" <?= ($disabled ? 'disabled' : '') ?>
                                                                              type="text" value="<?= $object_name ?>"
                                                                              data-input="<?= $table . $iterator ?>"
                                                                              data-function="<?= $function ?>"
                                                                              data-select="<?= $iterator ?>"
                                                                              data-table="<?= $table ?>" <?= ($anyway ? 'data-anyway="1"' : '') ?>
                                                                              autocomplete="off"
                                                                              class="form-control global-typeahead <?= $class ?>"
                                                                              placeholder="<?= $placeholder ?>" <?= $add_attr ?>>

        <input type="hidden" value="<?= $object_id ?>" name="<?= $table . ($multi ? '[' . $m . ']' : '') ?>"
               class="typeahead-value-<?= $table . $iterator ?>">

        <?php if ($show_categories): ?>
    </div>
</div>
<?php endif; ?>


