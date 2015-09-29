<?php

namespace Wallabag\CoreBundle\EventListener;

use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Wallabag\CoreBundle\Entity\Config;

class RegistrationConfirmedListener implements EventSubscriberInterface
{
    private $em;
    private $container;

    public function __construct(Container $container, $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_CONFIRMED => 'authenticate',
        );
    }

    public function authenticate(FilterUserResponseEvent $event, $eventName = null, EventDispatcherInterface $eventDispatcher = null)
    {
        if (!$event->getUser()->isEnabled()) {
            return;
        }

        $config = new Config($event->getUser());
        $config->setTheme($this->container->getParameter('theme'));
        $config->setItemsPerPage($this->container->getParameter('items_on_page'));
        $config->setRssLimit($this->container->getParameter('rss_limit'));
        $config->setLanguage($this->container->getParameter('language'));
        $this->em->persist($config);
        $this->em->flush();
    }
}
