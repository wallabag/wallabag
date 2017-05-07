<?php

namespace Wallabag\UserBundle\EventListener;

use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wallabag\CoreBundle\Entity\Config;

/**
 * This listener will create the associated configuration when a user register.
 * This configuration will be created right after the registration (no matter if it needs an email validation).
 */
class CreateConfigListener implements EventSubscriberInterface
{
    private $registry;
    private $theme;
    private $itemsOnPage;
    private $rssLimit;
    private $language;
    private $readingSpeed;
    private $actionMarkAsRead;
    private $listMode;

    public function __construct(ManagerRegistry $registry = null, $theme, $itemsOnPage, $rssLimit, $language, $readingSpeed, $actionMarkAsRead, $listMode)
    {
        $this->registry = $registry;
        $this->theme = $theme;
        $this->itemsOnPage = $itemsOnPage;
        $this->rssLimit = $rssLimit;
        $this->language = $language;
        $this->readingSpeed = $readingSpeed;
        $this->actionMarkAsRead = $actionMarkAsRead;
        $this->listMode = $listMode;
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
        // If there is no manager, this means that only Doctrine DBAL is configured
        // In this case we can do nothing and just return
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }

        $em = $this->registry->getManager();

        $config = new Config($event->getUser());
        $config->setTheme($this->theme);
        $config->setItemsPerPage($this->itemsOnPage);
        $config->setRssLimit($this->rssLimit);
        $config->setLanguage($this->language);
        $config->setReadingSpeed($this->readingSpeed);
        $config->setActionMarkAsRead($this->actionMarkAsRead);
        $config->setListMode($this->listMode);

        $em->persist($config);
        $em->flush();
    }
}
