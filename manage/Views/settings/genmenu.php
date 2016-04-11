<h4>
    <?= l('sets_list') ?> <a style="text-decoration:none" href="<?= $this->all_configs['prefix'] ?>settings/add">+</a>
</h4>

<ul>
    <?php foreach ($sqls as $pps): ?>
        <li>
            <a href="<?= $this->all_configs['prefix'] ?>settings/<?= $pps['id'] ?>" <?= (isset($this->all_configs['arrequest'][1]) && $pps['id'] == $this->all_configs['arrequest'][1] ? ' style="font-weight: bold"' : '') ?> >
                <?= $pps['title'] ?>
            </a>
        </li>
    <?php endforeach; ?>

</ul>
