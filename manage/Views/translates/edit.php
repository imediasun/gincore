<small>
    <a href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/copy">
        <?= l('скопировать языки') ?>
    </a>
</small>
<h3>
    <?= $config['name'] ?>
    <a href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add">+</a>
</h3>
<form action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/save" method="post">
    <fieldset class="main">
        <?php foreach ($translates as $id => $langs): ?>
            <legend>(id <?= $id ?>)</legend>
            <div>
                <?php if (isset($this->config[$this->all_configs['arrequest'][1]]['var'])): ?>
                    <?php if (is_array($this->config[$this->all_configs['arrequest'][1]]['var'])): ?>
                        <?php $vars_vals = array(); ?>
                        <?php foreach ($this->config[$this->all_configs['arrequest'][1]]['var'] as $var): ?>
                            <?php $vars_vals[] = $table[$id][$var]; ?>
                        <?php endforeach; ?>
                        <span class="muted"><?= implode(', ', $vars_vals) ?></span>
                    <?php else: ?>
                        <span
                            class="muted"><?= $table[$id][$this->config[$this->all_configs['arrequest'][1]]['var']] ?></span>
                    <?php endif; ?>
                <?php endif; ?>
                <?php foreach ($config['fields'] as $field => $field_name): ?>
                    <legend><?= $field_name ?></legend>
                    <p class="text-muted"><?= $field ?></p>

                    <?php foreach ($langs as $lng => $translate): ?>
                        <?php $value = htmlspecialchars($translate[$field]); ?>
                        <span class="form-group" style="display:block">
                            <label><?= $languages[$lng]['name'] ?>, <?= $lng ?></label>
                            <?php $f_name = 'data[' . $id . '][' . $lng . '][' . $field . ']'; ?>
                            <?php if ($textarea || strlen($value) > 50): ?>
                                <textarea class="form-control <?= $textarea ? 'tinymce' : '' ?>" style="height: 150px"
                                          name="<?= $f_name ?>"><?= $value ?></textarea>
                            <?php else: ?>
                                <input class="form-control" type="text" name="<?= $f_name ?>" value="<?= $value ?>">
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
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
                fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt"
            });
        });
    </script>
<?php endif; ?>