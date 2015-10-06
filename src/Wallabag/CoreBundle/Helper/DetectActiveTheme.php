<?php

namespace Wallabag\CoreBundle\Helper;

use Liip\ThemeBundle\Helper\DeviceDetectionInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Wallabag\UserBundle\Entity\User;

/**
 * This class intend to detect the active theme for the logged in user.
 * It will retrieve the configured theme of the user.
 *
 * If no user where logged in, it will returne the default theme
 */
class DetectActiveTheme implements DeviceDetectionInterface
{
    protected $securityContext;
    protected $defaultTheme;

    /**
     * @param SecurityContextInterface $securityContext Needed to retrieve the current user
     * @param string                   $defaultTheme    Default theme when user isn't logged in
     */
    public function __construct(SecurityContextInterface $securityContext, $defaultTheme)
    {
        $this->securityContext = $securityContext;
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
        $user = $this->securityContext->getToken()->getUser();

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
