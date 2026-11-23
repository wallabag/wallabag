<?php

namespace Wallabag\Tests\Integration;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group legacy
 */
class AppKernelDeprecationTest extends KernelTestCase
{
    use ExpectDeprecationTrait;

    public function testDoesNotOverrideEnvironmentVariablesOutsideProd(): void
    {
        $filesystem = new Filesystem();
        $legacyParametersPath = $this->getLegacyParametersPath();
        $cachePath = $this->getTestCachePath();
        $originalParameters = is_file($legacyParametersPath) ? file_get_contents($legacyParametersPath) : null;

        if (false === $originalParameters) {
            throw new \RuntimeException('Unable to read the existing app/config/parameters.yml file.');
        }

        $originalEnvironment = [$_SERVER, $_ENV];
        $databaseUrl = $_SERVER['DATABASE_URL'] ?? null;
        $_SERVER['WALLABAG_USER_AGENT'] = $_ENV['WALLABAG_USER_AGENT'] = 'Default user agent';
        self::ensureKernelShutdown();

        try {
            $filesystem->dumpFile($legacyParametersPath, $this->renderLegacyParametersFile($this->getTemporaryDatabasePath()));
            $filesystem->remove($cachePath);

            self::bootKernel();

            self::assertSame('Default user agent', $_SERVER['WALLABAG_USER_AGENT']);
            self::assertSame($databaseUrl, $_SERVER['DATABASE_URL'] ?? null);
            self::assertSame('test-secret', self::getContainer()->getParameter('kernel.secret'));
        } finally {
            self::ensureKernelShutdown();
            $this->restoreLegacyParameters($originalParameters);
            $filesystem->remove($cachePath);
            [$_SERVER, $_ENV] = $originalEnvironment;
        }
    }

    public function testOverridesDotenvVariablesWithLegacyParametersInProd(): void
    {
        if (!\extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('This test requires the pdo_sqlite extension because it boots the kernel with a temporary SQLite legacy parameters file.');
        }

        $filesystem = new Filesystem();
        $legacyParametersPath = $this->getLegacyParametersPath();
        $originalParameters = is_file($legacyParametersPath) ? file_get_contents($legacyParametersPath) : null;

        if (false === $originalParameters) {
            throw new \RuntimeException('Unable to read the existing app/config/parameters.yml file.');
        }

        $originalEnvironment = [$_SERVER, $_ENV];
        $_SERVER['SENTRY_DSN'] = $_ENV['SENTRY_DSN'] = 'real-sentry-dsn';
        $_SERVER['SYMFONY_DOTENV_VARS'] = $_ENV['SYMFONY_DOTENV_VARS'] = implode(',', array_merge(array_diff(explode(',', $_SERVER['SYMFONY_DOTENV_VARS'] ?? ''), ['SENTRY_DSN']), ['APP_SECRET', 'DATABASE_URL']));
        $_SERVER['APP_SECRET'] = $_ENV['APP_SECRET'] = 'dotenv-secret';
        $kernel = null;

        try {
            $filesystem->dumpFile($legacyParametersPath, $this->renderLegacyParametersFile('%kernel.project_dir%/var/legacy-parameters-deprecation-test.sqlite'));
            $filesystem->remove($this->getProdCachePath());

            $this->expectDeprecation('Since wallabag/wallabag 2.x: Loading configuration from "app/config/parameters.yml" is deprecated and will be removed in wallabag 3.0. Configure wallabag with environment variables instead.');

            $kernel = $this->bootProdKernel();
            self::assertSame('test-secret', $kernel->getContainer()->getParameter('kernel.secret'));
            self::assertSame('Legacy test user agent', $_SERVER['WALLABAG_USER_AGENT']);
            self::assertSame('sqlite:///' . $this->getTemporaryDatabasePath(), $_SERVER['DATABASE_URL']);
            self::assertSame('real-sentry-dsn', $_SERVER['SENTRY_DSN']);
        } finally {
            $kernel?->shutdown();
            $this->restoreLegacyParameters($originalParameters);
            $filesystem->remove($this->getProdCachePath());
            [$_SERVER, $_ENV] = $originalEnvironment;
        }
    }

    public function testKeepsTheEnvironmentVariablesMissingFromLegacyParameters(): void
    {
        if (!\extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('This test requires the pdo_sqlite extension because it boots the kernel with a temporary SQLite legacy parameters file.');
        }

        $filesystem = new Filesystem();
        $legacyParametersPath = $this->getLegacyParametersPath();
        $originalParameters = is_file($legacyParametersPath) ? file_get_contents($legacyParametersPath) : null;

        if (false === $originalParameters) {
            throw new \RuntimeException('Unable to read the existing app/config/parameters.yml file.');
        }

        $originalEnvironment = [$_SERVER, $_ENV];
        $_SERVER['WALLABAG_USER_AGENT'] = $_ENV['WALLABAG_USER_AGENT'] = 'Default user agent';
        $kernel = null;

        try {
            $filesystem->dumpFile($legacyParametersPath, $this->renderLegacyParametersFile($this->getTemporaryDatabasePath(), false));
            $filesystem->remove($this->getProdCachePath());

            $this->expectDeprecation('Since wallabag/wallabag 2.x: Loading configuration from "app/config/parameters.yml" is deprecated and will be removed in wallabag 3.0. Configure wallabag with environment variables instead.');

            $kernel = $this->bootProdKernel();
            self::assertSame('Default user agent', $_SERVER['WALLABAG_USER_AGENT']);
        } finally {
            $kernel?->shutdown();
            $this->restoreLegacyParameters($originalParameters);
            $filesystem->remove($this->getProdCachePath());
            [$_SERVER, $_ENV] = $originalEnvironment;
        }
    }

