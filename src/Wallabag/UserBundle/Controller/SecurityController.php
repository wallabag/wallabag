<?php

namespace Wallabag\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as FOSSecurityController;

/**
 * Extends login form in order to pass the registration_enabled parameter.
 */
class SecurityController extends FOSSecurityController
{
    protected function renderLogin(array $data)
    {
        return $this->render('FOSUserBundle:Security:login.html.twig',
            array_merge(
                $data,
                ['registration_enabled' => $this->container->getParameter('wallabag_user.registration_enabled')]
            )
        );
    }
}
