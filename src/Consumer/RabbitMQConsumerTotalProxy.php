<?php

namespace Wallabag\Consumer;

use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * A proxy class only used to count messages in a queue while lazy loading RabbitMQ services.
 * Only used in ImportController.
 */
class RabbitMQConsumerTotalProxy
{
    public function __construct(
        private readonly ServiceLocator $consumers,
    ) {
    }

    /**
     * Count message in RabbitMQ queue.
     *
     * It get one message without acking it (so it'll stay in the queue)
     * which will include the total of *other* messages in the queue.
     * Adding one to that messages will result in the full total message.
     *
     * @param string $importService The alias of the import service related
     */
    public function getTotalMessage(string $importService): int
    {
        if (!$this->consumers->has($importService)) {
            return 0;
        }

        $consumer = $this->consumers->get($importService);

        $message = $consumer->getChannel()->basic_get('wallabag.import.' . $importService);

        if (null === $message) {
            return 0;
        }

        return $message->getMessageCount() + 1;
    }
}
