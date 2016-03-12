<?php

class Response
{
    /**
     * @param        $content
     * @param string $type
     * @param int    $statusCode
     */
    public static function send($content, $type = 'html', $statusCode = 200)
    {
        self::sendHeader($type, $statusCode);
        self::sendContent($type, $content);
        exit();
    }

    /**
     * @param string $type
     * @param int    $statusCode
     */
    public static function sendHeader($type = 'html', $statusCode = 200)
    {
        $version = self::version();
        header("HTTP/{$version} {$statusCode} Ok");
        switch ($type) {
            case  'json':
                header("Content-Type: application/json; charset=UTF-8");
                break;
            case  'html':
            default:
                header("Content-Type: text/html; charset=UTF-8");
        }
    }

    /**
     * @param string $type
     * @param        $content
     */
    public function sendContent($type = 'html', $content = '')
    {
        switch ($type) {
            case  'json':
                if(!is_array($content)) {
                    $content = array($content);
                }
                echo json_encode($content);
                break;
            case  'html':
            default:
                echo $content;
        }
    }

    /**
     * @param $url
     */
    public static function redirect($url)
    {
        header("Location: {$url}");
        exit();
    }

    /**
     * @return string
     */
    protected static function version()
    {
        return (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') ? '1.0' : '1.1';
    }

    /**
     * @param     $content
     * @param int $statusCode
     */
    public static function html($content, $statusCode = 200)
    {
        self::send($content, 'html', $statusCode);
    }

    /**
     * @param     $content
     * @param int $statusCode
     */
    public static function json($content, $statusCode = 200)
    {
        self::send($content, 'json', $statusCode);
    }
}
