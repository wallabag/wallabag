<?php

namespace Tests\Wallabag\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Repository\AnnotationRepository;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\TagRepository;
use Wallabag\Twig\WallabagExtension;

class WallabagExtensionTest extends TestCase
{
    public function testRemoveWww()
    {
        $entryRepository = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $annotationRepository = $this->getMockBuilder(AnnotationRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepository = $this->getMockBuilder(TagRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new WallabagExtension($entryRepository, $annotationRepository, $tagRepository, $tokenStorage, 0, $translator, '');

        $this->assertSame('lemonde.fr', $extension->removeWww('www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeWww('lemonde.fr'));
        $this->assertSame('gist.github.com', $extension->removeWww('gist.github.com'));
    }

    public function testRemoveScheme()
    {
        $entryRepository = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $annotationRepository = $this->getMockBuilder(AnnotationRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepository = $this->getMockBuilder(TagRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new WallabagExtension($entryRepository, $annotationRepository, $tagRepository, $tokenStorage, 0, $translator, '');

        $this->assertSame('lemonde.fr', $extension->removeScheme('lemonde.fr'));
        $this->assertSame('gist.github.com', $extension->removeScheme('gist.github.com'));
        $this->assertSame('gist.github.com', $extension->removeScheme('https://gist.github.com'));
        $this->assertSame('gist.github.com', $extension->removeScheme('http://gist.github.com'));
    }

    public function testRemoveSchemeAndWww()
    {
        $entryRepository = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $annotationRepository = $this->getMockBuilder(AnnotationRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepository = $this->getMockBuilder(TagRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new WallabagExtension($entryRepository, $annotationRepository, $tagRepository, $tokenStorage, 0, $translator, '');

        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('http://www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('http://lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('https://www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('https://lemonde.fr'));
        $this->assertSame('gist.github.com', $extension->removeSchemeAndWww('https://gist.github.com'));
        $this->assertSame('ftp://gist.github.com', $extension->removeSchemeAndWww('ftp://gist.github.com'));
    }
}
