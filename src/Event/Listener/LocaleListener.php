<?php

namespace Wallabag\Event\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @see http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
 */
class LocaleListener implements EventSubscriberInterface
{
    public function __construct(
        private $defaultLocale = 'en',
    ) {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 17]],
        ];
    }
}
