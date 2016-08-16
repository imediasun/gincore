<h3>
    <?= $config['name'] ?>
    <a href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add" class="btn btn-primary"><?= l('Добавить шаблон') ?></a>
</h3>
<form action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/save" method="post">
    <fieldset class="main">
        <?php foreach ($translates as $id => $langs): ?>
            <div style="position: relative;">
                <?php $field = 'body' ?>
                <?php $field_name = l('Значение'); ?>
                <?php foreach ($langs as $lng => $translate): ?>
                    <?php if ($lng == $manage_lang): ?>
                        <a class="template-remove" onclick="return confirm('<?= l('Вы уверены, что хотите удалить этот шаблон?') ?>')" href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/delete/<?= $translate['sms_templates_id'] ?>"><i class="fa fa-remove"></i></a>
                        <legend><?= $translate['var'] ?> (<?= l($types[$translate['type']]) ?>)</legend>
                        <?php if($types[$translate['type']] == 'orders'): ?>
                            <?= l('В шаблоне возможно использование следующих переменных:') ?>
                            <table class="table-compact">
                                <tr> <td> {{order_id}} </td> <td> <?= l('Номер заказа') ?> </td> </tr>
                                <tr> <td> {{pay}} </td> <td> <?= l('Сумма к оплате') ?> </td> </tr>
                                <tr> <td> {{order_sum}} </td> <td> <?= l('Сумма заказа') ?> </td> </tr>
                                <tr> <td> {{client}} </td> <td> <?= l('ФИО клиента') ?> </td> </tr>
                                <tr> <td> {{warehouse}} </td> <td> <?= l('Склад') ?> </td> </tr>
                                <tr> <td> {{location}} </td> <td> <?= l('Локация') ?> </td> </tr>
                                <tr> <td> {{warehouse_address}} </td> <td> <?= l('Адрес склада') ?> </td> </tr>
                                <tr> <td> {{warehouse_phone}} </td> <td> <?= l('Телефон склада') ?> </td> </tr>
                            </table>
                        <?php endif; ?>
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