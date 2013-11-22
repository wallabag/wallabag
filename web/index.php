<?php

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../app/app.php';
require_once __DIR__.'/../app/controllers.php';

$app['debug'] = true;

$app->run();
