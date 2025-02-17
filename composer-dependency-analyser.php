<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

$config
    ->disableComposerAutoloadPathScan()
    ->disableExtensionsAnalysis()
    ->enableAnalysisOfUnusedDevDependencies()
    ->addPathToScan(__DIR__ . '/app', false)
    ->addPathToScan(__DIR__ . '/migrations', false)
    ->addPathToScan(__DIR__ . '/src', false)
    ->addPathToScan(__DIR__ . '/web', false)
    ->addPathToScan(__DIR__ . '/fixtures', true)
    ->addPathToScan(__DIR__ . '/tests', true)
    ->ignoreErrorsOnPackages([
        'doctrine/common',
        'egulias/email-validator',
        'ergebnis/composer-normalize',
        'friendsofphp/php-cs-fixer',
        'friendsoftwig/twigcs',
        'incenteev/composer-parameter-handler',
        'j0k3r/graby-site-config',
        'laminas/laminas-code',
        'lcobucci/jwt',
        'mgargano/simplehtmldom',
        'mnapoli/piwik-twig-extension',
        'ocramius/proxy-manager',
        'pagerfanta/twig',
        'php-http/mock-client',
        'phpstan/extension-installer',
        'phpstan/phpstan',
        'phpstan/phpstan-doctrine',
        'phpstan/phpstan-phpunit',
        'phpstan/phpstan-symfony',
        'psr/http-client',
        'psr/http-factory',
        'rulerz-php/doctrine-orm',
        'scheb/2fa-qr-code',
        'scheb/2fa-trusted-device',
        'shipmonk/composer-dependency-analyser',
        'symfony/asset',
        'symfony/css-selector',
        'symfony/doctrine-bridge',
        'symfony/google-mailer',
        'symfony/intl',
        'symfony/phpunit-bridge',
        'symfony/proxy-manager-bridge',
        'symfony/templating',
        'symfony/var-dumper',
        'twig/string-extra',
    ], [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackages([
        'monolog/monolog',
        'symfony/filesystem',
    ], [ErrorType::PROD_DEPENDENCY_ONLY_IN_DEV])
    ->ignoreErrorsOnPackages([
        'dama/doctrine-test-bundle',
        'doctrine/doctrine-fixtures-bundle',
        'symfony/debug-bundle',
        'symfony/maker-bundle',
        'symfony/web-profiler-bundle',
        'symfony/web-server-bundle',
    ], [ErrorType::DEV_DEPENDENCY_IN_PROD])
;

return $config;
