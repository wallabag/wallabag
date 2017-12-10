<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * @var ClassLoader
 */
$loader = require __DIR__ . '/../vendor/autoload.php';

AnnotationRegistry::registerUniqueLoader([$loader, 'loadClass']);

return $loader;
