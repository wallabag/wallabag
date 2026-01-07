<?php

namespace Wallabag\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Wallabag\Entity\User;

abstract class AbstractController extends BaseAbstractController
{
    /**
     * @return User|null
     */
    protected function getUser()
    {
        $user = parent::getUser();
        \assert(null === $user || $user instanceof User);

        return $user;
    }
}
