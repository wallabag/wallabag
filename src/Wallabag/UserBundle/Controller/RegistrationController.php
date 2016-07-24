<?php

namespace Wallabag\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as FOSRegistrationController;
use Symfony\Component\HttpFoundation\Request;

class RegistrationController extends FOSRegistrationController
{
    public function registerAction(Request $request)
    {
        if ($this->container->getParameter('wallabag_user.registration_enabled')) {
            return parent::registerAction($request);
        }

        return $this->redirectToRoute('fos_user_security_login', [], 301);
    }
}
