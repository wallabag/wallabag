<?php

namespace Tests\Wallabag\CoreBundle\Twig;

use PHPUnit\Framework\TestCase;
use Wallabag\CoreBundle\Twig\WallabagExtension;

class WallabagExtensionTest extends TestCase
{
    public function testRemoveWww()
    {
        $entryRepository = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepository = $this->getMockBuilder('Wallabag\CoreBundle\Repository\TagRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new WallabagExtension($entryRepository, $tagRepository, $tokenStorage, 0, $translator, '');

        $this->assertSame('lemonde.fr', $extension->removeWww('www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeWww('lemonde.fr'));
        $this->assertSame('gist.github.com', $extension->removeWww('gist.github.com'));
    }

    public function testRemoveScheme()
    {
        $entryRepository = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepository = $this->getMockBuilder('Wallabag\CoreBundle\Repository\TagRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new WallabagExtension($entryRepository, $tagRepository, $tokenStorage, 0, $translator, '');

        $this->assertSame('lemonde.fr', $extension->removeScheme('lemonde.fr'));
        $this->assertSame('gist.github.com', $extension->removeScheme('gist.github.com'));
        $this->assertSame('gist.github.com', $extension->removeScheme('https://gist.github.com'));
    }

    public function testRemoveSchemeAndWww()
    {
        $entryRepository = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepository = $this->getMockBuilder('Wallabag\CoreBundle\Repository\TagRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new WallabagExtension($entryRepository, $tagRepository, $tokenStorage, 0, $translator, '');

        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('http://lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('https://www.lemonde.fr'));
        $this->assertSame('gist.github.com', $extension->removeSchemeAndWww('https://gist.github.com'));
        $this->assertSame('ftp://gist.github.com', $extension->removeSchemeAndWww('ftp://gist.github.com'));
    }
}
