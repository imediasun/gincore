<?= l('Заявки по данному') ?> <?= $by ?>:<br>
<div style="max-height: 500px; overflow: auto">

    <table class="table table-bordered table-condensed table-hover" style="max-width: 1100px">
        <thead>
        <tr>
            <td>
                <div class="radio">
                    <label>
                        <input' <?= (!$active_request ? 'checked' : '') ?> type="radio" name="crm_request" value="0">
                        <?= l('без заявки') ?>
                    </label>
                </div>
            </td>
            <td><?= l('Клиент') ?></td>
            <td><?= l('Устройство') ?></td>
            <td><?= l('Оператор') ?></td>
            <td><?= l('Комментарий') ?></td>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($requests as $req): ?>
            <tr>
                <td>
                    <div class="radio">
                        <label>
                            <input type="radio" <?= ($active_request == $req['id'] ? 'checked' : '') ?>
                                   name="crm_request"
                                   data-client_fio="<?= $req['client']['fio'] ?>"
                                   data-client_id="<?= $req['client_id'] ?>"
                                   data-product_id="<?= $req['product_id'] ?>"
                                   data-referer_id="<?= $req['referer_id'] ?>"
                                   data-code="<?= $req['code'] ?>"
                                   data-product_name="<?= $req['product'] ?>"
                                   value="<?= $req['id'] ?>">
                            №<?= $req['id'] ?> <?= l('от') ?> <?= do_nice_date($req['date'], true, true, 0, true) ?>
                        </label>
                    </div>
                </td>
                <td>
                <span class="<?= $req['client']['data'] == $client['data'] ? 'orange' : '' ?>">
                    <?= $req['client']['data'] ?>
                </span>
                </td>
                <td>
                    <a href="<?= $this->all_configs['siteprefix'] . gen_full_link(getMapIdByProductId($req['product_id'])) ?>"
                       target="_blank" class="<?= $req['product'] == $product ? 'orange' : '' ?>">
                        <?= $req['product'] ?>
                    </a>
                </td>
                <td>
                    <i><?= getUsernameById($req['operator_id']) ?></i><br>
                </td>
                <td>
                    <i><?= $req['comment'] ?></i><br>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
    <?php if ($active_request): ?>
        <script>
            check_active_request();
        </script>
    <?php endif; ?>
</div>
