<?php if (in_array($act, array('location', 'label'))): ?>
    <div class="printer_preview unprint">
        <div class="row" style="text-align: center">
            <button class="btn btn-primary" onclick="javascript:window.print()">
                <i class="cursor-pointer fa fa-print"></i><?= l('Печать') ?>
            </button>
        </div>
        <p><i class="fa fa-info-circle"></i><?= l('Формат этикеток настроен под печать на термопринтере HPRT LPQ58') ?></p>
        <img src="<?= $this->all_configs['prefix'] ?>img/hprt_lpq58.jpg">
    </div>
<?php endif; ?>
