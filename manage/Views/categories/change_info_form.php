<div class="row-fluid">
    <div class="col-sm-12">
        <form id="category-addition-info" method="POST">
            <fieldset>
           <label><?= l('Дополнительная информация') ?></label>
            <input type="hidden" name='category_id' value="<?= $category['id'] ?>"/>
            <textarea class="form-control" name='information' style="width: 100%; height: 250px;"><?= h($category['information']) ?></textarea>
            </fieldset>
        </form>
    </div>
</div>