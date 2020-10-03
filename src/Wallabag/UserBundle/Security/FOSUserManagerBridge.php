<?php


namespace Wallabag\UserBundle\Security;


use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface as FOSUserManagerInterface;
use Seb\AuthenticatorBundle\Security\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUserManagerBridge implements UserManagerInterface
{

    private $userManager;
    private $entityManager;
    private $eventDispatcher;

    public function __construct(FOSUserManagerInterface $userManager,
            EntityManagerInterface $entityManager,
            EventDispatcherInterface $eventDispatcher)
    {
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createUser($credentials)
    {
        $user = $this->userManager->createUser();
        $user->setEnabled(true);
        $user->setUsername($credentials['username']);
        $user->setEmail($credentials['username']);
        // Password cannot be null, any value is
        // ok as long as its not a real password hash
        $user->setPassword('IMAP_AUTH');

        return $user;
    }

    public function persistUser(UserInterface $user)
    {
        if ($this->entityManager->contains($user)) {
            // Noting to do, user is already persisted
            return;
        }

        $this->userManager->updateUser($user);
        $this->eventDispatcher->dispatch(FOSUserEvents::USER_CREATED, new UserEvent($user));
        $this->userManager->reloadUser($user);
    }
}
