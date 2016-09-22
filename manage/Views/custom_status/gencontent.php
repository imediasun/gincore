<h2><?= $title ?></h2>
<form action="<?= $this->all_configs['prefix'] ?>/custom_status/update" method="POST">
    <input type="hidden" name="custom-statuses" value=""/>
    <table class="table">
        <thead>
        <tr>
            <th>id</th>
            <th><?= l('Наименование статуса') ?></th>
            <th style="text-align: center"><?= l('Закрывать заявку') ?></th>
            <th style="text-align: center"><?= l('Удалить') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($values as $id => $value): ?>
            <tr>
                <td>
                    <?= $id ?>
                </td>
                <td>
                    <input class="form-control" type="text" name="name[<?= $id ?>]"
                           value="<?= h($value['name']) ?>"/>
                </td>
                <td>
                    <center>
                        <input class="checkbox" type="checkbox"
                               name="close[<?= $id ?>]" <?= $value['active'] == 0 ? 'checked' : '' ?> />
                    </center>
                </td>
                <td>
                    <center>
                        <input class="checkbox" type="checkbox" name="delete[<?= $id ?>]"/>
                    </center>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td>
            </td>
            <td>
                <input class="form-control" type="text" name="name[new]" value=""
                       placeholder="<?= l('Введите название нового статуса') ?>"/>
            </td>
            <td>
                <center>
                    <input class="checkbox" type="checkbox" name="close[new]"/>
                </center>
            </td>
            <td>
            </td>
        </tr>
        </tfoot>
    </table>
    <div class="form-group">
        <input type="submit" value="<?= l('save') ?>" class="btn btn-primary">
    </div>
</form>
