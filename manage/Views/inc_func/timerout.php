<a href="#" data-o_id="<?= $order_id ?>" id="btn-timer-<?= $order_id ?>" class="label-menu-corner"
   onclick="alert_box(this, false, 'alarm-clock', undefined, undefined, 'messages.php')">
    <i href="javascript:void(0);" class="fa fa-bell cursor-pointer btn-timer"></i>
    <span id="alarm-timer-<?= $order_id ?>" data-o_id="<?= $order_id ?>"
          class="<?= $show_timer == false ? 'hidden' : '' ?> alarm-timer"></span>
    <?php if ($order_id == 0): ?>
        <span data-o_id="1"
              onclick="alert_box(this, false, 'get-messages', undefined, undefined, 'messages.php', event)"
              class="count-alarm-timer cursor-pointer label label-success"></span>
    <?php endif; ?>
</a>
