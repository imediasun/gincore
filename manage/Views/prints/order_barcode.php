<div style="border: 1px solid grey; width: 4cm; height: 2.5cm; margin-left: 7px; margin-top: -1px; ">
    <div style="width: 4cm; height: 2.5cm; display: table-cell; vertical-align: middle; text-align: center">
        <?= $this->renderFile('prints/_bar_code', array(
            'id' => $order['id']
        )) ?>
    </div>
</div>
