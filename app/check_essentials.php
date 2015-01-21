<?php
if (!file_exists(__DIR__ . '/config/config.inc.php')) {
    die('wallabag seems to be not installed. Please execute ./bin/install before.');
}

if (version_compare(PHP_VERSION, '5.3.3', '<')) {
    die('This software require PHP 5.3.3 minimum');
}

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    if (! ini_get('short_open_tag')) {
        die('This software require to have short tags enabled, check your php.ini => "short_open_tag = On"');
    }
}