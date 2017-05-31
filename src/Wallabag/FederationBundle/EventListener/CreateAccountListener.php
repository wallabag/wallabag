<?php

namespace Wallabag\FederationBundle\EventListener;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\FederationBundle\Entity\Account;

/**
 * This listener will create the associated configuration when a user register.
 * This configuration will be created right after the registration (no matter if it needs an email validation).
 */
class CreateAccountListener implements EventSubscriberInterface
{
    private $em;
    private $domainName;

    public function __construct(EntityManager $em, $domainName)
    {
        $this->em = $em;
        $this->domainName = $domainName;
    }

    public static function getSubscribedEvents()
    {
        return [
            // when a user register using the normal form
            FOSUserEvents::REGISTRATION_COMPLETED => 'createAccount',
            // when we manually create a user using the command line
            // OR when we create it from the config UI
            FOSUserEvents::USER_CREATED => 'createAccount',
        ];
    }

    public function createAccount(UserEvent $event)
    {
        $user = $event->getUser();
        $account = new Account();
        $account->setUser($user)
            ->setUsername($user->getUsername())
            ->setServer($this->domainName);

        $this->em->persist($account);

        $user->setAccount($account);

        $this->em->persist($user);
        $this->em->flush();
    }
}
