<?php

require_once __DIR__.'/abstract_template.php';

class location extends AbstractTemplate
{
    public function draw_one($object)
    {
        $location = $this->all_configs['db']->query('SELECT w.title, l.location, l.id
                FROM {warehouses} as w, {warehouses_locations} as l
                WHERE l.id=?i AND l.wh_id=w.id', array($object))->row();

        $result = '';
        if ($location) {
            $result .= '<div class="label-box">';

            $src = $this->all_configs['prefix'] . 'print.php?bartype=sn&barcode=L-' . $location['id'];
            $result .= '<div class="label-box-code"><img src="' . $src . '" alt="S/N" title="S/N" /></div>';

            $result .= '<div style="font-size: 1.4em;" class="label-box-title">' . htmlspecialchars($location['location']) . '</div>';

            $result .= '</div>';
        }
        return $result;
    }
}
