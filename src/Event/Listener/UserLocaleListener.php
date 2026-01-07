<?php

namespace Wallabag\Event\Listener;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Wallabag\Entity\User;

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
    public function __construct(
        private readonly SessionInterface $session,
    ) {
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        \assert($user instanceof User);

        if (null !== $user->getConfig()->getLanguage() && null === $this->session->get('_locale')) {
            $this->session->set('_locale', $user->getConfig()->getLanguage());
        }
    }
}
