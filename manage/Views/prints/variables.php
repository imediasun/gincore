<div class="row">
    <?php if (!empty($variables)): ?>
        <?php $count = ceil(count($variables) / 3); ?>
        <?php $i = 0; ?>

        <div class="span5">
        <?php foreach ($variables as $name => $variable): ?>
            <?php if ($i == $count): ?>
                <?php $i = 0; ?>
                </div>
                <div class="span5">
            <?php endif; ?>
            <p><b>{{<?= $name ?>}}</b> - <?= $variable['name'] ?></p>
            <?php $i++ ?>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
