<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Helper\EntriesExport;
use Wallabag\CoreBundle\Helper\EpubContentCleaner;
use Wallabag\CoreBundle\Helper\InstanceImageLocator;
use Wallabag\UserBundle\Entity\User;

class EntriesExportEpubTest extends TestCase
{
    public function testEpubWithAnElementTooBigToBeSplit()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        $cleaner = $this->createMock(EpubContentCleaner::class);
        $cleaner->method('clean')->willReturnArgument(1);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(new User());
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $entry = $this->createMock(Entry::class);
        $entry->method('getId')->willReturn(1);
        $entry->method('getUrl')->willReturn('https://example.org/article');
        $entry->method('getTitle')->willReturn('Article');
        $entry->method('getTags')->willReturn([]);
        $entry->method('getPublishedBy')->willReturn([]);
        $entry->method('getPublishedAt')->willReturn(null);
        $entry->method('getCreatedAt')->willReturn(new \DateTime());
        $entry->method('getUserReadingTime')->willReturn(5);
        $entry->method('getPreviewPicture')->willReturn(null);
        $entry->method('getContent')->willReturn('<pre>' . str_repeat("line of code\n", 30000) . '</pre><p>after</p>');

        $export = new EntriesExport(
            $translator,
            'https://app.wallabag.it',
            '',
            $tokenStorage,
            $cleaner,
            $this->createMock(InstanceImageLocator::class)
        );

        $response = $export->setEntries([$entry])->exportAs('epub');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/epub+zip', $response->headers->get('Content-type'));
    }
}
