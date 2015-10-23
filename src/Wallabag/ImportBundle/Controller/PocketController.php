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
        $pocket = $this->get('wallabag_import.import.pocket_import');
        $authUrl = $pocket->oAuthRequest($this->generateUrl('import', array(), true), $this->generateUrl('callbackpocket', array(), true));

        return $this->redirect($authUrl, 301);
    }

    /**
     * @Route("/callback-pocket", name="callbackpocket")
     */
    public function callbackAction()
    {
        $pocket = $this->get('wallabag_import.import.pocket_import');
        $accessToken = $pocket->oAuthAuthorize();
        $pocket->import($accessToken);

        return $this->redirect($this->generateUrl('homepage'));
    }
}
