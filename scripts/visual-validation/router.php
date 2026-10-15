<?php

// This router is never used by Wallabag's normal web entrypoints.
if ('cli-server' !== \PHP_SAPI || '1' !== getenv('WALLABAG_ISOLATED_VISUAL') || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Isolated local validation only.');
}
$root = dirname(__DIR__, 2);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath($root . '/web' . rawurldecode($path));
if ($file && 0 === strpos($file, $root . '/web/') && is_file($file) && 'php' !== strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
    return false;
}
require_once $root . '/app/AppKernel.php';
require_once $root . '/vendor/autoload.php';
header('X-Wallabag-Validation: synthetic-network-none');
$kernel = new AppKernel('test', false);
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
