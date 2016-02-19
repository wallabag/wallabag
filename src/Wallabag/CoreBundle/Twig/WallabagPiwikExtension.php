<?php

namespace Wallabag\CoreBundle\Twig;

use Craue\ConfigBundle\Util\Config;
use PiwikTwigExtension\PiwikTwigExtension;

class WallabagPiwikExtension extends PiwikTwigExtension
{
    public function __construct(Config $craueConfig)
    {
        parent::__construct($craueConfig->get('piwik_host'), $craueConfig->get('piwik_site_id'), $craueConfig->get('piwik_enabled'));
    }
}
