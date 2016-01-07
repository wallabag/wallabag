<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Wallabag\CoreBundle\Authenticator;

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
