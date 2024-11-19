<?php

namespace Wallabag\Event\Listener;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationListener implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $registrationEnabled;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct($registrationEnabled, UrlGeneratorInterface $urlGenerator)
    {
        $this->registrationEnabled = $registrationEnabled;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialize',
        ];
    }

    public function onRegistrationInitialize(GetResponseUserEvent $event)
    {
        if ($this->registrationEnabled) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('fos_user_security_login'), 301));
    }
}
