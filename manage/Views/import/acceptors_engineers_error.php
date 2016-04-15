<?php if ($acceptors): ?>
    <label><?= l('Добавьте приемщиков') ?></label>:
    <ol>
        <?php foreach ($acceptors as $acceptor): ?>
            <li><?= $acceptor ?></li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
<?php if ($engineers): ?>
    <label><?= l('Добавьте инженеров') ?></label>:
    <ol>
        <?php foreach ($engineers as $engineer): ?>
            <li><?= $engineer ?></li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
<?php if ($managers): ?>
    <label><?= l('Добавьте менеджеров') ?></label>:
    <ol>
        <?php foreach ($managers as $manager): ?>
            <li><?= $manager ?></li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
