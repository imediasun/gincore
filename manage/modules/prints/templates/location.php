<?php

require_once __DIR__.'/abstract_template.php';

class location extends AbstractTemplate
{
    public function draw_one($object)
    {
        $location = $this->all_configs['db']->query('SELECT w.title, l.location, l.id
                FROM {warehouses} as w, {warehouses_locations} as l
                WHERE l.id=?i AND l.wh_id=w.id', array($object))->row();

        return $this->view->renderFile('prints/location', array(
            'location' => $location
        ));
    }
}
