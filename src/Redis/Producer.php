<?php

namespace Wallabag\Redis;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Simpleue\Queue\RedisQueue;

/**
 * This is a proxy class for "Simpleue\Queue\RedisQueue".
 * It allow us to use the same way to publish a message between RabbitMQ & Redis: publish().
 *
 * It implements the ProducerInterface of RabbitMQ (yes it's ugly) so we can have the same
 * kind of class which implements the same interface.
 * So we can inject either a RabbitMQ producer or a Redis producer with the same signature
 */
class Producer implements ProducerInterface
{
    public function __construct(
        private readonly RedisQueue $queue,
    ) {
    }

    /**
     * Publish a message in the Redis queue.
     *
     * @param string $msgBody
     * @param string $routingKey           NOT USED
     * @param array  $additionalProperties NOT USED
     */
    public function publish($msgBody, $routingKey = '', $additionalProperties = [])
    {
        $this->queue->sendJob($msgBody);
    }
}
