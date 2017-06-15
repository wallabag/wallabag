<?php

namespace Tests\Wallabag\CoreBundle\Twig;

use Wallabag\CoreBundle\Twig\WallabagExtension;

class WallabagExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testRemoveWww()
    {
        $entryRepository = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $tagRepository = $this->getMockBuilder('Wallabag\CoreBundle\Repository\TagRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $notificationRepository = $this->getMockBuilder('Wallabag\CoreBundle\Repository\NotificationRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new WallabagExtension($entryRepository, $tagRepository, $notificationRepository, $tokenStorage, 0, 5, $translator);

        $this->assertEquals('lemonde.fr', $extension->removeWww('www.lemonde.fr'));
        $this->assertEquals('lemonde.fr', $extension->removeWww('lemonde.fr'));
        $this->assertEquals('gist.github.com', $extension->removeWww('gist.github.com'));
    }
}
