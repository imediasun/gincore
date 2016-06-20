<h3>
    <?= $config['name'] ?>
    <a href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add">+</a>
</h3>
<form action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/save" method="post">
    <fieldset class="main">
        <?php foreach ($translates as $id => $langs): ?>
            <div>
                <?php $field = 'body' ?>
                <?php $field_name = l('Значение'); ?>
                <?php foreach ($langs as $lng => $translate): ?>
                    <?php if ($lng == $manage_lang): ?>
                        <legend><?= $translate['var'] ?> (<?= l($types[$translate['type']]) ?>)</legend>
                        <?php $value = h($translate[$field]); ?>
                        <span class="form-group" style="display:block">
                                <?php $f_name = 'data[' . $id . '][' . $lng . '][' . $field . ']'; ?>
                            <?php if ($textarea || strlen($value) > 50): ?>
                                <textarea class="form-control <?= $textarea ? 'tinymce' : '' ?>"
                                          style="height: 150px"
                                          name="<?= $f_name ?>"><?= $value ?></textarea>
                            <?php else: ?>
                                <input class="form-control" type="text" name="<?= $f_name ?>" value="<?= $value ?>">
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <br><br>
        <?php endforeach; ?>
        <input type="submit" class="save-btn btn btn-primary" value="<?= l('save') ?>">
    </fieldset>
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