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
        if (!is_file(dirname(__DIR__, 2) . '/app/config/parameters.yml')) {
            $this->markTestSkipped('This test requires app/config/parameters.yml to be present.');
        }

        (new Filesystem())->remove(dirname(__DIR__, 2) . '/var/cache/test');
        $this->expectDeprecation('Since wallabag/wallabag 2.x: Loading configuration from "app/config/parameters.yml" is deprecated and will be removed in wallabag 3.0. Configure wallabag with environment variables instead.');

        self::bootKernel();
        self::ensureKernelShutdown();
    }
}
