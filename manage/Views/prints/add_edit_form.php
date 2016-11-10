 <?php if ($can_edit) : ?>
    <div class="well unprint">
        <div>
            <button class="btn btn-small btn-primary" id="editRedactor"><i class="icon-edit"></i> <?= l('Редактировать') ?>
            </button>
            <button class="btn btn-small btn-success" id="saveRedactor"><i class="icon-ok"></i><?= l('Сохранить') ?>
            </button>
            <button class="btn btn-small btn-" id="print"><i class="icon-print"></i><?= l('Печать') ?></button>
            <?php if ($act != 'users_template'): ?>
                <button class="btn btn-small btn-danger" id="restore" style="float: right"><i class="fa fa-cloud-upload"
                                                                                              aria-hidden="true"></i><?= l('Восстановить шаблон') ?>
                </button>
            <?php endif; ?>
        </div>
        <br><br>
        <h4><p class="text-success"> <?= l('Допустимые переменные') ?></p></h4>
        <?= $variables ?>
    </div>
    <div style="display:none; height: 300px" id="print_template">
        <textarea class="tinymce" rows="50"><?= $tpl ?></textarea>
        <iframe id="form_target" name="form_target" style="display:none"></iframe>
        <form id="my_form" action="<?= $this->all_configs['prefix'] ?>print.php?ajax=upload" target="form_target"
              method="post" enctype="multipart/form-data"
              style="width:0px;height:0;overflow:hidden">
            <input name="image" type="file" onchange="$('#my_form').submit();this.value='';">
        </form>
    </div>
    <div id="redactor"><?= $print_html ?></div>
 <?php else: ?>
     <div class="well unprint">
         <button class="btn btn-small btn-" id="print"><i class="icon-print"></i><?= l('Печать') ?></button>
     </div>
     <div id="redactor">
         <?= $print_html ?>
     </div>
 <?php endif; ?>
