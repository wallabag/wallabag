<?php

namespace Wallabag\CoreBundle\Helper\ContentProxy;

use GuzzleHttp\Cookie\FileCookieJar;

class FileCookieJarFactory
{
    private $cookieFilePathName;

    public function __construct($cookieFilePathName)
    {
        $this->cookieFilePathName = $cookieFilePathName;
    }

    /**
     * @return \GuzzleHttp\Cookie\CookieJar
     */
    public function createCookieJar()
    {
        return new FileCookieJar($this->cookieFilePathName);
    }
}
