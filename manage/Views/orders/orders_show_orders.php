<?php if ($hasPrivilege): ?>
    <div class="span12">
        <?= $clientsOrdersNavigation ?>
        <div class="pill-content">
            <div id="show_orders-orders" class="pill-pane active">
            </div>
            <div id="show_orders-sold" class="pill-pane">
            </div>
            <div id="show_orders-writeoff" class="pill-pane">
            </div>
        </div>
    </div>
<?php endif; ?>
