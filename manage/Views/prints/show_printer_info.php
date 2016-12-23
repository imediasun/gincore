<?php if (in_array($act, array(
    'location',
    'label',
    'label_filtered',
    'label_location',
    'price_list',
    'price_list_filtered',
    'price_list_location'
))): ?>
    <div class="printer_preview unprint">
        <p>
            <i class="fa fa-info-circle"></i><?= l('Формат этикеток настроен под печать на термопринтере HPRT LPQ58') ?>
        </p>
        <img src="<?= $this->all_configs['prefix'] ?>img/hprt_lpq58.jpg">
    </div>
<?php endif; ?>
