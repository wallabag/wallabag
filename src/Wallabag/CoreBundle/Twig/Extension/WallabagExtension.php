<?php

namespace Wallabag\CoreBundle\Twig\Extension;

class WallabagExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('readingTime', array($this, 'getReadingTime')),
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

    /**
     * For a given text, we calculate reading time for an article.
     *
     * @param $text
     *
     * @return float
     */
    public static function getReadingTime($text)
    {
        return floor(str_word_count(strip_tags($text)) / 200);
    }

    public function getName()
    {
        return 'wallabag_extension';
    }
}
