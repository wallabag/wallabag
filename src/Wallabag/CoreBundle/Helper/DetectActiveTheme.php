<?php

namespace Wallabag\CoreBundle\Helper;

use Liip\ThemeBundle\Helper\DeviceDetectionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\UserBundle\Entity\User;

/**
 * This class intend to detect the active theme for the logged in user.
 * It will retrieve the configured theme of the user.
 *
 * If no user where logged in, it will returne the default theme
 */
class DetectActiveTheme implements DeviceDetectionInterface
{
    protected $tokenStorage;
    protected $defaultTheme;

    /**
     * @param TokenStorageInterface $tokenStorage Needed to retrieve the current user
     * @param string                $defaultTheme Default theme when user isn't logged in
     */
    public function __construct(TokenStorageInterface $tokenStorage, $defaultTheme)
    {
        $this->tokenStorage = $tokenStorage;
        $this->defaultTheme = $defaultTheme;
    }

    public function setUserAgent($userAgent)
    {
    }

    /**
     * This should return the active theme for the logged in user.
     *
     * Default theme for:
     *     - anonymous user
     *     - user without a config (shouldn't happen ..)
     *
     * @return string
     */
    public function getType()
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return $this->defaultTheme;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return $this->defaultTheme;
        }

        $config = $user->getConfig();

        if (!$config) {
            return $this->defaultTheme;
        }

        return $config->getTheme();
    }
}
