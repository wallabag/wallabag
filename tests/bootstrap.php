<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

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
