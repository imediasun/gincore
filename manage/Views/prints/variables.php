<div class="row">
    <?php if (!empty($variables)): ?>
        <?php $count = ceil(count($variables) / 2); ?>
        <?php $i = 0; ?>

        <div class="span6">
        <?php foreach ($variables as $name => $variable): ?>
            <?php if ($i == $count): ?>
                <?php $i = 0; ?>
                </div>
                <div class="span6">
            <?php endif; ?>
            <p><b>{{<?= $name ?>}}</b> - <?= $variable['name'] ?></p>
            <?php $i++ ?>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
