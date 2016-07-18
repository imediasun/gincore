<ul class="list-unstyled inline clearfix m-b-md">
    <li class="">
        <a class="click_tab btn btn-info" href="<?= $link ?>#show_orders-orders" title="" onclick="click_tab(this, event)" data-open_tab="show_orders_orders">
            <i class="fa fa-wrench"></i> <?= l('РЕМОНТЫ') ?>
        </a>
    </li>
    <li class="">
        <a class="click_tab btn btn-primary" href="<?= $link ?>#show_orders-sold" title="" onclick="click_tab(this, event)" data-open_tab="show_orders_sold">
            <i class="fa fa-money"></i> <?= l('ПРОДАЖИ') ?>
        </a>
    </li>
    <li class="">
        <a class="click_tab btn btn-danger" href="<?= $link ?>#show_orders-writeoff" title=""
                    onclick="click_tab(this, event)" data-open_tab="show_orders_writeoff">
            <i class="fa fa-times"></i> <?= l('СПИСАНИЯ') ?>
        </a>
    </li>
    <li class="">
        <button data-toggle=".js-filters" type="button" class="toggle-hidden btn btn-default">
            <i class="fa fa-filter"></i> <?= l('Фильтровать') ?>
            <i class="fa fa-caret-down"></i>
        </button>
    </li>
    <li style="max-width:280px">
        <form method="POST" class="form-inline" onsubmit="return false;">
            <div class="input-group">
                <span class="input-group-btn">
                    <a href="<?= $prefix ?>orders" class="btn btn-default drop-quick-orders-serach"
                       type="button">
                        <i class="glyphicon glyphicon-remove-circle"></i>
                    </a>
                </span>
                <input type="text" value="<?= (isset($_GET['qsq']) ? htmlspecialchars($_GET['qsq']) : '') ?>"
                       name="search" class="form-control" id="orders_quick_search_query">
                <div class="input-group-btn">
                    <button type="submit" onclick="orders_quick_search(this, 'simple')" class="btn btn-primary"
                            aria-haspopup="true" aria-expanded="false">
                        <?= l('Искать') ?>
                    </button>
                    <div class="btn-group orders_quick_search_dropdown dropdown">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="#" onclick="orders_quick_search(this, 'co_id')"><?= l('По номеру заказа') ?></a>
                            </li>
                            <li>
                                <a href="#" onclick="orders_quick_search(this, 'cl')"><?= l('По клиенту') ?></a></li>
                            <li>
                                <a href="#" onclick="orders_quick_search(this, 'device')"><?= l('По устройству') ?></a>
                            </li>
                            <li>
                                <a href="#" onclick="orders_quick_search(this, 'serial')"><?= l('По сер. номеру') ?></a>
                            </li>
                            <li>
                                <a href="#" onclick="orders_quick_search(this, 'manager')"><?= l('По менеджеру') ?></a>
                            </li>
                            <li>
                                <a href="#" onclick="orders_quick_search(this, 'accepter')"><?= l('По приемщику') ?></a>
                            </li>
                            <li>
                                <a href="#" onclick="orders_quick_search(this, 'engineer')"><?= l('По инженеру') ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </li>
    <?php if ($hasPrivilege): ?>
        <li class="pull-right"><a href="<?= $prefix ?>orders/#create_order" class="btn btn-success hash_link"><?= l('Создать заказ') ?></a></li>
        <li class="pull-right"><a href="<?= $prefix ?>orders/ajax?act=export" target='_blank' class="btn btn-default"><?= l('Выгрузить') ?></a></li>
    <?php endif; ?>
</ul>
