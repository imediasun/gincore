<div class="btn-lock">
    <input type="hidden" name="lock-button" value="<?= (int)$locked ?>"/>
    <i class="fa fa-lock" aria-hidden="true" style="<?= $locked ? '' : 'display:none' ?>"
       onclick="return lock(this, 0);" title="<?= l('Отменить сохраненные настройки фильтра') ?>"></i>
    <i class="fa fa-unlock" aria-hidden="true" style="<?= $locked ? 'display:none' : '' ?>"
       onclick="return lock(this, 1);" title="<?= l('Сохранить настройки фильтра') ?>"></i>
</div>
<style>
    .btn-lock {
        -moz-user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
        cursor: pointer;
        display: inline-block;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.42857;
        margin-bottom: 0;
        padding: 6px 10px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
</style>
<script>
    function lock(_this, lock) {
        var $parent = $(_this).parents('.btn-lock').first();
        $parent.find('input').first().val(parseInt(lock));
        $parent.find('.fa').toggle();
    }
</script>