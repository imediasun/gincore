<h3><?= l('Уже выполняется переадресация') ?></h3>

<?php if ($links): ?>
    <table class='table table-bordered table-hover'>
        <tr>
            <td><b><?= l('Откуда') ?></b></td>
            <td><b><?= l('Куда') ?></b></td>
            <td></td>
        </tr>

        <?php foreach ($links as $link): ?>
            <tr>
                <td><?= $link['link_from'] ?></td>
                <td><?= $link['link_to'] ?></td>
                <td>
                    <a onClick="return confirm('<?= l('Удалить?') ?>');"
                       href="<?= $this->all_configs['prefix'] ?>seo/glue/del/<?= $link['id'] ?>"><?= l('Удалить') ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p style="padding:20px;"><?= l('Текущих переадресаций нет.') ?></p>
<?php endif; ?>
<a href='<?= $this->all_configs['prefix'] ?>seo/glue/del/all'
   onclick='return confirm("<?= l('Удалить все записи') ?>?")'><?= l('Удалить все') ?></a>
<h3><?= l('Добавление ссылок для переадресации') ?></h3>
<form action='<?= $this->all_configs[' prefix'] ?>seo/glue/add' method='POST'>
    <div class='form-group-row'>
        <div class='col-sm-6'>
            <textarea class='form-control' rows='10' name='linkfrom'></textarea>
        </div>
        <div class='col-sm-6'>
            <textarea class='form-control' rows='10' name='linkto'></textarea>
        </div>
    </div>
    <p><input type='submit' value='<?= l(' Добавить') ?>' class='btn btn-default'></p>
</form>
