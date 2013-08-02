<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */
 
class pocheTools
{
    public static function initPhp()
    {
        define('START_TIME', microtime(true));

        if (phpversion() < 5) {
            die(_('Oops, it seems you don\'t have PHP 5.'));
        }

        error_reporting(E_ALL);

        function stripslashesDeep($value) {
            return is_array($value)
                ? array_map('stripslashesDeep', $value)
                : stripslashes($value);
        }

        if (get_magic_quotes_gpc()) {
            $_POST = array_map('stripslashesDeep', $_POST);
            $_GET = array_map('stripslashesDeep', $_GET);
            $_COOKIE = array_map('stripslashesDeep', $_COOKIE);
        }

        ob_start();
        register_shutdown_function('ob_end_flush');
    }

    public static function isUrl($url)
    {
        $pattern = '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i';

        return preg_match($pattern, $url);
    }

    public static function getUrl()
    {
        $https = (!empty($_SERVER['HTTPS'])
                    && (strtolower($_SERVER['HTTPS']) == 'on'))
            || (isset($_SERVER["SERVER_PORT"])
                    && $_SERVER["SERVER_PORT"] == '443'); // HTTPS detection.
        $serverport = (!isset($_SERVER["SERVER_PORT"])
            || $_SERVER["SERVER_PORT"] == '80'
            || ($https && $_SERVER["SERVER_PORT"] == '443')
            ? '' : ':' . $_SERVER["SERVER_PORT"]);

        $scriptname = str_replace('/index.php', '/', $_SERVER["SCRIPT_NAME"]);

        if (!isset($_SERVER["SERVER_NAME"])) {
            return $scriptname;
        }

        return 'http' . ($https ? 's' : '') . '://'
            . $_SERVER["SERVER_NAME"] . $serverport . $scriptname;
    }

    public static function redirect($url = '')
    {
        if ($url === '') {
            $url = (empty($_SERVER['HTTP_REFERER'])?'?':$_SERVER['HTTP_REFERER']);
            if (isset($_POST['returnurl'])) {
                $url = $_POST['returnurl'];
            }
        }

        # prevent loop
        if (empty($url) || parse_url($url, PHP_URL_QUERY) === $_SERVER['QUERY_STRING']) {
            $url = pocheTool::getUrl();
        }

        if (substr($url, 0, 1) !== '?') {
            $ref = pocheTool::getUrl();
            if (substr($url, 0, strlen($ref)) !== $ref) {
                $url = $ref;
            }
        }
        header('Location: '.$url);
        exit();
    }

    public static function cleanURL($url)
    {

        $url = html_entity_decode(trim($url));

        $stuff = strpos($url,'&utm_source=');
        if ($stuff !== FALSE)
            $url = substr($url, 0, $stuff);
        $stuff = strpos($url,'?utm_source=');
        if ($stuff !== FALSE)
            $url = substr($url, 0, $stuff);
        $stuff = strpos($url,'#xtor=RSS-');
        if ($stuff !== FALSE)
            $url = substr($url, 0, $stuff);

        return $url;
    }

    public static function renderJson($data)
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json; charset=UTF-8');

        echo json_encode($data);
        exit();
    }

    public static function logm($message)
    {
        if (DEBUG_POCHE) {
            $t = strval(date('Y/m/d_H:i:s')) . ' - ' . $_SERVER["REMOTE_ADDR"] . ' - ' . strval($message) . "\n";
            file_put_contents('./log.txt', $t, FILE_APPEND);
        }
    }
}