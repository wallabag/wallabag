<?php

use Symfony\Component\Filesystem\Filesystem;

require __DIR__ . '/../vendor/autoload.php';

(new Filesystem())->remove(__DIR__ . '/../var/cache/test');
