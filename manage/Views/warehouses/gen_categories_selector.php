<select class="input-small searchselect" id="searchselect-<?= $num ?>"
        onchange="javascript:$('#goods-<?= $num ?>').attr('data-cat', this.value);">
    <option value="0"><?= l('Все разделы') ?></option>
    <?php foreach ($categories as $category): ?>
        <option value="<?= $category['id'] ?>"><?= $category['title'] ?></option>
    <?php endforeach; ?>
</select>
