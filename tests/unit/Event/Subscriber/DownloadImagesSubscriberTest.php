<?php

namespace Wallabag\Tests\Unit\Event\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Event\EntrySavedEvent;
use Wallabag\Event\Subscriber\DownloadImagesSubscriber;
use Wallabag\Helper\DownloadImages;

class DownloadImagesSubscriberTest extends TestCase
{
    public function testPublicArticleImagesAreNeverDownloadedByTheLegacyDownloader(): void
    {
        $entry = new Entry(new User());
        $entry->setXCaptureType('article');
        $entry->setContent('<img src="https://pbs.twimg.com/media/Synthetic.png">');
        $images = $this->createMock(DownloadImages::class);
        $images->expects(self::never())->method('processHtml');
        $images->expects(self::never())->method('processSingleImage');
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('flush');
        (new DownloadImagesSubscriber($em, $images, true, new NullLogger()))->onEntrySaved(new EntrySavedEvent($entry));
        self::assertSame('<img src="https://pbs.twimg.com/media/Synthetic.png">', $entry->getContent());
    }
}
