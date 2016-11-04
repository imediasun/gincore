<tr>
    <td>
        <a href="<?= $all_configs['prefix'] ?>clients/create/<?= $call['client_id'] ?>?new_call=<?= $call['id'] . ($call['type'] === '0' ? '&get_call' : '') ?>">
            <?= $call['id'] ?>
        </a>
    </td>
    <td>
        <?= $call['phone'] ?>
    </td>
    <td>
        <?php if ($call['open_requests']): ?>
            <a href="<?= $all_configs['prefix'] ?>clients/create/<?= $call['client_id'] ?> #requests">
                <span title="<?= l('Кол-во открытых заявок на звонок') ?> №<?= $call['id'] ?>"> <?= $call['open_requests'] ?></span>
            </a>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($call['tag_id'] != 0): ?>
            <span class="tag" style="background-color: <?= $tags[$call['tag_id']]['color'] ?>">
                <?= htmlspecialchars($tags[$call['tag_id']]['title']) ?>
            </span>
        <?php endif; ?>
    </td>
    <td>
        <a href="<?= $all_configs['prefix'] ?>clients/create/<?= $call['client_id'] ?>#calls">
            <?= htmlspecialchars($call['client_fio'] ?: 'id ' . $call['client_id']) ?>
        </a></td>
    <td><?= $call['operator_fio'] ?></td>
    <td><?= $call_types ?></td>
    <td>
        <?= (isset($referrers[$call['referer_id']]) ? $referrers[$call['referer_id']] : '') ?>
    </td>
    <td>
        <div style="position:relative">
            <?= htmlspecialchars($call['code']) ?>
            <small style="top:100%;margin-top:-3px;left:0;line-height:8px;
                                          position:absolute" class="muted">
                <?= $call['visitor_id'] ?>
            </small>
        </div>
    </td>
    <td><?= do_nice_date($call['date'], true) ?></td>
</tr>
