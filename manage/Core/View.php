<?php

require_once __DIR__ . '/Object.php';

class View extends Object
{
    /** @var  string */
    public $basePath;
    private $all_configs;

    /**
     * @param $all_configs
     */
    public function __construct(&$all_configs = array())
    {
        if (empty($all_configs)) {
            global $all_configs;
        }
        $this->all_configs = $all_configs;
        if (empty($this->all_configs)) {
            $this->basePath = __DIR__ . '/../';
        } else {
            $this->basePath = $all_configs['sitepath'] . 'manage';
        }
    }

    /**
     * @param $helper
     * @return null|Helper
     */
    public function load($helper)
    {
        if (!in_array($helper, $this->uses)) {
            $this->uses[] = $helper;
        }
        $this->applyUses('Helpers');
        if (property_exists($this, $helper)) {
            return $this->$helper;
        }
        return null;
    }

    /**
     * @param       $view
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function render($view, $params = [])
    {
        $viewFile = $this->getViewFile($view);
        if (!file_exists($viewFile)) {
            throw new Exception('View not found');
        }
        return $this->renderFile($viewFile, $params);
    }

    /**
     * @param $file
     * @param $params
     * @return string
     */
    public function renderFile($file, $params = array())
    {
        ob_start();
        ob_implicit_flush(false);
        extract($params, EXTR_OVERWRITE);
        require($this->getViewFile($file));
        return ob_get_clean();
    }

    /**
     * @param $view
     * @return string
     */
    protected function getViewFile($view)
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . $view . '.php';
    }

    /**
     * @param $layout
     * @return string
     */
    protected function getLayoutFile($layout)
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR . $layout . '.php';
    }

    /**
     * @param        $layout
     * @param string $content
     * @param array  $context
     * @return string
     */
    public function renderLayout($layout, $content = '', $context = [])
    {
        return $this->renderFile($this->getLayoutFile($layout), array_merge(['content' => $content], $context));
    }
}
