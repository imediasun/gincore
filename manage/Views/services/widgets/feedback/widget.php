<style>
    input[type=radio].gcw_form_control {
        display: inline-block;
        width: 5%;
    }
</style>
<div class="gcw">
    <div class="gcw_title gcw_show_modal" data-id="gcw_feedback_modal">
        <i class="fa fa-thumbs-o-up"></i>
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
        <div class="gcw_modal_body" style="background-color: lightgrey">
            <form class="gcw_form" data-action="<?= $widgets->get_requests_url('feedback') ?>" method="post">
                <input type="hidden" name="widget" value="feedback">
                <input type="hidden" name="action" value="add">
                <div class="gcw_form_group">
                    <label><?= l('Оцените работу инженера') ?></label>
                    <input class="gcw_form_control" type="radio" name="engineer" value="0"> <?= l('Ужасно'); ?>
                    <input class="gcw_form_control" type="radio" name="engineer" value="2.5"> <?= l('Плохо'); ?>
                    <input class="gcw_form_control" type="radio" name="engineer" value="5"> <?= l('Нормально'); ?>
                    <input class="gcw_form_control" type="radio" name="engineer" value="7.5"> <?= l('Хорошо'); ?>
                    <input class="gcw_form_control" type="radio" name="engineer" value="10"> <?= l('Отлично'); ?>
                </div>
                <div class="gcw_form_group">
                    <label><?= l('Оцените работу сотрудника, который выдавал Вам устройство после ремонта') ?></label>
                    <input class="gcw_form_control" type="radio" name="warehouse" value="0"> <?= l('Ужасно'); ?>
                    <input class="gcw_form_control" type="radio" name="warehouse" value="2.5"> <?= l('Плохо'); ?>
                    <input class="gcw_form_control" type="radio" name="warehouse" value="5"> <?= l('Нормально'); ?>
                    <input class="gcw_form_control" type="radio" name="warehouse" value="7.5"> <?= l('Хорошо'); ?>
                    <input class="gcw_form_control" type="radio" name="warehouse" value="10"> <?= l('Отлично'); ?>
                </div>
                <div class="gcw_form_group">
                    <label><?= l('Оцените работу менеджера, который общался с Вами по телефону') ?></label>
                    <input class="gcw_form_control" type="radio" name="manager" value="0"> <?= l('Ужасно'); ?>
                    <input class="gcw_form_control" type="radio" name="manager" value="2.5"> <?= l('Плохо'); ?>
                    <input class="gcw_form_control" type="radio" name="manager" value="5"> <?= l('Нормально'); ?>
                    <input class="gcw_form_control" type="radio" name="manager" value="7.5"> <?= l('Хорошо'); ?>
                    <input class="gcw_form_control" type="radio" name="manager" value="10"> <?= l('Отлично'); ?>
                </div>
                <div class="gcw_form_group">
                    <label><?= l('Комментарий к отзыву') ?></label>
                    <input class="gcw_form_control" type="text" name="comment">
                </div>
                <div class="gcw_form_group">
                    <label><?= l('Заработная плата сотрудников зависит от Ваших оценок') ?></label>
                </div>
                <div class="gcw_buttons">
                    <div class="gcw_form_group">
                        <label><?= l('Укажите Ваш код клиента') ?></label>
                        <input class="gcw_form_control" type="text" name="code">
                    </div>
                    <div class="gcw_form_group">
                        <label><?= l('или') ?></label>
                    </div>
                    <div class="gcw_form_group">
                        <label><?= l('Укажите номер телефона') ?></label>
                        <input class="gcw_form_control" type="text" name="phone">
                        <span><?= l('Номер должен совпадать с тем, который Вы указали в квитанции на ремонт'); ?></span>
                    </div>

                    <input type="submit" value="<?= l('Отправить') ?>" class="gcw_btn">
                </div>
                <div class="gcw_form_group">
                </div>
                <span class="gcw_form_error"></span>
            </form>
            <div id="gcw_form_html"></div>
        </div>
    </div>
</div>
