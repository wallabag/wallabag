<?php

namespace Wallabag\CoreBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wallabag\CoreBundle\Entity\Config;

class RegistrationConfirmedListener implements EventSubscriberInterface
{
    private $em;
    private $theme;
    private $itemsOnPage;
    private $rssLimit;
    private $language;

    public function __construct(EntityManager $em, $theme, $itemsOnPage, $rssLimit, $language)
    {
        $this->em = $em;
        $this->theme = $theme;
        $this->itemsOnPage = $itemsOnPage;
        $this->rssLimit = $rssLimit;
        $this->language = $language;
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
        $config->setTheme($this->theme);
        $config->setItemsPerPage($this->itemsOnPage);
        $config->setRssLimit($this->rssLimit);
        $config->setLanguage($this->language);
        $this->em->persist($config);
        $this->em->flush();
    }
}
