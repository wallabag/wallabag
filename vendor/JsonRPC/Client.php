<?php

namespace JsonRPC;

class Client
{
    private $url;
    private $timeout;
    private $debug;
    private $username;
    private $password;

    private $headers = array(
        'Connection: close',
        'Content-Type: application/json',
        'Accept: application/json'
    );


    public function __construct($url, $timeout = 5, $debug = false, $headers = array())
    {
        $this->url = $url;
        $this->timeout = $timeout;
        $this->debug = $debug;
        $this->headers = array_merge($this->headers, $headers);
    }


    public function authentication($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }


    public function execute($procedure, array $params = array())
    {
        $id = mt_rand();

        $payload = array(
            'jsonrpc' => '2.0',
            'method' => $procedure,
            'id' => $id
        );

        if (! empty($params)) {

            $payload['params'] = $params;
        }

        $result = $this->doRequest($payload);

        if (isset($result['id']) && $result['id'] == $id && array_key_exists('result', $result)) {

            return $result['result'];
        }
        else if ($this->debug && isset($result['error'])) {

            print_r($result['error']);
        }

        return null;
    }


    public function doRequest($payload)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'JSON-RPC PHP Client');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        if ($this->username && $this->password) {

            curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
        }

        $result = curl_exec($ch);
        $response = json_decode($result, true);

        curl_close($ch);

        return is_array($response) ? $response : array();
    }
}
