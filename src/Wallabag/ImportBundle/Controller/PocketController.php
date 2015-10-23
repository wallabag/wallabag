<?php

namespace Wallabag\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PocketController extends Controller
{
    /**
     * @Route("/import/pocket", name="pocket")
     */
    public function indexAction()
    {
        return $this->render('WallabagImportBundle:Pocket:index.html.twig', array());
    }

    /**
     * @Route("/import/pocket/auth", name="authpocket")
     */
    public function authAction()
    {
        $pocket = $this->get('wallabag_import.import.pocket_import');
        $authUrl = $pocket->oAuthRequest($this->generateUrl('import', array(), true), $this->generateUrl('callbackpocket', array(), true));

        return $this->redirect($authUrl, 301);
    }

    /**
     * @Route("/import/pocket/callback", name="callbackpocket")
     */
    public function callbackAction()
    {
        $pocket = $this->get('wallabag_import.import.pocket_import');
        $accessToken = $pocket->oAuthAuthorize();
        $pocket->import($accessToken);

        return $this->redirect($this->generateUrl('homepage'));
    }
}
