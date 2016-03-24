<tr>
    <td><?= $call['id'] ?></td>
    <td><?= $call['operator_fio'] ?></td>
    <td><?= $call_type ?></td>
    <td>
        <span class="pull-left cursor-pointer icon-list"
              onclick="alert_box(this, false, 1, {service:'crm/requests',action:'changes_history',type:'crm-call-change-referer_id'}, null, 'services/ajax.php')"
              data-o_id="<?= $call['id'] ?>" title="<?= l('История изменений') ?>"></span>
        <?= $referrers_list ?>
    </td>
    <td>
        <span class="pull-left cursor-pointer icon-list"
              onclick="alert_box(this, false, 1, {service:'crm/requests',action:'changes_history',type:'crm-call-change-code'}, null, 'services/ajax.php')"
              data-o_id="<?= $call['id'] ?>" title="<?= l('История изменений') ?>"></span>
        <input type="text" class="form-control call_code_mask" name="code[<?= $call['id'] ?>]"
               value="<?= htmlspecialchars($call['code']) ?>"></td>
    <td><?= do_nice_date($call['date'], true) ?></td>
    <td>
        <a href="<?= $all_configs['prefix'] ?> clients/create/<?= $call['client_id'] ?>?new_call=<?= $call['id'] . ($call['type'] === '0' ? '&get_call' : '') ?>" class="btn btn-default">
            <?= l('Создать') ?>
        </a>
    </td>
</tr>
