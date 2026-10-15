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
    // Serve explicitly: the release image's CLI server static shortcut produces
    // responses Chromium rejects, although PHP's permissive HTTP client accepts them.
    $types = ['css' => 'text/css', 'js' => 'application/javascript', 'svg' => 'image/svg+xml', 'png' => 'image/png', 'ico' => 'image/x-icon', 'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf', 'otf' => 'font/otf', 'json' => 'application/json'];
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($types[$extension] ?? 'application/octet-stream'));
    readfile($file);
    exit;
}
require_once $root . '/vendor/autoload.php';
require_once $root . '/app/AppKernel.php';
header('X-Wallabag-Validation: synthetic-network-none');
$kernel = new AppKernel('test', false);
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
