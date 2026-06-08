<?php

namespace Wallabag\Helper;

use GuzzleHttp\Psr7\Uri;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\Entity\Config;
use Wallabag\Entity\User;
use Wallabag\Enum\HomepageTarget;

/**
 * Manage redirections to avoid redirecting to empty routes.
 */
class Redirect
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @param string|null $url                    URL to redirect
     * @param bool        $ignoreActionMarkAsRead Ignore configured action when mark as read
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
            return $this->generateHomepageUrl($user);
        }

        if (null === $url) {
            return $this->generateHomepageUrl($user);
        }

        if (!Uri::isAbsolutePathReference(new Uri($url))) {
            return $this->generateHomepageUrl($user);
        }

        return $url;
    }

    private function generateHomepageUrl(User $user): string
    {
        return match ($user->getConfig()->getDefaultHomepage()) {
            HomepageTarget::Unread => $this->router->generate('unread'),
            HomepageTarget::All => $this->router->generate('all'),
            HomepageTarget::Archive => $this->router->generate('archive'),
            HomepageTarget::Starred => $this->router->generate('starred'),
            HomepageTarget::Tags => $this->router->generate('tag'),
        };
    }
}
