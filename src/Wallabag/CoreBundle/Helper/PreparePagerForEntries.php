<?php

namespace Wallabag\CoreBundle\Helper;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\UserBundle\Entity\User;

class PreparePagerForEntries
{
    private $router;
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, Router $router)
    {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
    }

    /**
     * @param User $user If user isn't logged in, we can force it (like for feed)
     *
     * @return Pagerfanta|null
     */
    public function prepare(AdapterInterface $adapter, User $user = null)
    {
        if (null === $user) {
            $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        }

        if (null === $user || !\is_object($user)) {
            return;
        }

        $entries = new Pagerfanta($adapter);
        $entries->setMaxPerPage($user->getConfig()->getItemsPerPage());

        return $entries;
    }
}
