<div class="span3"></div>
<div class="span9">
    <p class="text-danger"><?= l('Заказ') ?> №<?= $this->all_configs['arrequest'][2] ?> <?= l('не найден') ?> </p>
</div>

<?= $this->all_configs['chains']->append_js(); ?>
<?= $this->all_configs['suppliers_orders']->append_js(); ?>
