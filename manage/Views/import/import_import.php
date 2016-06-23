<div class="container-fluid">
    <div class="row">
        <div class="col-sm-6">
            <form id="import_form" method="post">
                <div class="form-group">
                    <label><?= l('Тип импорта') ?></label>
                    <select class="form-control" name="import_type" id="import_type" style="width:50%">
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
        <div class="col-sm-3">
        </div>
        <div class="col-sm-3">
            <div class="row" style="font-size: 3em; text-align: center">
                <i class="glyphicon glyphicon-warning-sign"></i>
            </div>
            <p> <?= l('Рекомендуем импортировать файлы в слудующем порядке:') ?> </p>
            <ol>
                <li> <?= l('База клиентов') ?> </li>
                <li> <?= l('Товары, категории') ?> </li>
                <li> <?= l('Заказы') ?> </li>
            </ol>
        </div>
    </div>
    <div class="row row-15" id="upload_messages"></div>
</div>
