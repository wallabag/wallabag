<?php

namespace Tests\Wallabag\SiteConfig;

use PHPUnit\Framework\TestCase;
use Wallabag\SiteConfig\ArraySiteConfigBuilder;
use Wallabag\SiteConfig\SiteConfig;

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
