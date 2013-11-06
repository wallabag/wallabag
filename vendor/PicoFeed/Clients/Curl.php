<?php

namespace PicoFeed\Clients;

use \PicoFeed\Logging;

class Curl extends \PicoFeed\Client
{
    private $body = '';
    private $body_length = 0;
    private $headers = array();
    private $headers_counter = 0;


    public function readBody($ch, $buffer)
    {
        $length = strlen($buffer);
        $this->body_length += $length;

        if ($this->body_length > $this->max_body_size) return -1;
        $this->body .= $buffer;

        return $length;
    }


    public function readHeaders($ch, $buffer)
    {
        $length = strlen($buffer);

        if ($buffer === "\r\n") {
            $this->headers_counter++;
        }
        else {

            if (! isset($this->headers[$this->headers_counter])) {
                $this->headers[$this->headers_counter] = '';
            }

            $this->headers[$this->headers_counter] .= $buffer;
        }

        return $length;
    }


    public function doRequest($follow_location = true)
    {
        $request_headers = array('Connection: close');

        if ($this->etag) $request_headers[] = 'If-None-Match: '.$this->etag;
        if ($this->last_modified) $request_headers[] = 'If-Modified-Since: '.$this->last_modified;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ini_get('open_basedir') === '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, $this->max_redirects);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For auto-signed certificates...
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'readBody'));
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'readHeaders'));
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'php://memory');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'php://memory');

        if (parent::$proxy_hostname) {

            curl_setopt($ch, CURLOPT_PROXYPORT, parent::$proxy_port);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
            curl_setopt($ch, CURLOPT_PROXY, parent::$proxy_hostname);

            if (parent::$proxy_username) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, parent::$proxy_username.':'.parent::$proxy_password);
            }
        }

        curl_exec($ch);

        Logging::log(\get_called_class().' cURL total time: '.curl_getinfo($ch, CURLINFO_TOTAL_TIME));
        Logging::log(\get_called_class().' cURL dns lookup time: '.curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME));
        Logging::log(\get_called_class().' cURL connect time: '.curl_getinfo($ch, CURLINFO_CONNECT_TIME));
        Logging::log(\get_called_class().' cURL speed download: '.curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD));

        if (curl_errno($ch)) {

            Logging::log(\get_called_class().' cURL error: '.curl_error($ch));

            curl_close($ch);
            return false;
        }

        curl_close($ch);

        list($status, $headers) = $this->parseHeaders(explode("\r\n", $this->headers[$this->headers_counter - 1]));

        if ($follow_location && ini_get('open_basedir') !== '' && ($status == 301 || $status == 302)) {

            $nb_redirects = 0;
            $this->url = $headers['Location'];
            $this->body = '';
            $this->body_length = 0;
            $this->headers = array();
            $this->headers_counter = 0;

            while (true) {

                $nb_redirects++;
                if ($nb_redirects >= $this->max_redirects) return false;

                $result = $this->doRequest(false);

                if ($result['status'] == 301 || $result['status'] == 302) {
                    $this->url = $result['headers']['Location'];
                    $this->body = '';
                    $this->body_length = 0;
                    $this->headers = array();
                    $this->headers_counter = 0;
                }
                else {
                    return $result;
                }
            }
        }

        return array(
            'status' => $status,
            'body' => $this->body,
            'headers' => $headers
        );
    }
}