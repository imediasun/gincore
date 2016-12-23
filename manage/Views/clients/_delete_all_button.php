<div class="pull-right">
    <span class="cursor-pointer glyphicon glyphicon-list"
          onclick="alert_box(this, false, 'changes:delete-client')"
          title="<?= l('История изменений') ?>">
    </span>
    <a class="btn btn-danger"
       onclick="return goto_delete_all(this, '<?= l('Вы действительно хотите удалить всех отфильтрованных клиентов?') ?>');">
        <?= l('Удалить все') ?>&nbsp;<i class="fa fa-times" aria-hidden="true"></i>
    </a>
</div>