    public function testFailsToResolveAppSecretWhenMissing(): void
    {
        $filesystem = new Filesystem();
        $legacyParametersPath = $this->getLegacyParametersPath();
        $cachePath = $this->getTestCachePath();
        $backupPath = $legacyParametersPath . '.bak';
        $hasLegacyParameters = is_file($legacyParametersPath);

        if ($hasLegacyParameters && file_exists($backupPath)) {
            throw new \RuntimeException('Cannot back up app/config/parameters.yml: parameters.yml.bak already exists.');
        }

        $originalEnvironment = $this->unsetEnvironmentVariables(['APP_SECRET']);
        self::ensureKernelShutdown();

        try {
            if ($hasLegacyParameters) {
                $filesystem->rename($legacyParametersPath, $backupPath);
            }
            $filesystem->remove($cachePath);

            $this->expectException(EnvNotFoundException::class);
            $this->expectExceptionMessage('APP_SECRET');
            self::bootKernel();
            self::getContainer()->getParameter('kernel.secret');
        } finally {
            self::ensureKernelShutdown();

            if ($hasLegacyParameters && is_file($backupPath)) {
                $filesystem->rename($backupPath, $legacyParametersPath);
            }

            $filesystem->remove($cachePath);
            $this->restoreEnvironmentVariables($originalEnvironment);
        }
    }

    private function getProjectRoot(): string
    {
        return \dirname(__DIR__, 2);
    }

    private function getLegacyParametersPath(): string
    {
        return $this->getProjectRoot() . '/app/config/parameters.yml';
    }

    private function getTestCachePath(): string
    {
        return $this->getProjectRoot() . '/var/cache/test';
    }

    private function getProdCachePath(): string
    {
        return $this->getProjectRoot() . '/var/cache/legacy-parameters-test';
    }

    private function bootProdKernel(): \AppKernel
    {
        $kernel = new class('prod', false) extends \AppKernel {
            public function getCacheDir(): string
            {
                return $this->getProjectDir() . '/var/cache/legacy-parameters-test';
            }
        };
        $kernel->boot();

        return $kernel;
    }

    private function restoreLegacyParameters(?string $originalParameters): void
    {
        if (null === $originalParameters) {
            (new Filesystem())->remove($this->getLegacyParametersPath());
        } else {
            (new Filesystem())->dumpFile($this->getLegacyParametersPath(), $originalParameters);
        }
    }

    private function getTemporaryDatabasePath(): string
    {
        return $this->getProjectRoot() . '/var/legacy-parameters-deprecation-test.sqlite';
    }

    private function unsetEnvironmentVariables(array $names): array
    {
        $original = [];

        foreach ($names as $name) {
            $original[$name] = [
                'env_exists' => \array_key_exists($name, $_ENV),
                'env' => $_ENV[$name] ?? null,
                'server_exists' => \array_key_exists($name, $_SERVER),
                'server' => $_SERVER[$name] ?? null,
                'process' => getenv($name),
            ];

            unset($_ENV[$name], $_SERVER[$name]);
            putenv($name);
        }

        return $original;
    }

    private function restoreEnvironmentVariables(array $original): void
    {
        foreach ($original as $name => $values) {
            if ($values['env_exists']) {
                $_ENV[$name] = $values['env'];
            }

            if ($values['server_exists']) {
                $_SERVER[$name] = $values['server'];
            }

            if (false === $values['process']) {
                putenv($name);
            } else {
                putenv($name . '=' . $values['process']);
            }
        }
    }

    private function renderLegacyParametersFile(string $sqlitePath, bool $includeUserAgent = true): string
    {
        return \sprintf(
            <<<'YAML'
parameters:
    database_driver: pdo_sqlite
    database_host: 127.0.0.1
    database_port: ~
    database_name: symfony
    database_user: root
    database_password: ~
    database_path: '%s'
    database_table_prefix: wallabag_
    database_socket: ~
    database_charset: utf8
    domain_name: http://127.0.0.1:8000
    server_name: wallabag test
    mailer_dsn: smtp://127.0.0.1
%s
    locale: en
    secret: test-secret
    twofactor_sender: no-reply@wallabag.org
    fosuser_registration: true
    fosuser_confirmation: true
    fos_oauth_server_access_token_lifetime: 3600
    fos_oauth_server_refresh_token_lifetime: 1209600
    from_email: wallabag@example.com
    sentry_dsn: legacy-sentry-dsn
    rabbitmq_host: 127.0.0.1
    rabbitmq_port: 5672
    rabbitmq_user: guest
    rabbitmq_password: guest
    rabbitmq_prefetch_count: 10
    redis_scheme: redis
    redis_host: 127.0.0.1
    redis_port: 6379
    redis_path: ~
    redis_password: ~
YAML,
            str_replace("'", "''", $sqlitePath),
            $includeUserAgent ? "    wallabag_user_agent: 'Legacy test user agent'" : '',
        );
    }
}
