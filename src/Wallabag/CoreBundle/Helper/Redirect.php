<?php

namespace Wallabag\CoreBundle\Helper;

use Symfony\Component\Routing\Router;

class Redirect
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $url      URL to redirect
     * @param string $fallback Fallback URL if $url is null
     *
     * @return string
     */
    public function to($url, $fallback = '')
    {
        $returnUrl = $url;

        if (null === $url) {
            if ('' !== $fallback) {
                $returnUrl = $fallback;
            } else {
                $returnUrl = $this->router->generate('homepage');
            }
        }

        return $returnUrl;
    }
}
