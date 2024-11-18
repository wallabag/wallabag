<?php

namespace Wallabag\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\Consumer;

/**
 * A proxy class only used to count messages in a queue while lazy loading RabbitMQ services.
 * Only used in ImportController.
 */
class RabbitMQConsumerTotalProxy
{
    private Consumer $pocketConsumer;
    private Consumer $readabilityConsumer;
    private Consumer $wallabagV1Consumer;
    private Consumer $wallabagV2Consumer;
    private Consumer $firefoxConsumer;
    private Consumer $chromeConsumer;
    private Consumer $instapaperConsumer;
    private Consumer $pinboardConsumer;
    private Consumer $deliciousConsumer;
    private Consumer $elcuratorConsumer;
    private Consumer $shaarliConsumer;
    private Consumer $pocketHtmlConsumer;
    private Consumer $omnivoreConsumer;

    public function __construct(
        Consumer $pocketConsumer,
        Consumer $readabilityConsumer,
        Consumer $wallabagV1Consumer,
        Consumer $wallabagV2Consumer,
        Consumer $firefoxConsumer,
        Consumer $chromeConsumer,
        Consumer $instapaperConsumer,
        Consumer $pinboardConsumer,
        Consumer $deliciousConsumer,
        Consumer $elcuratorConsumer,
        Consumer $shaarliConsumer,
        Consumer $pocketHtmlConsumer,
        Consumer $omnivoreConsumer
    ) {
        $this->pocketConsumer = $pocketConsumer;
        $this->readabilityConsumer = $readabilityConsumer;
        $this->wallabagV1Consumer = $wallabagV1Consumer;
        $this->wallabagV2Consumer = $wallabagV2Consumer;
        $this->firefoxConsumer = $firefoxConsumer;
        $this->chromeConsumer = $chromeConsumer;
        $this->instapaperConsumer = $instapaperConsumer;
        $this->pinboardConsumer = $pinboardConsumer;
        $this->deliciousConsumer = $deliciousConsumer;
        $this->elcuratorConsumer = $elcuratorConsumer;
        $this->shaarliConsumer = $shaarliConsumer;
        $this->pocketHtmlConsumer = $pocketHtmlConsumer;
        $this->omnivoreConsumer = $omnivoreConsumer;
    }

    /**
     * Count message in RabbitMQ queue.
     *
     * It get one message without acking it (so it'll stay in the queue)
     * which will include the total of *other* messages in the queue.
     * Adding one to that messages will result in the full total message.
     *
     * @param string $importService The import service related: pocket, readability, wallabag_v1 or wallabag_v2
     */
    public function getTotalMessage(string $importService): int
    {
        switch ($importService) {
            case 'pocket':
                $consumer = $this->pocketConsumer;
                break;
            case 'readability':
                $consumer = $this->readabilityConsumer;
                break;
            case 'wallabag_v1':
                $consumer = $this->wallabagV1Consumer;
                break;
            case 'wallabag_v2':
                $consumer = $this->wallabagV2Consumer;
                break;
            case 'firefox':
                $consumer = $this->firefoxConsumer;
                break;
            case 'chrome':
                $consumer = $this->chromeConsumer;
                break;
            case 'instapaper':
                $consumer = $this->instapaperConsumer;
                break;
            case 'pinboard':
                $consumer = $this->pinboardConsumer;
                break;
            case 'delicious':
                $consumer = $this->deliciousConsumer;
                break;
            case 'elcurator':
                $consumer = $this->elcuratorConsumer;
                break;
            case 'shaarli':
                $consumer = $this->shaarliConsumer;
                break;
            case 'pocket_html':
                $consumer = $this->pocketHtmlConsumer;
                break;
            case 'omnivore':
                $consumer = $this->omnivoreConsumer;
                break;
            default:
                return 0;
        }

        $message = $consumer->getChannel()->basic_get('wallabag.import.' . $importService);

        if (null === $message) {
            return 0;
        }

        return $message->delivery_info['message_count'] + 1;
    }
}
