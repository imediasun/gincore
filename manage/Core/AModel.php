<?php

require_once __DIR__ . '/Exceptions.php';
require_once __DIR__ . '/Object.php';

abstract class AModel extends Object
{
    /** @var  \go\DB\DB */
    protected $db;
    protected $all_configs;
    public $table = '';
    public $uses = array();

    public function __construct(&$all_configs = null)
    {
        if (empty($all_configs)) {
            global $all_configs;
        }
        $this->db = db();
        $this->all_configs = $all_configs;
        $this->applyUses();
    }

    /**
     * @return string
     */
    public function pk()
    {
        return 'id';
    }

    /**
     * @param $id
     * @return array
     */
    public function getByPk($id)
    {
        return $this->query('SELECT * FROM ?t WHERE ?q=?i', array($this->table, $this->pk(), $id))->row();
    }

    /**
     * @return array
     */
    abstract public function columns();

    /**
     * @param $options
     * @return bool|int
     */
    public function insert($options)
    {
        if (empty($options)) {
            return false;
        }
        $placeholders = array();
        $fields = array();
        $params = array(
            0 => $this->table,
            1 => '',
        );
        foreach ($options as $field => $value) {
            $onlyName = preg_replace('/`/', '', $field);
            if (!in_array($onlyName, $this->columns())) {
                continue;
            }
            $fields[] = $field;
            switch (true) {
                case is_numeric($value):
                    $placeholders[] = '?i';
                    break;
                case $value === 'null':
                    $placeholders[] = '?q';
                    break;
                case $value === null:
                    $placeholders[] = '?n';
                    break;
                default:
                    $placeholders[] = '?';
            }
            $params[] = $value;
        }
        $params[1] = implode(',', $fields);
        return $this->query("INSERT INTO ?t (?q) VALUES (" . implode(',', $placeholders) . ")", $params)->id();
    }

    /**
     * @param $conditions
     * @return string
     */
    public function makeConditionsQuery($conditions)
    {
        $conditionsQuery = '1=1';
        if (empty($conditions)) {
            return $conditionsQuery;
        }
        if (!is_array($conditions)) {
            return $conditions;
        }
        foreach ($conditions as $field => $value) {
            switch (true) {
                case is_array($value):
                    $conditionsQuery = $this->makeQuery('?q AND ?q IN (?l)', array($conditionsQuery, $field, $value));
                    break;
                case is_numeric($value):
                    $conditionsQuery = $this->makeQuery('?q AND ?q=?i', array($conditionsQuery, $field, $value));
                    break;
                default:
                    $conditionsQuery = $this->makeQuery('?q AND ?q=?', array($conditionsQuery, $field, $value));
            }
        }
        return $conditionsQuery;
    }

    /**
     * @param $options
     * @param $conditions
     * @return bool|int
     */
    public function update($options, $conditions = '1=1')
    {
        if (empty($options)) {
            return false;
        }
        $values = $this->prepareValues($options);

        return $this->query('UPDATE ?t SET ?q WHERE ?q',
            array($this->table, implode(',', $values), $this->makeConditionsQuery($conditions)))->id();
    }

    /**
     * @param        $field
     * @param        $value
     * @param string $conditions
     * @return bool|int
     */
    public function increase($field, $value, $conditions = '1=1')
    {
        if (!in_array($field, $this->columns())) {
            return false;
        }
        return $this->query('UPDATE ?t SET ?q=?q + ? WHERE ?q', array(
            $this->table,
            $field,
            $field,
            $value,
            $this->makeConditionsQuery($conditions)
        ))->ar();
    }

    /**
     * @param        $field
     * @param        $value
     * @param string $conditions
     * @return bool|int
     */
    public function decrease($field, $value, $conditions = '1=1')
    {
        if (!in_array($field, $this->columns())) {
            return false;
        }
        return $this->query('UPDATE ?t SET ?q=?q - ? WHERE ?q', array(
            $this->table,
            $field,
            $field,
            $value,
            $this->makeConditionsQuery($conditions)
        ))->ar();
    }

    /**
     * @param $id
     * @return int
     */
    public function delete($id)
    {
        return $this->deleteAll(array(
            $this->pk() => $id
        ));
    }

    /**
     * @param $conditions
     * @return int
     */
    public function deleteAll($conditions)
    {
        return $this->query('DELETE FROM ?t WHERE ?q',
            array($this->table, $this->makeConditionsQuery($conditions)))->ar();
    }

    /**
     * Выполнить запрос к базе данных
     *
     * @throws \go\DB\Exceptions\Connect
     *         ошибка при отложенном подключении
     * @throws \go\DB\Exceptions\Closed
     *         подключение закрыто
     * @throws \go\DB\Exceptions\Templater
     *         ошибка шаблонизатора запроса
     * @throws \go\DB\Exceptions\Query
     *         ошибка в запросе
     * @throws \go\DB\Exceptions\Fetch
     *         ошибка при разборе результата
     *
     * @param string $pattern
     *        шаблон запроса
     * @param array  $data [optional]
     *        входящие данные для запроса
     * @param string $fetch [optional]
     *        формат представления результата
     * @param string $prefix [optional]
     *        префикс таблиц для данного конкретного запроса
     * @return \go\DB\Result
     *         результат в заданном формате
     */
    public function query($pattern, $data = null, $fetch = null, $prefix = null)
    {
        return $this->db->query($pattern, $data, $fetch, $prefix);
    }

    /**
     * Сформировать запрос на основании шаблона и данных
     *
     * @throws \go\DB\Exceptions\Templater
     *
     * @param string $pattern
     * @param array  $data
     * @param string $prefix
     * @return string
     */
    public function makeQuery($pattern, $data, $prefix = null)
    {
        return $this->db->makeQuery($pattern, $data, $prefix);
    }

    /**
     * Выполнение "чистого" запроса
     *
     * @throws \go\DB\Exceptions\Connect
     * @throws \go\DB\Exceptions\Closed
     * @throws \go\DB\Exceptions\Query
     * @throws \go\DB\Exceptions\Fetch
     *
     * @param string $query
     *        SQL-запрос
     * @param string $fetch [optional]
     *        формат представления результата
     * @return \go\DB\Result
     *         результат в заданном формате
     */
    public function plainQuery($query, $fetch = null)
    {
        return $this->db->plainQuery($query, $fetch);
    }

    /**
     * @param $options
     * @return array
     */
    protected function prepareValues($options)
    {
        $values = array();
        foreach ($options as $field => $value) {
            $onlyName = preg_replace('/`/', '', $field);
            if (!in_array($onlyName, $this->columns())) {
                continue;
            }
            switch (true) {
                case is_numeric($value):
                    $values[] = $this->makeQuery('?q=?i', array($field, $value));
                    break;
                case $value == 'null':
                    $values[] = $this->makeQuery('?q=?q', array($field, $value));
                    break;
                default:
                    $values[] = $this->makeQuery('?q=?', array($field, $value));
            }
        }
        return $values;
    }
}