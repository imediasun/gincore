<div class="row-fluid" style="min-width: 350px">
    <div class="col-sm-12">
        <p> <?= l('Сайты поставщиков') ?></p>
        <?php if (!empty($links)): ?>
                <?php foreach ($links as $id => $link): ?>
                        <p> <?= h($link) ?><a href="<?= $link ?>" target="_blank"> <i class="fa fa-external-link" aria-hidden="true"></i> </a> </p>
                <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
