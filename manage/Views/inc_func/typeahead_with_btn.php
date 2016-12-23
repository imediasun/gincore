<div class="input-group">
    <?= $this->renderFile("inc_func/typeahead", array(
        'show_categories' => $show_categories,
        'categories' => isset($categories) ? $categories : array(),
        'disabled' => $disabled,
        'class_select' => $class_select,
        'iterator' => $iterator,
        'no_clear_if_null' => $no_clear_if_null,
        'table' => $table,
        'multi' => $multi,
        'm' => $m,
        'placeholder' => $placeholder,
        'add_attr' => $add_attr,
        'anyway' => $anyway,
        'function' => $function,
        'object_name' => $object_name,
        'object_id' => $object_id,
        'class' => $class
    )); ?>

    <div class="input-group-btn">
        <button <?= ($disabled ? 'disabled' : '') ?>
            type="button"
            data-form_id="<?= $add_btn['form_id'] ?>"
            data-action="<?= $add_btn['action'] ?>"
            class="typeahead_add_form btn btn-default">
            <?= $add_btn['name'] ?>
        </button>
    </div>
</div>

