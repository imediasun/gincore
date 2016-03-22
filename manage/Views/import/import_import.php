<div class="container-fluid">
    <div class="row">
        <div class="col-sm-6">
            <form id="import_form" method="post">
                <div class="form-group">
                    <label><?= l('Тип импорта') ?></label>
                    <select class="form-control" name="import_type" id="import_type">
                        <?= $this->renderFile('import/gen_types_select_options', array(
                            'selected' => $selected,
                            'options' => $options
                        )); ?>
                    </select>
                </div>
                <div id="import_form_part">
                    <?= $this->renderFile('import/get_import_form', array(
                        'type' => $selected,
                        'options' => $options,
                    )); ?>
                </div>
                <div class="form-group">
                    <input type="file" name="file">
                </div>
                <div class="form-group">
                    <button class="btn btn-success" type="button"
                            onclick="start_import(this)"><?= l('Запустить') ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="row row-15" id="upload_messages"></div>
</div>
