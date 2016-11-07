<?php

namespace Wallabag\CoreBundle\Helper;

use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Wallabag\CoreBundle\Entity\Config;

/**
 * Manage redirections to avoid redirecting to empty routes.
 */
class Redirect
{
    private $router;
    private $actionMarkAsRead;

    public function __construct(Router $router, TokenStorage $token)
    {
        $this->router = $router;
        $this->actionMarkAsRead = $token->getToken()->getUser()->getConfig()->getActionMarkAsRead();
    }

    /**
     * @param string $url      URL to redirect
     * @param string $fallback Fallback URL if $url is null
     *
     * @return string
     */
    public function to($url, $fallback = '')
    {
        if (Config::REDIRECT_TO_HOMEPAGE === $this->actionMarkAsRead) {
            return $this->router->generate('homepage');
        }

        if (null !== $url) {
            return $url;
        }

        if ('' === $fallback) {
            return $this->router->generate('homepage');
        }

        return $fallback;
    }
}
