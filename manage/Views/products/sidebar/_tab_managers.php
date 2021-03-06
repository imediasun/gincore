<!--     Managers-->
<div class="hpanel panel-collapse">
    <div class="panel-heading hbuilt showhide">
        <div class="panel-tools">
            <i class="fa fa-chevron-up"></i>
        </div>
        <?= l('Менеджеры') ?>
    </div>
    <div class="panel-body" style="display: none;">
        <div class="form-group">
            <label> <?= l('Автор') ?>: </label>
            <a href="/manage/users" target="_blank"><?= $author ?></a>
        </div>
        <div class="form-group"><label><?= l('manager') ?>: </label>

            <select class="good-multiselect form-control"
                <?= $this->all_configs['configs']['manage-product-managers'] == true ? ' multiple="multiple"' : '' ?> name="users[]">
                <option value="0"><?= l('Не выбран') ?></option>
                <?php if (!empty($managers)) : ?>
                    <?php  foreach ($managers as $manager) : ?>
                        <option value="<?= $manager['id'] ?>"<?= $manager['id'] == $manager['manager'] ? ' selected' : '' ?> ><?= $manager['login'] ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <?php if (!empty($histories)): ?>
            <table class="table table-striped">
                <thead>
                <tr>
                    <td><?= l('Автор') ?></td>
                    <td><?= l('Значения до изменения') ?></td>
                    <td><?= l('Дата') ?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($histories as $history): ?>
                    <tr>
                        <td>
                            <a href="<?= $this->all_configs['prefix'] ?>users"><?= $history['fio'] ? $history['fio'] : $history['login'] ?></a>
                        </td>
                        <td><?= $history['change'] ?></td>
                        <td><span title="<?= do_nice_date($history['date_add'],
                                false) ?>"><?= do_nice_date($history['date_add']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-error"><?= l('Нет ни одного изменения') ?></p>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        var $multiselect = $('.good-multiselect');
        setTimeout(function () {
            $multiselect.each(function () {
                var $this = $(this),
                    opts = multiselect_options;
                if (typeof $this.attr('data-numberDisplayed') !== 'undefined') {
                    opts.numberDisplayed = $this.attr('data-numberDisplayed');
                }
                $this.multiselect(opts);
            });
        }, 0);
    })
</script>