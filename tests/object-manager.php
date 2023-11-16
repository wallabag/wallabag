<?php

/**
 * Required for PHPStan.
 *
 * @see https://github.com/phpstan/phpstan-doctrine#configuration
 */
require __DIR__ . '/../vendor/autoload.php';

$kernel = new AppKernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
