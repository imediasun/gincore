<?php

abstract class Model
{

    /** @var  \go\DB\DB */
    protected $db;
    protected $all_configs;

    public function __construct()
    {
        global $all_configs;
        $this->db = db();
        $this->all_configs = $all_configs;
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
}