<?php

namespace Wallabag\Event\Listener;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Wallabag\Entity\Config;
use Wallabag\Entity\User;

/**
 * This listener will create the associated configuration when a user register.
 * This configuration will be created right after the registration (no matter if it needs an email validation).
 */
class CreateConfigListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private $itemsOnPage,
        private $feedLimit,
        private $language,
        private $readingSpeed,
        private $actionMarkAsRead,
        private $listMode,
        private $displayThumbnails,
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
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
        $language = $this->language;

        if ($this->requestStack->getMainRequest()) {
            $session = $this->requestStack->getMainRequest()->getSession();
            $language = $session->get('_locale', $this->language);
        }

        $user = $event->getUser();
        \assert($user instanceof User);

        $config = new Config($user);
        $config->setItemsPerPage($this->itemsOnPage);
        $config->setFeedLimit($this->feedLimit);
        $config->setLanguage($language);
        $config->setReadingSpeed($this->readingSpeed);
        $config->setActionMarkAsRead($this->actionMarkAsRead);
        $config->setListMode($this->listMode);
        $config->setDisplayThumbnails($this->displayThumbnails);

        $this->em->persist($config);
        $this->em->flush();
    }
}
