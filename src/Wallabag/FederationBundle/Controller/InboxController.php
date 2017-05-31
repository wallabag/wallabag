<?php

namespace Wallabag\FederationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wallabag\FederationBundle\Entity\Account;
use Wallabag\FederationBundle\Entity\Instance;
use Wallabag\FederationBundle\Federation\CloudId;

class InboxController extends Controller
{
    /**
     * @Route("/profile/inbox", name="user-inbox")
     *
     * @param Request $request
     * @return Response
     */
    public function userInboxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $response = new Response();

        if ($activity = json_decode($request->getContent())) {
            if ($activity->type === 'Follow' && isset($activity->actor->id)) {
                $cloudId = new CloudId($activity->actor->id);
                $account = new Account();
                $account->setServer($cloudId->getRemote())
                    ->setUsername($cloudId->getUser());
                $em->persist($account);
                $em->flush();

                $response->setStatusCode(201);
            } else {
                $response->setStatusCode(400);
            }
        }
        return $response;
    }
}
