<div class="gcw">
    <div class="gcw_title gcw_show_modal gcw_status_modal" data-id="gcw_status_modal">
        <i class="fa fa-question-circle-o" style="color:black; font-size: 1.2em"></i>
        <?= l('Cтатус ремонта') ?>
    </div>
</div>
<div class="gcw_modal_box" id="gcw_status_modal">
    <div class="gcw_modal_blackout"></div>
    <div class="gcw_modal">
        <div class="gcw_modal_title">
            <?= l('Узнать статус ремонта') ?>
            <span class="gcw_modal_close"></span>
        </div>
        <div class="gcw_modal_body">
            <form class="gcw_form" action="<?= $widgets->get_requests_url('status') ?>" method="post">
                <input type="hidden" name="widget" value="status">
                <input type="hidden" name="action" value="status_by_phone">
                <div class="gcw_form_group">
                    <label><?= l('Номер мобильного телефона') ?></label>
                    <input class="gcw_form_control" type="text" name="phone">
                </div>
                <input type="submit" value="<?= l('Отправить') ?>" class="gcw_btn">
                <span class="gcw_form_error"></span>
            </form>
            <div id="gcw_form_html"></div>
        </div>
        <div class="gcw_sign">Supported by <a href="http://gincore.net">Gincore</a></div>
    </div>
</div>
