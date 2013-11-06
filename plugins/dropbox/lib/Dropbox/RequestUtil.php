<?php
namespace Dropbox;

if (!function_exists('curl_init')) {
    throw new \Exception("The Dropbox SDK requires the cURL PHP extension, but it looks like you don't have it (couldn't find function \"curl_init\").  Library: \"" . __FILE__ . "\".");
}

if (!function_exists('json_decode')) {
    throw new \Exception("The Dropbox SDK requires the JSON PHP extension, but it looks like you don't have it (couldn't find function \"json_decode\").  Library: \"" . __FILE__ . "\".");
}

if (strlen((string) PHP_INT_MAX) < 19) {
    // Looks like we're running on a 32-bit build of PHP.  This could cause problems because some of the numbers
    // we use (file sizes, quota, etc) can be larger than 32-bit ints can handle.
    throw new \Exception("The Dropbox SDK uses 64-bit integers, but it looks like we're running on a version of PHP that doesn't support 64-bit integers (PHP_INT_MAX=" . ((string) PHP_INT_MAX) . ").  Library: \"" . __FILE__ . "\"");
}

/**
 * @internal
 */
final class RequestUtil
{
    /**
     * @param string $userLocale
     * @param string $host
     * @param string $path
     * @param array $params
     * @return string
     */
    static function buildUrl($userLocale, $host, $path, $params = null)
    {
        $url = self::buildUri($host, $path);
        $url .= "?locale=" . rawurlencode($userLocale);

        if ($params !== null) {
            foreach ($params as $key => $value) {
                Checker::argStringNonEmpty("key in 'params'", $key);
                if ($value !== null) {
                    if (is_bool($value)) {
                        $value = $value ? "true" : "false";
                    }
                    else if (is_int($value)) {
                        $value = (string) $value;
                    }
                    else if (!is_string($value)) {
                        throw new \InvalidArgumentException("params['$key'] is not a string, int, or bool");
                    }
                    $url .= "&" . rawurlencode($key) . "=" . rawurlencode($value);
                }
            }
        }
        return $url;
    }

    /**
     * @param string $host
     * @param string $path
     * @return string
     */
    static function buildUri($host, $path)
    {
        Checker::argStringNonEmpty("host", $host);
        Checker::argStringNonEmpty("path", $path);
        return "https://" . $host . "/" . $path;
    }

    /**
     * @param string $clientIdentifier
     * @param string $url
     * @return Curl
     */
    static function mkCurlWithoutAuth($clientIdentifier, $url)
    {
        $curl = new Curl($url);

        $curl->set(CURLOPT_CONNECTTIMEOUT, 10);

        // If the transfer speed is below 1kB/sec for 10 sec, abort.
        $curl->set(CURLOPT_LOW_SPEED_LIMIT, 1024);
        $curl->set(CURLOPT_LOW_SPEED_TIME, 10);

        //$curl->set(CURLOPT_VERBOSE, true);  // For debugging.
        // TODO: Figure out how to encode clientIdentifier (urlencode?)
        $curl->addHeader("User-Agent: ".$clientIdentifier." Dropbox-PHP-SDK");

        return $curl;
    }

    /**
     * @param string $clientIdentifier
     * @param string $url
     * @param string $accessToken
     * @return Curl
     */
    static function mkCurl($clientIdentifier, $url, $accessToken)
    {
        $curl = self::mkCurlWithoutAuth($clientIdentifier, $url);
        $curl->addHeader("Authorization: Bearer $accessToken");
        return $curl;
    }

    static function buildPostBody($params)
    {
        if ($params === null) return "";

        $pairs = array();
        foreach ($params as $key => $value) {
            Checker::argStringNonEmpty("key in 'params'", $key);
            if ($value !== null) {
                if (is_bool($value)) {
                    $value = $value ? "true" : "false";
                }
                else if (is_int($value)) {
                    $value = (string) $value;
                }
                else if (!is_string($value)) {
                    throw new \InvalidArgumentException("params['$key'] is not a string, int, or bool");
                }
                $pairs[] = rawurlencode($key) . "=" . rawurlencode((string) $value);
            }
        }
        return implode("&", $pairs);
    }

