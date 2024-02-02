<?php

namespace Tests\Wallabag\CoreBundle\SiteConfig;

use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\SiteConfig\ArraySiteConfigBuilder;
use Wallabag\CoreBundle\SiteConfig\SiteConfig;

class ArraySiteConfigBuilderTest extends TestCase
{
    public function testItReturnsSiteConfigThatExists()
    {
        $builder = new ArraySiteConfigBuilder(['example.com' => []]);
        $res = $builder->buildForHost('www.example.com');

        $this->assertInstanceOf(SiteConfig::class, $res);
    }

    public function testItReturnsFalseOnAHostThatDoesNotExist()
    {
        $builder = new ArraySiteConfigBuilder(['anotherexample.com' => []]);
        $res = $builder->buildForHost('example.com');

        $this->assertfalse($res);
    }
}
