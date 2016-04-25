<div class="tabbable">
    <ul class="nav nav-pills">
        <li>
            <a class="click_tab" data-open_tab="products_imt_main" onclick="click_tab(this, event)" href="#imt-main"
               title="">
                <?= l('Основные') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="products_imt_comments" onclick="click_tab(this, event)"
               href="#imt-comments" title="<?= l('Отзывы') ?>">
                <?= l('Отзывы') ?>
            </a>
        </li>
        <?php if ($this->all_configs['configs']['no-warranties'] == false): ?>
            <li>
                <a class="click_tab" data-open_tab="products_imt_warranties" onclick="click_tab(this, event)"
                   href="#imt-warranties" title="<?= l('Гарантийные пакеты') ?>">
                    <?= l('') ?>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a class="click_tab" data-open_tab="products_imt_related" onclick="click_tab(this, event)"
               href="#imt-related" title="<?= l('Сопутствующий') ?>">
                <?= l('Сопутствующий') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="products_imt_relatedgoods" onclick="click_tab(this, event)"
               href="#imt-relatedgoods" title="<?= l('Сопутствующие товары') ?>">
                <?= l('Сопут. товары') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="products_imt_relatedservice" onclick="click_tab(this, event)"
               href="#imt-relatedservice" title="<?= l('Сопутствующие услуги') ?>">
                <?= l('Сопут. услуги') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="products_imt_similar" onclick="click_tab(this, event)"
               href="#imt-similar" title="<?= l('Аналогичные') ?>">
                <?= l('Аналогичные') ?>
            </a>
        </li>
        <?php if ($this->all_configs['configs']['group-goods']): ?>
            <li>
                <a class="click_tab" data-open_tab="products_imt_group" onclick="click_tab(this, event)"
                   href="#imt-group"
                   title="<?= l('Группа') ?>">
                    <?= l('Группа') ?>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a class="click_tab" data-open_tab="products_imt_comments_links" onclick="click_tab(this, event)"
               href="#imt-comments_links" title="<?= l('Ссылки для парсинга') ?>">
                <?= l('Парсер') ?>
            </a>
        </li>
    </ul>

    <div class="pill-content">
        <div id="imt-main" class="pill-pane"></div>
        <div id="imt-comments_links" class="pill-pane"></div>

        <?php if ($this->all_configs['configs']['no-warranties'] == false): ?>
            <div id="imt-warranties" class="pill-pane"></div>
        <?php endif; ?>

        <div id="imt-comments" class="pill-pane"></div>
        <div id="imt-related" class="pill-pane"></div>
        <div id="imt-relatedgoods" class="pill-pane">
            <?= $products_imt_relatedgoods['html']; ?>
        </div>
        <div id="imt-relatedservice" class="pill-pane">
            <?= $products_imt_relatedservice['html']; ?>
        </div>
        <div id="imt-similar" class="pill-pane"></div>

        <?php if ($this->all_configs['configs']['group-goods']): ?>
            <div id="imt-group" class="pill-pane"></div>
        <?php endif; ?>

    </div>
</div>

