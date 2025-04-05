<?php

namespace Wallabag\Helper;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\NullAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Wallabag\Entity\User;

class PreparePagerForEntries
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @param User $user If user isn't logged in, we can force it (like for feed)
     *
     * @return Pagerfanta
     */
    public function prepare(AdapterInterface $adapter, ?User $user = null)
    {
        if (null === $user) {
            $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        }

        if (!$user instanceof User) {
            return new Pagerfanta(new NullAdapter());
        }

        $entries = new Pagerfanta($adapter);
        $entries->setMaxPerPage($user->getConfig()->getItemsPerPage());

        return $entries;
    }
}
