<?php

namespace Wallabag\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Translation\TranslatorInterface;
use Craue\ConfigBundle\Util\Config;
use FOS\UserBundle\Model\UserManagerInterface;
use Liip\ThemeBundle\ActiveTheme;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractWallabagController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'craue_config' => Config::class,
                'event_dispatcher' => EventDispatcherInterface::class, // Should move to DI
                'fos_user.user_manager' => UserManagerInterface::class,
                'translator' => TranslatorInterface::class,
                'validator' => ValidatorInterface::class,
                'liip_theme.active_theme' => ActiveTheme::class,
            ]
        );
    }
}
