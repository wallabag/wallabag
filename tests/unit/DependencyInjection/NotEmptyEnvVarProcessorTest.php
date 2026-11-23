<?php

namespace Wallabag\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Wallabag\DependencyInjection\NotEmptyEnvVarProcessor;

class NotEmptyEnvVarProcessorTest extends TestCase
{
    public function testReturnsTheValue(): void
    {
        $this->assertSame('secret', (new NotEmptyEnvVarProcessor())->getEnv('not_empty', 'APP_SECRET', static fn () => 'secret'));
    }

    public function testFailsOnAnEmptyValue(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Environment variable "APP_SECRET" must not be empty.');

        (new NotEmptyEnvVarProcessor())->getEnv('not_empty', 'APP_SECRET', static fn () => '');
    }
}
