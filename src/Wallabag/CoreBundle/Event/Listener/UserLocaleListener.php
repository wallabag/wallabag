<?php

namespace Wallabag\CoreBundle\Event\Listener;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Stores the locale of the user in the session after the login.
 * If no locale are defined (if user doesn't change it from the login screen), override it with the user's config one.
 *
 * This can be used by the LocaleListener afterwards.
 *
 * @see http://symfony.com/doc/master/cookbook/session/locale_sticky_session.html
 */
class UserLocaleListener
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (null !== $user->getConfig()->getLanguage() && null === $this->session->get('_locale')) {
            $this->session->set('_locale', $user->getConfig()->getLanguage());
        }
    }
}
