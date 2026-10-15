<?php

if ('cli' !== \PHP_SAPI || '1' !== getenv('WALLABAG_ISOLATED_VISUAL')) {
    exit(1);
}
require_once dirname(__DIR__, 2) . '/app/AppKernel.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
$application = new Symfony\Bundle\FrameworkBundle\Console\Application(new AppKernel('test', false));
$application->run();
