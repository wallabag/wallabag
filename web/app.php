<?php

if (!defined('WALLABAG_APP_PHP_DEPRECATED')) {
    define('WALLABAG_APP_PHP_DEPRECATED', true);
    trigger_error(
        'web/app.php is deprecated and will be removed in wallabag 3.0. Use web/index.php instead.',
        E_USER_DEPRECATED
    );
}

$_SERVER['APP_ENV'] ??= 'prod';
$_SERVER['APP_DEBUG'] ??= '0';

return require __DIR__.'/index.php';
