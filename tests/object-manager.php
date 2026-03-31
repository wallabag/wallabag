<?php

use Symfony\Component\Dotenv\Dotenv;

/**
 * Required for PHPStan.
 *
 * @see https://github.com/phpstan/phpstan-doctrine#configuration
 */
require __DIR__ . '/../vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

$kernel = new AppKernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
