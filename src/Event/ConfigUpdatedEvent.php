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

    protected $config;

    public function __construct(Config $entry)
    {
        $this->config = $entry;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
