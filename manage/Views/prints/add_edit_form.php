<div class="well unprint">
    <button class="btn btn-small btn-primary" id="editRedactor"><i class="icon-edit"></i> <?= l('Редактировать') ?></button>
    <button class="btn btn-small btn-success" id="saveRedactor"><i class="icon-ok"></i><?= l('Сохранить') ?></button>
    <button class="btn btn-small btn-" id="print"><i class="icon-print"></i><?= l('Печать') ?></button>
    <br><br>
    <h4><p class="text-success"><?= l('Допустимые переменные') ?></p></h4>
    <?= $variables ?>
</div>
<div style="display:none" id="print_tempalte"><?= $tpl ?></div>
<div id="redactor"><?= $print_html ?></div>