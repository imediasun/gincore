<select class="form-control"
        onchange="set_cookie(this, '<?= $this->all_configs['configs']['count-on-page'] ?>', this.value, 1)">
    <?php foreach ($this->all_configs['configs']['manage-count-on-page'] as $k => $v): ?>
        <option <?= $count == $k ? 'selected' : '' ?> value="<?= $k ?>"><?= h($v) ?></option>
    <?php endforeach; ?>
</select>
