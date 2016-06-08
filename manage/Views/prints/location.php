<?php if (!empty($location)): ?>
    <div class="label-box">
        <div class="label-box-code">
            <img src="<?= $this->all_configs['prefix'] . 'print.php?bartype=sn&barcode=L-' . $location['id']; ?>"
                 alt="S/N" title="S/N"/>
        </div>
        <div style="font-size: 1.4em;" class="label-box-title"><?= h($location['location']) ?></div>
    </div>
<?php endif; ?>
