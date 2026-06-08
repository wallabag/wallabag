<?php

namespace Wallabag\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Wallabag\Entity\User;
use Wallabag\Helper\Redirect;

abstract class AbstractController extends BaseAbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'Wallabag\Helper\Redirect' => Redirect::class,
        ]);
    }

    /**
     * Redirect to the user's configured default homepage.
     */
    protected function redirectToDefaultHomepage(): RedirectResponse
    {
        return $this->redirect($this->container->get(Redirect::class)->to(null, true));
    }

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
