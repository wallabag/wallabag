<?php

namespace JsonRPC;

class Server
{
    private $payload;
    static private $procedures = array();


    public function __construct($payload = '')
    {
        $this->payload = $payload;
    }


    public function allowHosts(array $hosts) {

        if (! in_array($_SERVER['REMOTE_ADDR'], $hosts)) {

            header('Content-Type: application/json');
            header('HTTP/1.0 403 Forbidden');
            echo '["Access Forbidden"]';
            exit;
        }
    }


    public function authentication(array $users)
    {
        // OVH workaround
        if (isset($_SERVER['REMOTE_USER'])) {

            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['REMOTE_USER'], 6)));
        }

        if (! isset($_SERVER['PHP_AUTH_USER']) ||
            ! isset($users[$_SERVER['PHP_AUTH_USER']]) ||
            $users[$_SERVER['PHP_AUTH_USER']] !== $_SERVER['PHP_AUTH_PW']) {

            header('WWW-Authenticate: Basic realm="JsonRPC"');
            header('Content-Type: application/json');
            header('HTTP/1.0 401 Unauthorized');
            echo '["Authentication failed"]';
            exit;
        }
    }


    public function register($name, \Closure $callback)
    {
        self::$procedures[$name] = $callback;
    }


    public function unregister($name)
    {
        if (isset(self::$procedures[$name])) {

            unset(self::$procedures[$name]);
        }
    }


    public function unregisterAll()
    {
        self::$procedures = array();
    }


    public function getResponse(array $data, array $payload = array())
    {
        if (! array_key_exists('id', $payload)) {

            return '';
        }

        $response = array(
            'jsonrpc' => '2.0',
            'id' => $payload['id']
        );

        $response = array_merge($response, $data);

        header('Content-Type: application/json');
        return json_encode($response);
    }


    public function mapParameters(array $request_params, array $method_params, array &$params)
    {
        // Positional parameters
        if (array_keys($request_params) === range(0, count($request_params) - 1)) {

            if (count($request_params) !== count($method_params)) return false;
            $params = $request_params;

            return true;
        }

        // Named parameters
        foreach ($method_params as $p) {

            $name = $p->getName();

            if (isset($request_params[$name])) {

                $params[$name] = $request_params[$name];
            }
            else {

                return false;
            }
        }

        return true;
    }


    public function execute()
    {
        // Parse payload
        if (empty($this->payload)) {

            $this->payload = file_get_contents('php://input');
        }

        if (is_string($this->payload)) {

            $this->payload = json_decode($this->payload, true);
        }

        // Check JSON format
        if (! is_array($this->payload)) {

            return $this->getResponse(array(
                'error' => array(
                    'code' => -32700,
                    'message' => 'Parse error'
                )),
                array('id' => null)
            );
        }

        // Handle batch request
        if (array_keys($this->payload) === range(0, count($this->payload) - 1)) {

            $responses = array();

            foreach ($this->payload as $payload) {

                if (! is_array($payload)) {

                    $responses[] = $this->getResponse(array(
                        'error' => array(
                            'code' => -32600,
                            'message' => 'Invalid Request'
                        )),
                        array('id' => null)
                    );
                }
                else {

                    $server = new Server($payload);
                    $response = $server->execute();

                    if ($response) $responses[] = $response;
                }
            }

            return empty($responses) ? '' : '['.implode(',', $responses).']';
        }

        // Check JSON-RPC format
        if (! isset($this->payload['jsonrpc']) ||
            ! isset($this->payload['method']) ||
            ! is_string($this->payload['method']) ||
            $this->payload['jsonrpc'] !== '2.0' ||
            (isset($this->payload['params']) && ! is_array($this->payload['params']))) {

            return $this->getResponse(array(
                'error' => array(
                    'code' => -32600,
                    'message' => 'Invalid Request'
                )),
                array('id' => null)
            );
        }

        // Procedure not found
        if (! isset(self::$procedures[$this->payload['method']])) {

            return $this->getResponse(array(
                'error' => array(
                    'code' => -32601,
                    'message' => 'Method not found'
                )),
                $this->payload
            );
        }

        $callback = self::$procedures[$this->payload['method']];
        $params = array();

        $reflection = new \ReflectionFunction($callback);

        if (isset($this->payload['params'])) {

            $parameters = $reflection->getParameters();

            if (! $this->mapParameters($this->payload['params'], $parameters, $params)) {

                return $this->getResponse(array(
                    'error' => array(
                        'code' => -32602,
                        'message' => 'Invalid params'
                    )),
                    $this->payload
                );
            }
        }

        $result = $reflection->invokeArgs($params);

        return $this->getResponse(array('result' => $result), $this->payload);
    }
}
