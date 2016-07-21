<?php

class Url
{
    /**
     * @param $params
     * @return string
     */
    static public function create($params)
    {
        global $all_configs;
        $url = array(

        );
        if (isset($params['controller'])) {
            $url[] = $params['controller'];
            unset($params['controller']);
        }
        if (isset($params['action'])) {
            $url[] = $params['action'];
            unset($params['action']);
        }
        $options = '';
        $hash = '';
        if (isset($params['options']) && is_array($params['options'])) {
            $options = http_build_query($params['options']);
            unset($params['options']);
        }
        if (isset($params['hash'])) {
            $hash = $params['hash'];
            unset($params['hash']);
        }
        if (!empty($params)) {
            foreach ($params as $name => $value) {
                $url[] = $value;
            }
        }
        return $all_configs['prefix']. implode('/', $url) . '?' . $options . $hash;
    }
}