<?php

namespace Wallabag\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Predis\Connection\Parameters;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppKernelLegacyRedisTest extends TestCase
{
    /**
     * @dataProvider dataForLegacyParameters
     */
    public function testLegacyParametersArePreserved(string $scheme, ?string $password): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('redis_scheme', $scheme);
        $container->setParameter('redis_host', 'localhost');
        $container->setParameter('redis_port', 6379);
        $container->setParameter('redis_path', 'unix' === $scheme ? '/run/redis/redis.sock' : null);
        $container->setParameter('redis_password', $password);

        $method = new \ReflectionMethod(\AppKernel::class, 'defineLegacyRedisUrlFallback');
        $method->setAccessible(true);
        $method->invoke(new \AppKernel('test', false), $container);

        // getParameter() would return the env placeholder, all() gives the default value
        $parameters = Parameters::create($container->getParameterBag()->all()['env(REDIS_URL)']);

        $this->assertSame($scheme, $parameters->scheme);
        $this->assertSame($password, $parameters->password);

        if ('unix' === $scheme) {
            $this->assertSame('/run/redis/redis.sock', $parameters->path);
        } else {
            $this->assertSame('localhost', $parameters->host);
            $this->assertSame(6379, $parameters->port);
        }
    }

    public static function dataForLegacyParameters(): array
    {
        return [
            'TCP without authentication' => ['tcp', null],
            'TCP with special characters' => ['tcp', 'p@ss:%/?# +&='],
            'Redis with special characters' => ['redis', 'p@ss:%/?# +&='],
            'zero password' => ['redis', '0'],
            'Unix with special characters' => ['unix', 'p@ss:%/?# +&='],
        ];
    }
}
