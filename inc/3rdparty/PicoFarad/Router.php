<?php

namespace PicoFarad\Router;

// Load controllers: bootstrap('controllers', 'controller1', 'controller2')
function bootstrap()
{
    $files = \func_get_args();
    $base_path = array_shift($files);

    foreach ($files as $file) {
        require $base_path.'/'.$file.'.php';
    }
}

// Execute a callback before each action
function before($value = null)
{
    static $before_callback = null;

    if (is_callable($value)) {
        $before_callback = $value;
    }
    else if (is_callable($before_callback)) {
        $before_callback($value);
    }
}

// Execute a callback before a specific action
function before_action($name, $value = null)
{
    static $callbacks = array();

    if (is_callable($value)) {
        $callbacks[$name] = $value;
    }
    else if (isset($callbacks[$name]) && is_callable($callbacks[$name])) {
        $callbacks[$name]($value);
    }
}

// Execute an action
function action($name, \Closure $callback)
{
    $handler = isset($_GET['action']) ? $_GET['action'] : 'default';

    if ($handler === $name) {
        before($name);
        before_action($name);
        $callback();
    }
}

// Execute an action only for POST requests
function post_action($name, \Closure $callback)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        action($name, $callback);
    }
}

// Execute an action only for GET requests
function get_action($name, \Closure $callback)
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        action($name, $callback);
    }
}

// Run when no action have been executed before
function notfound(\Closure $callback)
{
    before('notfound');
    before_action('notfound');
    $callback();
}

// Match a request like this one: GET /myhandler
function get($url, \Closure $callback)
{
    find_route('GET', $url, $callback);
}

// Match a request like this one: POST /myhandler
function post($url, \Closure $callback)
{
    find_route('POST', $url, $callback);
}

// Match a request like this one: PUT /myhandler
function put($url, \Closure $callback)
{
    find_route('PUT', $url, $callback);
}

// Match a request like this one: DELETE /myhandler
function delete($url, \Closure $callback)
{
    find_route('DELETE', $url, $callback);
}

// Define which callback to execute according to the URL and the HTTP verb
function find_route($method, $route, \Closure $callback)
{
    if ($_SERVER['REQUEST_METHOD'] === $method) {

        if (! empty($_SERVER['QUERY_STRING'])) {
            $url = substr($_SERVER['REQUEST_URI'], 0, -(strlen($_SERVER['QUERY_STRING']) + 1));
        }
        else {
            $url = $_SERVER['REQUEST_URI'];
        }

        $params = array();

        if (url_match($route, $url, $params)) {

            before($route);
            \call_user_func_array($callback, $params);
            exit;
        }
    }
}

// Parse url and find matches
function url_match($route_uri, $request_uri, array &$params)
{
    if ($request_uri === $route_uri) return true;
    if ($route_uri === '/' || $request_uri === '/') return false;

    $route_uri = trim($route_uri, '/');
    $request_uri = trim($request_uri, '/');

    $route_items = explode('/', $route_uri);
    $request_items = explode('/', $request_uri);
    $nb_route_items = count($route_items);

    if ($nb_route_items === count($request_items)) {

        for ($i = 0; $i < $nb_route_items; ++$i) {

            if ($route_items[$i][0] === ':') {

                $params[substr($route_items[$i], 1)] = $request_items[$i];
            }
            else if ($route_items[$i] !== $request_items[$i]) {

                $params = array();
                return false;
            }
        }

        return true;
    }

    return false;
}
