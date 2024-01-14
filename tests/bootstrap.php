<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require dirname(__DIR__) . '/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

(new Filesystem())->remove(__DIR__ . '/../var/cache/test');

(new Process([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:database:drop',
    '--force',
]))->run(function ($type, $buffer) {
    echo $buffer;
});

(new Process([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:database:create',
]))->mustRun(function ($type, $buffer) {
    echo $buffer;
});

(new Process([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:migrations:migrate',
    '--no-interaction',
    '-vv',
]))->mustRun(function ($type, $buffer) {
    echo $buffer;
});

(new Process([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:fixtures:load',
    '--no-interaction',
]))->mustRun(function ($type, $buffer) {
    echo $buffer;
});
