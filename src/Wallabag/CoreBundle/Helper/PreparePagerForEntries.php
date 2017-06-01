<?php

namespace Wallabag\CoreBundle\Helper;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @param AdapterInterface $adapter
     *
     * @return null|Pagerfanta
     */
    public function prepare(AdapterInterface $adapter)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $user || !is_object($user)) {
            return;
        }

        $entries = new Pagerfanta($adapter);
        $entries->setMaxPerPage($user->getConfig()->getItemsPerPage());

        return $entries;
    }
}
