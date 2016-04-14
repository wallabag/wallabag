<?php

namespace Wallabag\CoreBundle\Helper;

use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PreparePagerForEntries
{
    private $user;
    private $router;

    public function __construct(TokenStorage $token, Router $router)
    {
        $this->user = $token->getToken()->getUser();
        $this->router = $router;
    }

    /**
     * @param AdapterInterface $adapter
     * @param int              $page
     *
     * @return null|Pagerfanta
     */
    public function prepare(AdapterInterface $adapter, $page = 1)
    {
        $entries = new Pagerfanta($adapter);
        $entries->setMaxPerPage($this->user->getConfig()->getItemsPerPage());

        return $entries;
    }
}
