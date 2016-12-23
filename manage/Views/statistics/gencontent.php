<div id="graph" class="graph"></div>

<table id="report" class="tablesorter">
    <thead>
    <tr>
        <th class="{ sorter: false }"></th>
        <th><?= l('Дата') ?></th>
        <th class="{ sorter: false }"><?= l('Показы') ?></th>
        <th class="{ sorter: false }"><?= l('Клики') ?></th>
        <th class="{ sorter: false }">CTR</th>
        <th class="{ sorter: false }"><?= l('Просмотры') ?></th>
        <th class="{ sorter: false }"><?= l('Новые пользователи') ?></th>
        <th class="{ sorter: false }"><?= l('Новые пользователи %') ?></th>
        <th class="{ sorter: false }"><?= l('% отказа н.п.') ?></th>
        <th class="{ sorter: false }"><?= l('Звонки.') ?></th>
        <th class="{ sorter: false }"><?= l('Заявки') ?></th>
        <th class="{ sorter: false }"><?= l('Заказы по заявкам') ?></th>
        <th class="{ sorter: false }"><?= l('Заказы без заявок') ?></th>
        <th class="{ sorter: false }"><?= l('Общее кол-во заказов') ?></th>
        <th class="{ sorter: false }"><?= l('Кол-во оплат') ?></th>
        <th class="{ sorter: false }"><?= l('Создано на сумму') ?></th>
        <th class="{ sorter: false }"><?= l('Сумма') ?></th>
        <th class="{ sorter: false }"><?= l('Ср. чек') ?></th>
        <th class="{ sorter: false }">CPO</th>
        <th class="{ sorter: false }">ROI</th>
    </tr>
    </thead>
    <tbody>
    <tr class="loading">
        <td colspan="20" style="text-align: center">
            <div class="progress progress-striped active">
                <div class="bar" style="width: 100%;"></div>
            </div>
        </td>
    </tr>
    <tr class="nodata">
        <td colspan="20" style="text-align: center">
            <div class="message">
                <div class="alert alert-block">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h4><?= l('Пусто') ?>!</h4>
                </div>
            </div>
        </td>
    </tr>
    </tbody>
    <tfoot></tfoot>
</table>
