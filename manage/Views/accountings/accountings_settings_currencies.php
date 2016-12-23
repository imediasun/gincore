<?php if ($this->all_configs['oRole']->hasPrivilege('site-administration')): ?>
    <?php if (isset($_SESSION['save_currencies_error'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_SESSION['save_currencies_error']) ?></div>
        <?php unset($_SESSION['save_currencies_error']); ?>
    <?php endif; ?>
    <form method="post">
        <table class="table table-striped">
            <thead>
            <tr>
                <td><?= l('Наименование') ?></td>
                <td><?= l('Курс') ?></td>
                <td></td>
            </tr>
            </thead>
            <tbody id="edit-courses-from"><?= $controller->gen_currency_table() ?></tbody>
        </table>
    </form>
    <form class="form-inline">
        <label><?= l('Добавить валюту') ?> </label> 
        <select class="form-control" onchange="add_currency(this)" id="add_new_course">
            <?= $controller->gen_new_currency_options(false) ?>
        </select>
    </form>
<?php endif; ?>
