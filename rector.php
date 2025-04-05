<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/fixtures',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/web',
    ])
    ->withImportNames(importShortClasses: false)
    ->withAttributesSets(doctrine: true)
    ->withPhpSets(php73: true)
    ->withTypeCoverageLevel(0);
