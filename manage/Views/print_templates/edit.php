<h3>
    <?= $config['name'] ?>
    <a href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add"
       class="btn btn-primary"><?= l('Добавить шаблон') ?></a>
</h3>
<form action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/save" method="post">
    <fieldset class="main">
        <?php foreach ($translates as $id => $langs): ?>
            <div style="position: relative;">
                <?php $field = 'text' ?>
                <?php $field_name = l('Значение'); ?>
                <?php foreach ($langs as $lng => $translate): ?>
                    <?php if ($lng == $manage_lang): ?>
                        <a class="template-remove"
                           onclick="return confirm('<?= l('Вы уверены, что хотите удалить этот шаблон?') ?>')"
                           href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/delete/<?= $translate['sms_templates_id'] ?>"><i
                                class="fa fa-remove"></i></a>
                        <legend><?= $translate['var'] ?> (<?= l($translate['for_view']) ?>)</legend>
                        <?php if ($translate['for_view'] == 'repair_order'): ?>
                            <?= l('В шаблоне возможно использование следующих переменных:') ?>
                            <table class="table-compact">
                                <?php $arr = array(
                                    'id' => l('ID заказа на ремонт'),
                                    'sum' => l('Сумма за ремонт'),
                                    'prepay' => l('Предоплата'),
                                    'discount' => l('Скидка на заказ'),
                                    'sum_with_discount' => l('Сумма за ремонт с учетом скидки'),
                                    'qty_all' => l('Количество наименований'),
                                    'qty_products' => l('Количество запчастей'),
                                    'qty_services' => l('Количество услуг'),
                                    'sum_in_words' => l('Сумма за ремонт прописью'),
                                    'sum_paid' => l('Оплачено'),
                                    'sum_paid_in_words' => l('Оплачено прописью'),
                                    'sum_for_paid' => l('К оплате'),
                                    'sum_for_paid_in_words' => l('К оплате прописью'),
                                    'address' => l('Адрес'),
                                    'currency' => l('Валюта'),
                                    'phone' => l('Телефон клиента'),
                                    'fio' => l('ФИО или название клиента'),
                                    'order_data' => l('Дата создания заказа'),
                                    'now' => l('Текущая дата'),
                                    'warranty' => l('Гарантия'),
                                    'product' => l('Устройство'),
                                    'products_and_services' => l('Товары и услуги'),
                                    'color' => l('Цвет'),
                                    'serial' => l('Серийный номер'),
                                    'company' => l('Название компании'),
                                    'order' => l('Номер заказа'),
                                    'defect' => l('Неисправность'),
                                    'engineer' => l('Инженер'),
                                    'accepter' => l('Приемщик'),
                                    'comment' => l('Внешний вид'),
                                    'warehouse' => l('Название склада'),
                                    'warehouse_accept' => l('Название склада приема'),
                                    'wh_address' => l('Адрес склада'),
                                    'wh_phone' => l('Телефон склада'),
                                    'products' => l('Установленные запчасти'),
                                    'products_cost' => l('Установленные запчасти'),
                                    'services' => l('Услуги'),
                                    'services_cost' => l('Стоимость услуг'),
                                    'repair' => l('Вид ремонта'),
                                    'complect' => l('Комплектация'),
                                    'domain' => l('Домен сайта'),
                                    'barcode' => l('Штрихкод'),
                                    'sum_by_products_and_services' => l('Сумма за запчасти и услуги'),
                                    'sum_by_products' => l('Сумма за запчасти'),
                                    'sum_by_services' => l('Сумма за услуги'),
                                    'client_reg_data_1' => l('Клиент (Регистрационные данные 1)'),
                                    'client_reg_data_2' => l('Клиент (Регистрационные данные 2)'),
                                    'client_legal_address' => l('Клиент (Юридический адрес)'),
                                    'client_residential_address' => l('Клиент (Фактический адрес)'),
                                    'client_note' => l('Клиент (примечания)'),
                                ); ?>
                                <?php foreach ($arr as $var => $title): ?>
                                    <tr>
                                        <td> {{<?= h($var) ?>}}</td>
                                        <td> <?= h($title) ?> </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                        <?php if ($translate['for_view'] == 'sale_order'): ?>
                            <?= l('В шаблоне возможно использование следующих переменных:') ?>
                            <table class="table-compact">
                                <?php $arr = array(
                                    'id' => l('ID заказа на ремонт'),
                                    'sum' => l('Сумма за ремонт'),
                                    'qty_all' => l('Количество наименований'),
                                    'products_and_services' => l('Товары и услуги'),
                                    'discount' => l('Скидка на заказ'),
                                    'sum_with_discount' => l('Сумма заказа с учетом скидки'),
                                    'sum_paid' => l('Оплачено'),
                                    'sum_paid_in_words' => l('Оплачено прописью'),
                                    'product' => l('Устройство'),
                                    'serial' => l('Серийный номер'),
                                    'company' => l('Название компании'),
                                    'address' => l('Адрес'),
                                    'wh_phone' => l('Телефон склада'),
                                    'now' => l('Текущая дата'),
                                    'currency' => l('Валюта'),
                                    'sum_in_words' => l('Сумма за ремонт прописью'),
                                    'order' => l('Номер заказа'),
                                    'order_data' => l('Дата создания заказа'),
                                ); ?>
                                <?php foreach ($arr as $var => $title): ?>
                                    <tr>
                                        <td> {{<?= h($var) ?>}}</td>
                                        <td> <?= h($title) ?> </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                        <?php $value = h($translate[$field]); ?>
                        <span class="form-group" style="display:block; margin-top:20px">
                                <?php $f_name = 'data[' . $id . '][' . $lng . '][description]'; ?>
                            <label>
                               <?= l('Название') ?>
                                <input class="form-control" type="text" name="<?= $f_name ?>" value="<?= h($translate['description']) ?>">
                            </label>
                        </span>
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