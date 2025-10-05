<?php

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require __DIR__ . '/../vendor/autoload.php';

(new Filesystem())->remove(__DIR__ . '/../var/cache/test');

if (!isPartialRun()) {
    (new Process([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:database:drop',
        '--force',
        '--env=test',
        '--no-debug',
    ]))->run(function ($type, $buffer): void {
        echo $buffer;
    });

    (new Process([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:database:create',
        '--env=test',
        '--no-debug',
    ]))->mustRun(function ($type, $buffer): void {
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
    ]))->mustRun(function ($type, $buffer): void {
        echo $buffer;
    });

    (new Process([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:schema:validate',
        '--no-interaction',
        '--env=test',
        '-v',
    ]))->mustRun(function ($type, $buffer): void {
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
]))->mustRun(function ($type, $buffer): void {
    echo $buffer;
});
