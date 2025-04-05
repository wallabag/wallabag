<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

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
    ->withConfiguredRule(ClassPropertyAssignToConstructorPromotionRector::class, [
        'inline_public' => true,
    ])
    ->withRules([
        ReadOnlyPropertyRector::class,
    ])
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class => [
            __DIR__ . '/src/Entity/*',
        ],
    ])
    ->withPhpSets(php80: true)
    ->withTypeCoverageLevel(0);
