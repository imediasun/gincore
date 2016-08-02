<?php

require_once __DIR__ . '/../Core/Helper.php';

class HideField extends Helper
{
    public $options = array();
    public $field = '';

    /**
     * @param $field
     * @return string
     */
    protected function drawToggle($field)
    {
        if(empty($field)) {
            return '';
        }

        return  "<div class='form-group'  style='margin-bottom: 6px'><label>&nbsp;</label> <div class='input-group'><input type='checkbox'
                           name='config[{$field}]' " . (isset($this->options['hide'][$field]) ? 'checked' : '') .
            " class='test-toggle'></div></div>";
    }

    /**
     * @param $buffer
     * @return string
     */
    public function wrap($buffer)
    {
        return "<tr><td class='hide-field-td'>". $this->drawToggle($this->field) ."</td><td>{$buffer}</td></tr>";
    }

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param $field
     */
    public function start($field)
    {
        $this->field = $field;
        ob_start(array($this, 'wrap'));
    }

    /**
     *
     */
    public function end()
    {
        ob_end_flush();
    }
}
