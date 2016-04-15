<?php

namespace Wallabag\CoreBundle\Helper;

use Symfony\Component\Routing\Router;

/**
 * Manage redirections to avoid redirecting to empty routes.
 */
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
        if (null !== $url) {
            return $url;
        }

        if ('' === $fallback) {
            return $this->router->generate('homepage');
        }

        return $fallback;
    }
}
