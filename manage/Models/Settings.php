<?php
require_once __DIR__ . '/../Core/AModel.php';

class MSettings extends AModel
{
    public $table = 'settings';
    protected $settings = array();

    /**
     * Settings constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->settings = $this->all();
    }

    /**
     * @param     $name
     * @param     $value
     * @param     $title
     * @param     $description
     * @param int $ro
     */
    public function save($name, $value, $title, $description, $ro = 0)
    {
        $this->query("INSERT INTO ?t (description, `name`, `value`, title, ro) VALUES (?, ?, ?, ?, ?)", array(
            $this->table,
            $description,
            $name,
            $value,
            $title,
            $ro
        ))->ar();
    }

    /**
     * @param $field
     * @param $value
     * @return bool|mixed
     */
    protected function find($field, $value)
    {
        foreach ($this->settings as $setting) {
            if (isset($setting[$field]) && $setting[$field] == $value) {
                return $setting;
            }
        }
        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    public function check($name)
    {
        $setting = $this->find('name', $name);
        return !empty($setting);
    }

    /**
     * @param $name
     * @param $value
     * @return int
     */
    public function setByName($name, $value)
    {
        return $this->query("UPDATE ?t SET `value`=? WHERE `name`=?", array(
            $this->table,
            $value,
            $name
        ))->ar();
    }

    /**
     * @param $id
     * @param $value
     * @return int
     */
    public function setById($id, $value)
    {
        return $this->query("UPDATE ?t SET `value`=? WHERE `id`=?", array(
            $this->table,
            $value,
            $id
        ))->ar();
    }

    /**
     * @param $name
     * @return string
     */
    public function getByName($name)
    {
        $setting = $this->find('name', $name);
        return !empty($setting) ? $setting['value'] : false;
    }

    /**
     * @param string $condition
     * @return array
     */
    public function all($condition = '')
    {
        $query = '1=1';
        if (!empty($condition)) {
            $query = $this->makeQuery('?q AND ?q', array($query, $condition));
        }
        return $this->query("SELECT * FROM ?t WHERE ?q", array($this->table, $query))->assoc();
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'section',
            'description',
            'name',
            'value',
            'title',
            'ro',
        );
    }
}
