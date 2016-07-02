<?php

namespace Wallabag\UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as FOSSecurityController;

class SecurityController extends FOSSecurityController
{
    protected function renderLogin(array $data)
    {
        return $this->render('FOSUserBundle:Security:login.html.twig',
            array_merge(
                $data,
                array('registration_enabled' => $this->container->getParameter('wallabag_user.registration_enabled'))
            )
        );
    }
}
