<?php

namespace Tests\Wallabag\CoreBundle\Twig;

use Wallabag\CoreBundle\Twig\WallabagExtension;

class WallabagExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testRemoveWww()
    {
        $extension = new WallabagExtension();

        $this->assertEquals('lemonde.fr', $extension->removeWww('www.lemonde.fr'));
        $this->assertEquals('lemonde.fr', $extension->removeWww('lemonde.fr'));
        $this->assertEquals('gist.github.com', $extension->removeWww('gist.github.com'));
    }
}
