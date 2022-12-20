<?php

namespace App\EventListener;

use App\Entity\Config;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * This listener will create the associated configuration when a user register.
 * This configuration will be created right after the registration (no matter if it needs an email validation).
 */
class CreateConfigListener implements EventSubscriberInterface
{
    private $em;
    private $itemsOnPage;
    private $feedLimit;
    private $language;
    private $readingSpeed;
    private $actionMarkAsRead;
    private $listMode;
    private $session;
    private $displayThumbnails;

    public function __construct(EntityManagerInterface $em, $itemsOnPage, $feedLimit, $language, $readingSpeed, $actionMarkAsRead, $listMode, $displayThumbnails, SessionInterface $session)
    {
        $this->em = $em;
        $this->itemsOnPage = $itemsOnPage;
        $this->feedLimit = $feedLimit;
        $this->language = $language;
        $this->readingSpeed = $readingSpeed;
        $this->actionMarkAsRead = $actionMarkAsRead;
        $this->listMode = $listMode;
        $this->session = $session;
        $this->displayThumbnails = $displayThumbnails;
    }

    public static function getSubscribedEvents()
    {
        return [
            // when a user register using the normal form
            FOSUserEvents::REGISTRATION_COMPLETED => 'createConfig',
            // when we manually create a user using the command line
            // OR when we create it from the config UI
            FOSUserEvents::USER_CREATED => 'createConfig',
        ];
    }

    public function createConfig(UserEvent $event)
    {
        $config = new Config($event->getUser());
        $config->setItemsPerPage($this->itemsOnPage);
        $config->setFeedLimit($this->feedLimit);
        $config->setLanguage($this->session->get('_locale', $this->language));
        $config->setReadingSpeed($this->readingSpeed);
        $config->setActionMarkAsRead($this->actionMarkAsRead);
        $config->setListMode($this->listMode);
        $config->setDisplayThumbnails($this->displayThumbnails);

        $this->em->persist($config);
        $this->em->flush();
    }
}
