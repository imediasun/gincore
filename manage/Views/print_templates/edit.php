<h3>
    <?= $config['name'] ?>
    <a href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add"
       class="btn btn-primary"><?= l('Добавить шаблон') ?></a>
</h3>
<form action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/save" method="post">
    <?php $accordeon = 0; ?>
    <div class="panel-group" id="accordion">
        <?php foreach ($translates as $id => $langs): ?>
            <div class="panel panel-default">
                <?php $field = 'text' ?>
                <?php $field_name = l('Значение'); ?>
                <?php foreach ($langs as $lng => $translate): ?>
                    <?php if ($lng == $manage_lang): ?>
                        <div class="panel-heading" style="position: relative">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?= $accordeon ?>">
                                    <?= empty($translate['description']) ? $translate['var'] : $translate['description'] ?>
                                    (<?= l($translate['for_view']) ?>)
                                </a>
                                <a class="template-remove"
                                   onclick="return confirm('<?= l('Вы уверены, что хотите удалить этот шаблон?') ?>')"
                                   href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/delete/<?= $translate['var_id'] ?>"><i
                                        class="fa fa-remove"></i></a>
                            </h4>
                        </div>
                        <div id="collapse<?= $accordeon++ ?>" class="panel-collapse collapse">
                            <div class="panel-body">
                                <?php if ($translate['for_view'] == 'repair_order'): ?>
                                    <?= $this->renderFile('print_templates/_repair_template_arr'); ?>
                                <?php endif; ?>
                                <?php if ($translate['for_view'] == 'sale_order'): ?>
                                    <?= $this->renderFile('print_templates/_sale_template_arr'); ?>
                                <?php endif; ?>
                                <?php $value = h($translate[$field]); ?>
                                <div class="row-fluid">
                                    <div class="span3">
                                        <div class="form-group" style="display:block; margin-top:20px">
                                            <?php $f_name = 'data[' . $id . '][' . $lng . '][description]'; ?>
                                            <label>
                                                <?= l('Название') ?>
                                                <input class="form-control" type="text" name="<?= $f_name ?>" required
                                                       value="<?= h($translate['description']) ?>">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="span3">
                                        <div class="form-group" style="display:block; margin-top:20px">
                                            <?php $f_name = 'data[' . $id . '][' . $lng . '][priority]'; ?>
                                            <label>
                                                <?= l('Приоритет') ?> <?= InfoPopover::getInstance()->createQuestion('l_create_new_template_priority') ?>
                                                <input class="form-control" type="text" name="<?= $f_name ?>"
                                                       value="<?= h($translate['priority']) ?>">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row-fluid">
                                    <div class="span3">
                                        <label><?= l('Для формы') ?></label>
                                        <?php $f_name = 'data[' . $id . '][' . $lng . '][for_view]'; ?>
                                        <select class="form-control" name="<?= $f_name ?>" required>
                                            <?php foreach (array('repair_order', 'sale_order') as $item): ?>
                                                <option <?= ($translate['for_view'] == $item) ? 'selected' : ''; ?>
                                                    value="<?= $item ?>"><?= l($item) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row-fluid" style="margin-top: 20px">
                                <span class="form-group" style="display:block">
                            <?php $f_name = 'data[' . $id . '][' . $lng . '][' . $field . ']'; ?>
                                    <?php if ($textarea || strlen($value) > 50): ?>
                                        <textarea class="form-control <?= $textarea ? 'tinymce' : '' ?>"
                                                  style="height: 150px"
                                                  name="<?= $f_name ?>"><?= $value ?></textarea>
                                    <?php else: ?>
                                        <input class="form-control" type="text" name="<?= $f_name ?>"
                                               value="<?= $value ?>">
                                    <?php endif; ?>
                            </span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <input type="submit" class="save-btn btn btn-primary" value="<?= l('save') ?>">
</form>

<?php if ($textarea): ?>
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
<?php endif; ?>