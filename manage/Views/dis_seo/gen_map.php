<h2><?= l('Метаданные страниц сайта') ?></h2>

<form method="post" action="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/map/save">
    <table class="table table-condensed table-hover table-bordered">
        <thead>
        <tr>
            <th>id</th>
            <th><?= l('Страница') ?></th>
            <th>url</th>
            <th>title</th>
            <th>keywords</th>
            <th>description</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($pages)): ?>
            <?php foreach ($pages as $id => $page): ?>
                <tr>
                    <td><?= $page['id'] ?></td>
                    <td style="width:150px">
                        <a href="<?= $this->all_configs['prefix'] ?>map/<?= $page['id'] ?>" target="_blank">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </a> <?= $page['name'] ?><br>
                    </td>
                    <td>
                        <input class="form-control" type="text" name="page[<?= $page['id'] ?>][url]"
                               value="<?= h($page['url']) ?>">
                    </td>
                    <td>
                        <textarea class="form-control" rows="3"
                                  name="page[<?= $page['id'] ?>][fullname]"><?= h($page['fullname']) ?></textarea>
                    </td>
                    <td>
                        <textarea class="form-control" rows="3"
                                  name="page[<?= $page['id'] ?>][metakeywords]"><?= h($page['metakeywords']) ?></textarea>
                    </td>
                    <td>
                        <textarea class="form - control" rows="3"
                                  name="page[<?= $page['id'] ?>][metadescription]"><?= h($page['metadescription']) ?></textarea>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <input type="submit" class="btn btn-primary save_fixed" value="<?= l('Сохранить') ?>">
</form>

