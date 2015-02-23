<?php

namespace Wallabag\CoreBundle\Helper;

use Liip\ThemeBundle\Helper\DeviceDetectionInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Wallabag\CoreBundle\Entity\User;

class DetectActiveTheme implements DeviceDetectionInterface
{
    protected $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function setUserAgent($userAgent)
    {
    }

    /**
     * This should return the active theme for the logged in user.
     * No active theme for:
     *     - anonymous user
     *     - user without a config (shouldn't happen..)
     *
     * @return string
     */
    public function getType()
    {
        $user = $this->securityContext->getToken()->getUser();

        // anon user don't deserve a theme
        if (!$user instanceof User) {
            return false;
        }

        $config = $user->getConfig();

        if (!$config) {
            return false;
        }

        return $config->getTheme();
    }
}
