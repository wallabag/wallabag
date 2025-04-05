<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/fixtures',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/web',
    ])
    ->withRootFiles()
    ->withImportNames(importShortClasses: false)
    ->withAttributesSets(symfony: true, doctrine: true, gedmo: true, jms: true, sensiolabs: true)
    ->withConfiguredRule(ClassPropertyAssignToConstructorPromotionRector::class, [
        'inline_public' => true,
    ])
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__ . '/src/Entity/*',
        ],
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(0);
