<?php

namespace Wallabag\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Wallabag\Entity\Config;

/**
 * This event is fired as soon as user configuration is updated.
 */
class ConfigUpdatedEvent extends Event
{
    public const NAME = 'config.updated';

    public function __construct(
        protected Config $config,
    ) {
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
