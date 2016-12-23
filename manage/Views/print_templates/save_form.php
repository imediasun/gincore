<h3><?= $config['name'] ?></h3>
<form data-='' action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add/save"
      method="post">

        <?php foreach ($columns as $column): ?>
            <?php if ($column['Field'] == 'for_view'): ?>
                <div class="form-group">
                    <label><?= l('Для формы') ?></label>
                    <select class="form-control" name="data[for_view]" required
                            onchange="$('.js-arr').hide();$('.js-'+this.options[this.selectedIndex].value).show(); return true;">
                        <option value=""><?= l('Выберите экран для которого используется этот шаблон') ?></option>
                        <?php foreach (array('repair_order', 'sale_order') as $item): ?>
                            <option <?= (isset($_POST['data']['for_view']) && $_POST['data']['for_view'] == $item) ? 'selected' : ''; ?>
                                value="<?= $item ?>"><?= l($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php if (!in_array($column['Field'], array('id', 'type', 'for_view'))): ?>
                <div class="form-group">
                    <label>
                        <?php if ($column['Field'] == 'description'): ?>
                            <?= l('Название') ?>
                        <?php elseif ($column['Field'] == 'var'): ?>
                            <?= l('Идентификатор') ?>
                        <?php elseif ($column['Field'] == 'priority'): ?>
                            <?= l('Приоритет') ?><?= InfoPopover::getInstance()->createQuestion('l_create_new_template_priority') ?>
                        <?php else: ?>
                            <?= l($column['Field']) ?>
                        <?php endif; ?>
                    </label>
                    <input required class="form-control" name="data[<?= $column['Field'] ?>]" type="text"
                           value="<?= isset($_POST['data'][$column['Field']]) ? $_POST['data'][$column['Field']] : '' ?>">
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <legend><?= l('Шаблон') ?></legend>
        <div class="js-arr js-repair_order" style="display: none">
            <?= $this->renderFile('print_templates/_repair_template_arr'); ?>
        </div>
        <div class="js-arr js-sale_order" style="display: none">
            <?= $this->renderFile('print_templates/_sale_template_arr'); ?>
        </div>
        <?php foreach ($config['fields'] as $field => $field_name): ?>
            <div class="form-group">
                <label><?= $field_name ?>, <?= $manage_lang ?></label>
                <textarea class="form-control tinymce"
                          style="height: 150px"
                          name="translates[<?= $manage_lang ?>][<?= $field ?>]"></textarea>
            </div>
        <?php endforeach; ?>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="<?= l('save') ?>">
    </div>
</form>
<script type="text/javascript" src="<?= $this->all_configs['prefix']; ?>js/tinymce/tinymce.min.js"></script>
<script>
    $(document).ready(function () {
        tinymce.init({
            selector: '.tinymce',
            theme: 'modern',
            plugins: [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars code fullscreen',
                'insertdatetime nonbreaking save table contextmenu directionality',
                'template paste textcolor colorpicker textpattern imagetools'
            ],
            toolbar1: 'insertfile undo redo | styleselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            toolbar2: "bold italic | forecolor backcolor |  fontselect |  fontsizeselect",
            fontsize_formats: "4pt 6pt 8pt 10pt 12pt 14pt 18pt 24pt 36pt"
        });
    });
</script>
