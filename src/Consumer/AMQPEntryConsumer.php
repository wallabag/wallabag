<?php

namespace Wallabag\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPEntryConsumer extends AbstractConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg): int|bool
    {
        return $this->handleMessage($msg->getBody());
    }
}
