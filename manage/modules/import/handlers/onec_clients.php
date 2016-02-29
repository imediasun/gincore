<?php

require_once __DIR__.'/abstract_import_provider.php';

class onec_clients extends abstract_import_provider
{
    private $cols = array(
        0 => 'Контрагент, Контактное лицо'
    );
    public $has_custom_data_handler = true;

    /**
     * @param $rows
     * @return array
     */
    function get_data($rows)
    {
        $data = array();
        $count = count($rows);
        for ($i = 0; $i < $count; $i++) {
            $current = current($rows);
            $next = next($rows);
            $has_phones = $this->has_phones($next[0]);
            $data[] = array(
                'phones' => $has_phones ? $this->get_phones($next[0]) : array(),
                'fio' => trim(trim(trim($current[0]), ',')),
                'email' => null,
                'address' => null,
            );
            if ($has_phones) {
                if (next($rows) === false) {
                    break;
                }
            } elseif ($next === false) {
                break;
            }
        }
        return $data;
    }

    /**
     * @param $string
     * @return bool
     */
    private function has_phones($string)
    {
        return strlen(preg_replace('/\D/', '', $string)) > 7;
    }

    /**
     * @param $string
     * @return array
     */
    private function get_phones($string)
    {
        return explode(',', preg_replace('/[^\d,]/', '', $string));
    }

    /**
     * @return array
     */
    function get_cols()
    {
        return $this->cols;
    }
}