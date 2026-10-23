<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Predis\Client;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class RedisConfigurationTest extends TestCase
{
    private $previousEnv;

    protected function setUp(): void
    {
        $this->previousEnv = [getenv('REDIS_URL'), $_ENV['REDIS_URL'] ?? null, $_SERVER['REDIS_URL'] ?? null];
        putenv('REDIS_URL');
        unset($_ENV['REDIS_URL'], $_SERVER['REDIS_URL']);
    }

    protected function tearDown(): void
    {
        putenv(false === $this->previousEnv[0] ? 'REDIS_URL' : 'REDIS_URL=' . $this->previousEnv[0]);
        if (null !== $this->previousEnv[1]) {
            $_ENV['REDIS_URL'] = $this->previousEnv[1];
        } else {
            unset($_ENV['REDIS_URL']);
        }
        if (null !== $this->previousEnv[2]) {
            $_SERVER['REDIS_URL'] = $this->previousEnv[2];
        } else {
            unset($_SERVER['REDIS_URL']);
        }
    }

    /**
     * @dataProvider dataForLegacyParameters
     */
    public function testLegacyParametersArePreserved($scheme, $password)
    {
        $parameters = $this->createClient([
            'scheme' => $scheme,
            'host' => 'localhost',
            'port' => 6379,
            'path' => 'unix' === $scheme ? '/run/redis/redis.sock' : null,
            'password' => $password,
        ])->getConnection()->getParameters();

        $this->assertSame($scheme, $parameters->scheme);
        $this->assertSame($password, $parameters->password);
        if ('unix' === $scheme) {
            $this->assertSame('/run/redis/redis.sock', $parameters->path);
        }
    }

    public function dataForLegacyParameters()
    {
        return [
            'TCP without authentication' => ['tcp', null],
            'TCP with authentication' => ['tcp', 'example-password'],
            'TCP with special characters' => ['tcp', 'p@ss:%/?# +&='],
            'Redis with special characters' => ['redis', 'p@ss:%/?# +&='],
            'zero password' => ['redis', '0'],
            'Unix with special characters' => ['unix', 'p@ss:%/?# +&='],
            'TLS with special characters' => ['tls', 'p@ss:%/?# +&='],
        ];
    }

    public function testRedisUrlOverridesLegacyParameters()
    {
        $_ENV['REDIS_URL'] = 'redis://:url-password@redis.example:6380/3';
        $parameters = $this->createClient()->getConnection()->getParameters();

        $this->assertSame('redis.example', $parameters->host);
        $this->assertSame(6380, $parameters->port);
        $this->assertSame('url-password', $parameters->password);
        $this->assertSame('3', $parameters->database);
    }

    public function testEmptyRedisUrlUsesLegacyParameters()
    {
        $_ENV['REDIS_URL'] = '';
        $parameters = $this->createClient()->getConnection()->getParameters();

        $this->assertSame('localhost', $parameters->host);
        $this->assertSame('legacy-password', $parameters->password);
    }

    private function createClient(array $parameters = [])
    {
        $config = Yaml::parseFile(__DIR__ . '/../app/config/services.yml');
        $container = new ContainerBuilder();
        foreach ($parameters + ['scheme' => 'tcp', 'host' => 'localhost', 'port' => 6379, 'path' => null, 'password' => 'legacy-password'] as $key => $value) {
            $container->setParameter('redis_' . $key, $value);
        }
        $container->setParameter('wallabag_core_redis_parameters', $config['parameters']['wallabag_core_redis_parameters']);
        $container->register(Client::class, Client::class)
            ->setArguments([$config['services'][Client::class]['arguments']['$parameters']])
            ->setPublic(true);
        $container->compile(true);

        return $container->get(Client::class);
    }
}
