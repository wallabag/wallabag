<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

class Tools
{
    public static function initPhp()
    {
        define('START_TIME', microtime(true));

        if (phpversion() < 5) {
            die(_('Oops, it seems you don\'t have PHP 5.'));
        }

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

    public static function getPocheUrl()
    {
        $https = (!empty($_SERVER['HTTPS'])
                    && (strtolower($_SERVER['HTTPS']) == 'on'))
            || (isset($_SERVER["SERVER_PORT"])
                    && $_SERVER["SERVER_PORT"] == '443') // HTTPS detection.
            || (isset($_SERVER["SERVER_PORT"]) //Custom HTTPS port detection
                    && $_SERVER["SERVER_PORT"] == SSL_PORT)
             || (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                    && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');

        $serverport = (!isset($_SERVER["SERVER_PORT"])
            || $_SERVER["SERVER_PORT"] == '80'
            || $_SERVER["SERVER_PORT"] == HTTP_PORT
            || ($https && $_SERVER["SERVER_PORT"] == '443')
            || ($https && $_SERVER["SERVER_PORT"]==SSL_PORT) //Custom HTTPS port detection
            ? '' : ':' . $_SERVER["SERVER_PORT"]);

        $scriptname = str_replace('/index.php', '/', $_SERVER["SCRIPT_NAME"]);

        if (!isset($_SERVER["HTTP_HOST"])) {
            return $scriptname;
        }

        $host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']));

        if (strpos($host, ':') !== false) {
            $serverport = '';
        }

        return 'http' . ($https ? 's' : '') . '://'
            . $host . $serverport . $scriptname;
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
            $url = Tools::getPocheUrl();
        }

        if (substr($url, 0, 1) !== '?') {
            $ref = Tools::getPocheUrl();
            if (substr($url, 0, strlen($ref)) !== $ref) {
                $url = $ref;
            }
        }
        self::logm('redirect to ' . $url);
        header('Location: '.$url);
        exit();
    }

    public static function getTplFile($view)
    {
        $views = array(
            'install', 'import', 'export', 'config', 'tags',
            'edit-tags', 'view', 'login', 'error'
            );

        if (in_array($view, $views)) {
            return $view . '.twig';
        }

        return 'home.twig';
    }

    public static function getFile($url)
    {
        $timeout = 15;
        $useragent = "Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0";

        if (in_array ('curl', get_loaded_extensions())) {
            # Fetch feed from URL
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            if (!ini_get('open_basedir') && !ini_get('safe_mode')) {
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);

            # for ssl, do not verified certificate
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE );

            # FeedBurner requires a proper USER-AGENT...
            curl_setopt($curl, CURL_HTTP_VERSION_1_1, true);
            curl_setopt($curl, CURLOPT_ENCODING, "gzip, deflate");
            curl_setopt($curl, CURLOPT_USERAGENT, $useragent);

            $data = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $httpcodeOK = isset($httpcode) and ($httpcode == 200 or $httpcode == 301);
            curl_close($curl);
        } else {
            # create http context and add timeout and user-agent
            $context = stream_context_create(
                array(
                    'http' => array(
                        'timeout' => $timeout,
                        'header' => "User-Agent: " . $useragent,
                        'follow_location' => true
                    ),
                    'ssl' => array(
                        'verify_peer' => false,
                        'allow_self_signed' => true
                    )
                )
            );

            # only download page lesser than 4MB
            $data = @file_get_contents($url, false, $context, -1, 4000000);

            if (isset($http_response_header) and isset($http_response_header[0])) {
                $httpcodeOK = isset($http_response_header) and isset($http_response_header[0]) and ((strpos($http_response_header[0], '200 OK') !== FALSE) or (strpos($http_response_header[0], '301 Moved Permanently') !== FALSE));
            }
        }

        # if response is not empty and response is OK
        if (isset($data) and isset($httpcodeOK) and $httpcodeOK) {

            # take charset of page and get it
            preg_match('#<meta .*charset=.*>#Usi', $data, $meta);

            # if meta tag is found
            if (!empty($meta[0])) {
                preg_match('#charset="?(.*)"#si', $meta[0], $encoding);
                # if charset is found set it otherwise, set it to utf-8
                $html_charset = (!empty($encoding[1])) ? strtolower($encoding[1]) : 'utf-8';
                if (empty($encoding[1])) $encoding[1] = 'utf-8';
            } else {
                $html_charset = 'utf-8';
                $encoding[1] = '';
            }

            # replace charset of url to charset of page
            $data = str_replace('charset=' . $encoding[1], 'charset=' . $html_charset, $data);

            return $data;
        }
        else {
            return FALSE;
        }
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
        if (DEBUG_POCHE && php_sapi_name() != 'cli') {
            $t = strval(date('Y/m/d_H:i:s')) . ' - ' . $_SERVER["REMOTE_ADDR"] . ' - ' . strval($message) . "\n";
            file_put_contents(CACHE . '/log.txt', $t, FILE_APPEND);
            error_log('DEBUG POCHE : ' . $message);
        }
    }

