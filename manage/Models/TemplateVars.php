<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MTemplateVars
 *
 */
class MTemplateVars extends AModel
{
    public $table = 'template_vars';
    protected $prefix = '';

    /**
     * @param $value
     * @return mixed
     */
    public function deletePrefix($value)
    {

        return array(
            'description' => $value['description'],
            'var' => preg_replace("/{$this->prefix}/", '', $value['var'])
        );
    }

    /**
     * @param $forView
     * @return array
     */
    public function getUsersPrintTemplates($forView) {
        $templates = $this->query('SELECT var, description FROM ?t WHERE var like "%print_template%" AND for_view=? ORDER by prioriy ASC', array(
            $this->table,
            $forView
        ))->assoc();
        $this->prefix = 'print_template_';
        return array_map(array($this, 'deletePrefix'), $templates);
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'var',
            'for_view',
            'description',
            'priority'
        );
    }
}
