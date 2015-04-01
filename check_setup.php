<?php

// Check if /cache is writeable
if (! is_writable('cache')) {
    die('The directory "cache" must be writeable by your web server user');
}

// install folder still present, need to install wallabag
if (is_file('install/index.php')) {
    require('install/index.php');
    exit;
}
