<?php

namespace Wallabag\CoreBundle\Helper;

final class Tools
{
    /**
     * Download a file (typically, for downloading pictures on web server).
     *
     * @param $url
     *
     * @return bool|mixed|string
     */
    public static function getFile($url)
    {
        $timeout = 15;
        $useragent = 'Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0';

        if (in_array('curl', get_loaded_extensions())) {
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
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);

            # FeedBurner requires a proper USER-AGENT...
            curl_setopt($curl, CURL_HTTP_VERSION_1_1, true);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip, deflate');
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
                        'header' => 'User-Agent: '.$useragent,
                        'follow_location' => true,
                    ),
                    'ssl' => array(
                        'verify_peer' => false,
                        'allow_self_signed' => true,
                    ),
                )
            );

            # only download page lesser than 4MB
            $data = @file_get_contents($url, false, $context, -1, 4000000);

            if (isset($http_response_header) and isset($http_response_header[0])) {
                $httpcodeOK = isset($http_response_header) and isset($http_response_header[0]) and ((strpos($http_response_header[0], '200 OK') !== false) or (strpos($http_response_header[0], '301 Moved Permanently') !== false));
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
                if (empty($encoding[1])) {
                    $encoding[1] = 'utf-8';
                }
            } else {
                $html_charset = 'utf-8';
                $encoding[1] = '';
            }

            # replace charset of url to charset of page
            $data = str_replace('charset='.$encoding[1], 'charset='.$html_charset, $data);

            return $data;
        } else {
            return false;
        }
    }

    /**
     * Encode a URL by using a salt.
     *
     * @param $string
     *
     * @return string
     */
    public static function encodeString($string)
    {
        return sha1($string.SALT);
    }

    public static function generateToken()
    {
        if (ini_get('open_basedir') === '') {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // alternative to /dev/urandom for Windows
                $token = substr(base64_encode(uniqid(mt_rand(), true)), 0, 20);
            } else {
                $token = substr(base64_encode(file_get_contents('/dev/urandom', false, null, 0, 20)), 0, 15);
            }
        } else {
            $token = substr(base64_encode(uniqid(mt_rand(), true)), 0, 20);
        }

        return str_replace('+', '', $token);
    }

    /**
     * For a given text, we calculate reading time for an article.
     *
     * @param $text
     *
     * @return float
     */
    public static function getReadingTime($text)
    {
        return floor(str_word_count(strip_tags($text)) / 200);
    }
}
