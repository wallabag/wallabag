<?php

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'], true) || PHP_SAPI === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

if (!defined('WALLABAG_APP_DEV_PHP_DEPRECATED')) {
    define('WALLABAG_APP_DEV_PHP_DEPRECATED', true);
    trigger_error(
        'web/app_dev.php is deprecated and will be removed in wallabag 3.0. Use web/index.php instead.',
        E_USER_DEPRECATED
    );
}

$_SERVER['APP_ENV'] ??= 'dev';
$_SERVER['APP_DEBUG'] ??= '1';

return require __DIR__.'/index.php';
