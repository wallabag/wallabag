<?php

namespace Wallabag\CoreBundle\Helper;

use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\CoreBundle\Entity\Config;

/**
 * Manage redirections to avoid redirecting to empty routes.
 */
class Redirect
{
    private $router;
    private $tokenStorage;

    public function __construct(Router $router, TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $url                    URL to redirect
     * @param string $fallback               Fallback URL if $url is null
     * @param bool   $ignoreActionMarkAsRead Ignore configured action when mark as read
     *
     * @return string
     */
    public function to($url, $fallback = '', $ignoreActionMarkAsRead = false)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $user || !\is_object($user)) {
            return $url;
        }

        if (!$ignoreActionMarkAsRead &&
              Config::REDIRECT_TO_HOMEPAGE === $user->getConfig()->getActionMarkAsRead()) {
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
