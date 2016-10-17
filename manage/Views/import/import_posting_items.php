<div class="container-fluid">
    <div class="row">
        <div class="col-sm-6">
            <form id="import_form" method="post">
                <input type="hidden" name="import_type" value="posting_items"/>
                <h4><?= l('Импорт товарных остатков') ?></h4>
                <?= $body ?>
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
                <li> <?= l('Категории') ?> </li>
                <li> <?= l('Товарная номенклатура') ?> </li>
                <li> <?= l('Товарные остатки') ?></li>
                <li> <?= l('Заказы') ?> </li>
            </ol>
        </div>
    </div>
    <div class="row row-15" id="upload_messages"></div>
</div>
