<?php

namespace Wallabag\Helper;

use GuzzleHttp\Psr7\Uri;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\Entity\Config;
use Wallabag\Entity\User;

/**
 * Manage redirections to avoid redirecting to empty routes.
 */
class Redirect
{
    private $router;
    private $tokenStorage;

    public function __construct(UrlGeneratorInterface $router, TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $url                    URL to redirect
     * @param bool   $ignoreActionMarkAsRead Ignore configured action when mark as read
     *
     * @return string
     */
    public function to($url, $ignoreActionMarkAsRead = false)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!$user instanceof User) {
            if (null === $url) {
                return $this->router->generate('homepage');
            }

            if (!Uri::isAbsolutePathReference(new Uri($url))) {
                return $this->router->generate('homepage');
            }

            return $url;
        }

        if (!$ignoreActionMarkAsRead
              && Config::REDIRECT_TO_HOMEPAGE === $user->getConfig()->getActionMarkAsRead()) {
            return $this->router->generate('homepage');
        }

        if (null === $url) {
            return $this->router->generate('homepage');
        }

        if (!Uri::isAbsolutePathReference(new Uri($url))) {
            return $this->router->generate('homepage');
        }

        return $url;
    }
}
