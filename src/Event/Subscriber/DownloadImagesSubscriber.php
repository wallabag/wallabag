<?php

namespace Wallabag\Event\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wallabag\Entity\Entry;
use Wallabag\Event\EntryDeletedEvent;
use Wallabag\Event\EntrySavedEvent;
use Wallabag\Helper\DownloadImages;

class DownloadImagesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DownloadImages $downloadImages,
        private $enabled,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntrySavedEvent::NAME => 'onEntrySaved',
            EntryDeletedEvent::NAME => 'onEntryDeleted',
        ];
    }

    /**
     * Download images and updated the data into the entry.
     */
    public function onEntrySaved(EntrySavedEvent $event)
    {
        if (!$this->enabled) {
            $this->logger->debug('DownloadImagesSubscriber: disabled.');

            return;
        }

        $entry = $event->getEntry();

        $html = $this->downloadImages($entry);
        if (false !== $html) {
            $this->logger->debug('DownloadImagesSubscriber: updated html.');

            $entry->setContent($html);
        }

        // update preview picture
        $previewPicture = $this->downloadPreviewImage($entry);
        if (false !== $previewPicture) {
            $this->logger->debug('DownloadImagesSubscriber: update preview picture.');

            $entry->setPreviewPicture($previewPicture);
        }

        $this->em->persist($entry);
        $this->em->flush();
    }

    /**
     * Remove images related to the entry.
     */
    public function onEntryDeleted(EntryDeletedEvent $event)
    {
        if (!$this->enabled) {
            $this->logger->debug('DownloadImagesSubscriber: disabled.');

            return;
        }

        $this->downloadImages->removeImages($event->getEntry()->getId());
    }

    /**
     * Download all images from the html.
     *
     * @todo If we want to add async download, it should be done in that method
     *
     * @return string
     */
    private function downloadImages(Entry $entry)
    {
        return $this->downloadImages->processHtml(
            $entry->getId(),
            $entry->getContent(),
            $entry->getUrl()
        );
    }

    /**
     * Download the preview picture.
     *
     * @todo If we want to add async download, it should be done in that method
     *
     * @return string|false False in case of async
     */
    private function downloadPreviewImage(Entry $entry)
    {
        return $this->downloadImages->processSingleImage(
            $entry->getId(),
            $entry->getPreviewPicture(),
            $entry->getUrl()
        );
    }
}
