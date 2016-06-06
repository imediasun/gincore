<?php namespace services\widgets;

class widgets extends \service
{

    private static $instance = null;

    /**
     * @param $file
     * @return mixed
     */
    private function get_encoded_file($file)
    {
        $file_path = __DIR__ . '/' . $file;
        $data = str_replace("\n", "", file_get_contents($file_path));
        $data = rawUrlEncode($data);
        return $data;
    }

    /**
     * @param $file
     * @return string
     */
    public function attach_js($file)
    {
        $script = $this->get_encoded_file($file);
        return
            '(function(){' .
            'var s = document.createElement("script");' .
            's.type = "text/javascript";' .
            's.innerHTML = decodeURIComponent("' . $script . '");' .
            'document.getElementsByTagName("head")[0].appendChild(s);' .
            '})();';;
    }

    /**
     * @param $file
     * @return string
     */
    public function attach_css($file)
    {
        $style = $this->get_encoded_file($file);
        return
            '(function(){' .
            'var s = document.createElement("style");' .
            's.rel = "stylesheet";' .
            's.innerHTML = decodeURIComponent("' . $style . '");' .
            'document.getElementsByTagName("head")[0].appendChild(s);' .
            '})();';
    }

    /**
     * @param $html
     * @return string
     */
    public function add_html($html)
    {
        return "jQuery(document).ready(function () {
        if (!document.getElementById('gcwidgets')) {
            var e = jQuery('<div></div>');
            jQuery('body').append(e);
            e.attr('id', 'gcwidgets');
        }
        jQuery('#gcwidgets').append(decodeURIComponent('" . rawUrlEncode($html) . "'));
    });";
    }

    /**
     * @param $has_jquery
     * @return string
     */
    public function load_widget_service($has_jquery)
    {
        $core_scripts = '';
        if (!$has_jquery) {
            $core_scripts .= $this->attach_js('assets/jquery-1.10.2.min.js');
        }
        $core_scripts .= $this->attach_js('assets/jquery.xdomainrequest.min.js');
        return $core_scripts;
    }

    /**
     * @return string
     */
    public function get_requests_url()
    {
        $protocol = isHTTPS() ? 'https' : 'http';
        return "//{$_SERVER['HTTP_HOST']}{$this->all_configs['siteprefix']}widget.php?ajax";
    }

    /**
     * @return null|widgets
     */
    public static function getInstanse()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * widgets constructor.
     */
    private function __construct()
    {
    }
}
