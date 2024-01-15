<?php

/**
 * Required for PHPStan.
 *
 * @see https://github.com/phpstan/phpstan-doctrine#configuration
 */

use Wallabag\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$kernel = new Kernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
