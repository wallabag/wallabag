<?php

// PHP 5.3 minimum
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('This software require PHP 5.3.0 minimum');
}

// Short tags must be enabled for PHP < 5.4
if (version_compare(PHP_VERSION, '5.4.0', '<')) {

    if (! ini_get('short_open_tag')) {
        die('This software require to have short tags enabled, check your php.ini => "short_open_tag = On"');
    }
}

// Old libxml2 version are not supported (Debian Lenny is too old)
if (version_compare(LIBXML_DOTTED_VERSION, '2.7.0', '<')) {
    die('This software require at least libxml2 version 2.7.0');
}

// Check XML functions
if (! function_exists('simplexml_load_string')) {
    die('PHP extension required: SimpleXML');
}

if (! function_exists('xml_parser_create')) {
    die('PHP extension required: XML Parser');
}

if (! function_exists('dom_import_simplexml')) {
    die('PHP extension required: DOM');
}

// Check PDO Sqlite
if (! extension_loaded('pdo_sqlite')) {
    die('PHP extension required: pdo_sqlite');
}

// Check for curl
if (! function_exists('curl_init') && ! ini_get('allow_url_fopen')) {
    die('You must have "allow_url_fopen=On" or curl extension installed');
}

// Check if /data is writeable
if (! is_writable('data')) {
    die('The directory "data" must be writeable by your web server user');
}
