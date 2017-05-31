<?php

namespace Wallabag\FederationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\FederationBundle\Entity\Account;
use Wallabag\UserBundle\Entity\User;

class OutboxController extends Controller
{
    /**
     * @Route("/profile/outbox", name="user-outbox")
     *
     * @param Request $request
     * @param Account $user
     */
    public function userOutboxAction(Request $request, Account $user)
    {

    }
}
