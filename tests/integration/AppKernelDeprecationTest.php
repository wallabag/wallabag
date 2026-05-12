<?php

namespace Wallabag\Tests\Integration;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group legacy
 */
class AppKernelDeprecationTest extends KernelTestCase
{
    use ExpectDeprecationTrait;

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

        self::ensureKernelShutdown();

        try {
            $filesystem->remove($sqlitePath);
            $filesystem->dumpFile($legacyParametersPath, $this->renderLegacyParametersFile($sqlitePath));
            $filesystem->remove($cachePath);

            $this->expectDeprecation('Since wallabag/wallabag 2.x: Loading configuration from "app/config/parameters.yml" is deprecated and will be removed in wallabag 3.0. Configure wallabag with environment variables instead.');

            self::bootKernel();
            self::ensureKernelShutdown();
        } finally {
            self::ensureKernelShutdown();

            if (null === $originalParameters) {
                $filesystem->remove($legacyParametersPath);
            } else {
                $filesystem->dumpFile($legacyParametersPath, $originalParameters);
            }

            $filesystem->remove([$cachePath, $sqlitePath]);
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

    private function renderLegacyParametersFile(string $sqlitePath): string
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
    wallabag_user_agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.92 Safari/535.2
    locale: en
    secret: test-secret
    twofactor_sender: no-reply@wallabag.org
    fosuser_registration: true
    fosuser_confirmation: true
    fos_oauth_server_access_token_lifetime: 3600
    fos_oauth_server_refresh_token_lifetime: 1209600
    from_email: wallabag@example.com
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
            str_replace("'", "''", $sqlitePath)
        );
    }
}
