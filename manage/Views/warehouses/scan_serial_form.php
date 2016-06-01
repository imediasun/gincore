<div class="input-append scan-serial-block">
    <div class="scan-serial-error"></div>
    <input id="scan-serial-<?= $id ?>" onkeyup="is_enter($('.btn-scan_serial'), event, '<?= $id ?> ', 'scan_serial')"
           class="scan-serial focusin" type="text" placeholder="<?= l('Серийный номер') ?>">
    <button class="btn-scan_serial btn" onclick="scan_serial(this, '<?= $id ?>)" type="button">
        <?= l('Добавить') ?>
    </button>
</div>
