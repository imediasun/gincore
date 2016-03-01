<?php

class import_class
{
    protected $import_settings;
    private $source_file; // файл импорта (полный путь)
    private $type; // тип импорта
    private $provider; // провайдер

    /** @var abstract_import_handler */
    private $import_handler = null;

    /**
     * import_class constructor.
     * @param       $all_configs
     * @param       $source_file
     * @param       $type
     * @param       $provider
     * @param array $import_settings
     */
    function __construct($all_configs, $source_file, $type, $provider, $import_settings = array())
    {
        $this->all_configs = $all_configs;
        $this->include_path = $this->all_configs['path'] . 'modules/import/';
        $this->source_file = $source_file;
        $this->type = $type;
        $this->provider = $provider;
        $this->import_settings = $import_settings;
        set_time_limit(0);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function run()
    {
        $this->set_import_handler();
        return $this->execute_import();
    }

    /**
     * @return array
     */
    private function execute_import()
    {
        $filename = $this->include_path . 'files/' . $this->type . '.csv';
        $file = fopen($filename, "r");
        $counter = 0;
        $rows = array();
        while (($row = fgetcsv($file, 1000, $this->csv_delimeter($filename))) !== false) {
            if (!$counter) {
                if (!$this->import_handler->check_format($row)) {
                    return array(
                        'state' => false,
                        'message' => l('Неправильная структура файла для типа импорта и провайдера')
                    );
                }
            } else {
                $rows[] = $row;
            }
            $counter++;
        }
        return $this->import_handler->run($rows);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function get_import_provider_handler()
    {
        $provider_handler_name = $this->provider . '_' . $this->type;
        if (file_exists($this->include_path . 'handlers/' . $provider_handler_name . '.php')) {
            require $this->include_path . 'handlers/' . $provider_handler_name . '.php';
            return new $provider_handler_name($this->all_configs);
        } else {
            throw new Exception('import provider handler ' . $provider_handler_name . ' not found');
        }
    }

    /**
     * @throws Exception
     */
    private function set_import_handler()
    {
        $import_handler_name = 'import_' . $this->type;
        if (file_exists($this->include_path . $import_handler_name . '.php')) {
            require $this->include_path . $import_handler_name . '.php';
            $this->import_handler = new $import_handler_name($this->all_configs, $this->get_import_provider_handler(),
                $this->import_settings);
        } else {
            throw new Exception('import handler ' . $import_handler_name . ' not found');
        }
    }

    /**
     * @param     $file
     * @param int $capture_limit_in_kb
     * @return mixed
     */
    public function csv_delimeter($file, $capture_limit_in_kb = 10)
    {
        // capture starting memory usage
        $output['peak_mem']['start'] = memory_get_peak_usage(true);

        // log the limit how much of the file was sampled (in Kb)
        $output['read_kb'] = $capture_limit_in_kb;

        // read in file
        $fh = fopen($file, 'r');
        $contents = fread($fh, ($capture_limit_in_kb * 1024)); // in KB
        fclose($fh);

        // specify allowed field delimiters
        $delimiters = array(
            'comma' => ',',
            'semicolon' => ';',
            'tab' => "\t",
            'pipe' => '|',
            'colon' => ':'
        );

        // specify allowed line endings
        $line_endings = array(
            'rn' => "\r\n",
            'n' => "\n",
            'r' => "\r",
            'nr' => "\n\r"
        );

        // loop and count each line ending instance
        foreach ($line_endings as $key => $value) {
            $line_result[$key] = substr_count($contents, $value);
        }

        // sort by largest array value
        asort($line_result);

        // log to output array
        $output['line_ending']['results'] = $line_result;
        $output['line_ending']['count'] = end($line_result);
        $output['line_ending']['key'] = key($line_result);
        $output['line_ending']['value'] = $line_endings[$output['line_ending']['key']];
        $lines = explode($output['line_ending']['value'], $contents);

        // remove last line of array, as this maybe incomplete?
        array_pop($lines);

        // create a string from the legal lines
        $complete_lines = implode(' ', $lines);

        // log statistics to output array
        $output['lines']['count'] = count($lines);
        $output['lines']['length'] = strlen($complete_lines);

        // loop and count each delimiter instance
        foreach ($delimiters as $delimiter_key => $delimiter) {
            $delimiter_result[$delimiter_key] = substr_count($complete_lines, $delimiter);
        }

        // sort by largest array value
        asort($delimiter_result);

        // log statistics to output array with largest counts as the value
        $output['delimiter']['results'] = $delimiter_result;
        $output['delimiter']['count'] = end($delimiter_result);
        $output['delimiter']['key'] = key($delimiter_result);
        $output['delimiter']['value'] = $delimiters[$output['delimiter']['key']];

        // capture ending memory usage
        $output['peak_mem']['end'] = memory_get_peak_usage(true);

        return $output['delimiter']['value'];
    }
}