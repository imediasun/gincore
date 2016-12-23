<div class="pull-right">
    <span class="cursor-pointer glyphicon glyphicon-list"
          onclick="alert_box(this, false, 'changes:delete-product')"
          title="<?= l('История изменений') ?>">
    </span>
    <a class="btn btn-danger"
       onclick="return  goto_delete_all(this, '<?= l('Вы действительно хотите удалить все отфильтрованные товары/услуги?') ?>');">
        <?= l('Удалить все') ?>&nbsp;<i class="fa fa-times" aria-hidden="true"></i>
    </a>
</div>