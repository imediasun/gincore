<div class="row row-15">
    <form method="post">
        <table class="table table-borderless stocktaking-filters">
            <tbody>
            <tr>
                <td>
                    <label><?= l('Склад') ?></label>
                </td>
                <td>

                    <select onchange="change_warehouse(this)" class="multiselect form-control" name="warehouses[]"
                            multiple="multiple">
                        <?php if (!empty($warehouses)): ?>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <option <?= in_array($warehouse['id'], $warehouses_selected) ? 'selected' : '' ?>
                                    value="<?= $warehouse['id'] ?>"><?= $warehouse['title'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </td>
                <td></td>
                <td></td>
                <td>
                    <label><?= l('Недостача') ?>:</label>
                    <span class="js-deficit">0</span><?= l('шт.') ?>
                </td>
                <td>
                    <button type="button" class="btn btn-primary" name="export-deficit"><?= l('Экспорт') ?></button>
                </td>
            </tr>
            <tr>
                <td>
                    <label><?= l('Локация') ?></label>
                </td>
                <td>
                    <select class="multiselect form-control select-location" name="locations[]" multiple="multiple">
                        <?= $whSelect ?>
                    </select>

                </td>
                <td></td>
                <td></td>
                <td>
                    <label><?= l('Излишек') ?>:</label>
                    <span class="js-surplus">0</span><?= l('шт.') ?>
                </td>
                <td>
                    <button type="button" class="btn btn-primary" name="export-surplus"><?= l('Экспорт') ?></button>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>
                    <lable><?= l('Серийный N') ?></lable>
                </td>
                <td>

                    <div class="input-group col-sm-6">
                        <input class="form-control" name="serial" placeholder="<?= l('серийный номер') ?>" value="<?= ((isset($_GET['serial']) && !empty($_GET['serial'])) ? htmlspecialchars(urldecode($_GET['serial'])) : '') ?>" />
                        <div class="input-group-btn">
                            <input class="btn" type="submit" name="filter-warehouses" value="<?= l('Поиск') ?>" />
                        </div>
                    </div>
                </td>
                <td></td>
                <td></td>

            </tr>
            </tbody>
        </table>
    </form>
</div>
<script>
    jQuery(document).ready(function () {
        init_multiselect();
    });
</script>