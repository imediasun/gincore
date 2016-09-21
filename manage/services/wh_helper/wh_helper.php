<?php namespace services\wh_helper;

class wh_helper extends \service
{

    private static $instance = null;
    private $cache_folder = 'cache/';
    private $cache_warehouses = 'cache/warehouses.json';

    /**
     * @param $all_configs
     */
    public function set_all_configs($all_configs)
    {
        parent::set_all_configs($all_configs);
        $this->cache_folder = $this->all_configs['path'] . 'services/wh_helper/' . $this->cache_folder;
        $this->cache_warehouses = $this->all_configs['path'] . 'services/wh_helper/' . $this->cache_warehouses;
    }

    // массив всех складов и их локаций
    /**
     * @return array|mixed
     */
    public function get_warehouses()
    {
        if (file_exists($this->cache_warehouses)) {
            return json_decode(file_get_contents($this->cache_warehouses), true);
        } else {
            $wh = $this->all_configs['db']->query(
                "SELECT l.id as location_id, l.location as location_name, w.*
                 FROM {warehouses_locations} as l
                 LEFT JOIN {warehouses} as w ON w.id = l.wh_id
                 ORDER BY w.id, location_name
                "
            )->assoc('id');
            $warehouses = array();
            foreach ($wh as $w) {
                if (!isset($warehouses[$w['id']])) {
                    $warehouses[$w['id']] = $w;
                    unset($warehouses[$w['id']]['location_id']);
                    unset($warehouses[$w['id']]['location_name']);
                    $warehouses[$w['id']]['locations'] = array();
                }
                $warehouses[$w['id']]['locations'][$w['location_id']] = array(
                    'id' => $w['location_id'],
                    'name' => $w['location_name']
                );
            }

            if (!is_dir($this->cache_folder)) {
                mkdir($this->cache_folder, 0775);
            }
            file_put_contents($this->cache_warehouses, json_encode($warehouses));
            return $warehouses;
        }
    }

    /**
     *
     */
    function clear_cache()
    {
        if (file_exists($this->cache_warehouses)) {
            unlink($this->cache_warehouses);
        }
    }

    /**
     * @return null|wh_helper
     */
    public static function getInstanse()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * wh_helper constructor.
     */
    private function __construct()
    {
    }
}