<style>
    input[type=radio].gcw_form_control {
        display: inline-block;
        width: 5%;
    }
</style>
<div class="gcw">
    <div class="gcw_title gcw_show_modal gcw_feedback_modal" data-id="gcw_feedback_modal" style="background-color: <?= $bg_color ?>; color: <?= $fg_color ?>">
        <i class="fa fa-comment" style="color:white; font-size: 1.2em; margin-right: 5px"></i>
        <?= l('Оставить отзыв') ?>
    </div>
</div>
<div class="gcw_modal_box" id="gcw_feedback_modal">
    <div class="gcw_modal_blackout"></div>
    <div class="gcw_modal">
        <div class="gcw_modal_title">
            <?= l('Оставить отзывы') ?>
            <span class="gcw_modal_close"></span>
        </div>
        <div class="gcw_modal_body js-feedback-body" style="background-color: #f1f1f1">
            <form class="gcw_form js-feedback-form" data-action="<?= $widgets->get_requests_url('feedback') ?>" method="post">
                <input type="hidden" name="widget" value="feedback">
                <input type="hidden" name="action" value="add">
                <div class="gcw_form_group">
                    <label><?= l('Оцените работу инженера') ?><span style="color: red">*</span></label>
                    <input class="gcw_form_control" type="radio" name="engineer" required value="0"> <?= l('Ужасно'); ?>
                    <input class="gcw_form_control" type="radio" name="engineer" required value="2.5"> <?= l('Плохо'); ?>
                    <input class="gcw_form_control" type="radio" name="engineer" required value="5"> <?= l('Нормально'); ?>
                    <input class="gcw_form_control" type="radio" name="engineer" required value="7.5"> <?= l('Хорошо'); ?>
                    <input class="gcw_form_control" type="radio" name="engineer" required value="10"> <?= l('Отлично'); ?>
                </div>
                <br>
                <div class="gcw_form_group">
                    <label><?= l('Оцените работу сотрудника, который выдавал Вам устройство после ремонта') ?><span style="color: red">*</span></label>
                    <input class="gcw_form_control" type="radio" name="acceptor" required value="0"> <?= l('Ужасно'); ?>
                    <input class="gcw_form_control" type="radio" name="acceptor" required value="2.5"> <?= l('Плохо'); ?>
                    <input class="gcw_form_control" type="radio" name="acceptor" required value="5"> <?= l('Нормально'); ?>
                    <input class="gcw_form_control" type="radio" name="acceptor" required value="7.5"> <?= l('Хорошо'); ?>
                    <input class="gcw_form_control" type="radio" name="acceptor" required value="10"> <?= l('Отлично'); ?>
                </div>
                <br>
                <div class="gcw_form_group">
                    <label><?= l('Оцените работу менеджера, который общался с Вами по телефону') ?><span style="color: red">*</span></label>
                    <input class="gcw_form_control" type="radio" name="manager" required value="0"> <?= l('Ужасно'); ?>
                    <input class="gcw_form_control" type="radio" name="manager" required value="2.5"> <?= l('Плохо'); ?>
                    <input class="gcw_form_control" type="radio" name="manager" required value="5"> <?= l('Нормально'); ?>
                    <input class="gcw_form_control" type="radio" name="manager" required value="7.5"> <?= l('Хорошо'); ?>
                    <input class="gcw_form_control" type="radio" name="manager" required value="10"> <?= l('Отлично'); ?>
                </div>
                <br>
                <div class="gcw_form_group">
                    <label><?= l('Комментарий к отзыву') ?></label>
                    <textarea class="gcw_form_control" name="comment"> </textarea>
                    <span><small><?= l('Заработная плата сотрудников зависит от Ваших оценок') ?></small></span>
                </div>
                <div class="gcw_buttons">
                    <div class="gcw_form_group">
                        <label><b><?= l('Укажите Ваш код клиента') ?></b></label>
                        <input class="gcw_form_control" type="text" name="code">
                    </div>
                    <div class="gcw_form_group">
                        <label><?= l('или') ?></label>
                    </div>
                    <div class="gcw_form_group">
                        <label><b><?= l('Укажите номер телефона') ?></b></label>
                        <input class="gcw_form_control" type="text" name="phone">
                        <span><small><?= l('Номер должен совпадать с тем, который Вы указали в квитанции на ремонт'); ?></small></span>
                    </div>

                    <input type="submit" value="<?= l('Отправить') ?>" class="gcw_btn">
                </div>
                <div class="gcw_form_group">
                </div>
                <span class="gcw_form_error"></span>
            </form>
        </div>
        <div class="gcw_sign"><a href="http://gincore.net">Supported by <b>Gincore</b></a></div>
    </div>
</div>
