<?php

require_once __DIR__ . '/../Core/Helper.php';

class LockButton extends Helper
{
    public function show($locked=false)
    {
       return $this->view->renderFile('helpers/lock_button/show', array(
           'locked' => $locked
       ));
    }
}
