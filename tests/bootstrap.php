<?php

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require __DIR__ . '/../vendor/autoload.php';

(new Filesystem())->remove(__DIR__ . '/../var/cache/test');

(new Process([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:database:drop',
    '--force',
    '--env=test',
]))->run();

(new Process([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:database:create',
    '--env=test',
]))->mustRun();

(new Process([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:migrations:migrate',
    '--no-interaction',
    '--env=test',
    '-vv',
]))->mustRun();

(new Process([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:fixtures:load',
    '--no-interaction',
    '--env=test',
]))->mustRun();
