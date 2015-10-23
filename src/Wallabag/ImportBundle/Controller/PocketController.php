<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Wallabag\ImportBundle\Import\PocketImport;

class PocketController extends Controller
{
    /**
     * @Route("/import", name="import")
     */
    public function importAction()
    {
        return $this->render('WallabagImportBundle:Pocket:index.html.twig', array());
    }

    /**
     * @Route("/auth-pocket", name="authpocket")
     */
    public function authAction()
    {
        $pocket = new PocketImport($this->get('security.token_storage'), $this->get('session'), $this->getDoctrine()->getManager(), $this->container->getParameter('pocket_consumer_key'));
        $authUrl = $pocket->oAuthRequest($this->generateUrl('import', array(), true), $this->generateUrl('callbackpocket', array(), true));

        return $this->redirect($authUrl, 301);
    }

    /**
     * @Route("/callback-pocket", name="callbackpocket")
     */
    public function callbackAction()
    {
        $pocket = new PocketImport($this->get('security.token_storage'), $this->get('session'), $this->getDoctrine()->getManager(), $this->container->getParameter('pocket_consumer_key'));
        $accessToken = $pocket->oAuthAuthorize();
        $pocket->import($accessToken);

        return $this->redirect($this->generateUrl('homepage'));
    }
}
