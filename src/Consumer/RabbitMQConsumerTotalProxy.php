<?php

namespace Wallabag\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\Consumer;

/**
 * A proxy class only used to count messages in a queue while lazy loading RabbitMQ services.
 * Only used in ImportController.
 */
class RabbitMQConsumerTotalProxy
{
    public function __construct(
        private readonly Consumer $pocketConsumer,
        private readonly Consumer $readabilityConsumer,
        private readonly Consumer $wallabagV1Consumer,
        private readonly Consumer $wallabagV2Consumer,
        private readonly Consumer $firefoxConsumer,
        private readonly Consumer $chromeConsumer,
        private readonly Consumer $instapaperConsumer,
        private readonly Consumer $pinboardConsumer,
        private readonly Consumer $deliciousConsumer,
        private readonly Consumer $elcuratorConsumer,
        private readonly Consumer $shaarliConsumer,
        private readonly Consumer $pocketHtmlConsumer,
        private readonly Consumer $pocketCsvConsumer,
        private readonly Consumer $omnivoreConsumer,
    ) {
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
            case 'pocket_csv':
                $consumer = $this->pocketCsvConsumer;
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

        return $message->getMessageCount() + 1;
    }
}
