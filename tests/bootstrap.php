<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

(new Filesystem())->remove(__DIR__ . '/../var/cache/test');

if (!isPartialRun()) {
    (new Process([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:database:drop',
        '--force',
        '--env=test',
        '--no-debug',
    ]))->run(function ($type, $buffer) {
        echo $buffer;
    });

    (new Process([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:database:create',
        '--env=test',
        '--no-debug',
    ]))->mustRun(function ($type, $buffer) {
        echo $buffer;
    });

    (new Process([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:migrations:migrate',
        '--no-interaction',
        '--env=test',
        '--no-debug',
        '-vv',
    ]))->mustRun(function ($type, $buffer) {
        echo $buffer;
    });

    (new Process([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:schema:validate',
        '--no-interaction',
        '--env=test',
        '-v',
    ]))->mustRun(function ($type, $buffer) {
        echo $buffer;
    });
}

(new Process([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:fixtures:load',
    '--no-interaction',
    '--env=test',
    '--no-debug',
]))->mustRun(function ($type, $buffer) {
    echo $buffer;
});
