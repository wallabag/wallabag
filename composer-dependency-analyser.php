<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

return $config
    ->disableComposerAutoloadPathScan()
    ->enableAnalysisOfUnusedDevDependencies()
    ->addPathToScan(__DIR__ . '/app', false)
    ->addPathToScan(__DIR__ . '/migrations', false)
    ->addPathToScan(__DIR__ . '/src', false)
    ->addPathToScan(__DIR__ . '/web', false)
    ->addPathToScan(__DIR__ . '/fixtures', true)
    ->addPathToScan(__DIR__ . '/tests', true)
    ->ignoreErrorsOnPackages([
        'babdev/pagerfanta-bundle',
        'doctrine/common',
        'doctrine/doctrine-migrations-bundle',
        'egulias/email-validator',
        'ergebnis/composer-normalize',
        'friendsofphp/php-cs-fixer',
        'friendsofsymfony/jsrouting-bundle',
        'friendsoftwig/twigcs',
        'incenteev/composer-parameter-handler',
        'jms/serializer-bundle',
        'laminas/laminas-code',
        'lcobucci/jwt',
        'mgargano/simplehtmldom',
        'mnapoli/piwik-twig-extension',
        'nelmio/api-doc-bundle',
        'nelmio/cors-bundle',
        'ocramius/proxy-manager',
        'pagerfanta/twig',
        'php-http/client-common',
        'php-http/httplug',
        'php-http/mock-client',
        'phpstan/extension-installer',
        'phpstan/phpstan',
        'phpstan/phpstan-doctrine',
        'phpstan/phpstan-phpunit',
        'phpstan/phpstan-symfony',
        'psr/http-client',
        'psr/http-factory',
        'psr/http-message',
        'rulerz-php/doctrine-orm',
        'scheb/2fa-bundle',
        'scheb/2fa-qr-code',
        'scheb/2fa-trusted-device',
        'sentry/sentry-symfony',
        'shipmonk/composer-dependency-analyser',
        'stof/doctrine-extensions-bundle',
        'symfony/asset',
        'symfony/browser-kit',
        'symfony/css-selector',
        'symfony/debug-bundle',
        'symfony/doctrine-bridge',
        'symfony/google-mailer',
        'symfony/intl',
        'symfony/maker-bundle',
        'symfony/monolog-bundle',
        'symfony/phpunit-bridge',
        'symfony/polyfill-php80',
        'symfony/polyfill-php81',
        'symfony/proxy-manager-bridge',
        'symfony/security-bundle',
        'symfony/templating',
        'symfony/twig-bundle',
        'symfony/var-dumper',
        'symfony/web-profiler-bundle',
        'symfony/web-server-bundle',
        'twig/extra-bundle',
        'twig/string-extra',
        'wallabag/rulerz-bundle',
        'willdurand/hateoas-bundle',
    ], [ErrorType::UNUSED_DEPENDENCY])
;
