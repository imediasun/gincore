<tr>
    <td width="70%">
        <label> <?= l('Выберите цвет фона для виджета') ?></label>
    </td>
    <td>
        <div id="demo_endis"
             class="input-group colorpicker-auto colorpicker-component colorpicker-element">
            <input class="form-control colorpicker" type="text" name="bg-color"
                   placeholder='<?= l('введите цвет') ?>' value="<?= $bg_color ?>">
                                        <span class="input-group-addon">
                                            <i class='show-color' style="background-color: <?= $bg_color ?>;"></i>
                                        </span>
        </div>
    </td>
</tr>
<tr>
    <td>
        <label> <?= l('Выберите цвет текста для виджета') ?></label>
    </td>
    <td>
        <div id="demo_endis" class="input-group colorpicker-auto colorpicker-component colorpicker-element">
            <input class="form-control colorpicker" type="text" name="fg-color"
                   placeholder='<?= l('введите цвет') ?>' value="<?= $fg_color ?>">
                                        <span class="input-group-addon">
                                            <i class='show-color' style="background-color: <?= $fg_color ?>;"></i>
                                        </span>
        </div>
    </td>
</tr>
<script>
    function init_colorpickers(){
        $('.colorpicker.colorpicker-element').colorpicker('destroy');
        $('.colorpicker-auto').colorpicker();
    }
    jQuery(document).ready(function () {
        init_colorpickers();
    });
</script>
