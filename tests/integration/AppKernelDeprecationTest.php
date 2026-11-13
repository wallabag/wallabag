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

    public function testLoadsEnvironmentFallbacksWhenLegacyParametersAreMissing(): void
    {
        $filesystem = new Filesystem();
        $legacyParametersPath = $this->getLegacyParametersPath();
        $cachePath = $this->getTestCachePath();
        $backupPath = $legacyParametersPath . '.bak';
        $hasLegacyParameters = is_file($legacyParametersPath);

        if ($hasLegacyParameters && file_exists($backupPath)) {
            throw new \RuntimeException('Cannot back up app/config/parameters.yml: parameters.yml.bak already exists.');
        }

        self::ensureKernelShutdown();

        try {
            if ($hasLegacyParameters) {
                $filesystem->rename($legacyParametersPath, $backupPath);
            }
            $filesystem->remove($cachePath);

            self::bootKernel();

            $container = self::getContainer();
            $expectedFallbacks = [
                'DEFAULT_LOCALE' => 'en',
                'WALLABAG_BASE_URL' => 'http://127.0.0.1:8000',
                'MAILER_DSN' => 'smtp://127.0.0.1',
                'WALLABAG_REGISTRATION_ENABLED' => '0',
                'WALLABAG_CONFIRMATION_ENABLED' => '1',
                'WALLABAG_FROM_EMAIL' => 'wallabag@example.com',
                'WALLABAG_SERVER_NAME' => 'Your wallabag instance',
                'WALLABAG_TWOFACTOR_SENDER' => 'no-reply@wallabag.org',
                'WALLABAG_RABBITMQ_PREFETCH_COUNT' => '10',
                'SENTRY_DSN' => '',
                'WALLABAG_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.92 Safari/535.2',
                'WALLABAG_OAUTH_ACCESS_TOKEN_LIFETIME' => '3600',
                'WALLABAG_OAUTH_REFRESH_TOKEN_LIFETIME' => '1209600',
                'WALLABAG_TABLE_PREFIX' => 'wallabag_',
                'DATABASE_URL' => 'sqlite:///' . $this->getProjectRoot() . '/data/db/wallabag.sqlite',
                'REDIS_URL' => 'redis://127.0.0.1:6379',
                'RABBITMQ_URL' => 'amqp://guest:guest@127.0.0.1:5672',
            ];

            foreach ($expectedFallbacks as $name => $value) {
                self::assertSame($value, $container->getParameter('env(' . $name . ')'), $name);
            }
            self::assertFalse($container->hasParameter('env(APP_SECRET)'));
        } finally {
            self::ensureKernelShutdown();

            if ($hasLegacyParameters && is_file($backupPath)) {
                $filesystem->rename($backupPath, $legacyParametersPath);
            }

            $filesystem->remove($cachePath);
        }
    }

    public function testTriggersDeprecationWhenLegacyParametersAreLoaded(): void
    {
        if (!\extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('This test requires the pdo_sqlite extension because it boots the kernel with a temporary SQLite legacy parameters file.');
        }

        $filesystem = new Filesystem();
        $legacyParametersPath = $this->getLegacyParametersPath();
        $cachePath = $this->getTestCachePath();
        $sqlitePath = $this->getTemporaryDatabasePath();
        $originalParameters = is_file($legacyParametersPath) ? file_get_contents($legacyParametersPath) : null;

        if (false === $originalParameters) {
            throw new \RuntimeException('Unable to read the existing app/config/parameters.yml file.');
        }

        $originalEnvironment = $this->unsetEnvironmentVariables(['APP_SECRET', 'SENTRY_DSN', 'WALLABAG_USER_AGENT']);
        self::ensureKernelShutdown();

        try {
            $filesystem->remove($sqlitePath);
            $filesystem->dumpFile($legacyParametersPath, $this->renderLegacyParametersFile($sqlitePath));
            $filesystem->remove($cachePath);

            $this->expectDeprecation('Since wallabag/wallabag 2.x: Loading configuration from "app/config/parameters.yml" is deprecated and will be removed in wallabag 3.0. Configure wallabag with environment variables instead.');

            self::bootKernel();
            $container = self::getContainer();
            self::assertSame('test-secret', $container->getParameter('env(APP_SECRET)'));
            self::assertSame('legacy-sentry-dsn', $container->getParameter('env(SENTRY_DSN)'));
            self::assertSame('Legacy test user agent', $container->getParameter('env(WALLABAG_USER_AGENT)'));
            self::ensureKernelShutdown();
        } finally {
            self::ensureKernelShutdown();

            if (null === $originalParameters) {
                $filesystem->remove($legacyParametersPath);
            } else {
                $filesystem->dumpFile($legacyParametersPath, $originalParameters);
            }

            $filesystem->remove([$cachePath, $sqlitePath]);
            $this->restoreEnvironmentVariables($originalEnvironment);
        }
    }

    public function testFillsMissingLegacyUserAgentFromEnvironmentFallback(): void
    {
        if (!\extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('This test requires the pdo_sqlite extension because it boots the kernel with a temporary SQLite legacy parameters file.');
        }

        $filesystem = new Filesystem();
        $legacyParametersPath = $this->getLegacyParametersPath();
        $cachePath = $this->getTestCachePath();
        $sqlitePath = $this->getTemporaryDatabasePath();
        $originalParameters = is_file($legacyParametersPath) ? file_get_contents($legacyParametersPath) : null;

        if (false === $originalParameters) {
            throw new \RuntimeException('Unable to read the existing app/config/parameters.yml file.');
        }

        $originalEnvironment = $this->unsetEnvironmentVariables(['WALLABAG_USER_AGENT']);
        self::ensureKernelShutdown();

        try {
            $filesystem->remove($sqlitePath);
            $filesystem->dumpFile($legacyParametersPath, $this->renderLegacyParametersFile($sqlitePath, false));
            $filesystem->remove($cachePath);

            $this->expectDeprecation('Since wallabag/wallabag 2.x: Loading configuration from "app/config/parameters.yml" is deprecated and will be removed in wallabag 3.0. Configure wallabag with environment variables instead.');

            self::bootKernel();
            self::assertSame(
                'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.92 Safari/535.2',
                self::getContainer()->getParameter('env(WALLABAG_USER_AGENT)')
            );
        } finally {
            self::ensureKernelShutdown();

            if (null === $originalParameters) {
                $filesystem->remove($legacyParametersPath);
            } else {
                $filesystem->dumpFile($legacyParametersPath, $originalParameters);
            }

            $filesystem->remove([$cachePath, $sqlitePath]);
            $this->restoreEnvironmentVariables($originalEnvironment);
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
            $includeUserAgent ? "    wallabag_user_agent: 'Legacy test user agent'" : ''
        );
    }
}