    /**
     * @param string $accessToken
     * @param string $userLocale
     * @param string $host
     * @param string $path
     * @param array|null $params
     *
     * @return HttpResponse
     *
     * @throws Exception
     */
    static function doPost($clientIdentifier, $accessToken, $userLocale, $host, $path, $params = null)
    {
        Checker::argStringNonEmpty("accessToken", $accessToken);

        $url = self::buildUri($host, $path);

        if ($params === null) $params = array();
        $params['locale'] = $userLocale;

        $curl = self::mkCurl($clientIdentifier, $url, $accessToken);
        $curl->set(CURLOPT_POST, true);
        $curl->set(CURLOPT_POSTFIELDS, self::buildPostBody($params));

        $curl->set(CURLOPT_RETURNTRANSFER, true);
        return $curl->exec();
    }

    /**
     * @param string $accessToken
     * @param string $userLocale
     * @param string $host
     * @param string $path
     * @param array|null $params
     *
     * @return HttpResponse
     *
     * @throws Exception
     */
    static function doGet($clientIdentifier, $accessToken, $userLocale, $host, $path, $params = null)
    {
        Checker::argStringNonEmpty("accessToken", $accessToken);

        $url = self::buildUrl($userLocale, $host, $path, $params);

        $curl = self::mkCurl($clientIdentifier, $url, $accessToken);
        $curl->set(CURLOPT_HTTPGET, true);
        $curl->set(CURLOPT_RETURNTRANSFER, true);

        return $curl->exec();
    }

    /**
     * @param string $responseBody
     * @return mixed
     * @throws Exception_BadResponse
     */
    static function parseResponseJson($responseBody)
    {
        $obj = json_decode($responseBody, TRUE, 10);
        if ($obj === null) {
            throw new Exception_BadResponse("Got bad JSON from server: $responseBody");
        }
        return $obj;
    }

    static function unexpectedStatus($httpResponse)
    {
        $sc = $httpResponse->statusCode;

        $message = "HTTP status $sc";
        if (is_string($httpResponse->body)) {
            // TODO: Maybe only include the first ~200 chars of the body?
            $message .= "\n".$httpResponse->body;
        }

        if ($sc === 400) return new Exception_BadRequest($message);
        if ($sc === 401) return new Exception_InvalidAccessToken($message);
        if ($sc === 500 || $sc === 502) return new Exception_ServerError($message);
        if ($sc === 503) return new Exception_RetryLater($message);

        return new Exception_BadResponse("Unexpected $message");
    }

    /**
     * @param int $maxRetries
     *    The number of times to retry it the action if it fails with one of the transient
     *    API errors.  A value of 1 means we'll try the action once and if it fails, we
     *    will retry once.
     *
     * @param callable $action
     *    The the action you want to retry.
     *
     * @return mixed
     *    Whatever is returned by the $action callable.
     */
    static function runWithRetry($maxRetries, $action)
    {
        Checker::argNat("maxRetries", $maxRetries);

        $retryDelay = 1;
        $numRetries = 0;
        while (true) {
            try {
                return $action();
            }
            // These exception types are the ones we think are possibly transient errors.
            catch (Exception_NetworkIO $ex) {
                $savedEx = $ex;
            }
            catch (Exception_ServerError $ex) {
                $savedEx = $ex;
            }
            catch (Exception_RetryLater $ex) {
                $savedEx = $ex;
            }

            // We maxed out our retries.  Propagate the last exception we got.
            if ($numRetries >= $maxRetries) throw $savedEx;

            $numRetries++;
            sleep($retryDelay);
            $retryDelay *= 2;  // Exponential back-off.
        }
        throw new \RuntimeException("unreachable");
    }

}
