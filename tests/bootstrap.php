<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

if ('unit' === getCurrentTestSuite()) {
    return;
}

(new Filesystem())->remove(__DIR__ . '/../var/cache/test');

if (!isPartialRun()) {
    runBootstrapCommand([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:database:drop',
        '--force',
        '--env=test',
        '--no-debug',
    ], false);

    runBootstrapCommand([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:database:create',
        '--env=test',
        '--no-debug',
    ]);

    runBootstrapCommand([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:migrations:migrate',
        '--no-interaction',
        '--env=test',
        '--no-debug',
        '-vv',
    ]);

    runBootstrapCommand([
        'php',
        __DIR__ . '/../bin/console',
        'doctrine:schema:validate',
        '--no-interaction',
        '--env=test',
        '-v',
    ]);
}

runBootstrapCommand([
    'php',
    __DIR__ . '/../bin/console',
    'doctrine:fixtures:load',
    '--no-interaction',
    '--env=test',
    '--no-debug',
]);