    public static function encodeString($string)
    {
        return sha1($string . SALT);
    }

    public static function checkVar($var, $default = '')
    {
        return ((isset ($_REQUEST["$var"])) ? htmlentities($_REQUEST["$var"]) : $default);
    }

    public static function getDomain($url)
    {
        return parse_url($url, PHP_URL_HOST);
    }

    public static function getReadingTime($text) {
        $word = str_word_count(strip_tags($text));
        $minutes = floor($word / 200);
        $seconds = floor($word % 200 / (200 / 60));
        $time = array('minutes' => $minutes, 'seconds' => $seconds);

        return $minutes;
    }

    public static function getDocLanguage($userlanguage) {
        $lang = explode('.', $userlanguage);
        return str_replace('_', '-', $lang[0]);
    }

    public static function status($status_code)
    {
        if (strpos(php_sapi_name(), 'apache') !== false) {

            header('HTTP/1.0 '.$status_code);
        }
        else {

            header('Status: '.$status_code);
        }
    }

    public static function download_db() {
        header('Content-Disposition: attachment; filename="poche.sqlite.gz"');
        self::status(200);

        header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/octet-stream');
        echo gzencode(file_get_contents(STORAGE_SQLITE));

        exit;
    }

    public static function getPageContent(Url $url)
    {
        // Saving and clearing context
        $REAL = array();
        foreach( $GLOBALS as $key => $value ) {
            if( $key != 'GLOBALS' && $key != '_SESSION' && $key != 'HTTP_SESSION_VARS' ) {
                $GLOBALS[$key] = array();
                $REAL[$key] = $value;
            }
        }
        // Saving and clearing session
        if ( isset($_SESSION) ) {
            $REAL_SESSION = array();
            foreach( $_SESSION as $key => $value ) {
                $REAL_SESSION[$key] = $value;
                unset($_SESSION[$key]);
            }
        }

        // Running code in different context
        $scope = function() {
            extract( func_get_arg(1) );
            $_GET = $_REQUEST = array(
                        "url" => $url->getUrl(),
                        "max" => 5,
                        "links" => "preserve",
                        "exc" => "",
                        "format" => "json",
                        "submit" => "Create Feed"
            );
            ob_start();
            require func_get_arg(0);
            $json = ob_get_contents();
            ob_end_clean();
            return $json;
        };
        $json = $scope( "inc/3rdparty/makefulltextfeed.php", array("url" => $url) );

        // Clearing and restoring context
        foreach( $GLOBALS as $key => $value ) {
            if( $key != "GLOBALS" && $key != "_SESSION" ) {
                unset($GLOBALS[$key]);
            }
        }
        foreach( $REAL as $key => $value ) {
            $GLOBALS[$key] = $value;
        }
        // Clearing and restoring session
        if ( isset($REAL_SESSION) ) {
            foreach( $_SESSION as $key => $value ) {
                unset($_SESSION[$key]);
            }
            foreach( $REAL_SESSION as $key => $value ) {
                $_SESSION[$key] = $value;
            }
        }

        return json_decode($json, true);
    }

    /**
     * Returns whether we handle an AJAX (XMLHttpRequest) request.
     * @return boolean whether we handle an AJAX (XMLHttpRequest) request.
     */
    public static function isAjaxRequest()
    {
      return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
    }

}
