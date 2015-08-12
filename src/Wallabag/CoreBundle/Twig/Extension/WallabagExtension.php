<?php

namespace Wallabag\CoreBundle\Twig\Extension;

class WallabagExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('domainName', array($this, 'getDomainName')),
        );
    }

    /**
     * Returns the domain name for a URL.
     *
     * @param $url
     *
     * @return string
     */
    public static function getDomainName($url)
    {
        return parse_url($url, PHP_URL_HOST);
    }

    public function getName()
    {
        return 'wallabag_extension';
    }
}
