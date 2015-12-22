<?php

namespace Wallabag\CoreBundle\Helper\ContentProxy\Authenticator\WebsiteAuthenticator;

interface UrlBased
{
    public function setUri($uri);

    public function getUri();
}
