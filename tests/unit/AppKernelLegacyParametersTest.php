<?php

namespace Wallabag\Tests\Unit;

use PHPUnit\Framework\TestCase;

class AppKernelLegacyParametersTest extends TestCase
{
    private const VARS = ['WALLABAG_SERVER_NAME', 'WALLABAG_FROM_EMAIL', 'WALLABAG_TWOFACTOR_SENDER'];

    private array $backup = [];

    protected function setUp(): void
    {
        foreach ([...self::VARS, 'SYMFONY_DOTENV_VARS'] as $name) {
            $this->backup[$name] = [$_SERVER[$name] ?? null, $_ENV[$name] ?? null, getenv($name)];
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->backup as $name => [$server, $env, $getenv]) {
            unset($_SERVER[$name], $_ENV[$name]);
            putenv($name);

            if (null !== $server) {
                $_SERVER[$name] = $server;
            }

            if (null !== $env) {
                $_ENV[$name] = $env;
            }

            if (false !== $getenv) {
                putenv($name . '=' . $getenv);
            }
        }
    }

    public function testLegacyParametersWinOverDotenvDefaultsOnly(): void
    {
        // Value loaded from .env and still equal to the shipped default: the legacy parameter must be used
        $this->setVar('WALLABAG_SERVER_NAME', 'Your wallabag instance');
        // Value loaded from a dotenv file but customized (e.g. .env.local): it is kept
        $this->setVar('WALLABAG_FROM_EMAIL', 'custom@example.org');
        // Real environment variable, even with the default value: it is kept
        $this->setVar('WALLABAG_TWOFACTOR_SENDER', 'no-reply@wallabag.org');
        $_SERVER['SYMFONY_DOTENV_VARS'] = $_ENV['SYMFONY_DOTENV_VARS'] = 'WALLABAG_SERVER_NAME,WALLABAG_FROM_EMAIL';

        $method = new \ReflectionMethod(\AppKernel::class, 'preferLegacyParametersOverDotenvDefaults');
        $method->setAccessible(true);
        $method->invoke(new \AppKernel('test', false), self::VARS);

        $this->assertArrayNotHasKey('WALLABAG_SERVER_NAME', $_SERVER);
        $this->assertArrayNotHasKey('WALLABAG_SERVER_NAME', $_ENV);
        $this->assertFalse(getenv('WALLABAG_SERVER_NAME'));
        $this->assertSame('custom@example.org', $_SERVER['WALLABAG_FROM_EMAIL']);
        $this->assertSame('no-reply@wallabag.org', $_SERVER['WALLABAG_TWOFACTOR_SENDER']);
    }

    private function setVar(string $name, string $value): void
    {
        $_SERVER[$name] = $_ENV[$name] = $value;
        putenv($name . '=' . $value);
    }
}
